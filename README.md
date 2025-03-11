# 🚀 Icarus - Gestão Inteligente de Frotas e Rastreamento Veicular

<div align="center">
  <img src="https://placehold.co/800x400/1a202c/FFFFFF?text=Icarus+Platform" alt="Icarus Platform" width="800"/>
</div>

## 🔍 Visão Geral

Icarus é uma plataforma moderna e completa para gestão de frotas e rastreamento veicular, desenvolvida com Laravel e Filament. Integrada nativamente com o Traccar, ela oferece uma solução robusta e escalável para empresas de rastreamento veicular, locadoras e gestores de frotas.

### ✨ Principais Diferenciais

- **Interface intuitiva e moderna** construída com Filament
- **Sincronização bidirecional** com plataforma Traccar
- **Gestão completa** de clientes, veículos e equipamentos
- **Dashboard personalizado** para diferentes perfis de usuários
- **Fluxos operacionais** otimizados para empresas de rastreamento

## 📋 Funcionalidades

### Módulos Principais

🏢 **Gestão de Clientes**
- Cadastro completo com dados de contato e documentação
- Visualização centralizada de veículos e serviços
- Controle de contratos e assinaturas
- Sincronização bidirecional com Traccar

🚗 **Gestão de Veículos**
- Cadastro detalhado com informações técnicas
- Vinculação automatizada com dispositivos de rastreamento
- Histórico de serviços e manutenções
- Sincronização bidirecional com Traccar
- Sincronização de pivotagem com Cliente no Traccar

📡 **Gestão de Equipamentos**
- Controle de estoque e movimentação
- Rastreamento de status (Em estoque, Com cliente, Em manutenção)
- Integração com dispositivos via IMEI/ID
- Histórico completo de mudanças de status
- Sincronização bidirecional com Traccar

🔧 **Operações e Serviços**
- Agendamento de instalações e manutenções
- Atribuição a técnicos com notificações automáticas
- Histórico completo de atendimentos

💲 **Financeiro e Cobranças**
- Gestão de mensalidades e contratos
- Controle de pagamentos e inadimplência
- Relatórios financeiros detalhados

## 🔌 Integração com Traccar

