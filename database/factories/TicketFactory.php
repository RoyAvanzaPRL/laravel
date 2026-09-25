<?php

namespace Database\Factories;

use App\Enums\TicketStatus;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Ticket>
 */
class TicketFactory extends Factory
{
    public function definition(): array
    {
        return [
            'creator_id' => User::factory(),
            'assignee_id' => null,
            'title' => fake()->sentence(6),
            'body' => fake()->paragraph(),
            'status' => TicketStatus::Open,
            'resolved_at' => null,
            'closed_at' => null,
            'last_activity_at' => now(),
        ];
    }

    public function forCreator(User $user): static
    {
        return $this->state(fn () => [
            'creator_id' => $user->id,
        ]);
    }

    public function assigned(?User $user = null): static
    {
        return $this->state(fn () => [
            'assignee_id' => $user?->id ?? User::factory(),
        ]);
    }

    public function assignedTo(User $user): static
    {
        return $this->assigned($user);
    }

    public function inProgress(): static
    {
        return $this->state(fn () => [
            'status' => TicketStatus::InProgress,
            'assignee_id' => User::factory(),
            'resolved_at' => null,
            'closed_at' => null,
        ]);
    }

    public function resolved(): static
    {
        return $this->state(fn () => [
            'status' => TicketStatus::Resolved,
            'assignee_id' => User::factory(),
            'resolved_at' => now(),
            'closed_at' => null,
        ]);
    }

    public function closed(): static
    {
        return $this->state(fn () => [
            'status' => TicketStatus::Closed,
            'closed_at' => now(),
        ]);
    }
}