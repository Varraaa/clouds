<?php

namespace App\Http\Controllers;

use App\Http\Requests\BookingShowRequest;
use App\Http\Requests\StorePassengerDetailRequest;
use App\Interface\FlightRepositoryInterface;
use App\Interface\TransactionRepositoryInterface;
use App\Mail\TransactionSuccessMail; // Import Class Mail
use Barryvdh\DomPDF\Facade\Pdf; // Import PDF Facade
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail; // Import Support Mail

class BookingController extends Controller
{
    private FlightRepositoryInterface $flightRepository;
    private TransactionRepositoryInterface $transactionRepository;

    public function __construct(
        FlightRepositoryInterface $flightRepository,
        TransactionRepositoryInterface $transactionRepository
    ) {
        $this->flightRepository = $flightRepository;
        $this->transactionRepository = $transactionRepository;
    }

    public function booking(Request $request, $flightNumber)
    {
        $this->transactionRepository->saveTransactionDataToSession(array_merge(
            $request->all(),
            ['quantity' => $request->query('quantity', 1)]
        ));

        return redirect()->route('booking.chooseSeat', ['flightNumber' => $flightNumber]);
    }

    public function chooseSeat(Request $request, $flightNumber)
    {
        $transaction = $this->transactionRepository->getTransactionDataFromSession();
        $flight = $this->flightRepository->getFlightByFlightNumber($flightNumber);
        $tier = $flight->classes->find($transaction['flight_class_id']);

        return view('pages.booking.choose-seat', compact('transaction', 'flight', 'tier'));
    }

    public function confirmSeat(Request $request, $flightNumber)
    {
        $this->transactionRepository->saveTransactionDataToSession($request->all());

        return redirect()->route('booking.passengerDetails', ['flightNumber' => $flightNumber]);
    }

    public function passengerDetails(Request $request, $flightNumber)
    {
        $transaction = $this->transactionRepository->getTransactionDataFromSession();
        $flight = $this->flightRepository->getFlightByFlightNumber($flightNumber);
        $tier = $flight->classes->find($transaction['flight_class_id']);

        return view('pages.booking.passenger-details', compact('transaction', 'flight', 'tier'));
    }

    public function savePassengerDetails(StorePassengerDetailRequest $request, $flightNumber)
    {
        $this->transactionRepository->saveTransactionDataToSession($request->all());

        return redirect()->route('booking.checkout', ['flightNumber' => $flightNumber]);
    }

    public function checkout($flightNumber)
    {
        $transaction = $this->transactionRepository->getTransactionDataFromSession();
        $flight = $this->flightRepository->getFlightByFlightNumber($flightNumber);
        $tier = $flight->classes->find($transaction['flight_class_id']);

        return view('pages.booking.checkout', compact('transaction', 'flight', 'tier'));
    }

    public function payment(Request $request)
    {
        $this->transactionRepository->saveTransactionDataToSession($request->all());

        $transaction = $this->transactionRepository->saveTransaction($this->transactionRepository->getTransactionDataFromSession());

        // Set your Merchant Server Key
        \Midtrans\Config::$serverKey = config('midtrans.serverKey');
        // Set to Development/Sandbox Environment (default)
        \Midtrans\Config::$isProduction = config('midtrans.isProduction');
        // Set sanitization on (default)
        \Midtrans\Config::$isSanitized = config('midtrans.isSanitized');
        // Set 3DS transaction for credit card to true
        \Midtrans\Config::$is3ds = config('midtrans.is3ds');

        $params = [
            'transaction_details' => [
                'order_id' => $transaction->code,
                'gross_amount' => $transaction->grandtotal,
            ],

            'callbacks' => [
                'finish' => route('booking.success'),
            ],
        ];

        $paymentUrl = \Midtrans\Snap::createTransaction($params)->redirect_url;

        return redirect($paymentUrl);
    }

    public function success(Request $request)
    {
        $transaction = $this->transactionRepository->getTransactionByCode($request->order_id);

        if (!$transaction) {
            return redirect()->route('home');
        }

        // ==========================================
        // FITUR KIRIM EMAIL KE MAILTRAP (TAMBAHAN)
        // ==========================================
        if (!empty($transaction->email)) {
            // Jeda 1 detik agar tidak kena rate limit Mailtrap
            sleep(1);

            Mail::to($transaction->email)->send(new TransactionSuccessMail($transaction));
        }

        return view('pages.booking.success', compact('transaction'));
    }

    public function checkBooking()
    {
        return view('pages.booking.check-booking');
    }

    public function show(BookingShowRequest $request)
    {
        $transaction = $this->transactionRepository->getTransactionByCodePhone($request->code, $request->phone);

        if (!$transaction) {
            return redirect()->back()->with('error', 'Data Transaksi Tidak Ditemukan');
        }

        return view('pages.booking.detail', compact('transaction'));
    }

    // ==========================================
    // FITUR DOWNLOAD PDF (TAMBAHAN)
    // ==========================================
    public function downloadPDF($id)
    {
        $transaction = $this->transactionRepository->getTransactionByCode($id);

        if (!$transaction) {
            return redirect()->back()->with('error', 'Transaksi tidak ditemukan');
        }

        $pdf = Pdf::loadView('pdf.boarding-pass', compact('transaction')); //agar file pdf bisa terdonload

        return $pdf->download('BoardingPass-' . $transaction->code . '.pdf'); //agar file pdf bisa terdonload
    }
}