<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Encryption\Encrypter; // Tetap gunakan ini atau Illuminate\Support\Encrypter (keduanya sama)

class UserKyc extends Model
{
    protected $fillable = [
        'user_id',
        'nik',
        'nik_hash',
        'ktp_image_path',
        'selfie_image_path',
        'rejection_reason',
    ];

    // ❌ HAPUS ATAU KOMENTARI BAGIAN INI:
    // protected $casts = [
    //     'nik' => 'encrypted',
    // ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    protected function kycEncrypter(): Encrypter
    {
        // Mengambil key yang sudah di-decode jika formatnya base64, atau langsung pakai jika text murni
        $key = config('app.kyc.key');
        if (str_starts_with($key, 'base64:')) {
            $key = base64_decode(substr($key, 7));
        }

        return new Encrypter($key, 'AES-256-CBC');
    }

    // MUTATOR: Otomatis enkripsi sebelum masuk DB
    public function setNikAttribute($value)
    {
        if (!empty($value)) {
            $this->attributes['nik'] = $this->kycEncrypter()->encrypt($value);
        }
    }

    // ACCESSOR: Otomatis dekripsi saat dibaca di aplikasi
    public function getNikAttribute($value)
    {
        if (empty($value)) return null;

        try {
            return $this->kycEncrypter()->decrypt($value);
        } catch (\Exception $e) {
            return '[Error Decrypting NIK]';
        }
    }

    // Pembuatan Blind Index Otomatis untuk Pencarian
    protected static function booted()
    {
        static::saving(function ($kyc) {
            // Cek apakah kolom 'nik' sedang diisi atau diubah
            if ($kyc->isDirty('nik')) {
                // Ambil data NIK asli yang masih mentah (belum terenkripsi) dari input form
                // Jika Mutator sudah berjalan, kita ambil langsung dari atribut 'nik' yang belum disave
                $nikMentah = $kyc->nik;

                if (!empty($nikMentah)) {
                    // Buat hash unik berbasis salt kustom kita dari NIK mentah tersebut
                    $kyc->nik_hash = hash_hmac('sha256', $nikMentah, config('app.kyc.salt'));
                }
            }
        });
    }
}
