<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Repositories\Interfaces\ClientRepositoryInterface;
use App\Repositories\Eloquent\ClientRepository;
use App\Repositories\Interfaces\ProjectRepositoryInterface;
use App\Repositories\Eloquent\ProjectRepository;
use App\Repositories\Interfaces\InvoiceRepositoryInterface;
use App\Repositories\Eloquent\InvoiceRepository;
use App\Repositories\Interfaces\ServerRepositoryInterface;
use App\Repositories\Eloquent\ServerRepository;
use App\Repositories\Interfaces\ClientHostingRepositoryInterface;
use App\Repositories\Eloquent\ClientHostingRepository;
use App\Repositories\Interfaces\CurrencyRepositoryInterface;
use App\Repositories\Eloquent\CurrencyRepository;

class RepositoryServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(ClientRepositoryInterface::class, ClientRepository::class);
        $this->app->bind(ProjectRepositoryInterface::class, ProjectRepository::class);
        $this->app->bind(InvoiceRepositoryInterface::class, InvoiceRepository::class);
        $this->app->bind(ServerRepositoryInterface::class, ServerRepository::class);
        $this->app->bind(ClientHostingRepositoryInterface::class, ClientHostingRepository::class);
        $this->app->bind(CurrencyRepositoryInterface::class, CurrencyRepository::class);
    }

    public function boot(): void
    {
        //
    }
}
