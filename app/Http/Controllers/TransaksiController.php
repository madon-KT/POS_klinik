<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Transaksi;
use App\Models\Obat;
use App\Models\TransaksiDetail;
use Barryvdh\DomPDF\Facade\Pdf as PDF;
use Mike42\Escpos\Printer;
use Mike42\Escpos\PrintConnectors\WindowsPrintConnector;


class TransaksiController extends Controller
{
    public function index(Request $request)
    {
        $query = Transaksi::with('details.obat');

        // Pilihan jumlah per halaman (default 10)
        $perPage = $request->input('per_page', 10);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('details.obat', function ($q) use ($search) {
                $q->where('nama_obat', 'like', '%' . $search . '%');
            });
            $transaksis = $query->get(); // non-paginate agar hasilnya semua
        } else {
            $transaksis = $query->paginate($perPage);
        }

        return view('transaksi.index', compact('transaksis', 'perPage'));
    }


    public function create()
    {
        $obats = Obat::all();
        $obatListJson = $obats->map(function ($o) {
            return [
                'id' => $o->id,
                'nama' => $o->nama_obat,
                'stok' => $o->stok,
            ];
        })->values(); // tambahkan ->values() untuk memastikan array reindex

        return view('transaksi.create', [
            'obats' => $obats,
            'obatListJson' => $obatListJson,
        ]);
    }
    public function store(Request $request)
    {
        $request->validate([
            'items' => 'required|array',
            'items.*.obat_id' => 'required|exists:obats,id',
            'items.*.jumlah' => 'required|integer|min:1',
        ]);

        $total_harga = 0;
        $transaksi = Transaksi::create(['total_harga' => 0]);

        foreach ($request->items as $item) {
            $obat = Obat::findOrFail($item['obat_id']);

            // CEK STOK TERSEDIA
            if ($obat->stok < $item['jumlah']) {
                // Batalkan transaksi yang belum selesai
                $transaksi->delete();
                return redirect()->back()->withErrors([
                    'stok' => "Stok untuk {$obat->nama_obat} tidak mencukupi. Sisa stok: {$obat->stok}"
                ])->withInput();
            }

            $subtotal = $obat->harga * $item['jumlah'];
            $total_harga += $subtotal;

            $transaksi->details()->create([
                'obat_id' => $obat->id,
                'jumlah' => $item['jumlah'],
                'subtotal' => $subtotal,
            ]);

            $obat->stok -= $item['jumlah'];
            $obat->save();
        }

        $transaksi->update(['total_harga' => $total_harga]);

        return redirect()->route('transaksi.index')->with('success', 'Transaksi berhasil disimpan.');
    }
    public function cetakNota($id)
    {
        $transaksi = Transaksi::with('details.obat')->findOrFail($id);
        $pdf = PDF::loadView('transaksi.nota', compact('transaksi'));
        return $pdf->stream('nota_transaksi.pdf');
    }

    public function cetakNotaThermal($id)
    {
        $transaksi = Transaksi::with('details.obat')->findOrFail($id);

        try {
            // Sambungkan ke printer thermal (ganti 'POS-58' dengan nama printer Anda)
            $connector = new WindowsPrintConnector("POS-58");
            //$connector = new BluetoothPrintConnector("POS-58");
            $printer = new Printer($connector);

            // Header Nota
            $printer->setJustification(Printer::JUSTIFY_CENTER);
            $printer->text("Klinik Azizi\n");
            $printer->text("Bacem\n");
            $printer->text("Telp: 085141661730\n");
            $printer->text("------------------------------\n");

            // Detail Transaksi
            $printer->setJustification(Printer::JUSTIFY_LEFT);
            foreach ($transaksi->details as $detail) {
                $printer->text($detail->obat->nama_obat . " x " . $detail->jumlah . "\n");
                $printer->text(" Rp " . number_format($detail->subtotal, 0, ',', '.') . "\n");
            }

            // Total Harga
            $printer->text("------------------------------\n");
            $printer->setJustification(Printer::JUSTIFY_RIGHT);
            $printer->text("Total: Rp " . number_format($transaksi->total_harga, 0, ',', '.') . "\n");

            // Footer Nota
            $printer->setJustification(Printer::JUSTIFY_CENTER);
            $printer->text("------------------------------\n");
            $printer->text("Terima kasih atas kunjungan Anda!\n");

            // Potong Kertas
            $printer->cut();

            // Tutup koneksi ke printer
            $printer->close();

            return redirect()->route('transaksi.index')->with('success', 'Nota berhasil dicetak!');
        } catch (\Exception $e) {
            return redirect()->route('transaksi.index')->with('error', 'Gagal mencetak nota: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        $transaksi = Transaksi::findOrFail($id);

        // Hanya hapus transaksi tanpa mengubah stok
        $transaksi->details()->delete();
        $transaksi->delete();

        return redirect()->route('transaksi.index')->with('success', 'Transaksi berhasil dihapus.');
    }
}
