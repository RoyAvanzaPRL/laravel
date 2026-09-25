<?php

namespace App\Providers;

use App\Repositories\Attachments\AttachmentRepository;
use App\Repositories\Attachments\EloquentAttachmentRepository;
use App\Repositories\Comments\CommentRepository;
use App\Repositories\Comments\EloquentCommentRepository;
use App\Repositories\Tickets\EloquentTicketRepository;
use App\Repositories\Tickets\TicketRepository;
use App\Repositories\Users\EloquentUserRepository;
use App\Repositories\Users\UserRepository;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(TicketRepository::class, EloquentTicketRepository::class);
        $this->app->bind(UserRepository::class, EloquentUserRepository::class);
        $this->app->bind(CommentRepository::class, EloquentCommentRepository::class);
        $this->app->bind(AttachmentRepository::class, EloquentAttachmentRepository::class);
    }
}
