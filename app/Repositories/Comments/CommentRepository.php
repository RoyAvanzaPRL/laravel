<?php

namespace App\Repositories\Comments;

use App\Models\Comment;

interface CommentRepository
{
    /**
     * @param  array<string, mixed>  $attributes
     */
    public function create(array $attributes): Comment;
}
