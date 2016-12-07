<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Estado extends Model
{
    protected $table = 'estados';

    protected $fillable = ['nombre'];

    public function productos()
    {
        return $this->hasMany('App\Estado');
    }

    public function usuario()
    {
        return $this->belongsTo('App\User','estado_id');
    }

    public function productosCount()
    {
        return $this->hasOne('App\Producto')->selectRaw('estado_id, count(*) as count')->groupBy('estado_id');
        // replace module_id with appropriate foreign key if needed
    }
}
