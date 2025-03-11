<?php

namespace App\Integrations\Traccar;

use App\Integrations\Base\TrackingIntegration;
use App\Models\Customer;
use App\Models\Vehicle;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class TraccarDatabaseIntegration extends TrackingIntegration
{
    /**
     * Integration identifier
     */
    protected string $key = 'traccar_database';

    /**
     * Integration display name
     */
    protected string $name = 'Traccar Database';

    /**
     * Integration description
     */
    protected string $description = 'Integração direta com o banco de dados do Traccar';

    /**
     * Whether the integration is enabled
     */
    protected bool $enabled = false;

    /**
     * Integration configuration
     */
    protected array $config = [
        'connection' => 'traccar',
        'host' => '',
        'port' => '3306',
        'database' => 'traccar',
        'username' => '',
        'password' => '',
    ];

    public function __construct()
    {
        // Load configuration from settings if available
        $setting = \App\Models\Setting::where('key', 'traccar_database_integration')->first();
        if ($setting && ! empty($setting->value)) {
            $config = json_decode($setting->value, true);
            if (is_array($config)) {
                $this->config = array_merge($this->config, $config);
                $this->enabled = $config['enabled'] ?? false;
            }
        }

        // Configure the database connection
        $this->setupDatabaseConnection();
    }

    /**
     * Set up the database connection for Traccar
     */
    private function setupDatabaseConnection(): void
    {
        // Set up the database connection configuration
        config(['database.connections.traccar' => [
            'driver' => 'mysql',
            'url' => null,
            'host' => $this->config['host'],
            'port' => $this->config['port'],
            'database' => $this->config['database'],
            'username' => $this->config['username'],
            'password' => $this->config['password'],
            'unix_socket' => '',
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
            'prefix_indexes' => true,
            'strict' => true,
            'engine' => null,
            'options' => extension_loaded('pdo_mysql') ? array_filter([
                \PDO::MYSQL_ATTR_SSL_CA => null,
            ]) : [],
        ]]);
    }

    /**
     * Validate if the integration is properly configured
     */
    public function validate(): bool
    {
        if (! $this->enabled) {
            return false;
        }

        if (empty($this->config['host']) || empty($this->config['database']) ||
            empty($this->config['username']) || empty($this->config['password'])) {
            return false;
        }

        try {
            // Test the connection
            DB::connection('traccar')->select('SELECT 1');

            return true;
        } catch (Exception $e) {
            Log::error('Traccar Database validation error: '.$e->getMessage());

            return false;
        }
    }

    /**
     * Synchronize a customer with Traccar
     *
     * @return int|null Traccar user ID
     */
    public function syncCustomer(Customer $customer): ?int
    {
        if (! $this->enabled) {
            return null;
        }

        try {
            if ($customer->traccar_id) {
                // Update existing user in Traccar
                DB::connection('traccar')->table('tc_users')
                    ->where('id', $customer->traccar_id)
                    ->update([
                        'name' => $customer->name,
                        'email' => $customer->email,
                        'phone' => $customer->phone,
                    ]);

                return $customer->traccar_id;
            } else {
                // Create new user in Traccar
                $email = $customer->email ?: ($customer->id.'@placeholder.com');

                // Check if email already exists
                $existingUser = DB::connection('traccar')->table('tc_users')
                    ->where('email', $email)
                    ->first();

                if ($existingUser) {
                    $customer->update(['traccar_id' => $existingUser->id]);

                    return $existingUser->id;
                }

                $traccarUserId = DB::connection('traccar')->table('tc_users')->insertGetId([
                    'name' => $customer->name,
                    'email' => $email,
                    'phone' => $customer->phone,
                    'password' => hash('sha512', Str::random(10)), // Generate a random password hash
                    'salt' => '',
                    'readonly' => false,
                    'administrator' => false,
                    'map' => '',
                    'latitude' => 0,
                    'longitude' => 0,
                    'zoom' => 0,
                    'twelvehourformat' => false,
                    'limitcommands' => false,
                    'disabled' => false,
                    'devicelimit' => -1,
                    'userlimit' => 0,
                    'devicereadonly' => false,
                    'expiration' => null,
                ]);

                if ($traccarUserId) {
                    $customer->update(['traccar_id' => $traccarUserId]);

                    return $traccarUserId;
                }

                return null;
            }
        } catch (Exception $e) {
            Log::error('Traccar Database error: '.$e->getMessage());

            return null;
        }
    }

    /**
     * Synchronize a vehicle with Traccar
     *
     * @return int|null Traccar device ID
     */
    public function syncVehicle(Vehicle $vehicle): ?int
    {
        if (! $this->enabled) {
            return null;
        }

        try {
            // Ensure customer is synced first
            if (! $vehicle->customer->traccar_id) {
                $this->syncCustomer($vehicle->customer);
            }

            if ($vehicle->traccar_id) {
                // Update existing device in Traccar
                DB::connection('traccar')->table('tc_devices')
                    ->where('id', $vehicle->traccar_id)
                    ->update([
                        'name' => $vehicle->license_plate ?: $vehicle->model,
                        'uniqueid' => $vehicle->device_id ?: 'temp_'.$vehicle->id,
                        'phone' => $vehicle->phone_number,
                        'model' => $vehicle->model,
                        'contact' => $vehicle->customer->phone,
                        'disabled' => ! $vehicle->active,
                    ]);

                return $vehicle->traccar_id;
            } else {
                // Create new device in Traccar
                $deviceId = $vehicle->device_id ?: 'temp_'.$vehicle->id;

                // Check if device already exists
                $existingDevice = DB::connection('traccar')->table('tc_devices')
                    ->where('uniqueid', $deviceId)
                    ->first();

                if ($existingDevice) {
                    $vehicle->update(['traccar_id' => $existingDevice->id]);

                    // Ensure the device is linked to the customer
                    $this->linkDeviceToUser($existingDevice->id, $vehicle->customer->traccar_id);

                    return $existingDevice->id;
                }

                $traccarDeviceId = DB::connection('traccar')->table('tc_devices')->insertGetId([
                    'name' => $vehicle->license_plate ?: $vehicle->model,
                    'uniqueid' => $deviceId,
                    'phone' => $vehicle->phone_number,
                    'model' => $vehicle->model,
                    'contact' => $vehicle->customer->phone,
                    'category' => 'car',
                    'disabled' => ! $vehicle->active,
                    'lastupdate' => now(),
                ]);

                if ($traccarDeviceId) {
                    $vehicle->update(['traccar_id' => $traccarDeviceId]);

                    // Link device to user in Traccar
                    $this->linkDeviceToUser($traccarDeviceId, $vehicle->customer->traccar_id);

                    return $traccarDeviceId;
                }

                return null;
            }
        } catch (Exception $e) {
            Log::error('Traccar Database error: '.$e->getMessage());

            return null;
        }
    }

    /**
     * Link a device to a user in Traccar
     *
     * @param  int  $deviceId  Traccar device ID
     * @param  int  $userId  Traccar user ID
     * @return bool Success status
     */
    public function linkDeviceToUser(int $deviceId, int $userId): bool
    {
        try {
            // Check if the permission already exists
            $existingPermission = DB::connection('traccar')->table('tc_user_device')
                ->where('userid', $userId)
                ->where('deviceid', $deviceId)
                ->first();

            if (! $existingPermission) {
                $result = DB::connection('traccar')->table('tc_user_device')->insert([
                    'userid' => $userId,
                    'deviceid' => $deviceId,
                ]);

                if ($result) {
                    Log::info("Linked device ID {$deviceId} to user ID {$userId} in Traccar DB");

                    return true;
                }

                return false;
            }

            // Already linked
            return true;
        } catch (Exception $e) {
            Log::error('Error linking device to user in Traccar: '.$e->getMessage());

            return false;
        }
    }

    /**
     * Fetch all devices from Traccar and sync with our database
     */
    public function syncAllFromTracking(): void
    {
        if (! $this->enabled) {
            return;
        }

        try {
            // Sync users
            $traccarUsers = DB::connection('traccar')->table('tc_users')->get();

            foreach ($traccarUsers as $traccarUser) {
                $customer = Customer::where('traccar_id', $traccarUser->id)->first();
                if (! $customer) {
                    // Create new customer
                    $customer = Customer::create([
                        'name' => $traccarUser->name,
                        'email' => $traccarUser->email,
                        'phone' => $traccarUser->phone,
                        'traccar_id' => $traccarUser->id,
                    ]);
                }
            }

            // Sync devices
            $traccarDevices = DB::connection('traccar')->table('tc_devices')->get();

            foreach ($traccarDevices as $traccarDevice) {
                $vehicle = Vehicle::where('traccar_id', $traccarDevice->id)->first();
                if (! $vehicle) {
                    // Find customer for this device
                    $permission = DB::connection('traccar')->table('tc_user_device')
                        ->where('deviceid', $traccarDevice->id)
                        ->first();

                    if ($permission) {
                        $customer = Customer::where('traccar_id', $permission->userid)->first();
                        if ($customer) {
                            // Create new vehicle
                            $vehicle = Vehicle::create([
                                'customer_id' => $customer->id,
                                'model' => $traccarDevice->model ?? 'Unknown',
                                'device_id' => $traccarDevice->uniqueid,
                                'phone_number' => $traccarDevice->phone,
                                'active' => ! $traccarDevice->disabled,
                                'traccar_id' => $traccarDevice->id,
                            ]);
                        }
                    }
                }
            }
        } catch (Exception $e) {
            Log::error('Traccar Database error during full sync: '.$e->getMessage());
        }
    }

    /**
     * Get data for a specific vehicle from the tracking system
     */
    public function getVehicleData(Vehicle $vehicle): ?array
    {
        if (! $this->enabled || ! $vehicle->traccar_id) {
            return null;
        }

        try {
            $device = DB::connection('traccar')->table('tc_devices')
                ->where('id', $vehicle->traccar_id)
                ->first();

            if ($device) {
                return (array) $device;
            }

            return null;
        } catch (Exception $e) {
            Log::error('Traccar Database error getting vehicle data: '.$e->getMessage());

            return null;
        }
    }

    /**
     * Get positions for a specific vehicle from the tracking system
     *
     * @param  string  $from  ISO datetime
     * @param  string  $to  ISO datetime
     */
    public function getVehiclePositions(Vehicle $vehicle, string $from, string $to): ?array
    {
        if (! $this->enabled || ! $vehicle->traccar_id) {
            return null;
        }

        try {
            $positions = DB::connection('traccar')->table('tc_positions')
                ->where('deviceid', $vehicle->traccar_id)
                ->where('devicetime', '>=', $from)
                ->where('devicetime', '<=', $to)
                ->orderBy('devicetime', 'asc')
                ->get();

            if ($positions) {
                return $positions->map(function ($position) {
                    return (array) $position;
                })->toArray();
            }

            return null;
        } catch (Exception $e) {
            Log::error('Traccar Database error getting vehicle positions: '.$e->getMessage());

            return null;
        }
    }

    /**
     * Get the integration settings view
     */
    public function getSettingsView(): string
    {
        return 'integrations.traccar.database-settings';
    }
}
