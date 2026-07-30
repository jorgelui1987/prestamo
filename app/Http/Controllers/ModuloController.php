<?php

namespace App\Http\Controllers;

class ModuloController extends Controller
{
    public function show(string $titulo, string $icono, string $descripcion)
    {
        return view('modulos.placeholder', compact('titulo', 'icono', 'descripcion'));
    }
}
