<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class ListUsersWithRoles extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'users:list';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Lista todos los usuarios con sus roles asignados';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $users = User::with('role')->get();

        if ($users->isEmpty()) {
            $this->warn('No hay usuarios en el sistema.');
            return Command::SUCCESS;
        }

        $this->info('=== Usuarios en el Sistema ===');
        $this->newLine();

        $tableData = [];
        foreach ($users as $user) {
            $tableData[] = [
                'ID' => $user->id,
                'Nombre' => $user->name,
                'Email' => $user->email,
                'Rol' => $user->role?->display_name ?? 'Sin rol',
                'Es Admin' => $user->isAdmin() ? '✓' : '✗',
                'Creado' => $user->created_at->format('Y-m-d H:i'),
            ];
        }

        $this->table(
            ['ID', 'Nombre', 'Email', 'Rol', 'Es Admin', 'Creado'],
            $tableData
        );

        $this->newLine();
        $this->info('Total de usuarios: ' . $users->count());

        return Command::SUCCESS;
    }
}