O sistema possui integração nativa com o [Traccar](https://www.traccar.org/), plataforma open source líder em rastreamento GPS:

- **Sincronização automática** de clientes, veículos e dispositivos
- **Painel unificado** para gestão de dispositivos
- **Importação e exportação** de dados entre plataformas
- **Visualização em tempo real** de localização e status

## 🛠️ Requisitos Técnicos

- PHP 8.1 ou superior
- MySQL 5.7 ou superior (ou SQLite para testes)
- Composer
- Node.js e NPM
- Servidor com suporte a WebSockets (opcional)

## ⚡ Instalação Rápida

### 1. Clone o repositório

```bash
git clone https://github.com/riccefarias/project-icarus.git
cd project-icarus
```

### 2. Instale as dependências

```bash
composer install
npm install
```

### 3. Configure o ambiente

```bash
cp .env.example .env
php artisan key:generate
```

### 4. Configure o banco de dados no arquivo `.env`

```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=icarus
DB_USERNAME=root
DB_PASSWORD=
```

### 5. Execute as migrações

```bash
php artisan migrate --seed
```

### 6. Compile os assets

```bash
npm run build
```

### 7. Inicie o servidor

```bash
php artisan serve
```

Acesse o sistema em: http://localhost:8000  
Usuário Padrão: admin@example.com  
Senha: admin

### 8. Configuração do Worker com Supervisor

Para garantir que as tarefas em background (como sincronização com Traccar) sejam processadas corretamente, configure o Supervisor:

1. Instale o Supervisor:

```bash
sudo apt-get install supervisor
```

2. Crie um arquivo de configuração para o Icarus:

```bash
sudo nano /etc/supervisor/conf.d/icarus-worker.conf
```

3. Adicione a seguinte configuração:

```
[program:icarus-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /caminho/para/seu/projeto/artisan queue:work --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/caminho/para/seu/projeto/storage/logs/worker.log
stopwaitsecs=3600
```

4. Atualize o caminho `/caminho/para/seu/projeto/` para o diretório real da sua instalação.

5. Recarregue e inicie o Supervisor:

```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start icarus-worker:*
```

6. Verifique o status dos workers:

```bash
sudo supervisorctl status
```

### Alternativa: Iniciar o Worker Manualmente

Se preferir não usar o Supervisor, você pode iniciar o worker manualmente (ideal para ambiente de desenvolvimento):

```bash
php artisan queue:work --sleep=3 --tries=3
```

Para manter o worker rodando em segundo plano:

```bash
nohup php artisan queue:work --sleep=3 --tries=3 > storage/logs/worker.log 2>&1 &
```

Nota: Este método não oferece o mesmo nível de confiabilidade que o Supervisor para ambientes de produção.

## 🔧 Configuração do Traccar

Para utilizar a integração com o Traccar, acesse o painel administrativo do Icarus:

1. Navegue até **Integrações → Traccar**
2. Configure os parâmetros de conexão:
   - URL da API do Traccar
   - Credenciais de acesso
   - Opções de sincronização
3. Teste a conexão e salve as configurações
4. Use o botão "Sincronizar Agora" para iniciar a sincronização manual

## 📱 Configuração de SMS (Opcional)

Para habilitar o envio de comandos SMS para configuração de rastreadores:

1. Acesse **Configurações → Integrações → SMS**
2. Selecione o provedor de SMS desejado
3. Configure os parâmetros da API do provedor
4. Cadastre os templates de comandos SMS para os modelos de rastreadores suportados
5. Teste o envio de comandos pela interface

## 🗺️ Roadmap

O desenvolvimento do Icarus está organizado nas seguintes fases:

### Fase 1 (Base) ✅
- [x] Configuração do ambiente
- [x] Gestão de Clientes
- [x] Gestão de Veículos
- [x] Integração básica com Traccar
- [x] Gestão de Equipamentos
- [x] Sincronização de pivotagem de Dispositivo com o Cliente
- [x] Histórico de movimentação de Equipamentos

### Fase 2 (Operações) 🔄
- [ ] Gestão de Serviços
- [ ] Configuração de Equipamentos por SMS
- [ ] Dashboard operacional
- [ ] API para aplicativo móvel

### Fase 3 (Financeiro) 📊
- [ ] Gestão de Cobranças
- [ ] Relatórios gerenciais
- [ ] Notificações automáticas
- [ ] Integrações com gateways de pagamento

### Fase 4 (Expansão) 🌐
- [ ] Módulo para motoristas/aplicativo
- [ ] Telemetria avançada
- [ ] Machine learning para previsão de manutenção
- [ ] Interface para clientes finais

## 💼 Suporte Comercial

Para implementações personalizadas, suporte empresarial ou treinamentos, entre em contato:
- Email: angelo@kore.ag
- Site: https://www.kore.ag

## 📄 Licença Open Source

Este projeto é licenciado sob a licença MIT, o que significa que você pode:

- Usar o software para qualquer finalidade, comercial ou não
- Modificar o código e criar trabalhos derivados
- Distribuir o software original ou modificado

A única exigência é manter o aviso de copyright e a licença MIT original. Veja o arquivo [LICENSE](LICENSE) para mais detalhes.

## 👥 Contribuindo

Icarus é um projeto Open Source e contribuições são muito bem-vindas! Você pode ajudar de várias formas:

- Reportando bugs e sugerindo melhorias via Issues
- Enviando Pull Requests com correções ou novos recursos
- Melhorando a documentação
- Compartilhando o projeto

Veja nosso [guia de contribuição](CONTRIBUTING.md) para mais detalhes sobre como participar.

## 💖 Apoie o Projeto

Se o Icarus é útil para você ou sua empresa, considere apoiar o desenvolvimento:

<div align="center">

### 🎁 Faça uma Doação

[![PayPal](https://img.shields.io/badge/PayPal-00457C?style=for-the-badge&logo=paypal&logoColor=white)](https://www.paypal.com/donate/?hosted_button_id=BV22UKANF7CGA)
[![PIX](https://img.shields.io/badge/PIX-00C300?style=for-the-badge&logo=pix&logoColor=white)](http://picpay.me/riccefarias)

### ⭐ Ou Simplesmente Dê uma Estrela no GitHub

Seu apoio nos motiva a continuar melhorando o projeto!

</div>

---

<div align="center">
  <p>Desenvolvido com ❤️ pela Equipe KORE</p>
</div>