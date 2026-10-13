<?php

namespace App\Http\Middleware;

use Illuminate\Cookie\Middleware\EncryptCookies as Middleware;

class EncryptCookies extends Middleware
{
    /**
     * The names of the cookies that should not be encrypted.
     *
     * @var array<int, string>
     */
    protected $except = [
        // Mode nuit choisi (« dark » / « light ») : lu par le serveur pour
        // envoyer directement les bonnes CSS, sans flash de l'ancien design.
        'stcd-theme',
    ];
}
