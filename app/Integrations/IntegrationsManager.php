<?php

namespace App\Integrations;

use App\Integrations\Base\TrackingIntegration;
use App\Models\Setting;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class IntegrationsManager
{
    /**
     * Integration types
     */
    public const TYPE_TRACKING = 'tracking';

    public const TYPE_PAYMENT = 'payment';

    public const TYPE_WHATSAPP = 'whatsapp';

    public const TYPE_CHAT = 'chat';

    public const TYPE_NOTIFICATION = 'notification';

    /**
     * @var array<string, array<string, mixed>>
     */
    protected array $integrations = [];

    /**
     * Currently active integrations by type
     *
     * @var array<string, mixed>
     */
    protected array $activeIntegrations = [];

    /**
     * Load all available integrations
     */
    public function loadIntegrations(): void
    {
        $this->integrations = [
            self::TYPE_TRACKING => [],
            self::TYPE_PAYMENT => [],
            self::TYPE_WHATSAPP => [],
            self::TYPE_CHAT => [],
            self::TYPE_NOTIFICATION => [],
        ];

        // Load tracking integrations
        $this->loadIntegrationsOfType(self::TYPE_TRACKING, 'Traccar', TrackingIntegration::class);

        // Aqui carregaríamos outros tipos de integração quando forem implementados
        // Por exemplo:
        // $this->loadIntegrationsOfType(self::TYPE_PAYMENT, 'Payment', PaymentIntegration::class);
        // $this->loadIntegrationsOfType(self::TYPE_WHATSAPP, 'Whatsapp', WhatsappIntegration::class);
        // etc.

        // Carrega as integrações ativas
        $this->loadActiveIntegrations();
    }

    /**
     * Load integrations of a specific type from a directory
     *
     * @param  string  $type  Integration type
     * @param  string  $directory  Directory name under app/Integrations
     * @param  string  $baseClass  Base class that integrations should extend or implement
     */
    private function loadIntegrationsOfType(string $type, string $directory, string $baseClass): void
    {
        $files = glob(app_path("Integrations/{$directory}/*.php"));

        foreach ($files as $file) {
            $className = pathinfo($file, PATHINFO_FILENAME);
            $fullClassName = "App\\Integrations\\{$directory}\\{$className}";

            if (class_exists($fullClassName)) {
                try {
                    $integration = new $fullClassName;

                    if (is_a($integration, $baseClass)) {
                        $this->integrations[$type][$integration->getKey()] = $integration;
                    }
                } catch (\Exception $e) {
                    Log::error("Failed to load {$type} integration {$className}: ".$e->getMessage());
                }
            }
        }

    }

    /**
     * Get all available integrations of a specific type
     *
     * @param  string  $type  Type of integrations to get (default: tracking)
     */
    public function getIntegrations(string $type = self::TYPE_TRACKING): Collection
    {
        return collect($this->integrations[$type] ?? []);
    }

    /**
     * Get a specific integration by key and type
     *
     * @param  string  $key  Integration key
     * @param  string  $type  Integration type (default: tracking)
     */
    public function getIntegration(string $key, string $type = self::TYPE_TRACKING): ?TrackingIntegration
    {
        return $this->integrations[$type][$key] ?? null;
    }

    /**
     * Get the currently active integration of a specific type
     *
     * @param  string  $type  Integration type (default: tracking)
     */
    public function getActiveIntegration(string $type = self::TYPE_TRACKING): ?TrackingIntegration
    {
        return $this->activeIntegrations[$type] ?? null;
    }

    /**
     * Set the active integration of a specific type
     *
     * @param  string  $key  Integration key
     * @param  string  $type  Integration type (default: tracking)
     */
    public function setActiveIntegration(string $key, string $type = self::TYPE_TRACKING): bool
    {
        if (! isset($this->integrations[$type][$key])) {
            return false;
        }

        $this->activeIntegrations[$type] = $this->integrations[$type][$key];

        // Save the active integration key to settings
        Setting::updateOrCreate(
            ['key' => 'active_'.$type.'_integration'],
            ['value' => $key]
        );

        return true;
    }

    /**
     * Load the active integrations from settings
     */
    public function loadActiveIntegrations(): void
    {
        $this->activeIntegrations = [];

        // Load all integration types
        $this->loadActiveIntegrationType(self::TYPE_TRACKING);
        $this->loadActiveIntegrationType(self::TYPE_PAYMENT);
        $this->loadActiveIntegrationType(self::TYPE_WHATSAPP);
        $this->loadActiveIntegrationType(self::TYPE_CHAT);
        $this->loadActiveIntegrationType(self::TYPE_NOTIFICATION);
    }

    /**
     * Load active integration for a specific type
     *
     * @param  string  $type  Integration type
     */
    private function loadActiveIntegrationType(string $type): void
    {
        $setting = Setting::where('key', 'active_'.$type.'_integration')->first();

        if ($setting && isset($this->integrations[$type][$setting->value])) {
            $this->activeIntegrations[$type] = $this->integrations[$type][$setting->value];
        } elseif (! empty($this->integrations[$type])) {
            // Set the first integration as active if none is set
            $this->activeIntegrations[$type] = reset($this->integrations[$type]);

            Setting::updateOrCreate(
                ['key' => 'active_'.$type.'_integration'],
                ['value' => $this->activeIntegrations[$type]->getKey()]
            );
        }
    }
}
