<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Producto extends Model
{
    //

    protected $table = 'productos';

    protected $fillable = ['nombre', 'descripcion', 'tipo','precio','cambio', 'user_id','url','categoria_id','subcategoria_id','estado_id'];

    public function usuario()
    {
        return $this->belongsTo('App\User','user_id');
    }

    public function estado()
    {
        return $this->belongsTo('App\Estado','estado_id');
    }

    public function fotos()
    {
        return $this->hasMany('App\Foto');
    }

    public function comentarios()
    {
        return $this->hasMany('App\Comentario');
    }

    public function categoria()
    {
        return $this->belongsTo('App\Categoria','categoria_id');
    }

    public function subcategoria()
    {
        return $this->belongsTo('App\Subcategoria','subcategoria_id');
    }

    //FILTROS

    public function scopeAprobado($query, $val)
    {
        return $query->where('aprobado', $val);
    }
}
