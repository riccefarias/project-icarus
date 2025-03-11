<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;

class UpdateSystemWidget extends Widget
{
    protected static ?int $sort = 3;
    
    protected int|string|array $columnSpan = 'full';
    
    protected static string $view = 'filament.widgets.update-system-widget';
    
    protected function getViewData(): array
    {
        $updateInfo = $this->checkForUpdates();
        
        return [
            'hasUpdates' => $updateInfo['hasUpdates'],
            'currentVersion' => $updateInfo['currentVersion'],
            'remoteVersion' => $updateInfo['remoteVersion'],
        ];
    }
    
    protected function checkForUpdates(): array
    {
        try {
            // Forçar fetch para verificar atualizações
            exec('cd ' . base_path() . ' && git fetch 2>&1', $fetchOutput, $fetchReturnVar);
            
            if ($fetchReturnVar !== 0) {
                return [
                    'hasUpdates' => false,
                    'currentVersion' => $this->getProjectVersion(),
                    'remoteVersion' => 'Erro ao verificar',
                ];
            }
            
            // Verificar se há commits à frente ou atrás
            exec('cd ' . base_path() . ' && git status -uno 2>&1', $statusOutput, $statusReturnVar);
            $statusText = implode("\n", $statusOutput);
            $hasUpdates = strpos($statusText, 'Your branch is behind') !== false;
            
            $currentVersion = $this->getProjectVersion();
            $remoteVersion = $this->getRemoteVersion();
            
            return [
                'hasUpdates' => $hasUpdates,
                'currentVersion' => $currentVersion,
                'remoteVersion' => $remoteVersion,
            ];
        } catch (\Exception $e) {
            return [
                'hasUpdates' => false,
                'currentVersion' => $this->getProjectVersion(),
                'remoteVersion' => 'Erro: ' . $e->getMessage(),
            ];
        }
    }
    
    protected function getProjectVersion(): string
    {
        $versionFile = base_path('version.txt');
        
        if (file_exists($versionFile)) {
            return trim(file_get_contents($versionFile));
        }
        
        return '0.1.0';
    }
    
    protected function getRemoteVersion(): string
    {
        try {
            // Verificar se há atualizações no repositório
            if (function_exists('curl_init')) {
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, 'https://raw.githubusercontent.com/riccefarias/project-icarus/main/version.txt');
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
                curl_setopt($ch, CURLOPT_TIMEOUT, 5);
                $remoteVersion = trim(curl_exec($ch));
                $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                curl_close($ch);
                
                if ($httpCode == 200 && !empty($remoteVersion)) {
                    return $remoteVersion;
                }
            }
            
            // Fallback: tentar usar comandos Git
            exec('cd ' . base_path() . ' && git fetch && git show origin/main:version.txt 2>/dev/null', $output, $returnVar);
            
            if ($returnVar === 0 && !empty($output[0])) {
                return trim($output[0]);
            }
            
            // Se não conseguiu obter a versão remota, retorna a mesma versão atual com "+"
            // para indicar que pode haver uma atualização, mas não sabemos qual é
            return $this->getProjectVersion() . '+';
        } catch (\Exception $e) {
            return $this->getProjectVersion() . '+';
        }
    }
    
    public static function canView(): bool
    {
        try {
            $versionFile = base_path('version.txt');
            
            if (!file_exists($versionFile)) {
                return false;
            }
            
            $currentVersion = trim(file_get_contents($versionFile));
            
            // Verificar versão remota usando cURL
            if (function_exists('curl_init')) {
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, 'https://raw.githubusercontent.com/riccefarias/project-icarus/main/version.txt');
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 3);
                curl_setopt($ch, CURLOPT_TIMEOUT, 3);
                $remoteVersion = trim(curl_exec($ch));
                $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                curl_close($ch);
                
                if ($httpCode == 200 && !empty($remoteVersion) && version_compare($remoteVersion, $currentVersion, '>')) {
                    return true;
                }
            }
            
            // Fallback: tentar usar comandos Git
            exec('cd ' . base_path() . ' && git fetch && git show origin/main:version.txt 2>/dev/null', $output, $returnVar);
            
            if ($returnVar === 0 && !empty($output[0])) {
                $remoteVersion = trim($output[0]);
                return version_compare($remoteVersion, $currentVersion, '>');
            }
            
            return false;
        } catch (\Exception $e) {
            return false;
        }
    }
}