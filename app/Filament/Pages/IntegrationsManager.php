<?php

namespace App\Filament\Pages;

use App\Integrations\IntegrationsManager as IntegrationsManagerService;
use App\Models\Setting;
use Filament\Pages\Page;

class IntegrationsManager extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected static string $view = 'filament.pages.integrations-manager';

    protected static ?string $navigationLabel = 'Integrações';

    protected static ?string $title = 'Gerenciador de Integrações';

    protected static ?string $navigationGroup = 'Configurações';

    protected static ?int $navigationSort = 1;

    /**
     * @var IntegrationsManagerService
     */
    protected $integrationsManager;

    // Propriedade de integração ativa removida

    /**
     * @var array
     */
    public $categories = [];

    /**
     * @var array
     */
    public $traccarApiConfig = [
        'enabled' => false,
        'url' => '',
        'username' => '',
        'password' => '',
    ];

    /**
     * @var array
     */
    public $traccarDatabaseConfig = [
        'enabled' => false,
        'host' => '',
        'port' => '3306',
        'database' => 'traccar',
        'username' => '',
        'password' => '',
    ];

    public function mount(): void
    {
        $this->integrationsManager = app(IntegrationsManagerService::class);
        $this->integrationsManager->loadIntegrations();

        // Set up categories and integrations
        $this->setupCategories();

        // Load Traccar API configuration
        $this->traccarApiConfig = [
            'enabled' => config('services.traccar.enabled', false),
            'url' => config('services.traccar.url', ''),
            'username' => config('services.traccar.username', ''),
            'password' => config('services.traccar.password', ''),
        ];

        // Load Traccar Database configuration
        $setting = Setting::where('key', 'traccar_database_integration')->first();
        if ($setting && ! empty($setting->value)) {
            $this->traccarDatabaseConfig = json_decode($setting->value, true);
        }
    }

    private function setupCategories(): void
    {
        // Define categories with their integrations
        $this->categories = [
            'tracking' => [
                'name' => 'Plataformas de Rastreamento',
                'description' => 'Integração com sistemas de rastreamento de veículos.',
                'integrations' => [
                    'traccar_api' => [
                        'name' => 'Traccar API',
                        'description' => 'Integração com o Traccar via API REST.',
                        'enabled' => $this->traccarApiConfig['enabled'] ?? false,
                        'configForm' => 'traccarApiConfigForm',
                    ],
                    'traccar_database' => [
                        'name' => 'Traccar Database',
                        'description' => 'Integração direta com o banco de dados do Traccar.',
                        'enabled' => $this->traccarDatabaseConfig['enabled'] ?? false,
                        'configForm' => 'traccarDatabaseConfigForm',
                    ],
                ],
            ],
            // Adicione outras categorias conforme necessário
            /*
            'payment' => [
                'name' => 'Gateways de Pagamento',
                'description' => 'Integração com provedores de serviços de pagamento.',
                'integrations' => [
                    // ...
                ]
            ],
            'sms' => [
                'name' => 'Serviços de SMS',
                'description' => 'Integração com provedores de serviços de SMS.',
                'integrations' => [
                    // ...
                ]
            ]
            */
        ];
    }

    // Métodos de configuração movidos para IntegrationSettings

    // Método removido pois a seleção de integração ativa foi movida

    // Métodos de teste de conexão movidos para IntegrationSettings

    protected function updateEnvSettings(array $values): void
    {
        $envFile = app()->environmentFilePath();
        $str = file_get_contents($envFile);

        if (count($values) > 0) {
            foreach ($values as $envKey => $envValue) {
                $str .= "\n"; // In case the searched variable is not found
                $keyPosition = strpos($str, "{$envKey}=");
                $endOfLinePosition = strpos($str, "\n", $keyPosition);
                $oldLine = substr($str, $keyPosition, $endOfLinePosition - $keyPosition);

                // If key does not exist, add it
                if (! $keyPosition || ! $endOfLinePosition || ! $oldLine) {
                    $str .= "{$envKey}={$envValue}\n";
                } else {
                    $str = str_replace($oldLine, "{$envKey}={$envValue}", $str);
                }
            }
        }

        file_put_contents($envFile, $str);
    }
}
