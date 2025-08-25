<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class Rifa extends Model
{
    use HasFactory;
    
    protected $table = 'rifas';

    protected $fillable = [
        'titulo',
        'descripcion',
        'fecha_inicio',
        'fecha_fin',
        'premio',
        'estado',
        'creada_por'
    ];

    public function boletos()
    {
        return $this->hasMany(Boleto::class, 'rifa_id');
    }

}
