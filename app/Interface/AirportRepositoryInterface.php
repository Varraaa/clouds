<?php

namespace App\Interface;

interface AirportRepositoryInterface 
{
    public function getAllAirport();

    public function getAirportBySlug($slug);

    public function getAirportByIataCode($iataCode);
}



