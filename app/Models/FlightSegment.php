<?php

namespace App\Models;

// Untuk mengaktifkan fitur Factory (membuat data tiruan/palsu secara otomatis untuk uji coba database)
use Illuminate\Database\Eloquent\Factories\HasFactory; 

// Untuk menjadikan class ini sebagai Model Laravel (menghubungkan class PHP dengan tabel di database MySQL/SQLite)
use Illuminate\Database\Eloquent\Model; 

// Untuk mengaktifkan fitur "Recycle Bin" (menyembunyikan data saat dihapus tanpa benar-benar menghapusnya secara permanen dari database)
use Illuminate\Database\Eloquent\SoftDeletes; 

class FlightSegment extends Model
{
    use HasFactory, SoftDeletes;
    protected $fillable = [
        'sequence',
        'flight_id',
        'airport_id',
        'time',
    ];

    // RELASI 
    public function flight()
    {
        return $this->belongsTo(flight::class);
    }

    public function a()
    {
        return $this->belongsTo(Airport::class);
    }
}
