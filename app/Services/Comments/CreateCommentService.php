<?php

namespace App\Services\Comments;

use App\Models\Comment;
use App\Models\Ticket;
use App\Models\User;
use App\Repositories\Attachments\AttachmentRepository;
use App\Repositories\Comments\CommentRepository;
use Illuminate\Http\UploadedFile;

class CreateCommentService
{
    public function __construct(
        private CommentRepository $comments,
        private AttachmentRepository $attachments,
    ) {}

    public function handle(Ticket $ticket, User $user, string $body, ?UploadedFile $file = null): Comment
    {
        $comment = $this->comments->create([
            'ticket_id' => $ticket->id,
            'user_id' => $user->id,
            'body' => $body,
        ]);

        if ($file !== null) {
            $path = $file->store('attachments/'.$ticket->id, 'local');

            $this->attachments->create([
                'ticket_id' => $ticket->id,
                'comment_id' => $comment->id,
                'user_id' => $user->id,
                'original_name' => $file->getClientOriginalName(),
                'path' => $path,
                'mime' => $file->getClientMimeType(),
                'size' => $file->getSize(),
            ]);
        }

        return $comment->load(['user', 'attachments']);
    }
}
