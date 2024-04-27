<?php

namespace App\Http\Controllers;

use Dompdf\Dompdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\View;
use PhpOffice\PhpSpreadsheet\IOFactory;
use ZipArchive;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

class PackingListController extends Controller
{
    private $sheet;
    public function waBlastPackingList(Request $request)
    {
        // Your existing code to generate payslip data goes here...
        $file = $request->file('file');
        $spreadsheet = IOFactory::load($file);
        // Calculation::getInstance($spreadsheet)->calculate();
        //read the data from the excel file
        $sheet = $spreadsheet->getActiveSheet();
        $this->sheet = $sheet;

        try {
            $vessel = $this->getCellByRowAndColumn(1, 2);
            $voyage = $this->getCellByRowAndColumn(2, 2);
            $pol = $this->getCellByRowAndColumn(3, 2);
            $pod = $this->getCellByRowAndColumn(4, 2);
            $tdVessel = $this->getCellByRowAndColumn(5, 2);
            $etaVendor = $this->getCellByRowAndColumn(6, 2);

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
                ];
                $row++;
            }

            //for from 0 to 1
            for ($i = 0; $i < 1; $i++) {
                $pdf = new DomPdf();
                $pdf->loadHtml(View::make('packing-list', [
                    'nomor_konosemen' => 'KC.SUB.REO.2403.9.053 (PTD)',
                    'customer' => 'PT. Karya Cipta',
                    'alamat' => 'Jl. Raya Cikarang - Cibarusah KM 3,5',
                    'no_telp' => '021-8987654',
                    'top' => '30 Hari sesudah bongkar',
                    'lokasi_bayar' => 'Surabaya',
                    'rute' => $pol . '-' . $pod,
                    'trip' => '9',
                    'kapal' => 'KM. ' . $vessel . ' Voy ' . $voyage,
                    'jenis_kiriman_type_cont' => 'FCL / ' . $table[$i]['size'] . 'FT ' . $table[$i]['type'],
                    'tanggal_berangkat' => $tdVessel,
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
                ])->render());
                $pdf->setPaper('A4', 'portrait');
                $pdf->render();
                $pdfFileName = 'Packing List ' . time() . '.pdf';
                //just return the pdf and don't save it
                return $pdf->stream($pdfFileName);
            }



            // return response()->json(['message' => $table, 'success' => true], 200);
        } catch (\Exception $e) {
            // Exception occurred
            return response()->json(['message' => $e->getMessage(), 'success' => false], 500);
        }
    }

    private function getCellByRowAndColumn($row, $column)
    {
        return $this->sheet->getCell([$column, $row])->getValue();
    }
}
