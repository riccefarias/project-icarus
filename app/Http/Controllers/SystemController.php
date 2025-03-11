<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Filament\Notifications\Notification;

class SystemController extends Controller
{
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
            // Verificar versão remota usando cURL
            if (function_exists('curl_init')) {
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, 'https://raw.githubusercontent.com/seu-usuario/icarus/main/version.txt');
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
            
            // Se não conseguir obter a versão remota, assume a mesma da local
            return $this->getProjectVersion();
        } catch (\Exception $e) {
            return $this->getProjectVersion();
        }
    }
    
    public function update(Request $request)
    {
        try {
            // Verificar se há atualizações disponíveis
            $currentVersion = $this->getProjectVersion();
            $remoteVersion = $this->getRemoteVersion();
            
            // Verifica se a versão remota é maior que a atual usando comparação semântica
            $hasUpdates = version_compare($remoteVersion, $currentVersion, '>');
            
            if (!$hasUpdates) {
                Notification::make()
                    ->title('Sistema já atualizado')
                    ->body("O sistema já está na versão mais recente ($currentVersion).")
                    ->success()
                    ->send();
                
                return redirect('/');
            }
            
            // Obter a versão antes de atualizar
            $prevVersion = $this->getProjectVersion();
            
            // Realizar o git pull
            exec('cd ' . base_path() . ' && git pull 2>&1', $pullOutput, $pullReturnVar);
            
            if ($pullReturnVar !== 0) {
                Notification::make()
                    ->title('Erro ao atualizar')
                    ->body('Ocorreu um erro ao tentar atualizar o sistema: ' . implode("\n", $pullOutput))
                    ->danger()
                    ->send();
                
                return redirect('/');
            }
            
            // Obter a nova versão do servidor remoto
            $newVersion = $this->getRemoteVersion();
            
            // Atualizar o arquivo de versão local com a nova versão
            file_put_contents(base_path('version.txt'), $newVersion);
            
            // Executar migrações se houver
            Artisan::call('migrate', ['--force' => true]);
            
            // Limpar cache
            Artisan::call('optimize:clear');
            
            Notification::make()
                ->title('Sistema atualizado com sucesso')
                ->body("O sistema foi atualizado da versão {$prevVersion} para {$newVersion}.")
                ->success()
                ->send();
            
            return redirect('/');
        } catch (\Exception $e) {
            Notification::make()
                ->title('Erro ao atualizar')
                ->body('Ocorreu um erro inesperado: ' . $e->getMessage())
                ->danger()
                ->send();
            
            return redirect('/');
        }
    }
}