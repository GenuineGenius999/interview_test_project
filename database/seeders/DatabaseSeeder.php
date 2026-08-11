<?php

namespace Database\Seeders;

use App\Models\Organization;
use App\Models\Ticket;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $baseline = CarbonImmutable::parse('2026-01-05 09:00:00');

        $northwind = Organization::factory()->create([
            'name' => 'Northwind Services',
            'created_at' => $baseline,
            'updated_at' => $baseline,
        ]);

        $contoso = Organization::factory()->create([
            'name' => 'Contoso Operations',
            'created_at' => $baseline,
            'updated_at' => $baseline,
        ]);

        $northwindUsers = [
            User::factory()->manager()->for($northwind)->create([
                'name' => 'Morgan North',
                'email' => 'manager@northwind.test',
                'password' => 'password',
                'created_at' => $baseline,
                'updated_at' => $baseline,
            ]),
            User::factory()->for($northwind)->create([
                'name' => 'Taylor North',
                'email' => 'member@northwind.test',
                'password' => 'password',
                'created_at' => $baseline,
                'updated_at' => $baseline,
            ]),
        ];

        $contosoUsers = [
            User::factory()->manager()->for($contoso)->create([
                'name' => 'Casey Contoso',
                'email' => 'manager@contoso.test',
                'password' => 'password',
                'created_at' => $baseline,
                'updated_at' => $baseline,
            ]),
            User::factory()->for($contoso)->create([
                'name' => 'Riley Contoso',
                'email' => 'member@contoso.test',
                'password' => 'password',
                'created_at' => $baseline,
                'updated_at' => $baseline,
            ]),
        ];

        $this->seedTickets($northwind, $northwindUsers, 'Northwind', $baseline);
        $this->seedTickets($contoso, $contosoUsers, 'Contoso', $baseline->addHour());
    }

    /**
     * @param  array<int, User>  $users
     */
    private function seedTickets(
        Organization $organization,
        array $users,
        string $label,
        CarbonImmutable $baseline
    ): void {
        $statuses = Ticket::STATUSES;
        $priorities = Ticket::PRIORITIES;

        for ($number = 1; $number <= 15; $number++) {
            $timestamp = $baseline->addDays($number);

            Ticket::factory()
                ->for($organization)
                ->for($users[($number - 1) % count($users)], 'creator')
                ->create([
                    'title' => sprintf('%s service request %02d', $label, $number),
                    'description' => sprintf(
                        'Fictional %s ticket %02d for search and filtering exercises.',
                        $label,
                        $number
                    ),
                    'priority' => $priorities[($number - 1) % count($priorities)],
                    'status' => $statuses[($number - 1) % count($statuses)],
                    'created_at' => $timestamp,
                    'updated_at' => $timestamp,
                ]);
        }
    }
}
