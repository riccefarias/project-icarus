<?php

namespace App\Filament\Pages;

use App\Integrations\IntegrationsManager;
use App\Models\Setting;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Http;

class IntegrationSettings extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected static string $view = 'filament.pages.integration-settings';

    protected static ?string $navigationLabel = 'Configuração de Integração';

    protected static ?string $navigationGroup = 'Configurações';

    protected static bool $shouldRegisterNavigation = false;

    public string $integration = '';

    public ?string $integrationTitle = null;

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

    public function mount($integration = null): void
    {
        $this->integration = $integration ?? request()->query('integration', '');

        if (empty($this->integration)) {
            $this->redirectRoute('filament.admin.pages.integrations-manager');

            return;
        }

        // Formatar o título com base no slug da integração
        $this->integrationTitle = match ($this->integration) {
            'traccar-api' => 'Configuração do Traccar API',
            'traccar-database' => 'Configuração do Traccar Database',
            default => 'Configuração de Integração'
        };

        // Carregar as configurações apropriadas
        $this->loadConfigurationForIntegration();
    }

    protected function loadConfigurationForIntegration(): void
    {
        if ($this->integration === 'traccar-api') {
            $setting = Setting::where('key', 'traccar_api_integration')->first();
            if ($setting && ! empty($setting->value)) {
                $this->traccarApiConfig = json_decode($setting->value, true);
            } else {
                // Fallback to config if no database settings are found
                $this->traccarApiConfig = [
                    'enabled' => config('services.traccar.enabled', false),
                    'url' => config('services.traccar.url', ''),
                    'username' => config('services.traccar.username', ''),
                    'password' => config('services.traccar.password', ''),
                ];
            }
        } elseif ($this->integration === 'traccar-database') {
            $setting = Setting::where('key', 'traccar_database_integration')->first();
            if ($setting && ! empty($setting->value)) {
                $this->traccarDatabaseConfig = json_decode($setting->value, true);
            }
        }
    }

    public function getTitle(): string
    {
        return $this->integrationTitle;
    }

    public function saveTraccarApiSettings(): void
    {
        // Validate form
        $this->validate([
            'traccarApiConfig.url' => 'required|url',
            'traccarApiConfig.username' => 'required',
            'traccarApiConfig.password' => 'required',
        ]);

        // Save settings to database instead of .env
        Setting::updateOrCreate(
            ['key' => 'traccar_api_integration'],
            ['value' => json_encode($this->traccarApiConfig)]
        );

        // Set as active integration if enabled
        if ($this->traccarApiConfig['enabled']) {
            app(IntegrationsManager::class)->setActiveIntegration('traccar_api');
        }

        Notification::make()
            ->title('Configurações Salvas')
            ->body('As configurações do Traccar API foram salvas com sucesso.')
            ->success()
            ->send();

        $this->redirectRoute('filament.admin.pages.integrations-manager');
    }

    public function saveTraccarDatabaseSettings(): void
    {
        // Validate form
        $this->validate([
            'traccarDatabaseConfig.host' => 'required',
            'traccarDatabaseConfig.port' => 'required',
            'traccarDatabaseConfig.database' => 'required',
            'traccarDatabaseConfig.username' => 'required',
            'traccarDatabaseConfig.password' => 'required',
        ]);

        // Save settings to database
        Setting::updateOrCreate(
            ['key' => 'traccar_database_integration'],
            ['value' => json_encode($this->traccarDatabaseConfig)]
        );

        // Set as active integration if enabled
        if ($this->traccarDatabaseConfig['enabled']) {
            app(IntegrationsManager::class)->setActiveIntegration('traccar_database');
        }

        Notification::make()
            ->title('Configurações Salvas')
            ->body('As configurações do Traccar Database foram salvas com sucesso.')
            ->success()
            ->send();

        $this->redirectRoute('filament.admin.pages.integrations-manager');
    }

    public function testTraccarApiConnection(): void
    {
        try {
            $response = Http::withBasicAuth(
                $this->traccarApiConfig['username'],
                $this->traccarApiConfig['password']
            )->get("{$this->traccarApiConfig['url']}/users");

            if ($response->successful()) {
                Notification::make()
                    ->title('Conexão bem-sucedida')
                    ->body('A conexão com o servidor Traccar foi estabelecida com sucesso.')
                    ->success()
                    ->send();
            } else {
                Notification::make()
                    ->title('Falha na conexão')
                    ->body('Não foi possível conectar ao servidor Traccar. Código: '.$response->status())
                    ->danger()
                    ->send();
            }
        } catch (\Exception $e) {
            Notification::make()
                ->title('Erro ao testar conexão')
                ->body('Ocorreu um erro ao testar a conexão: '.$e->getMessage())
                ->danger()
                ->send();
        }
    }

    public function testTraccarDatabaseConnection(): void
    {
        try {
            $config = [
                'driver' => 'mysql',
                'host' => $this->traccarDatabaseConfig['host'],
                'port' => $this->traccarDatabaseConfig['port'],
                'database' => $this->traccarDatabaseConfig['database'],
                'username' => $this->traccarDatabaseConfig['username'],
                'password' => $this->traccarDatabaseConfig['password'],
            ];

            $temporaryConnection = new \PDO(
                "mysql:host={$config['host']};port={$config['port']};dbname={$config['database']}",
                $config['username'],
                $config['password']
            );

            // Try to execute a simple query
            $stmt = $temporaryConnection->query('SHOW TABLES');
            if ($stmt) {
                $tables = $stmt->fetchAll(\PDO::FETCH_COLUMN);

                Notification::make()
                    ->title('Conexão bem-sucedida')
                    ->body('A conexão com o banco de dados Traccar foi estabelecida com sucesso. '.count($tables).' tabelas encontradas.')
                    ->success()
                    ->send();
            } else {
                Notification::make()
                    ->title('Conexão estabelecida, mas sem tabelas')
                    ->body('A conexão com o banco de dados foi estabelecida, mas não foi possível ler as tabelas.')
                    ->warning()
                    ->send();
            }
        } catch (\Exception $e) {
            Notification::make()
                ->title('Erro ao testar conexão')
                ->body('Ocorreu um erro ao testar a conexão com o banco de dados: '.$e->getMessage())
                ->danger()
                ->send();
        }
    }

    // Removido método de atualização do arquivo .env, agora utilizamos o banco de dados
}
