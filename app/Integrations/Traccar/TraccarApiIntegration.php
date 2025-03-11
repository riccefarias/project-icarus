<?php

namespace App\Integrations\Traccar;

use App\Integrations\Base\TrackingIntegration;
use App\Models\Customer;
use App\Models\Equipment;
use App\Models\Vehicle;
use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TraccarApiIntegration extends TrackingIntegration
{
    /**
     * Integration identifier
     */
    protected string $key = 'traccar_api';

    /**
     * Integration display name
     */
    protected string $name = 'Traccar API';

    /**
     * Integration description
     */
    protected string $description = 'Integração com o Traccar via API REST';

    /**
     * Whether the integration is enabled
     */
    protected bool $enabled = false;

    /**
     * Integration configuration
     */
    protected array $config = [
        'url' => '',
        'username' => '',
        'password' => '',
    ];

    public function __construct()
    {
        // Load configuration from database settings
        $setting = \App\Models\Setting::where('key', 'traccar_api_integration')->first();
        
        if ($setting && !empty($setting->value)) {
            $config = json_decode($setting->value, true);
            $this->enabled = $config['enabled'] ?? false;
            $this->config = [
                'url' => $config['url'] ?? '',
                'username' => $config['username'] ?? '',
                'password' => $config['password'] ?? '',
            ];
        } 
        // Fallback to environment if database settings don't exist
        else if (config('services.traccar.enabled')) {
            $this->enabled = config('services.traccar.enabled');
            $this->config = [
                'url' => config('services.traccar.url'),
                'username' => config('services.traccar.username'),
                'password' => config('services.traccar.password'),
            ];
        }
    }

    /**
     * Validate if the integration is properly configured
     */
    public function validate(): bool
    {
        if (!$this->enabled) {
            return false;
        }

        if (empty($this->config['url']) || empty($this->config['username']) || empty($this->config['password'])) {
            return false;
        }

        try {
            $response = Http::withBasicAuth($this->config['username'], $this->config['password'])
                ->get("{$this->config['url']}/users");
            
            return $response->successful();
        } catch (Exception $e) {
            Log::error("Traccar API validation error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Synchronize a customer with Traccar
     *
     * @param Customer $customer
     * @return int|null Traccar user ID
     */
    public function syncCustomer(Customer $customer): ?int
    {
        if (!$this->enabled) {
            return null;
        }

        try {
            if ($customer->traccar_id) {
                // Update existing user in Traccar
                $response = Http::withBasicAuth($this->config['username'], $this->config['password'])
                    ->put("{$this->config['url']}/users/{$customer->traccar_id}", [
                        'name' => $customer->name,
                        'email' => $customer->email,
                        'phone' => $customer->phone,
                    ]);
                
                if ($response->successful()) {
                    return $customer->traccar_id;
                }
                
                Log::error("Failed to update customer in Traccar: " . $response->body());
                return null;
            } else {
                // Create new user in Traccar
                $response = Http::withBasicAuth($this->config['username'], $this->config['password'])
                    ->post("{$this->config['url']}/users", [
                        'name' => $customer->name,
                        'email' => $customer->email ?: ($customer->id . '@placeholder.com'),
                        'phone' => $customer->phone,
                        'password' => md5(rand()), // Generate a random password
                    ]);
                
                if ($response->successful()) {
                    $traccarUser = $response->json();
                    $customer->update(['traccar_id' => $traccarUser['id']]);
                    return $traccarUser['id'];
                }
                
                Log::error("Failed to create customer in Traccar: " . $response->body());
                return null;
            }
        } catch (Exception $e) {
            Log::error("Traccar API error: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Synchronize a vehicle with Traccar
     *
     * @param Vehicle $vehicle
     * @return int|null Traccar device ID
     */
    public function syncVehicle(Vehicle $vehicle): ?int
    {
        if (!$this->enabled) {
            return null;
        }

        try {
            // Ensure customer is synced first
            if (!$vehicle->customer->traccar_id) {
                $this->syncCustomer($vehicle->customer);
            }

            // Check if the vehicle has an equipment
            if (!$vehicle->equipment_id) {
                Log::warning("Vehicle ID {$vehicle->id} has no equipment associated");
                return null;
            }

            $equipment = Equipment::find($vehicle->equipment_id);
            if (!$equipment) {
                Log::warning("Equipment ID {$vehicle->equipment_id} not found for vehicle ID {$vehicle->id}");
                return null;
            }

            // Sync the equipment with Traccar
            $traccarId = $this->syncEquipment($equipment, $vehicle);

            // If successful, update the vehicle's traccar_id
            if ($traccarId) {
                $vehicle->update(['traccar_id' => $traccarId]);
                return $traccarId;
            }

            return null;
        } catch (Exception $e) {
            Log::error("Traccar API error in syncVehicle: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Synchronize equipment with Traccar
     *
     * @param Equipment $equipment
     * @param Vehicle|null $vehicle Optional associated vehicle
     * @return int|null Traccar device ID
     */
    public function syncEquipment(Equipment $equipment, ?Vehicle $vehicle = null): ?int
    {
        if (!$this->enabled) {
            return null;
        }

        try {
            $vehicleData = [];
            $customerId = null;

            // If we have a vehicle, get its data
            if ($vehicle) {
                $vehicleData = [
                    'name' => $vehicle->license_plate ?: $vehicle->model,
                    'model' => $vehicle->model,
                    'disabled' => !$vehicle->active,
                ];

                // If vehicle is assigned to a customer, get customer data
                if ($vehicle->customer_id) {
                    $customerId = $vehicle->customer->traccar_id;
                    $vehicleData['contact'] = $vehicle->customer->phone;
                }
            } else {
                // No vehicle, just use equipment data
                $vehicleData = [
                    'name' => "Equipment {$equipment->serial_number}",
                    'model' => $equipment->model,
                    'disabled' => ($equipment->status === 'defective'),
                ];
            }

            // Common data for create/update
            $deviceData = array_merge($vehicleData, [
                'uniqueId' => $equipment->serial_number,
                'phone' => $equipment->phone_number,
            ]);

            if ($equipment->traccar_id) {
                // Update existing device in Traccar
                $response = Http::withBasicAuth($this->config['username'], $this->config['password'])
                    ->put("{$this->config['url']}/devices/{$equipment->traccar_id}", $deviceData);
                
                if ($response->successful()) {
                    return $equipment->traccar_id;
                }
                
                Log::error("Failed to update equipment in Traccar: " . $response->body());
                return null;
            } else {
                // Create new device in Traccar
                $response = Http::withBasicAuth($this->config['username'], $this->config['password'])
                    ->post("{$this->config['url']}/devices", $deviceData);
                
                if ($response->successful()) {
                    $traccarDevice = $response->json();
                    $equipment->update(['traccar_id' => $traccarDevice['id']]);
                    
                    // Link device to user in Traccar if we have a customer
                    if ($customerId) {
                        Http::withBasicAuth($this->config['username'], $this->config['password'])
                            ->post("{$this->config['url']}/permissions", [
                                'userId' => $customerId,
                                'deviceId' => $traccarDevice['id'],
                            ]);
                    }
                    
                    return $traccarDevice['id'];
                }
                
                Log::error("Failed to create equipment in Traccar: " . $response->body());
                return null;
            }
        } catch (Exception $e) {
            Log::error("Traccar API error in syncEquipment: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Fetch all devices from Traccar and sync with our database
     */
    public function syncAllFromTracking(): void
    {
        if (!$this->enabled) {
            return;
        }

        try {
            // Sync users
            $response = Http::withBasicAuth($this->config['username'], $this->config['password'])
                ->get("{$this->config['url']}/users");
                
            if ($response->successful()) {
                $traccarUsers = $response->json();
                foreach ($traccarUsers as $traccarUser) {
                    $customer = Customer::where('traccar_id', $traccarUser['id'])->first();
                    if (!$customer) {
                        // Create new customer
                        $customer = Customer::create([
                            'name' => $traccarUser['name'],
                            'email' => $traccarUser['email'],
                            'phone' => $traccarUser['phone'] ?? null,
                            'traccar_id' => $traccarUser['id'],
                        ]);
                        Log::info("Created customer id {$customer->id} for Traccar user {$traccarUser['id']}");
                    }
                }
            }

            // Sync devices as Equipment
            $response = Http::withBasicAuth($this->config['username'], $this->config['password'])
                ->get("{$this->config['url']}/devices");
                
            if ($response->successful()) {
                $traccarDevices = $response->json();
                Log::info("Found " . count($traccarDevices) . " devices in Traccar");
                
                // Log all devices for debugging
                foreach ($traccarDevices as $index => $device) {
                    Log::info("Device {$index}: " . json_encode($device));
                }
                
                // Get device-user permissions to find ownership
                $permissionsResponse = Http::withBasicAuth($this->config['username'], $this->config['password'])
                    ->get("{$this->config['url']}/permissions");
                    
                $deviceUserMap = [];
                if ($permissionsResponse->successful()) {
                    $permissions = $permissionsResponse->json();
                    Log::info("Found " . count($permissions) . " permissions in Traccar");
                    
                    // Create a map of deviceId to userId
                    foreach ($permissions as $permission) {
                        if (isset($permission['deviceId']) && isset($permission['userId'])) {
                            $deviceUserMap[$permission['deviceId']] = $permission['userId'];
                            Log::info("Permission mapping: Device {$permission['deviceId']} belongs to User {$permission['userId']}");
                        }
                    }
                } else {
                    Log::error("Failed to get permissions from Traccar API: " . $permissionsResponse->body());
                }
                
                foreach ($traccarDevices as $traccarDevice) {
                    // Check if we already have this device in our equipment
                    $equipment = Equipment::where('traccar_id', $traccarDevice['id'])->first();
                    
                    if (!$equipment) {
                        // Create new equipment
                        Log::info("Creating equipment for Traccar device {$traccarDevice['id']}");
                        
                        try {
                            $equipment = Equipment::create([
                                'serial_number' => $traccarDevice['uniqueId'],
                                'model' => $traccarDevice['model'] ?? 'Unknown',
                                'brand' => 'Traccar',
                                'status' => 'with_customer', // Default status
                                'imei' => $traccarDevice['uniqueId'], // Using uniqueId as IMEI if not available
                                'traccar_id' => $traccarDevice['id'],
                                'phone_number' => $traccarDevice['phone'] ?? null,
                            ]);
                            
                            if ($equipment) {
                                Log::info("Created equipment id {$equipment->id} for Traccar device {$traccarDevice['id']}");
                                
                                // Now try to create a vehicle for this equipment if possible
                                // Check if we have a user associated with this device
                                $traccarUserId = $deviceUserMap[$traccarDevice['id']] ?? null;
                                $customer = null;
                                
                                if ($traccarUserId) {
                                    $customer = Customer::where('traccar_id', $traccarUserId)->first();
                                }
                                
                                $vehicle = Vehicle::create([
                                    'customer_id' => $customer ? $customer->id : null,
                                    'equipment_id' => $equipment->id,
                                    'model' => $traccarDevice['model'] ?? 'Unknown',
                                    'license_plate' => $traccarDevice['name'] ?? null,
                                    'active' => !($traccarDevice['disabled'] ?? false),
                                    'traccar_id' => $traccarDevice['id'],
                                ]);
                                
                                if ($vehicle) {
                                    Log::info("Created vehicle id {$vehicle->id} for equipment {$equipment->id}");
                                } else {
                                    Log::error("Failed to create vehicle for equipment {$equipment->id}");
                                }
                            } else {
                                Log::error("Failed to create equipment for Traccar device {$traccarDevice['id']}");
                            }
                        } catch (\Exception $e) {
                            Log::error("Error creating equipment: " . $e->getMessage());
                        }
                    } else {
                        // Equipment exists, update it
                        $equipment->update([
                            'phone_number' => $traccarDevice['phone'] ?? $equipment->phone_number,
                            'model' => $traccarDevice['model'] ?? $equipment->model,
                        ]);
                        
                        // Check if this equipment is associated with any vehicle
                        $vehicle = Vehicle::where('equipment_id', $equipment->id)->first();
                        
                        if (!$vehicle) {
                            // No vehicle associated, create one
                            $traccarUserId = $deviceUserMap[$traccarDevice['id']] ?? null;
                            $customer = null;
                            
                            if ($traccarUserId) {
                                $customer = Customer::where('traccar_id', $traccarUserId)->first();
                            }
                            
                            $vehicle = Vehicle::create([
                                'customer_id' => $customer ? $customer->id : null,
                                'equipment_id' => $equipment->id,
                                'model' => $traccarDevice['model'] ?? 'Unknown',
                                'license_plate' => $traccarDevice['name'] ?? null,
                                'active' => !($traccarDevice['disabled'] ?? false),
                                'traccar_id' => $traccarDevice['id'],
                            ]);
                            
                            if ($vehicle) {
                                Log::info("Created vehicle id {$vehicle->id} for existing equipment {$equipment->id}");
                            }
                        } else {
                            // Update vehicle data
                            $vehicle->update([
                                'license_plate' => $traccarDevice['name'] ?? $vehicle->license_plate,
                                'active' => !($traccarDevice['disabled'] ?? !$vehicle->active),
                            ]);
                        }
                    }
                }
            }
        } catch (Exception $e) {
            Log::error("Traccar API error during full sync: " . $e->getMessage());
        }
    }

    /**
     * Get data for a specific vehicle from the tracking system
     * 
     * @param Vehicle $vehicle
     * @return array|null
     */
    public function getVehicleData(Vehicle $vehicle): ?array
    {
        if (!$this->enabled) {
            return null;
        }

        // Check if vehicle has equipment
        if (!$vehicle->equipment_id) {
            Log::warning("Vehicle {$vehicle->id} has no equipment associated");
            return null;
        }

        $equipment = Equipment::find($vehicle->equipment_id);
        if (!$equipment || !$equipment->traccar_id) {
            Log::warning("Equipment for vehicle {$vehicle->id} not found or has no Traccar ID");
            return null;
        }

        try {
            $response = Http::withBasicAuth($this->config['username'], $this->config['password'])
                ->get("{$this->config['url']}/devices/{$equipment->traccar_id}");
                
            if ($response->successful()) {
                return $response->json();
            }
            
            return null;
        } catch (Exception $e) {
            Log::error("Traccar API error getting vehicle data: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Get data for a specific equipment from the tracking system
     * 
     * @param Equipment $equipment
     * @return array|null
     */
    public function getEquipmentData(Equipment $equipment): ?array
    {
        if (!$this->enabled || !$equipment->traccar_id) {
            return null;
        }

        try {
            $response = Http::withBasicAuth($this->config['username'], $this->config['password'])
                ->get("{$this->config['url']}/devices/{$equipment->traccar_id}");
                
            if ($response->successful()) {
                return $response->json();
            }
            
            return null;
        } catch (Exception $e) {
            Log::error("Traccar API error getting equipment data: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Get positions for a specific vehicle from the tracking system
     * 
     * @param Vehicle $vehicle
     * @param string $from ISO datetime 
     * @param string $to ISO datetime
     * @return array|null
     */
    public function getVehiclePositions(Vehicle $vehicle, string $from, string $to): ?array
    {
        if (!$this->enabled) {
            return null;
        }

        // Check if vehicle has equipment
        if (!$vehicle->equipment_id) {
            Log::warning("Vehicle {$vehicle->id} has no equipment associated");
            return null;
        }

        $equipment = Equipment::find($vehicle->equipment_id);
        if (!$equipment || !$equipment->traccar_id) {
            Log::warning("Equipment for vehicle {$vehicle->id} not found or has no Traccar ID");
            return null;
        }

        try {
            $response = Http::withBasicAuth($this->config['username'], $this->config['password'])
                ->get("{$this->config['url']}/positions", [
                    'deviceId' => $equipment->traccar_id,
                    'from' => $from,
                    'to' => $to,
                ]);
                
            if ($response->successful()) {
                return $response->json();
            }
            
            return null;
        } catch (Exception $e) {
            Log::error("Traccar API error getting vehicle positions: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Get positions for a specific equipment from the tracking system
     * 
     * @param Equipment $equipment
     * @param string $from ISO datetime 
     * @param string $to ISO datetime
     * @return array|null
     */
    public function getEquipmentPositions(Equipment $equipment, string $from, string $to): ?array
    {
        if (!$this->enabled || !$equipment->traccar_id) {
            return null;
        }

        try {
            $response = Http::withBasicAuth($this->config['username'], $this->config['password'])
                ->get("{$this->config['url']}/positions", [
                    'deviceId' => $equipment->traccar_id,
                    'from' => $from,
                    'to' => $to,
                ]);
                
            if ($response->successful()) {
                return $response->json();
            }
            
            return null;
        } catch (Exception $e) {
            Log::error("Traccar API error getting equipment positions: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Get the integration settings view
     */
    public function getSettingsView(): string
    {
        return 'integrations.traccar.api-settings';
    }
}