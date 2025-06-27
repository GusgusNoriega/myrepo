<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DemoController extends Controller
{
    public function seccion11() { return view('secciones.seccion11'); }
    public function seccion12() { return view('secciones.seccion12'); }
    public function seccion21() { return view('secciones.seccion21'); }
    public function seccion22() { return view('secciones.seccion22'); }
}
