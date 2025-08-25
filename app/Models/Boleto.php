<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Boleto extends Model
{
    protected $table = 'boletos';

    protected $fillable = [
        'usuario_id', 'rifa_id', 'cantidad', 'estado'
    ];

    public function rifa()
    {
        return $this->belongsTo(Rifa::class, 'rifa_id');
    }

    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }

}
