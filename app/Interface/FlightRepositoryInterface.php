<?php

namespace App\Interface;

interface FlightRepositoryInterface 
{
    public function getAllFlight($filter = null);

    public function getFlighhtByFlightNumber($FlightNumber);
}



