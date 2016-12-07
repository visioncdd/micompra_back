<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Foto extends Model
{
    protected $table = 'fotos';

    protected $fillable = ['foto', 'producto_id'];

    public function producto()
    {
        return $this->belongsTo('App\Producto','producto_id');
    }
}
