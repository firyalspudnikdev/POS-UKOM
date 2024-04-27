<?php

namespace App\Http\Controllers;

use App\Models\Barang;
use App\Models\Pelanggan;

class HomeController extends Controller
{
    public function index()
    {
        $barangs = Barang::all();
        $pelanggans = Pelanggan::all();
  
        return view('home', compact('barangs', 'pelanggans'));
    }
}
