<?php

namespace Database\Factories;

use App\Models\Attachment;
use App\Models\Comment;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Attachment>
 */
class AttachmentFactory extends Factory
{
    public function definition(): array
    {
        $name = fake()->word().'.pdf';

        return [
            'ticket_id' => Ticket::factory(),
            'comment_id' => null,
            'user_id' => User::factory(),
            'original_name' => $name,
            'path' => 'attachments/'.$name,
            'mime' => 'application/pdf',
            'size' => fake()->numberBetween(1_000, 500_000),
        ];
    }

    public function forComment(Comment $comment): static
    {
        return $this->state(fn () => [
            'ticket_id' => $comment->ticket_id,
            'comment_id' => $comment->id,
        ]);
    }
}