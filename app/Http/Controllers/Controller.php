<?php

namespace App\Http\Controllers;

abstract class Controller
{
    function homepage() {
    return view('welcome');
}
}
