<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;
use Illuminate\Support\Facades\DB;

class SystemInfoWidget extends Widget
{
    protected static ?int $sort = 2;
    protected static bool $isLazy = false;
    
    protected int|string|array $columnSpan = 2;
    
    protected static string $view = 'filament.widgets.system-info-widget';
    
    protected function getViewData(): array
    {
        $gitInfo = $this->getGitVersionInfo();
        
        return [
            'phpVersion' => PHP_VERSION,
            'laravelVersion' => app()->version(),
            'databaseType' => DB::connection()->getPdo()->getAttribute(\PDO::ATTR_DRIVER_NAME),
            'gitCurrentVersion' => $gitInfo['current'] ?? 'Desconhecido',
            'gitLatestVersion' => $gitInfo['latest'] ?? 'Desconhecido',
            'hasUpdates' => $gitInfo['hasUpdates'] ?? false,
        ];
    }
    
    protected function getGitVersionInfo(): array
    {
        try {
            // Forçar fetch para verificar atualizações
            exec('cd ' . base_path() . ' && git fetch 2>&1', $fetchOutput, $fetchReturnVar);
            
            if ($fetchReturnVar !== 0) {
                return [
                    'current' => 'Erro ao verificar',
                    'latest' => 'Erro ao verificar',
                    'hasUpdates' => false,
                ];
            }
            
            // Obter hash da versão atual
            exec('cd ' . base_path() . ' && git rev-parse HEAD 2>&1', $currentOutput, $currentReturnVar);
            $currentHash = $currentReturnVar === 0 ? substr($currentOutput[0], 0, 7) : 'unknown';
            
            // Obter hash da versão remota
            exec('cd ' . base_path() . ' && git rev-parse origin/main 2>&1', $latestOutput, $latestReturnVar);
            $latestHash = $latestReturnVar === 0 ? substr($latestOutput[0], 0, 7) : 'unknown';
            
            // Verificar se há commits à frente ou atrás
            exec('cd ' . base_path() . ' && git status -uno 2>&1', $statusOutput, $statusReturnVar);
            $statusText = implode("\n", $statusOutput);
            $hasUpdates = strpos($statusText, 'Your branch is behind') !== false;
            
            return [
                'current' => $currentHash,
                'latest' => $latestHash,
                'hasUpdates' => $hasUpdates,
            ];
        } catch (\Exception $e) {
            return [
                'current' => 'Erro',
                'latest' => 'Erro',
                'hasUpdates' => false,
                'error' => $e->getMessage(),
            ];
        }
    }
}