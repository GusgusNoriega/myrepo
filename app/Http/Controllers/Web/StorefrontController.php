<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;

class StorefrontController extends Controller
{
    public function show(int $business)
    {
        // Pasamos el id del negocio a la vista
        return view('paginas.tienda', ['businessId' => $business]);
    }

     public function product(int $product)
    {
        // Pasamos el ID del producto a la vista
        return view('paginas.product', ['productId' => $product]);
    }
}
