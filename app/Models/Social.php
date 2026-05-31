<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Social extends Model
{
    use HasFactory;

    /**
     * Properti ini menentukan nama tabel yang terkait dengan model.
     * Secara default Laravel akan menjadikannya jamak (socials),
     * namun mendefinisikannya secara eksplisit adalah praktik yang baik.
     *
     * @var string
     */
    protected $table = 'socials';

    /**
     * Atribut yang dapat diisi secara massal (mass assignable).
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'url',
        'icon',
    ];
}
