<?php

namespace Database\Factories;

use App\Models\Organization;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Ticket>
 */
class TicketFactory extends Factory
{
    protected $model = Ticket::class;

    public function definition(): array
    {
        return [
            'organization_id' => Organization::factory(),
            'created_by' => function (array $attributes) {
                return User::factory()->create([
                    'organization_id' => $attributes['organization_id'],
                ])->id;
            },
            'title' => $this->faker->sentence(5),
            'description' => $this->faker->paragraphs(2, true),
            'priority' => $this->faker->randomElement(Ticket::PRIORITIES),
            'status' => $this->faker->randomElement(Ticket::STATUSES),
        ];
    }
}
