<!DOCTYPE html>
<html>
<head>
    <title>Laporan Transaksi</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            margin: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
        }
        .header h2 {
            margin: 0;
        }
        .header p {
            margin: 5px 0;
        }
        table {
            border-collapse: collapse;
            width: 100%;
            margin-top: 10px;
        }
        table, th, td {
            border: 1px solid black;
        }
        th, td {
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
    </style>
</head>
<body>
    <div class="header">
    <h2>Laporan Transaksi</h2>

    @if (request('tanggal_mulai') && request('tanggal_selesai'))
        <p>
            Laporan transaksi dari tanggal
            {{ \Carbon\Carbon::parse(request('tanggal_mulai'))->format('d-m-Y') }}
            s/d
            {{ \Carbon\Carbon::parse(request('tanggal_selesai'))->format('d-m-Y') }}
        </p>
    @else
        <p>Laporan transaksi seluruh periode</p>
    @endif

    <p>Dicetak pada: {{ now()->format('d-m-Y H:i:s') }}</p>
</div>

    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Tanggal Transaksi</th>
                <th>Nama Obat</th>
                <th>Jumlah</th>
                <th>Subtotal</th>
                <th>Total Harga</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($transaksis as $index => $transaksi)
                @foreach ($transaksi->details as $detail)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $transaksi->created_at->format('d-m-Y') }}</td>
                        <td>{{ $detail->obat->nama_obat ?? 'Obat tidak ditemukan' }}</td>
                        <td>{{ $detail->jumlah }} x Rp {{ number_format($detail->subtotal / $detail->jumlah, 0, ',', '.') }}</td>
                        <td>Rp {{ number_format($detail->subtotal, 0, ',', '.') }}</td>
                        @if ($loop->first)
                            <td rowspan="{{ $transaksi->details->count() }}">Rp {{ number_format($transaksi->total_harga, 0, ',', '.') }}</td>
                        @endif
                    </tr>
                @endforeach
            @empty
                <tr>
                    <td colspan="6" class="text-center">Tidak ada data transaksi</td>
                </tr>
            @endforelse
        </tbody>
        <tr>
    <td colspan="4" style="text-align: right; font-weight: bold;">Total Pendapatan</td>
    <td style="font-weight: bold;">Rp {{ number_format($totalPendapatan, 0, ',', '.') }}</td>
    <td></td>
</tr>
    </table>
</body>
</html>
