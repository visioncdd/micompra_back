<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Categoria extends Model
{
    protected $table = 'categorias';

    protected $fillable = ['titulo', 'name', 'icono'];

    public function sub()
    {
        return $this->hasMany('App\Subcategoria');
    }

    public function productos()
    {
        return $this->hasMany('App\Producto');
    }

    public function productosCount()
    {
        return $this->hasOne('App\Producto')->selectRaw('categoria_id, count(*) as count')->groupBy('categoria_id');
        // replace module_id with appropriate foreign key if needed
    }
}
