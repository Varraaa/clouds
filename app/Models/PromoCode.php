<?php

namespace App\Models;

// Untuk mengaktifkan fitur Factory (membuat data tiruan/palsu secara otomatis untuk uji coba database)
use Illuminate\Database\Eloquent\Factories\HasFactory; 

// Untuk menjadikan class ini sebagai Model Laravel (menghubungkan class PHP dengan tabel di database MySQL/SQLite)
use Illuminate\Database\Eloquent\Model; 

// Untuk mengaktifkan fitur "Recycle Bin" (menyembunyikan data saat dihapus tanpa benar-benar menghapusnya secara permanen dari database)
use Illuminate\Database\Eloquent\SoftDeletes; 

class PromoCode extends Model
{
    use HasFactory, SoftDeletes;
    protected $fillable = [
        'code',
        'discount_type',
        'discount',
        'valid_until',
        'is_used',
    ];

    // RELASI 
    public function transaction()
    {
        return $this->hasOne(Transaction::class);
    }
}
