<?php

namespace App\Http\Controllers;

use Dompdf\Dompdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\View;
use PhpOffice\PhpSpreadsheet\IOFactory;
use ZipArchive;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

class PackingListController extends Controller
{
    private $sheet;
    public function fclWaBlastPackingList(Request $request)
    {
        // Your existing code to generate payslip data goes here...
        $file = $request->file('file');
        $spreadsheet = IOFactory::load($file);
        // Calculation::getInstance($spreadsheet)->calculate();
        //read the data from the excel file
        $sheet = $spreadsheet->getActiveSheet();
        $this->sheet = $sheet;

        try {
            $zip = new ZipArchive;
            // $zipFileName = 'Payslips_' . date('Ymd_His') . '.zip';
            $currentTime = date('H.i.s');
            $zipFileName =  'Packing List ' . ' (' . $currentTime . ')' . '.zip';
            $zipFilePath = storage_path('app/' . $zipFileName);
            $array_pdf_to_delete = [];
            if ($zip->open($zipFilePath, ZipArchive::CREATE) === true) {

                $vessel = $this->getCellByRowAndColumn(1, 2);
                $voyage = $this->getCellByRowAndColumn(2, 2);
                $pol = $this->getCellByRowAndColumn(3, 2);
                $pod = $this->getCellByRowAndColumn(4, 2);
                $tdVessel = $this->getCellByRowAndColumn(5, 2);
                $tdVessel = str_replace('/', '-', $tdVessel);
                $tdVessel = date('d F Y', strtotime($tdVessel));
                $etaVessel = $this->getCellByRowAndColumn(6, 2);
                $etaVessel = str_replace('/', '-', $etaVessel);
                $etaVessel = date('d F Y', strtotime($etaVessel));

                //get table
                $table = [];
                $row = 9;
                $column = 1;
                while ($this->getCellByRowAndColumn($row, $column) != null) {
                    $table[] = [
                        'no' => $this->getCellByRowAndColumn($row, $column),
                        'container' => $this->getCellByRowAndColumn($row, $column + 1),
                        'seal' => $this->getCellByRowAndColumn($row, $column + 2),
                        'size' => $this->getCellByRowAndColumn($row, $column + 3),
                        'type' => $this->getCellByRowAndColumn($row, $column + 4),
                        'penerima' => $this->getCellByRowAndColumn($row, $column + 5),
                    ];
                    $row++;
                }

                // //get all the container data first
                $pdf_to_print = [];
                foreach ($table as $row) {
                    $container = $row['container'];
                    $seal = $row['seal'];
                    if ($container == null || $container == '' || $seal == null || $seal == '') {
                        return response()->json(['message' => 'Container or Seal is empty', 'success' => false], 200);
                    }
                    //container has pattern ABCD 123456, so a few letters and a few numbers, but someetimes the user gives ABCD123456, so when that happens, we need to add a space after the letters
                    // $container_spaced = preg_replace('/([a-zA-Z])([0-9])/', '$1 $2', $container);
                    //seal also has pattern letters and numbers, so we need to add a space after the letters
                    // $seal_spaced = preg_replace('/([a-zA-Z])([0-9])/', '$1 $2', $seal);
                    $response = Http::post('https://krislines.com/api/v1.0/segel', [
                        'token' => 'YjYyNzhiZjFkNDUwYWJmMzVhYWM5NDkwNTA0ZWEyZWI=',
                        'segel' => $seal,
                        'kontainer' => $container,
                    ]);
                    $response = json_decode($response->getBody()->getContents(), true);
                    if ($response['success'] == false) {
                        return response()->json(['message' => $container . ' & ' . $seal . ' not found.', 'success' => false], 200);
                    }
                    $header = $response['data'][0]['header'];
                    $detail = $response['data'][0]['detail'][0];
                    $barangs = $response['data'][0]['detail'];



                    $kode_pengirim = $detail['kode_pengirim'];
                    $trip = $detail['trip'];


                    // Check if the key already exists in the array
                    if (!isset($pdf_to_print[$kode_pengirim])) {
                        $response = Http::post('https://krislines.com/api/v1.0/customer', [
                            'token' => 'YjYyNzhiZjFkNDUwYWJmMzVhYWM5NDkwNTA0ZWEyZWI=',
                            'kode' => $kode_pengirim,
                        ]);

                        $response = json_decode($response->getBody()->getContents(), true);
                        $pengirim_jenis = $response['data'][0]['jenis'];
                        $pengirim_nama = $response['data'][0]['nama'];
                        if ($pengirim_jenis != 'DLL') {
                            //prepend to pengirim_nama
                            $pengirim_nama = $pengirim_jenis . ' ' . $pengirim_nama;
                        }
                        $pengirim_alamat = $response['data'][0]['alamat'];
                        //if peengirim_alamat is null then set it to empty string
                        $pengirim_alamat = $pengirim_alamat == null ? '' : $pengirim_alamat;
                        $pengirim_telp = $response['data'][0]['telp'];
                        if ($pengirim_telp == null) {
                            $pengirim_telp = '';
                        }
                        // If not, create a new entry
                        $pdf_to_print[$kode_pengirim] = [
                            'nomor_konosemen' => $detail['no_konosemen'],
                            'pengirim_nama' => $pengirim_nama,
                            'pengirim_alamat' => $pengirim_alamat,
                            'pengirim_telp' => $pengirim_telp,
                            'trip' => $trip,
                            'total_barang_semua' => 0,
                            'total_m3_semua' => 0,
                            'total_berat_semua' => 0,
                            'barang' => [],
                        ];
                    }

                    $barangs_pdf = [];
                    $total_barang = 0;
                    $total_m3 = 0;
                    $total_berat = 0;
                    $penerima = '';
                    $no_container_already_exists = false;

                    foreach ($barangs as $barang) {
                        $total_barang += $barang['qty'];
                        $total_m3 += $barang['p'] * $barang['l'] * $barang['t'] * $barang['qty'];
                        $total_berat += $barang['berat'] * $barang['qty'];
                        if ($row['penerima'] != null && $row['penerima'] != '') {
                            $penerima = $row['penerima'];
                        } else {
                            $penerima = $barang['penerima'];
                        }
                        // if this is the first barang then include the no kontainer, else just ''
                        if ($no_container_already_exists == false) {
                            $no_container_already_exists = true;
                        } else {
                            $header['no_kontainer'] = '';
                        }
                        $barangs_pdf[] = [
                            'QTY' => $barang['qty'],
                            'NO_CONT' => $header['no_kontainer'],
                            // 'PENERIMA' => $barang['penerima'],
                            'PENERIMA' => $penerima,
                            'NAMA_BARANG' => $barang['nm_inv'],
                            'P' => $barang['p'],
                            'L' => $barang['l'],
                            'T' => $barang['t'],
                            'W' => $barang['berat'],
                            'JENIS_ORDER' => $barang['tipe'],
                            'TOTAL M3' => $barang['p'] * $barang['l'] * $barang['t'] * $barang['qty'],
                            'TOTAL BERAT' => $barang['berat'] * $barang['qty'],
                        ];
                    }

                    $pdf_to_print[$kode_pengirim]['total_barang_semua'] += $total_barang;
                    $pdf_to_print[$kode_pengirim]['total_m3_semua'] += $total_m3;
                    $pdf_to_print[$kode_pengirim]['total_berat_semua'] += $total_berat;

                    //array merge the barangs_pdf to the barang key
                    $pdf_to_print[$kode_pengirim]['barang'] = array_merge($pdf_to_print[$kode_pengirim]['barang'], $barangs_pdf);
                }

                for ($i = 0; $i < count($pdf_to_print); $i++) {
                    $kode_pengirim = array_keys($pdf_to_print)[$i];


                    $pdf = new DomPdf();
                    $pdf->loadHtml(View::make('packing-list', [
                        'nomor_konosemen' => $pdf_to_print[$kode_pengirim]['nomor_konosemen'],
                        'customer' => $pdf_to_print[$kode_pengirim]['pengirim_nama'],
                        'alamat' => $pdf_to_print[$kode_pengirim]['pengirim_alamat'],
                        'no_telp' => $pdf_to_print[$kode_pengirim]['pengirim_telp'],
                        'rute' => $pol . '-' . $pod,
                        'trip' => $pdf_to_print[$kode_pengirim]['trip'],
                        'kapal' => 'KM. ' . $vessel,
                        'voyage' => $voyage,
                        'tanggal_berangkat' => $tdVessel,
                        'tanggal_sampai' => $etaVessel,
                        'total_barang' => $pdf_to_print[$kode_pengirim]['total_barang_semua'],
                        'total_m3' => $pdf_to_print[$kode_pengirim]['total_m3_semua'],
                        'total_berat' => $pdf_to_print[$kode_pengirim]['total_berat_semua'],
                        'barang' => $pdf_to_print[$kode_pengirim]['barang'],

                    ])->render());
                    $pdf->setPaper('A4', 'portrait');
                    $pdf->render();
                    //with counter
                    $pdfFileName = 'Packing List ' . $pdf_to_print[$kode_pengirim]['pengirim_nama'] . ' (' . $i + 1 . ').pdf';
                    // $pdfFileName = 'Packing List ' . $container . '.pdf';
                    //just return the pdf and don't save it
                    // return $pdf->stream($pdfFileName);

                    $pdfFilePath = storage_path('app/' . $pdfFileName);
                    file_put_contents($pdfFilePath, $pdf->output());
                    $zip->addFile($pdfFilePath, $pdfFileName);
                    $array_pdf_to_delete[] = $pdfFilePath;
                }

                $zip->close();
                // //delete all the pdf files
                foreach ($array_pdf_to_delete as $pdf_to_delete) {
                    unlink($pdf_to_delete);
                }

                //get the binary content of the zip file, so we can delete it before returning the response
                $zipFileBinary = file_get_contents($zipFilePath);
                //delete the zip file
                unlink($zipFilePath);
                //return the binary content of the zip file
                return response($zipFileBinary)
                    ->header('Content-Type', 'application/zip')
                    ->header('Content-Disposition', 'attachment; filename="' . $zipFileName . '"');

                // return response()->download($zipFilePath);
            }
            // return response()->json(['message' => $table, 'success' => true], 200);
        } catch (\Exception $e) {
            // Exception occurred
            return response()->json(['message' => $e->getMessage(), 'success' => false], 500);
        }
    }

