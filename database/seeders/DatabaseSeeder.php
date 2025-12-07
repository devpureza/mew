<?php

namespace Database\Seeders;

use App\Models\Guest;
use App\Models\User;
use App\Models\Wedding;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $superAdmin = User::factory()->superAdmin()->create([
            'name' => 'Super Admin MEW',
            'email' => 'superadmin@mew.test',
            'cpf' => '99999999999',
        ]);

        $admin = User::factory()->admin()->create([
            'name' => 'Admin MEW',
            'email' => 'admin@mew.test',
            'cpf' => '88888888888',
        ]);

        $coupleOne = User::factory()->couple()->create([
            'name' => 'Casal MEW 01',
            'email' => 'casal01@mew.test',
            'cpf' => '77777777777',
        ]);

        $coupleTwo = User::factory()->couple()->create([
            'name' => 'Casal MEW 02',
            'email' => 'casal02@mew.test',
            'cpf' => '66666666666',
        ]);

        $wedding = Wedding::create([
            'title' => 'Casamento Praia do Amanhecer',
            'event_date' => now()->addMonths(6)->toDateString(),
            'location' => 'Praia do Forte, BA',
            'location_details' => 'Cerimônia ao pôr do sol, salão Vista Mar',
            'notes' => 'Evento de demonstração para o CMS MEW.',
        ]);

        $wedding->couples()->attach([
            $coupleOne->id => ['role' => 'couple', 'is_primary' => true],
            $coupleTwo->id => ['role' => 'couple', 'is_primary' => false],
        ]);

        $guestParent = Guest::create([
            'wedding_id' => $wedding->id,
            'invited_by_user_id' => $coupleOne->id,
            'name' => 'João Silva',
            'cpf' => '55555555555',
            'status' => 'pending',
            'is_head_of_family' => true,
            'party_size' => 3,
            'notes' => 'Prefere mesa próxima à pista.',
        ]);

        Guest::create([
            'wedding_id' => $wedding->id,
            'invited_by_user_id' => $coupleOne->id,
            'parent_guest_id' => $guestParent->id,
            'name' => 'Ana Silva',
            'status' => 'pending',
            'is_head_of_family' => false,
        ]);

        Guest::create([
            'wedding_id' => $wedding->id,
            'invited_by_user_id' => $coupleOne->id,
            'parent_guest_id' => $guestParent->id,
            'name' => 'Pedro Silva',
            'status' => 'pending',
            'is_head_of_family' => false,
        ]);
    }
}
