<?php

namespace App\Models;

// Untuk mengaktifkan fitur Factory (membuat data tiruan/palsu secara otomatis untuk uji coba database)
use Illuminate\Database\Eloquent\Factories\HasFactory; 

// Untuk menjadikan class ini sebagai Model Laravel (menghubungkan class PHP dengan tabel di database MySQL/SQLite)
use Illuminate\Database\Eloquent\Model; 

// Untuk mengaktifkan fitur "Recycle Bin" (menyembunyikan data saat dihapus tanpa benar-benar menghapusnya secara permanen dari database)
use Illuminate\Database\Eloquent\SoftDeletes; 

class Flight extends Model
{
    use HasFactory, SoftDeletes;
    protected $fillable = [
        'flight_number',
        'airline_id',
    ];

    // RELASI 
    public function airline()
    {
        return $this->belongsTo(Airline::class);
    }

    public function segments()
    {
        return $this->hasMany(FlightSegment::class);
    }

    public function classes()
    {
        return $this->hasMany(FlightClass::class);
    }

    public function seats()
    {
        return $this->hasMany(FlightSeat::class);
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }
}