    public function lclWaBlastPackingList(Request $request)
    {
        // Your existing code to generate payslip data goes here...
        $trip_no = $request->trip_no;
        try {
            $zip = new ZipArchive;
            // $zipFileName = 'Payslips_' . date('Ymd_His') . '.zip';
            $currentTime = date('H.i.s');
            $zipFileName =  'Packing List ' . ' (' . $currentTime . ')' . '.zip';
            $zipFilePath = storage_path('app/' . $zipFileName);
            $array_pdf_to_delete = [];
            if ($zip->open($zipFilePath, ZipArchive::CREATE) === true) {
                $response = Http::post('https://krislines.com/api/v1.0/trip_isi', [
                    'token' => 'YjYyNzhiZjFkNDUwYWJmMzVhYWM5NDkwNTA0ZWEyZWI=',
                    'trip_no' => $trip_no
                ]);
                $response = json_decode($response->getBody()->getContents(), true);

                if ($response['success'] == false) {
                    return response()->json(['message' => $trip_no . ' not found.', 'success' => false], 200);
                }
                $datas = $response['data'];
                $pdf_to_print = [];
                for ($i = 0; $i < count($datas); $i++) {
                    $data = $datas[$i];
                    $kode_cust = $data['kd_cust'];

                    if ($kode_cust != 'MIT.000010' && $kode_cust != 'MIT.000167') {
                        continue;
                    }
                    //make the pdf_to_print become like this data
                    // {
                    //     "MIT.1": {
                    //         "cust_details": {
                    //             "nama": "pengirim_nama", //+
                    //             "alamat": "pengirim_alamat", //+
                    //             "telp": "pengirim_telp" //+
                    //         },
                    //         "data": {
                    //             "total_semua_m3": "total_semua_m3",
                    //             "total_semua_berat": "total_semua_berat",
                    //             "MRTU1234": [
                    //                 {
                    //                     "tgl_layar": "tgl_layar",
                    //                     "tgls": "tgls",
                    //                     "voyage_no": "voyage_no",
                    //                     "rute": "rute",
                    //                     "kapal": "kapal",
                    //                     "nama_barang": "nama_barang",
                    //                     "qty": "qty",
                    //                     "p": "p",
                    //                     "l": "l",
                    //                     "t": "t",
                    //                     "w": "w", //+
                    //                     "total_m3": "total_m3",
                    //                     "total_berat": "total_berat",
                    //                     "penerima": "penerima" //+
                    //                 },
                    //                 {
                    //                     "tgl_layar": "tgl_layar",
                    //                     "tgls": "tgls",
                    //                     "voyage_no": "voyage_no",
                    //                     "rute": "rute",
                    //                     "kapal": "kapal",
                    //                     "nama_barang": "nama_barang",
                    //                     "qty": "qty",
                    //                     "p": "p",
                    //                     "l": "l",
                    //                     "t": "t",
                    //                     "w": "w", //+
                    //                     "total_m3": "total_m3",
                    //                     "total_berat": "total_berat",
                    //                     "penerima": "penerima" //+
                    //                 }
                    //             ]
                    //         }
                    //     }
                    // }

                    $current_customer_name = $data['nm_cust'];


                    $no_kontainer = $data['no_kontainer'];
                    $tgl = $data['tgl'];
                    $tgl = str_replace('/', '-', $tgl);
                    $tgl = date('d F Y', strtotime($tgl));
                    $tgls = $data['tgls'];
                    $tgls = str_replace('/', '-', $tgls);
                    $tgls = date('d F Y', strtotime($tgls));
                    $voyage_no = $data['voyage_no'];
                    $rute = $data['nm_from'] . '-' . $data['nm_to'];
                    $kapal = $data['sip'];
                    $nama_barang = $data['nm_inv'];
                    $qty = $data['qty'];
                    $p = $data['p'];
                    $l = $data['l'];
                    $t = $data['t'];
                    $w = 1;
                    $total_m3 = $p * $l * $t * $qty;
                    $total_berat = $w * $qty;
                    $penerima = "PENERIMA";
                    $nomor_konosemen = "NOMOR KONO";
                    $jenis_order = $data['muatan'];

                    // Check if the key already exists in the array, if not initialize it and also total_semua_m3 and total_semua_berat
                    if (!isset($pdf_to_print[$kode_cust])) {
                        $response = Http::post('https://krislines.com/api/v1.0/customer', [
                            'token' => 'YjYyNzhiZjFkNDUwYWJmMzVhYWM5NDkwNTA0ZWEyZWI=',
                            'kode' => $kode_cust,
                        ]);

                        $response = json_decode($response->getBody()->getContents(), true);
                        $alamat_cust = $response['data'][0]['alamat'];
                        $telp_cust = $response['data'][0]['telp'];
                        $pdf_to_print[$kode_cust] = [
                            'cust_details' => [
                                'nama' => $current_customer_name,
                                'alamat' => $alamat_cust,
                                'telp' => $telp_cust,
                                'nomor_konosemen' => $nomor_konosemen,
                                'rute' => $rute,
                            ],
                            'data' => [
                                'total_semua_m3' => 0,
                                'total_semua_berat' => 0,
                                'total_semua_qty' => 0,
                                'items' => [],
                            ],
                        ];
                    }

                    $pdf_to_print[$kode_cust]['data']['total_semua_m3'] += $total_m3;
                    $pdf_to_print[$kode_cust]['data']['total_semua_berat'] += $total_berat;
                    $pdf_to_print[$kode_cust]['data']['total_semua_qty'] += $qty;

                    $pdf_to_print[$kode_cust]['data']['items'][] = [
                        'NO_CONT' => $data['no_kontainer'],
                        'tgl' => $tgl,
                        'tgls' => $tgls,
                        'voyage_no' => $voyage_no,
                        'kapal' => $kapal,
                        'NAMA_BARANG' => $nama_barang,
                        'QTY' => $qty,
                        'P' => $p,
                        'L' => $l,
                        'T' => $t,
                        'W' => $w,
                        'TOTAL M3' => $total_m3,
                        'TOTAL BERAT' => $total_berat,
                        'PENERIMA' => $penerima,
                        'JENIS_ORDER' => $jenis_order
                    ];
                }

                for ($i = 0; $i < count($pdf_to_print); $i++) {
                    $kode_pengirim = array_keys($pdf_to_print)[$i];


                    $pdf = new DomPdf();
                    $pdf->loadHtml(View::make('lcl-packing-list', [
                        'nomor_konosemen' => $pdf_to_print[$kode_pengirim]['cust_details']['nomor_konosemen'],
                        'customer' => $pdf_to_print[$kode_pengirim]['cust_details']['nama'],
                        'alamat' => $pdf_to_print[$kode_pengirim]['cust_details']['alamat'],
                        'no_telp' => $pdf_to_print[$kode_pengirim]['cust_details']['telp'],
                        'rute' => $pdf_to_print[$kode_pengirim]['cust_details']['rute'],
                        'trip' => $trip_no,
                        'kapal' => 'KM. ' . $pdf_to_print[$kode_pengirim]['data']['items'][0]['kapal'],
                        'voyage' => $pdf_to_print[$kode_pengirim]['data']['items'][0]['voyage_no'],
                        'tanggal_berangkat' => $pdf_to_print[$kode_pengirim]['data']['items'][0]['tgl'],
                        'tanggal_sampai' => $pdf_to_print[$kode_pengirim]['data']['items'][0]['tgls'],
                        'total_barang' => $pdf_to_print[$kode_pengirim]['data']['total_semua_qty'],
                        'total_m3' => $pdf_to_print[$kode_pengirim]['data']['total_semua_m3'],
                        'total_berat' => $pdf_to_print[$kode_pengirim]['data']['total_semua_berat'],
                        'barang' => $pdf_to_print[$kode_pengirim]['data']['items'],

                    ])->render());
                    $pdf->setPaper('A4', 'portrait');
                    $pdf->render();
                    //check if $pdf_to_print[$kode_pengirim]['cust_details']['nama'] is free of characters that are not allowed in file names and replace them with whitespace
                    $pdf_to_print[$kode_pengirim]['cust_details']['nama'] = preg_replace('/[^A-Za-z0-9\-]/', ' ', $pdf_to_print[$kode_pengirim]['cust_details']['nama']);
                    //with counter
                    $pdfFileName = 'Packing List ' . $pdf_to_print[$kode_pengirim]['cust_details']['nama'] . ' (' . $i + 1 . ').pdf';
                    // $pdfFileName = 'Packing List ' . $container . '.pdf';
                    //just return the pdf and don't save it
                    // return $pdf->stream($pdfFileName);

                    $pdfFilePath = storage_path('app/' . $pdfFileName);
                    file_put_contents($pdfFilePath, $pdf->output());
                    $zip->addFile($pdfFilePath, $pdfFileName);
                    $array_pdf_to_delete[] = $pdfFilePath;
                }

                $zip->close();
                // //delete all the pdf files
                foreach ($array_pdf_to_delete as $pdf_to_delete) {
                    unlink($pdf_to_delete);
                }

                //get the binary content of the zip file, so we can delete it before returning the response
                $zipFileBinary = file_get_contents($zipFilePath);
                //delete the zip file
                unlink($zipFilePath);
                //return the binary content of the zip file
                return response($zipFileBinary)
                    ->header('Content-Type', 'application/zip')
                    ->header('Content-Disposition', 'attachment; filename="' . $zipFileName . '"');
            }
        } catch (\Exception $e) {
            // Exception occurred
            return response()->json(['message' => $e->getMessage(), 'success' => false], 200);
        }
    }



    private function getCellByRowAndColumn($row, $column)
    {
        return $this->sheet->getCell([$column, $row])->getValue();
    }


    public function testApi()
    {
        $container = 'MRLU 2361256';
        $seal = 'H 936126';
        $response = Http::post('https://krislines.com/api/v1.0/segel', [
            'token' => 'YjYyNzhiZjFkNDUwYWJmMzVhYWM5NDkwNTA0ZWEyZWI=',
            'segel' => $seal,
            'kontainer' => $container,
        ]);

        $response = json_decode($response->getBody()->getContents(), true);
        $header = $response['data'][0]['header'];
        $detail = $response['data'][0]['detail'][0];
        $barangs = $response['data'][0]['detail'];
        return $barangs;
    }
}
