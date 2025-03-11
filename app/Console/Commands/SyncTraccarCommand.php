<?php

namespace App\Console\Commands;

use App\Jobs\SyncTraccarPlatform;
use Illuminate\Console\Command;

class SyncTraccarCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'traccar:sync {--force : Força sincronização incluindo pivotagem de dispositivos e usuários}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sincroniza dados com a plataforma Traccar';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Iniciando sincronização com a plataforma Traccar...');

        // Dispatcha o job para a fila
        SyncTraccarPlatform::dispatch();

        $this->info('Trabalho de sincronização enviado para a fila com sucesso!');

        if ($this->option('force')) {
            $this->info('Opção --force detectada, as relações de pivotagem entre dispositivos e usuários serão sincronizadas.');
        }

        $this->info('Use o comando "php artisan queue:work" para processar a fila se ainda não estiver rodando.');
    }
}
