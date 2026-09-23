<?php

namespace App\Repositories\Attachments;

use App\Models\Attachment;

class EloquentAttachmentRepository implements AttachmentRepository
{
    public function create(array $attributes): Attachment
    {
        return Attachment::query()->create($attributes);
    }
}
