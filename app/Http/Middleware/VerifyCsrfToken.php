<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as BaseVerifier;

class VerifyCsrfToken extends BaseVerifier
{
    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * @var array
     */
    protected $except = [
        'login',
        'register',
        'get_user',
        'usuario/edit',
        'publicar',
        'publicacion/delete',
        'publicacion/aprobar',
        'publicacion/add_foto',
        'favorito',
        'comentar',
        'comentario_d',
        'comentario_r',
        'comentario_rd',
        'admin',
        'add_cat',
        'delete_cat',
        'edit_cat',
    ];
}
