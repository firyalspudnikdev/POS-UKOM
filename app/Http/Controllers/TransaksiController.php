<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Barang;
use App\Models\Pelanggan;
use App\Models\Transaksi;
use App\Models\ErorLog;
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

        $action = $request->action;
       
        if($action == "create"){
            // Cek stok barang
            $barang = Barang::where('id_barang', $id_barang)->first();
            // dd($barang->stock);
            if ($barang->stock < $jumlah) {
                $errorLog = new ErorLog();
                $errorLog->eror_code = '400';
                $errorLog->eror_desc = 'Stok barang dengan ID ' . $id_barang . ' tidak mencukupi untuk jumlah yang diminta.';
                $errorLog->save();
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
        }else if($action == "edit"){
            // Validasi request
       
            $request->validate([
                'id_pelanggan' => 'required|exists:pelanggan,id_pelanggan',
                'id_barang' => 'required|exists:barang,id_barang',
                'jumlah' => 'required|integer|min:1',
            ]);
        
            // Ambil data transaksi dari database
            $transaksi = Transaksi::where('id_transaksi', $request->id_transaksi)->first();
            
            // Periksa apakah transaksi ditemukan
            if(!$transaksi) {
                return response()->json(['message' => 'Transaction not found'], 404);
            }
        
            // Cek apakah ada perubahan pada barang atau jumlah
            if ($transaksi->id_barang != $request->id_barang || $transaksi->jumlah != $request->jumlah) {
                // Cek stok barang
                $barang = Barang::where('id_barang', $request->id_barang)->first();
                if ($barang->stock < $request->jumlah) {
                    return response()->json(['message' => 'Stok barang tidak mencukupi'], 400);
                }
        
                // Perbarui transaksi
                Transaksi::where('id_transaksi', $transaksi->id_transaksi)
                ->update([
                    'id_pelanggan' => $request->id_pelanggan,
                    'id_barang' => $request->id_barang,
                    'jumlah' => $request->jumlah
                ]);
            
        
                // Perbarui stok barang
                Barang::where('id_barang', $request->id_barang)->update(['stock' => DB::raw("stock - $request->jumlah")]);
            }
        
            return response()->json(['message' => 'Transaksi berhasil diperbarui'], 200);
        }
        
        
    }

    public function delete($id)
    {

        
        $transaksi = Transaksi::where('id_transaksi', $id)->first();
      
        if (!$transaksi) {
            return response()->json(['error' => 'Transaction not found'], 404);
        }
        
        Transaksi::where('id_transaksi', $id)->delete();
        
        return response()->json(['message' => 'Transaction deleted successfully'], 200);
    }

    public function edit($id)
    {
        // Temukan data transaksi berdasarkan ID
        $transaksi = Transaksi::join('barang', 'transaksi.id_barang', '=', 'barang.id_barang')->join('pelanggan', 'transaksi.id_pelanggan', '=', 'pelanggan.id_pelanggan')->where('id_transaksi', $id)->first();
        // dd($transaksi->get());
        // Periksa apakah data transaksi ditemukan
        if(!$transaksi) {
            return response()->json(['error' => 'Transaction not found'], 404);
        }
        //   dd($transaksi->get());
        // Jika ditemukan, kembalikan data dalam bentuk JSON
        return response()->json(['data' => $transaksi]);
    }
}
