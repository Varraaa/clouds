<?php

namespace App\Http\Controllers;

class BookingController extends Controller
{
    public function checkBooking() 
    {
        return view('pages.booking.check-booking');
    }
}
