<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Subcategoria extends Model
{
    protected $table = 'subcategorias';

    protected $fillable = ['titulo', 'name', 'icono', 'categoria_id'];

    public function categoria()
    {
        return $this->belongsTo('App\Categoria','categoria_id');
    }

    public function productos()
    {
        return $this->hasMany('App\Producto');
    }

    public function productosCount()
    {
        return $this->hasOne('App\Producto')->selectRaw('subcategoria_id, count(*) as count')->groupBy('subcategoria_id');
        // replace module_id with appropriate foreign key if needed
    }
}
