<?php

namespace App\Repositories;

use App\Interface\TransactionRepositoryInterface;
use App\Models\Transaction;
use App\Models\TransactionPassengers;
use App\Models\FlightSeat;
use App\Models\FlightClass;
use App\Models\PromoCode;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class TransactionRepository implements TransactionRepositoryInterface
{
    protected $sessionKey = 'transaction_data';

    public function getTransactionDataFromSession()
    {
        return Session::get($this->sessionKey);
    }

    public function saveTransactionDataToSession($data)
    {
        Session::put($this->sessionKey, $data);

        return $data;
    }

    public function checkPromoCode($code)
    {
        if (empty($code)) {
            return null;
        }

        return PromoCode::where('code', $code)
            ->where('is_used', false)
            ->where('valid_until', '>=', Carbon::now())
            ->first();
    }

    private function generateTransactionCode()
    {
        return "BWAGARUDA" . rand(1000, 9999);
    }

    protected function calculateDiscount($subtotal, PromoCode $promo)
    {
        if ($promo->discount_type === 'percentage') {
            $discount = ($promo->discount / 100) * $subtotal;
        } else {
            $discount = $promo->discount;
        }

        return min($discount, $subtotal);
    }

    public function saveTransaction($data)
    {
        return DB::transaction(function () use ($data) {
            $numberOfPassengers = count($data['passengers'] ?? []);

            $flightClass = FlightClass::findOrFail($data['flight_class_id']);

            $subtotal = $flightClass->price * $numberOfPassengers;

            $grandtotal = $subtotal;
            $promo = null;

            if (!empty($data['promo_code'])) {
                $promo = $this->checkPromoCode($data['promo_code']);

                if ($promo) {
                    $discount = $this->calculateDiscount($subtotal, $promo);
                    $grandtotal = $subtotal - $discount;
                }
            }

            $code = $this->generateTransactionCode();

            $transaction = Transaction::create([
                'code' => $code,
                'flight_id' => $data['flight_id'],
                'flight_class_id' => $data['flight_class_id'],
                'name' => $data['name'],
                'email' => $data['email'],
                'phone' => $data['phone'],
                'number_of_passengers' => $numberOfPassengers,
                'promo_code_id' => $promo?->id,
                'payment_status' => $data['payment_status'] ?? 'pending',
                'subtotal' => $subtotal,
                'grandtotal' => $grandtotal,
            ]);

            if (!empty($data['passengers'])) {
                foreach ($data['passengers'] as $passenger) {
                    TransactionPassengers::create([
                        'transaction_id' => $transaction->id,
                        'flight_seat_id' => $passenger['flight_seat_id'],
                        'date_of_birth' => $passenger['date_of_birth'],
                        'nationality' => $passenger['nationality'],
                    ]);

                    FlightSeat::where('id', $passenger['flight_seat_id'])
                        ->update(['is_available' => false]);
                }
            }

            if ($promo) {
                $promo->update(['is_used' => true]);
            }

            Session::forget($this->sessionKey);

            return $transaction->load('passengers', 'flight', 'class', 'promo');
        });
    }

    public function getTransactionByCode($code)
    {
        return Transaction::with(['flight.airline', 'flight.segments.airport', 'class', 'passengers.seat', 'promo'])
            ->where('code', $code)
            ->first();
    }

    public function getTransactionByCodeEmailPhone($code, $email, $phone)
    {
        return Transaction::with(['flight.airline', 'flight.segments.airport', 'class', 'passengers.seat', 'promo'])
            ->where('code', $code)
            ->where('email', $email)
            ->where('phone', $phone)
            ->first();
    }
}