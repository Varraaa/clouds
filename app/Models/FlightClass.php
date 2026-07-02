<?php

namespace App\Models;

// Untuk mengaktifkan fitur Factory (membuat data tiruan/palsu secara otomatis untuk uji coba database)
use Illuminate\Database\Eloquent\Factories\HasFactory; 

// Untuk menjadikan class ini sebagai Model Laravel (menghubungkan class PHP dengan tabel di database MySQL/SQLite)
use Illuminate\Database\Eloquent\Model; 

// Untuk mengaktifkan fitur "Recycle Bin" (menyembunyikan data saat dihapus tanpa benar-benar menghapusnya secara permanen dari database)
use Illuminate\Database\Eloquent\SoftDeletes; 

class FlightClass extends Model
{
    use HasFactory, SoftDeletes;
    protected $fillable = [
        'flight_id',
        'class_type',
        'price',
        'total_seats',
    ];

    // RELASI 
    public function flight()
    {
        return $this->belongsTo(Flight::class);
    }

    public function facilities()
    {
        return $this->belongsToMany(Facility::class, 'flight_class_facility', 'flight_class_id', 'facility_id');
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }
}
    