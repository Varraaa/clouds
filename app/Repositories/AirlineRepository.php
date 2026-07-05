<?php

namespace App\Repositories;

use App\Interface\AirlineRepositoryInterface;
use App\Models\Airline;

class AirlineRepository implements AirlineRepositoryInterface
{
    public function getAllAirline()
    {
        return Airline::all();
    }
}