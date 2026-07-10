<?php

namespace App\Http\Controllers;
use App\Http\Controllers\Controller;
use App\Interface\AirportRepositoryInterface;

class HomeController extends Controller
{
    private AirportRepositoryInterface $airportRepository;

    public function __construct(AirportRepositoryInterface $airportRepository)
    {
        $this->airportRepository = $airportRepository;
     
    }
    public function index ()
    {
        $airports = $this->airportRepository->getAllAirport();
        return view('pages.home', compact('airports'));
    }
}
