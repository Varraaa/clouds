<?php

namespace App\Http\Controllers;

use App\Interface\AirlineRepositoryInterface;
use App\Interface\AirportRepositoryInterface;
use App\Interface\FlightRepositoryInterface;
use Illuminate\Http\Request;

class FlightController extends Controller
{
    private AirportRepositoryInterface $airportRepository;
    private AirlineRepositoryInterface $airlineRepository;
    private FlightRepositoryInterface $flightRepository;

    public function __construct(
        AirportRepositoryInterface $airportRepository,
        AirlineRepositoryInterface $airlineRepository, 
        FlightRepositoryInterface $flightRepository
    )
    {
        $this->airportRepository = $airportRepository;
        $this->airlineRepository = $airlineRepository;
        $this->flightRepository = $flightRepository;
     
    }
    
    public function index (Request $request)
    {

        $departure = $this->airportRepository->getAirportByIataCode($request->departure);
        $arrival = $this->airportRepository->getAirportByIataCode($request->arrival);

        $flights = $this->flightRepository->getAllFlights([
            'departure' => $departure->id ?? null,
            'arrival' => $arriaval->id ?? null,
            'date' => $request->id ?? null,
        ]);

        $airline = $this->airlineRepository->getAllAirline();

        return view('pages.flight.index', compact ('flights', 'airline'));
    }

    public function show(Request $request, $flightNumber)
    {
        $flight = $this->flightRepository->getFlightByFlightNumber($flightNumber);
        $quantity = $request->query('quantity', 1);

        return view('pages.flight.show', compact('flight', 'quantity'));
    }
}
