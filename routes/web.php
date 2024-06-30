<?php

use App\Http\Controllers\PackingListController;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    Route::get('/testapi', [PackingListController::class, 'testApi'])->name('testapi');
    Route::get('/dashboard', function () {
        return Inertia::render('Dashboard');
    })->name('dashboard');

    Route::get('/fcl', function () {
        return Inertia::render('Fcl');
    })->name('fcl');

    Route::get('/lcl', function () {
        return Inertia::render('Lcl');
    })->name('lcl');

    Route::post('/wa-blast/fcl-packing-list', [PackingListController::class, 'fclWaBlastPackingList'])->name('fclWaBlastPackingList');
    Route::post('/wa-blast/lcl-packing-list', [PackingListController::class, 'lclWaBlastPackingList'])->name('lclWaBlastPackingList');

    Route::get('/pl', function () {
        //return packing-list.blade.php laravel blade file
        return view('packing-list', [
            'nomor_konosemen' => 'KC.SUB.REO.2403.9.053 (PTD)',
            'customer' => 'PT. Karya Cipta',
            'alamat' => 'Jl. Raya Cikarang - Cibarusah KM 3,5',
            'no_telp' => '021-8987654',
            'top' => '30 Hari sesudah bongkar',
            'lokasi_bayar' => 'Surabaya',
            'rute' => 'Surabaya - Jakarta',
            'trip' => '9',
            'kapal' => 'KM. Karya Cipta Voy MR091E',
            'jenis_kiriman_type_cont' => 'FCL / 20FT GP',
            'tanggal_berangkat' => '2021-03-24',
            'berita_acara' => 'Ada',
            'buruh_bongkar' => 'Ada',
            'customer_penerima' => 'PT. Karya Cipta',
            'alamat_penerima' => 'Jl. Raya Cikarang - Cibarusah KM 3,5',
            'total_barang' => '1',
            'total_m3' => '1',
            'total_berat' => '1',
            'barang' =>  [
                [
                    'QTY' => '1',
                    'KODE_BARANG' => '123',
                    'NAMA_BARANG' => 'Kursi',
                    'P' => '1',
                    'L' => '1',
                    'T' => '1',
                    'W' => '1',
                    'TOTAL M3' => '1',
                    'TOTAL BERAT' => '1',
                ],
                [
                    'QTY' => '1',
                    'KODE_BARANG' => '123',
                    'NAMA_BARANG' => 'Kursi',
                    'P' => '1',
                    'L' => '1',
                    'T' => '1',
                    'W' => '1',
                    'TOTAL M3' => '1',
                    'TOTAL BERAT' => '1',
                ],
                [
                    'QTY' => '1',
                    'KODE_BARANG' => '123',
                    'NAMA_BARANG' => 'Kursi',
                    'P' => '1',
                    'L' => '1',
                    'T' => '1',
                    'W' => '1',
                    'TOTAL M3' => '1',
                    'TOTAL BERAT' => '1',
                ],
            ]

        ]);
    })->name('packingList');
});
