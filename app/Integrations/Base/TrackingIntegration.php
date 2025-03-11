<?php

namespace App\Integrations\Base;

use App\Models\Customer;
use App\Models\Vehicle;

abstract class TrackingIntegration
{
    /**
     * Integration identifier
     */
    protected string $key;

    /**
     * Integration display name
     */
    protected string $name;

    /**
     * Integration description
     */
    protected string $description;

    /**
     * Whether the integration is enabled
     */
    protected bool $enabled;

    /**
     * Integration configuration
     */
    protected array $config;

    /**
     * Get the integration key
     */
    public function getKey(): string
    {
        return $this->key;
    }

    /**
     * Get the integration name
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Get the integration description
     */
    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * Check if the integration is enabled
     */
    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    /**
     * Enable the integration
     */
    public function enable(): void
    {
        $this->enabled = true;
    }

    /**
     * Disable the integration
     */
    public function disable(): void
    {
        $this->enabled = false;
    }

    /**
     * Set the integration configuration
     */
    public function setConfig(array $config): void
    {
        $this->config = $config;
    }

    /**
     * Get the integration configuration
     */
    public function getConfig(): array
    {
        return $this->config;
    }

    /**
     * Validate if the integration is properly configured
     */
    abstract public function validate(): bool;

    /**
     * Synchronize a customer with the tracking system
     *
     * @param Customer $customer
     * @return int|null Tracking system user ID
     */
    abstract public function syncCustomer(Customer $customer): ?int;

    /**
     * Synchronize a vehicle with the tracking system
     *
     * @param Vehicle $vehicle
     * @return int|null Tracking system device ID
     */
    abstract public function syncVehicle(Vehicle $vehicle): ?int;

    /**
     * Fetch all devices from the tracking system and sync with our database
     */
    abstract public function syncAllFromTracking(): void;

    /**
     * Get data for a specific vehicle from the tracking system
     * 
     * @param Vehicle $vehicle
     * @return array|null
     */
    abstract public function getVehicleData(Vehicle $vehicle): ?array;

    /**
     * Get positions for a specific vehicle from the tracking system
     * 
     * @param Vehicle $vehicle
     * @param string $from ISO datetime 
     * @param string $to ISO datetime
     * @return array|null
     */
    abstract public function getVehiclePositions(Vehicle $vehicle, string $from, string $to): ?array;

    /**
     * Get the integration settings view
     */
    abstract public function getSettingsView(): string;
}