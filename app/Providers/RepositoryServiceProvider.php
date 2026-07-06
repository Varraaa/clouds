<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Interface\AirlineRepositoryInterface;
use App\Repositories\AirlineRepository;
use App\Interface\AirportRepositoryInterface;
use App\Repositories\AirportRepository;
use App\Interface\FlightRepositoryInterface;
use App\Repositories\FlightRepository;
use App\Interface\TransactionRepositoryInterface;
use App\Repositories\TransactionRepository;

// DISINI MELAKUKAN PENDAFTARAN UNTUK INTERFACE SERTA REPOSITORY YANG YANG SUDAH DI BUAT

class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->bind(AirlineRepositoryInterface::class, AirlineRepository::class);
        $this->app->bind(AirportRepositoryInterface::class, AirportRepository::class);
        $this->app->bind(FlightRepositoryInterface::class, FlightRepository::class);
        $this->app->bind(TransactionRepositoryInterface::class, TransactionRepository::class);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
