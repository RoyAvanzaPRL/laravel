<?php

namespace App\Repositories\Attachments;

use App\Models\Attachment;

interface AttachmentRepository
{
    /**
     * @param  array<string, mixed>  $attributes
     */
    public function create(array $attributes): Attachment;
}
