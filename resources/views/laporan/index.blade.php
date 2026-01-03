@extends('layouts.app')

@section('content')

<div class="flex">
    <!-- Sidebar -->
    <div id="sidebar" class="w-64 h-screen fixed top-0 left-0 bg-blue-500 p-4 text-white transition-all duration-300 ease-in-out">
        <!-- Logo -->
        <a href="/dashboard" class="flex items-center mb-4">
            <img src="{{ asset('images/logo.png') }}" alt="Logo" class="w-12 h-12">
            <span id="menuTitle" class="ml-2 text-lg font-bold">Klinik Azizi</span>
        </a>

        <!-- Tombol Toggle Sidebar -->
        <button onclick="toggleSidebar()" class="absolute -right-6 top-4 bg-blue-700 text-white p-2 rounded-r-lg shadow-lg">
            ☰
        </button>

        <ul>
            <li class="mb-2 flex items-center p-2 rounded {{ Request::is('dashboard') ? 'bg-blue-700' : 'hover:bg-blue-600' }}">
                <a href="/dashboard" class="flex items-center w-full">
                    <span class="icon">🏠</span>
                    <span class="menu-text ml-2">Dashboard</span>
                </a>
            </li>
            <li class="mb-2 flex items-center p-2 rounded {{ Request::is('obat') ? 'bg-blue-700' : 'hover:bg-blue-600' }}">
                <a href="/obat" class="flex items-center w-full">
                    <span class="icon">💊</span>
                    <span class="menu-text ml-2">Manajemen Obat</span>
                </a>
            </li>
            <li class="mb-2 flex items-center p-2 rounded {{ Request::is('transaksi') ? 'bg-blue-700' : 'hover:bg-blue-600' }}">
                <a href="/transaksi" class="flex items-center w-full">
                    <span class="icon">💰</span>
                    <span class="menu-text ml-2">Transaksi</span>
                </a>
            </li>
            <li class="mb-2 flex items-center p-2 rounded {{ Request::is('laporan') ? 'bg-blue-700' : 'hover:bg-blue-600' }}">
                <a href="/laporan" class="flex items-center w-full">
                    <span class="icon">📊</span>
                    <span class="menu-text ml-2">Laporan</span>
                </a>
            </li>
        </ul>
    </div>

    <!-- Main Content -->
    <div id="main-content" class="ml-64 p-6 transition-all duration-300 w-full">
        <h1 class="text-2xl font-bold mb-4">Laporan Transaksi</h1>

        <!-- Form Filter dan Pilihan Per Page -->
        <form action="{{ route('laporan.index') }}" method="GET" class="mb-4 flex flex-wrap justify-between items-end">
    <!-- Filter Tanggal -->
    <div class="flex gap-4 items-end">
        <div>
            <label for="tanggal_mulai" class="block text-sm font-medium text-gray-700">Tanggal Mulai</label>
            <input type="date" name="tanggal_mulai" id="tanggal_mulai" value="{{ request('tanggal_mulai') }}" class="border-gray-300 rounded shadow-sm p-2">
        </div>
        <div>
            <label for="tanggal_selesai" class="block text-sm font-medium text-gray-700">Tanggal Selesai</label>
            <input type="date" name="tanggal_selesai" id="tanggal_selesai" value="{{ request('tanggal_selesai') }}" class="border-gray-300 rounded shadow-sm p-2">
        </div>
        <div>
            <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded shadow">Tampilkan</button>
        </div>
        <a href="{{ route('laporan.pdf', request()->query()) }}"
   class="bg-red-500 text-white px-4 py-2 rounded shadow"
   target="_blank">
    Cetak PDF
</a>

        <div>
        <div>
        @php
    $refreshUrl = route('laporan.index', ['refresh' => true]);
@endphp
        
<button onclick="window.location.href='{{ $refreshUrl }}'" class="bg-gray-500 text-white px-4 py-2 rounded shadow">
    Refresh
</button>
        </div>

</div>
    </div>
    <!-- Pilihan Baris Per Page -->
    <div class="flex items-end">
        <div>
            <label for="per_page" class="block text-sm font-medium text-gray-700"></label>
            <select name="per_page" onchange="this.form.submit()" class="border-gray-300 rounded shadow-sm ">
                <option value="10" {{ request('per_page') == 10 ? 'selected' : '' }}>10</option>
                <option value="25" {{ request('per_page') == 25 ? 'selected' : '' }}>25</option>
                <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50</option>
                <option value="100" {{ request('per_page') == 100 ? 'selected' : '' }}>100</option>
            </select>
        </div>
    </div>
</form>

        <!-- Tabel Laporan -->
        <div class="overflow-x-auto">
            <table class="table-auto w-full border border-gray-300 shadow-md bg-white">
                <thead class="bg-blue-500 text-white">
                    <tr>
                        <th class="border px-4 py-2">No</th>
                        <th class="border px-4 py-2">Tanggal</th>
                        <th class="border px-4 py-2">Nama Obat</th>
                        <th class="border px-4 py-2">Jumlah</th>
                        <th class="border px-4 py-2">Subtotal</th>
                        <th class="border px-4 py-2">Total Harga</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($transaksis as $index => $transaksi)
                        <tr class="hover:bg-gray-100 text-center">
                            <td class="border px-4 py-2">{{ $transaksis->firstItem() + $index }}</td>
                            <td class="border px-4 py-2">{{ $transaksi->created_at->format('d-m-Y') }}</td>
                            <td class="border px-4 py-2">
                                @foreach ($transaksi->details as $detail)
                                    {{ $detail->obat->nama_obat }}<br>
                                @endforeach
                            </td>
                            <td class="border px-4 py-2">
                                @foreach ($transaksi->details as $detail)
                                    {{ $detail->jumlah }} x Rp {{ number_format($detail->subtotal / $detail->jumlah, 0, ',', '.') }}<br>
                                @endforeach
                            </td>
                            <td class="border px-4 py-2">
                                @foreach ($transaksi->details as $detail)
                                    Rp {{ number_format($detail->subtotal, 0, ',', '.') }}<br>
                                @endforeach
                            </td>
                            <td class="border px-4 py-2">Rp {{ number_format($transaksi->total_harga, 0, ',', '.') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <!-- Pagination -->
            <div class="mt-4">
                {{ $transaksis->appends(['per_page' => request('per_page'), 'tanggal_mulai' => request('tanggal_mulai'), 'tanggal_selesai' => request('tanggal_selesai')])->links() }}
            </div>
        </div>
    </div>
</div>

<script>
function toggleSidebar() {
    let sidebar = document.getElementById("sidebar");
    let mainContent = document.getElementById("main-content");
    let menuTextElements = document.querySelectorAll(".menu-text");
    let menuTitle = document.getElementById("menuTitle");

    // Toggle class sidebar lebar dan padding
    sidebar.classList.toggle("w-64");
    sidebar.classList.toggle("w-16");
    sidebar.classList.toggle("p-4");
    sidebar.classList.toggle("p-2");

    // Toggle margin main content untuk menyesuaikan sidebar
    mainContent.classList.toggle("ml-64");
    mainContent.classList.toggle("ml-16");

    // Toggle visibility teks menu (elemen dengan class menu-text)
    menuTextElements.forEach(el => el.classList.toggle("hidden"));

    // Toggle visibility judul menu
    menuTitle.classList.toggle("hidden");
}
</script>

@endsection
