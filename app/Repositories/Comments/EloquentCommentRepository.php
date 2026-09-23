<?php

namespace App\Repositories\Comments;

use App\Models\Comment;

class EloquentCommentRepository implements CommentRepository
{
    public function create(array $attributes): Comment
    {
        return Comment::query()->create($attributes);
    }
}
