<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Boarding Pass - {{ $transaction->code }}</title>
    <style>
        /* CSS Compatible with DomPDF */
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #ffffff;
        }

        .boarding-pass {
            max-width: 700px;
            margin: 0 auto;
            border: 1px solid #0056b3;
            padding: 20px;
            background-color: #f8f9fa;
        }

        .header-flight-info {
            width: 100%;
            background-color: #0056b3;
            color: white;
            padding: 15px;
            border-spacing: 0;
        }

        .header-flight-info td {
            vertical-align: top;
            width: 50%;
        }

        .from,
        .to {
            font-size: 16px;
        }

        .airport-title {
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 1px;
            opacity: 0.8;
        }

        .airport-name {
            font-size: 20px;
            font-weight: bold;
            margin: 5px 0;
        }

        .flight-time {
            font-size: 13px;
        }

        .details-table {
            width: 100%;
            margin-top: 20px;
            border-collapse: collapse;
        }

        .details-table td {
            width: 50%;
            vertical-align: top;
            font-size: 14px;
            color: #333;
        }

        .info-item {
            font-size: 15px;
            margin-bottom: 10px;
            color: #333;
        }

        .seats-list {
            margin: 5px 0 0 0;
            padding-left: 20px;
        }

        .seats-list li {
            margin-bottom: 4px;
        }

        .barcode {
            text-align: center;
            margin-top: 25px;
        }

        img.qr-code {
            width: 130px;
            height: 130px;
        }

        .footer {
            margin-top: 20px;
            border-top: 1px dashed #ccc;
            padding-top: 10px;
            text-align: center;
            font-size: 13px;
            color: #666;
        }
    </style>
</head>

<body>
    <div class="boarding-pass">
        <!-- Header Flight Info Table -->
        <table class="header-flight-info">
            <tr>
                <td class="from">
                    <div class="airport-title">FROM:</div>
                    <div class="airport-name">
                        {{ $transaction->flight->segments->first()->airport->name ?? $transaction->flight->segments->first()->name }}
                    </div>
                    <div class="flight-time">
                        {{ \Carbon\Carbon::parse($transaction->flight->segments->first()->time)->format('d F Y, H:i A') }}
                    </div>
                </td>
                <td class="to" style="text-align: right;">
                    <div class="airport-title">TO:</div>
                    <div class="airport-name">
                        {{ $transaction->flight->segments->last()->airport->name ?? $transaction->flight->segments->last()->name }}
                    </div>
                    <div class="flight-time">
                        {{ \Carbon\Carbon::parse($transaction->flight->segments->last()->time)->format('d F Y, H:i A') }}
                    </div>
                </td>
            </tr>
        </table>

        <!-- Passenger Details Table -->
        <table class="details-table">
            <tr>
                <td>
                    <div class="info-item">Passenger Name: <strong>{{ $transaction->name }}</strong></div>
                    <div class="info-item">Transaction Code: <strong>{{ $transaction->code }}</strong></div>
                    <div class="info-item">Flight Number: <strong>{{ $transaction->flight->flight_number }}</strong>
                    </div>
                    <div class="info-item">Class:
                        <strong>{{ \Str::ucfirst($transaction->class->class_type ?? ($transaction->class->name ?? 'Economy')) }}</strong>
                    </div>
                </td>
                <td>
                    <div class="info-item"><strong>Seats Assigned:</strong></div>
                    <ul class="seats-list">
                        @foreach ($transaction->passengers as $passenger)
                            <li>{{ $passenger->name }} - <strong>Seat: {{ $passenger->seat->name ?? '-' }}</strong>
                            </li>
                        @endforeach
                    </ul>
                </td>
            </tr>
        </table>

        <!-- QR Code Barcode -->
        @if (isset($qrCode))
            <div class="barcode">
                <img class="qr-code" src="{{ $qrCode }}" alt="QR Code">
            </div>
        @endif

        <!-- Footer -->
        <div class="footer">
            <p>Please present this boarding pass at the airport. Safe travels!</p>
        </div>
    </div>
</body>

</html>
