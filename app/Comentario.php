<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Comentario extends Model
{
    protected $table = 'comentarios';

    protected $fillable = ['id', 'producto_id', 'user_id','pregunta','respuesta'];

    public function producto()
    {
        return $this->belongsTo('App\Producto','producto_id');
    }
    public function usuario()
    {
        return $this->belongsTo('App\User','user_id');
    }

    // public function fotos()
    // {
    //     return $this->hasMany('App\Foto');
    // }
}
