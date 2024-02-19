<?php

namespace App\Http\Controllers;

class PruebasController extends Controller
{
    public function index()
    {
        $titulo = 'Titulo';
        $animales = ["perro", "gato", "pajaros"];
        return view('pruebas.index', array(
            'titulo' => $titulo,
            'animales' => $animales
        ));
    }
}
