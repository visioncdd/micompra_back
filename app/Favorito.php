<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Favorito extends Model
{
    protected $table = 'favoritos';

    protected $fillable = ['producto_id','user_id'];

    public function usuarios()
    {
        return $this->belongsTo('App\User','user_id');
    }

}
