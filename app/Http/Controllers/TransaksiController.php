<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Barang;
use App\Models\Pelanggan;
use App\Models\Transaksi;
use Illuminate\Support\Facades\DB;

class TransaksiController extends Controller
{

    public function index(Request $request)
    {
        // Ambil data transaksi dari model Transaksi
        $query = Transaksi::join('barang', 'transaksi.id_barang', '=', 'barang.id_barang')->join('pelanggan', 'transaksi.id_pelanggan', '=', 'pelanggan.id_pelanggan');
        
        // Kembalikan data dalam format JSON
        return response()->json([
            'data' =>$query->get()
        ]);
    }

    public function store(Request $request)
    {
        // Validasi request
        $request->validate([
            'id_pelanggan' => 'required|exists:pelanggan,id_pelanggan',
            'id_barang' => 'required|exists:barang,id_barang',
            'jumlah' => 'required|integer|min:1',
        ]);

        // Ambil data barang dari request
        $id_barang = $request->id_barang;
        $jumlah = $request->jumlah;

        // Cek stok barang
        $barang = Barang::where('id_barang', $id_barang)->first();
        // dd($barang->stock);
        if ($barang->stock < $jumlah) {
            return response()->json(['message' => 'Stok barang tidak mencukupi'], 400);
        }

        // Lakukan transaksi
        $transaksi = new Transaksi();
        $transaksi->id_pelanggan = $request->id_pelanggan;
        $transaksi->id_barang = $id_barang;
        $transaksi->jumlah = $jumlah;
        $transaksi->save();
  
        
        Barang::where('id_barang', $id_barang)->update(['stock' => DB::raw("stock - $jumlah")]);

        return response()->json(['message' => 'Transaksi berhasil disimpan'], 200);
    }
}
