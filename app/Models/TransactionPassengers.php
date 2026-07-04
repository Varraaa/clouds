<?php

namespace App\Models;

// Untuk mengaktifkan fitur Factory (membuat data tiruan/palsu secara otomatis untuk uji coba database)
use Illuminate\Database\Eloquent\Factories\HasFactory; 

// Untuk menjadikan class ini sebagai Model Laravel (menghubungkan class PHP dengan tabel di database MySQL/SQLite)
use Illuminate\Database\Eloquent\Model; 

// Untuk mengaktifkan fitur "Recycle Bin" (menyembunyikan data saat dihapus tanpa benar-benar menghapusnya secara permanen dari database)
use Illuminate\Database\Eloquent\SoftDeletes; 

class TransactionPassengers extends Model
{
    use HasFactory, SoftDeletes;
    protected $fillable = [
        'transaction_id',
        'flight_seat_id',
        'date_of_birth',
        'nationality',
    ];

    // RELASI 
    public function transaction()
    {
        return $this->belongsTo(Transaction::class);
    }

    public function seat()
    {
        return $this->belongsTo(FlightSeat::class);
    }
}
