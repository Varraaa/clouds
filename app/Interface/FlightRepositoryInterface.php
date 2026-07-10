<?php

namespace App\Interface;


interface FlightRepositoryInterface 
{
    public function getAllFlights($filter = null);

    public function getFlightByFlightNumber($FlightNumber);
}



