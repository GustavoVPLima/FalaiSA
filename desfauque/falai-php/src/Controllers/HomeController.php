<?php

namespace App\Controllers;

use App\Helpers\Auth;
use App\Helpers\View;

class HomeController
{
    public function index()
    {
        Auth::check();

        if (Auth::isAdmin()) {
            View::redirect('/admin');
        }

        View::show('index');
    }

    public function about()
    {
        View::show('sobre_nos');
    }
}
