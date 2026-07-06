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
        // 5. Dibungkus DB::transaction agar proses multi-insert aman dari data korup
        return DB::transaction(function () use ($data) {
            $data['code'] = $this->generateTransactionCode();
            $data['number_of_passengers'] = $this->countPassengers($data['passengers']);

            // AMBIL DATA FLIGHT CLASS UNTUK MENDAPATKAN FLIGHT ID DAN HARGA
            $flightClass = FlightClass::findOrFail($data['flight_class_id']);

            // PASTIKAN FLIGHT ID TERISI AGAR TIDAK EROR DI DATABASE
            $data['flight_id'] = $data['flight_id'] ?? $flightClass->flight_id;

            // HITUNG SUBTOTAL DAN GRAND TOTAL AWAL
            $data['subtotal'] = $flightClass->price * $data['number_of_passengers'];
            $data['grandtotal'] = $data['subtotal'];

            // TERAPKAN PROMO JIKA ADA
            if (!empty($data['promo_code'])) {
                $data = $this->applyPromoCode($data);
            }

            // TAMBAHKAN PPN
            $data['grandtotal'] = $this->addPPN($data['grandtotal']);

            // SIMPAN TRANSAKSI DAN PENUMPANG
            $transaction = $this->createTransaction($data);
            $this->savePassengers($data['passengers'], $transaction->id);

            session()->forget('transaction');

            return $transaction;
        });
    }

    private function generateTransactionCode()
    {
        return "BWACLOUDS" . rand(1000, 9999);
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

    private function applyPromoCode($data)
    {
        $promo = PromoCode::where('code', $data['promo_code'])
            ->where('valid_until', '>=', now())
            ->where('is_used', false)
            ->first();

        if ($promo) {
            if ($promo->discount_type === 'percentage') {
                $data['discount'] = $data['grandtotal'] * ($promo->discount / 100);
            } else {
                $data['discount'] = $promo->discount;
            }

            $data['grandtotal'] -= $data['discount'];
            $data['promo_code_id'] = $promo->id;

            // TANDAI PROMO CODE SEBAGAI SUDAH DIGUNAKAN
            $promo->update(['is_used' => true]);
        }

        return $data;
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
        ]);
    }

    private function savePassengers($passengers, $transactionId)
    {
        // 7. Proses looping dibuat lebih clean & safe demi menghindari eror array offset
        foreach ($passengers as $passenger) {
            TransactionPassengers::create([
                'transaction_id' => $transactionId,
                'name' => $passenger['name'],
                'identity_number' => $passenger['identity_number'] ?? null,
            ]);
        }
    }

    public function getTransactionByCode($code) // 1. Fix 'pulic' -> 'public'
    {
        return Transaction::where('code', $code)->first();
    }

    public function getTransactionByCodeEmailPhone($code, $email, $phone)
    {
        return Transaction::where('code', $code)
            ->where('email', $email)
            ->where('phone_number', $phone)
            ->first();
    }
}
