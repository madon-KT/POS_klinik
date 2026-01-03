<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Models\Transaksi;
use App\Models\Obat;
use App\Models\TransaksiDetail;
use Barryvdh\DomPDF\Facade\Pdf;

class LaporanController extends Controller
{
    public function index(Request $request)
    {
        $query = Transaksi::with('details.obat')->orderBy('created_at', 'asc');

        if ($request->filled('tanggal_mulai') && $request->filled('tanggal_selesai')) {
            $query->whereBetween('created_at', [
                $request->tanggal_mulai . ' 00:00:00',
                $request->tanggal_selesai . ' 23:59:59'
            ]);
        }

        $perPage = $request->input('per_page', 10);
        $transaksis = $query->paginate($perPage)->appends($request->except('page'));

        $totalPendapatan = TransaksiDetail::whereIn(
            'transaksi_id',
            $transaksis->pluck('id')
        )->sum('subtotal');

        return view('laporan.index', compact('transaksis', 'totalPendapatan'));
    }

    public function pdf(Request $request)
    {
        $query = Transaksi::with('details.obat');

        if ($request->filled('tanggal_mulai') && $request->filled('tanggal_selesai')) {
            $query->whereBetween('created_at', [
                $request->tanggal_mulai . ' 00:00:00',
                $request->tanggal_selesai . ' 23:59:59'
            ]);
        }

        $transaksis = $query->get();

        $totalPendapatan = TransaksiDetail::whereIn(
            'transaksi_id',
            $transaksis->pluck('id')
        )->sum('subtotal');

        // 🔴 DATA HARGA OBAT (BARU)
        $obats = Obat::orderBy('nama_obat')->get();
        
        $pdf = Pdf::loadView('laporan.template', compact('transaksis', 'totalPendapatan'))
                  ->setPaper('a4', 'landscape');

        return $pdf->download('laporan_transaksi.pdf');
    }
}
