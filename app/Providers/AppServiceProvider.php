<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Repositories\Tickets\EloquentTicketRepository;
use App\Repositories\Tickets\TicketRepository;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(TicketRepository::class, EloquentTicketRepository::class);
    }
}