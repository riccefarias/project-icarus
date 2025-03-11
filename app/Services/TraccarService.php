<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\Vehicle;
use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TraccarService
{
    protected $apiUrl;
    protected $apiUser;
    protected $apiPassword;
    protected $isEnabled;

    public function __construct()
    {
        $this->apiUrl = config('services.traccar.url');
        $this->apiUser = config('services.traccar.username');
        $this->apiPassword = config('services.traccar.password');
        $this->isEnabled = config('services.traccar.enabled', false);
    }

    /**
     * Synchronize a customer with Traccar
     *
     * @param Customer $customer
     * @return int|null Traccar user ID
     */
    public function syncCustomer(Customer $customer): ?int
    {
        if (!$this->isEnabled) {
            return null;
        }

        try {
            if ($customer->traccar_id) {
                // Update existing user in Traccar
                $response = Http::withBasicAuth($this->apiUser, $this->apiPassword)
                    ->put("{$this->apiUrl}/users/{$customer->traccar_id}", [
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
                $response = Http::withBasicAuth($this->apiUser, $this->apiPassword)
                    ->post("{$this->apiUrl}/users", [
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
        if (!$this->isEnabled) {
            return null;
        }

        try {
            // Ensure customer is synced first
            if (!$vehicle->customer->traccar_id) {
                $this->syncCustomer($vehicle->customer);
            }

            if ($vehicle->traccar_id) {
                // Update existing device in Traccar
                $response = Http::withBasicAuth($this->apiUser, $this->apiPassword)
                    ->put("{$this->apiUrl}/devices/{$vehicle->traccar_id}", [
                        'name' => $vehicle->license_plate ?: $vehicle->model,
                        'uniqueId' => $vehicle->device_id,
                        'phone' => $vehicle->phone_number,
                        'model' => $vehicle->model,
                        'contact' => $vehicle->customer->phone,
                        'disabled' => !$vehicle->active,
                    ]);
                
                if ($response->successful()) {
                    return $vehicle->traccar_id;
                }
                
                Log::error("Failed to update vehicle in Traccar: " . $response->body());
                return null;
            } else {
                // Create new device in Traccar
                $response = Http::withBasicAuth($this->apiUser, $this->apiPassword)
                    ->post("{$this->apiUrl}/devices", [
                        'name' => $vehicle->license_plate ?: $vehicle->model,
                        'uniqueId' => $vehicle->device_id ?: 'temp_' . $vehicle->id,
                        'phone' => $vehicle->phone_number,
                        'model' => $vehicle->model,
                        'contact' => $vehicle->customer->phone,
                        'disabled' => !$vehicle->active,
                    ]);
                
                if ($response->successful()) {
                    $traccarDevice = $response->json();
                    $vehicle->update(['traccar_id' => $traccarDevice['id']]);
                    
                    // Link device to user in Traccar
                    Http::withBasicAuth($this->apiUser, $this->apiPassword)
                        ->post("{$this->apiUrl}/permissions", [
                            'userId' => $vehicle->customer->traccar_id,
                            'deviceId' => $traccarDevice['id'],
                        ]);
                    
                    return $traccarDevice['id'];
                }
                
                Log::error("Failed to create vehicle in Traccar: " . $response->body());
                return null;
            }
        } catch (Exception $e) {
            Log::error("Traccar API error: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Fetch all devices from Traccar and sync with our database
     */
    public function syncAllFromTraccar(): void
    {
        if (!$this->isEnabled) {
            return;
        }

        try {
            // Sync users
            $response = Http::withBasicAuth($this->apiUser, $this->apiPassword)
                ->get("{$this->apiUrl}/users");
                
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
                    }
                }
            }

            // Sync devices
            $response = Http::withBasicAuth($this->apiUser, $this->apiPassword)
                ->get("{$this->apiUrl}/devices");
                
            if ($response->successful()) {
                $traccarDevices = $response->json();
                foreach ($traccarDevices as $traccarDevice) {
                    $vehicle = Vehicle::where('traccar_id', $traccarDevice['id'])->first();
                    if (!$vehicle) {
                        // Find customer for this device
                        $permissionsResponse = Http::withBasicAuth($this->apiUser, $this->apiPassword)
                            ->get("{$this->apiUrl}/permissions", [
                                'deviceId' => $traccarDevice['id'],
                            ]);
                            
                        if ($permissionsResponse->successful()) {
                            $permissions = $permissionsResponse->json();
                            $traccarUserId = null;
                            
                            foreach ($permissions as $permission) {
                                if (isset($permission['userId'])) {
                                    $traccarUserId = $permission['userId'];
                                    break;
                                }
                            }
                            
                            if ($traccarUserId) {
                                $customer = Customer::where('traccar_id', $traccarUserId)->first();
                                if ($customer) {
                                    // Create new vehicle
                                    $vehicle = Vehicle::create([
                                        'customer_id' => $customer->id,
                                        'model' => $traccarDevice['model'] ?? 'Unknown',
                                        'device_id' => $traccarDevice['uniqueId'],
                                        'phone_number' => $traccarDevice['phone'] ?? null,
                                        'active' => !($traccarDevice['disabled'] ?? false),
                                        'traccar_id' => $traccarDevice['id'],
                                    ]);
                                }
                            }
                        }
                    }
                }
            }
        } catch (Exception $e) {
            Log::error("Traccar API error during full sync: " . $e->getMessage());
        }
    }
}