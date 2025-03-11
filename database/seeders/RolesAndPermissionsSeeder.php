<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Limpar permissões e papéis existentes para evitar duplicação
        Permission::query()->delete();
        Role::query()->delete();

        // Criar permissões - clientes
        Permission::create(['name' => 'view customers']);
        Permission::create(['name' => 'create customers']);
        Permission::create(['name' => 'update customers']);
        Permission::create(['name' => 'delete customers']);

        // Criar permissões - veículos
        Permission::create(['name' => 'view vehicles']);
        Permission::create(['name' => 'create vehicles']);
        Permission::create(['name' => 'update vehicles']);
        Permission::create(['name' => 'delete vehicles']);

        // Criar permissões - equipamentos
        Permission::create(['name' => 'view devices']);
        Permission::create(['name' => 'create devices']);
        Permission::create(['name' => 'update devices']);
        Permission::create(['name' => 'delete devices']);

        // Criar permissões - integrações
        Permission::create(['name' => 'view integrations']);
        Permission::create(['name' => 'manage integrations']);

        // Criar permissões - usuários
        Permission::create(['name' => 'view users']);
        Permission::create(['name' => 'create users']);
        Permission::create(['name' => 'update users']);
        Permission::create(['name' => 'delete users']);

        // Criar roles
        $adminRole = Role::create(['name' => 'admin']);
        $tecnicoRole = Role::create(['name' => 'tecnico']);
        $clienteRole = Role::create(['name' => 'cliente']);

        // Atribuir permissões às roles

        // Admin tem acesso a tudo
        $adminRole->givePermissionTo(Permission::all());

        // Técnico tem acesso a clientes, veículos e equipamentos
        $tecnicoRole->givePermissionTo([
            'view customers', 'create customers', 'update customers',
            'view vehicles', 'create vehicles', 'update vehicles',
            'view devices', 'create devices', 'update devices',
            'view integrations',
        ]);

        // Cliente só tem acesso a visualizar seus próprios dados
        $clienteRole->givePermissionTo([
            'view customers',
            'view vehicles',
        ]);

        // Criar um usuário admin padrão se não existir
        $admin = User::where('email', 'admin@example.com')->first();

        if (! $admin) {
            $admin = User::create([
                'name' => 'Admin',
                'email' => 'admin@example.com',
                'password' => bcrypt('admin'),
                'type' => 'admin',
            ]);
        }

        $admin->assignRole('admin');
    }
}
