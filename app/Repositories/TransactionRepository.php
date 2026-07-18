<?php

namespace App\Repositories; // 1. Fix namespace sesuai folder asli project kamu

use App\Interface\TransactionRepositoryInterface;
use App\Models\Transaction;
use App\Models\FlightClass;
use App\Models\PromoCode;
use App\Models\TransactionPassengers;
use Illuminate\Support\Facades\DB; // Ditambahkan untuk mengaktifkan database transaction aman

class TransactionRepository implements TransactionRepositoryInterface
{
    public function getTransactionDataFromSession()
    {
        return session()->get('transaction');
    }

    public function saveTransactionDataToSession($data)
    {
        $transaction = session()->get('transaction', []); // 2. Fix typo $trasnsaction -> $transaction

        foreach ($data as $key => $value) {
            $transaction[$key] = $value;
        }

        session()->put('transaction', $transaction);
    }

    public function saveTransaction($data)
    {
        return DB::transaction(function () use ($data) {
            $data['code'] = $this->generateTransactionCode();
            $data['number_of_passengers'] = $this->countPassengers($data['passengers']);

            $flightClass = FlightClass::findOrFail($data['flight_class_id']);
            $data['flight_id'] = $data['flight_id'] ?? $flightClass->flight_id;

            // SUBTOTAL & TAX — tax dihitung dari subtotal SEBELUM diskon (samain kayak checkout page)
            $data['subtotal'] = $flightClass->price * $data['number_of_passengers'];
            $tax = $data['subtotal'] * 0.11;

            // DISKON (kalau ada promo) — dihitung dari subtotal, bukan dari grandtotal
            $data['discount'] = 0;
            if (!empty($data['promo_code'])) {
                $data = $this->applyPromoCode($data);
            }

            // GRAND TOTAL = subtotal - discount + tax
            $data['grandtotal'] = $data['subtotal'] - $data['discount'] + $tax;

            $transaction = $this->createTransaction($data);
            $this->savePassengers($data['passengers'], $transaction->id);

            session()->forget('transaction');

            return $transaction;
        });
    }

    private function applyPromoCode($data)
    {
        $promo = PromoCode::where('code', $data['promo_code'])
            ->where('valid_until', '>=', now())
            ->first();

        if ($promo) {
            if ($promo->discount_type === 'percentage') {
                // FIX: persentase dihitung dari subtotal, bukan dari grandtotal (yang tadinya masih ambigu isinya apa)
                $data['discount'] = $data['subtotal'] * ($promo->discount / 100);
            } else {
                $data['discount'] = $promo->discount;
            }

            $data['promo_code_id'] = $promo->id;
        }

        return $data;
    }

    private function generateTransactionCode()
    {
        return "CLOUDS_AIR" . rand(1000, 9999);
    }

    private function countPassengers($passengers)
    {
        return count($passengers);
    }

    private function calculateSubtotal($flightClassId, $numberOfPassengers)
    {
        $price = FlightClass::findOrFail($flightClassId)->price;
        return $price * $numberOfPassengers;
    }

    private function addPPN($grandtotal) // 8. Disamakan menjadi lowercase agar konsisten dengan key array
    {
        $ppn = $grandtotal * 0.11;
        return $grandtotal + $ppn;
    }

    private function createTransaction($data)
    {
        // 4. Di-filter menggunakan hanya kolom yang ada di tabel transactions agar tidak crash SQL
        return Transaction::create([
            'code' => $data['code'],
            'flight_id' => $data['flight_id'],
            'flight_class_id' => $data['flight_class_id'],
            'promo_code_id' => $data['promo_code_id'] ?? null,
            'number_of_passengers' => $data['number_of_passengers'],
            'subtotal' => $data['subtotal'],
            'discount' => $data['discount'] ?? 0,
            'grandtotal' => $data['grandtotal'],
            'payment_status' => 'pending',
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'],
        ]);
    }


    public function savePassengers($passengers, $transactionId)
    {
        $transaction = \App\Models\Transaction::find($transactionId);

        $sessionData = session()->get('transaction', []);
        $selectedSeats = array_values($sessionData['selected_seats'] ?? []);

        $fallbackSeat = \App\Models\FlightSeat::where('flight_id', $transaction->flight_id)
            ->where('is_available', true)
            ->orderBy('id')
            ->first();

        $seatIdsToMarkBooked = [];

        foreach ($passengers as $index => $passenger) {
            $seatId = $selectedSeats[$index] ?? ($fallbackSeat->id ?? null);
            $dateOfBirth = $this->resolveDateOfBirth($passenger);

            TransactionPassengers::create([
                'transaction_id'  => $transactionId,
                'name'            => $passenger['name'],
                'identity_number' => $passenger['identity_number'] ?? null,
                'nationality'     => $passenger['nationality'] ?? 'Indonesia',
                'flight_seat_id'  => $seatId,
                'date_of_birth'   => $dateOfBirth,
            ]);

            if ($seatId) {
                $seatIdsToMarkBooked[] = $seatId;
            }
        }

        // FIX BUG #1: tandai kursi yang baru dipesan jadi tidak tersedia lagi
        if (!empty($seatIdsToMarkBooked)) {
            \App\Models\FlightSeat::whereIn('id', $seatIdsToMarkBooked)
                ->update(['is_available' => false]);
        }
    }

    /**
     * Coba beberapa kemungkinan format field date of birth dari form,
     * karena bisa jadi dikirim sebagai string tunggal atau array day/month/year.
     */
    private function resolveDateOfBirth($passenger)
    {
        // Format 1: sudah string date lengkap, misal "2007-12-20"
        if (!empty($passenger['date_of_birth']) && is_string($passenger['date_of_birth'])) {
            return $passenger['date_of_birth'];
        }
        if (!empty($passenger['dob']) && is_string($passenger['dob'])) {
            return $passenger['dob'];
        }

        // Format 2: array day/month/year terpisah (sesuai tampilan 3 kolom di form)
        $day   = $passenger['dob']['day']
            ?? $passenger['date_of_birth']['day']
            ?? $passenger['dob_day']
            ?? null;

        $month = $passenger['dob']['month']
            ?? $passenger['date_of_birth']['month']
            ?? $passenger['dob_month']
            ?? null;

        $year  = $passenger['dob']['year']
            ?? $passenger['date_of_birth']['year']
            ?? $passenger['dob_year']
            ?? null;

        if ($day && $month && $year) {
            return sprintf('%04d-%02d-%02d', $year, $month, $day);
        }

        // fallback terakhir kalau format di atas semua gak ketemu
        return now()->format('Y-m-d');
    }



    public function getTransactionByCode($code)
    {
        return Transaction::where('code', $code)->first();
    }

    public function getTransactionByCodePhone($code, $phone)
    {
        return Transaction::where('code', $code)
            ->where('phone', $phone)
            ->first();
    }
}
