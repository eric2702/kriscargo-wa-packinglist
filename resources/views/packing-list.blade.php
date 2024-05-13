<!DOCTYPE html>
<html lang="en">

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>
    <style>
        /* set body font with calibri*/
        body {
            font-family: Arial, sans-serif !important;
        }


        h1,
        h2,
        h3,
        h4,
        h5,
        h6 {
            margin: 0;
            /* fontsize smaller */
        }

        h1 {
            font-size: 1.5em;
        }

        h2 {
            font-size: 1.3em;
        }

        h3 {
            font-size: 1.1em;
        }

        h4 {
            font-size: 1em;
        }

        h5 {
            font-size: 0.9em;
        }

        h6 {
            font-size: 0.75em;
        }

        p {
            margin: 0;
            /* fontsize smaller */
            font-size: 0.75em;
        }

        table {
            border-collapse: collapse;
            width: 100%;
        }

        /* tr second td must be text-align righ */
        .info tr td:nth-child(2) {
            text-align: right;
        }

        .info td {
            /* maximum width */
            max-width: 50px !important;
        }

        /* give inside border to .barang */
        .barang th {
            border: 1px solid #000;
        }

        .barang td,
        .barang th {
            /* border: 1px solid #000; */
            /* no need horizontal */
            /* border-bottom: 1px solid #000; */
            /* only lft and right */
            border-left: 1px solid #000;
            border-right: 1px solid #000;

        }

        table td {
            vertical-align: top;
        }

        .clearfix::after {
            content: "";
            display: table;
            clear: both;
        }

        /* Align the packing list to the right */
        .packing-list-container {
            text-align: right;
        }

        .footer td {
            width: 33.33%;
        }
    </style>
</head>

<body>
    <table class="header">
        <tr>
            <td>
                <img style="height:70px; float:left;"
                    src="data:image/png;base64,{{ base64_encode(file_get_contents(public_path('/images/logo.png'))) }}"
                    alt="">
                <div style="margin-left:10px">
                    <h5 style="">PT KRIS CARGO BAHTERA</h5>
                    <p>Jl. Bubutan 16-22, Blok B-18</p>
                    <p>Surabaya</p>
                    <p>Telp. 031-5326656 / 031-5326657</p>
                    <p><a href="https://www.kriscargo.co.id">www.kriscargo.co.id</a></p>
                </div>
            </td>

            <td>
                <div class="packing-list-container">
                    <h3>PACKING LIST</h3>
                    <p>PL.{{ $nomor_konosemen }}</p>
                </div>
            </td>
        </tr>
    </table>

    <div class="clearfix"></div>
    <hr style="border: none; border-top: 1px dotted black;">

    <table class="info">
        <tr>
            <td>
                <h6>CUSTOMER: <span style="font-weight: normal">{{ $customer }}</span></h6>
            </td>
            <td>
                <h6>RUTE: <span style="font-weight: normal">{{ $rute }}</span></h6>
            </td>
        </tr>
        <tr>
            <td>
                <h6>ALAMAT: <span style="font-weight: normal">{{ $alamat }}</span></h6>
            </td>
            <td>
                <h6>KAPAL: <span style="font-weight: normal">{{ $kapal }}</span></h6>
            </td>
            {{-- <td>
                <h6>TRIP: <span style="font-weight: normal">{{ $trip }}</span></h6>
            </td> --}}
        </tr>
        <tr>
            <td>
                <h6>NO TELP: <span style="font-weight: normal">{{ $no_telp }}</span></h6>
            </td>
            <td>
                <h6>VOYAGE: <span style="font-weight: normal">{{ $voyage }}</span></h6>
            </td>

        </tr>
        <tr>

            <td>
                {{-- <h6>TOP: <span style="font-weight: normal">{{ $top }}</span></h6> --}}
            </td>
            <td>
                <h6>ATD: <span style="font-weight: normal">{{ $tanggal_berangkat }}</span></h6>
            </td>
            {{-- <td>
                <h6>JENIS KIRIMAN / TYPE CONT: <span style="font-weight: normal">{{ $jenis_kiriman_type_cont }}</span>
                </h6>
            </td> --}}
        </tr>
        <tr>
            <td>
                {{-- <h6>LOKASI BAYAR: <span style="font-weight: normal">{{ $lokasi_bayar }}</span></h6> --}}
            </td>
            <td>
                <h6>ETA: <span style="font-weight: normal">{{ $tanggal_sampai }}</span></h6>

            </td>
        </tr>
        <tr>
            <td></td>
            <td>
                {{-- <h6>BERITA ACARA: <span style="font-weight: normal">{{ $berita_acara }}</span></h6> --}}
            </td>
        </tr>
        <tr>
            <td>
                {{-- <h6>CUSTOMER PENERIMA: <span style="font-weight: normal">{{ $customer_penerima }}</span></h6> --}}
            </td>
            <td>
                {{-- <h6>BURUH BONGKAR: <span style="font-weight: normal">{{ $buruh_bongkar }}</span></h6> --}}
            </td>
        </tr>
        <tr>
            <td>
                {{-- <h6>ALAMAT PENERIMA: <span style="font-weight: normal">{{ $alamat_penerima }}</span></h6> --}}
            </td>
        </tr>
    </table>

    {{-- make a table with header background gray --}}
    {{-- make a table with header background gray --}}
    <table class="barang" style="border-collapse: collapse; margin-top:20px;">
        <thead style="background-color: #999999;">
            <tr>
                <th style="padding: 5px;">
                    <h6>NO</h6>
                </th>
                <th style="padding: 5px;">
                    <h6>QTY</h6>
                </th>

                <th style="padding: 5px;">
                    <h6>NAMA BARANG</h6>
                </th>
                <th style="padding: 5px;">
                    <h6>P</h6>
                </th>
                <th style="padding: 5px;">
                    <h6>L</h6>
                </th>
                <th style="padding: 5px;">
                    <h6>T</h6>
                </th>
                <th style="padding: 5px;">
                    <h6>W</h6>
                </th>
                <th style="padding: 5px;">
                    <h6>TOTAL M3</h6>
                </th>
                <th style="padding: 5px;">
                    <h6>TOTAL BERAT</h6>
                </th>
                <th style="padding: 5px;">
                    <h6>CONTAINER</h6>
                </th>
                <th style="padding: 5px;">
                    <h6>JENIS ORDER</h6>
                </th>
                <th style="padding: 5px;">
                    <h6>PENERIMA</h6>
                </th>
            </tr>
        </thead>
        <tbody>
            @foreach ($barang as $item)
                <tr>


                    <td>
                        <p style="padding: 5px; text-align:center">{{ $loop->iteration }}</p>
                    </td>
                    <td>
                        <p style="padding: 5px; text-align:center">{{ $item['QTY'] }}</p>
                    </td>

                    <td>
                        <p style="padding: 5px; text-align:center">{{ $item['NAMA_BARANG'] }}</p>
                    </td>
                    <td>
                        <p style="padding: 5px; text-align:right">{{ $item['P'] }}</p>
                    </td>
                    <td>
                        <p style="padding: 5px; text-align:right">{{ $item['L'] }}</p>
                    </td>
                    <td>
                        <p style="padding: 5px; text-align:right">{{ $item['T'] }}</p>
                    </td>
                    <td>
                        <p style="padding: 5px; text-align:right">{{ $item['W'] }}</p>
                    </td>
                    <td>
                        <p style="padding: 5px; text-align:right">{{ $item['TOTAL M3'] }}</p>
                    </td>
                    <td>
                        <p style="padding: 5px; text-align:right">{{ $item['TOTAL BERAT'] }}</p>
                    </td>
                    <td>
                        @php
                            $no_cont_no_space = str_replace(' ', '', $item['NO_CONT']);
                        @endphp
                        <p style="padding: 5px; text-align:center"><a
                                href="https://kriscargo.co.id/index.php?track={{ $no_cont_no_space }}">{{ $item['NO_CONT'] }}</a>
                        </p>
                    </td>
                    <td>
                        <p style="padding: 5px; text-align:center">{{ $item['JENIS_ORDER'] }}</p>
                    </td>
                    <td>
                        <p style="padding: 5px; text-align:center">{{ $item['PENERIMA'] }}</p>
                    </td>
                </tr>
            @endforeach

            {{-- give some kind of a thead of total barang (spans 3 column) --}}
            <tr style="border: 1px solid #000;">
                <td colspan="3" style="background-color: #999999; padding: 5px;">
                    <h6>Total Barang: {{ $total_barang }}</h6>
                </td>
                <td colspan="4" style="background-color: #999999; padding: 5px;"></td>
                <td colspan="1" style="background-color: #999999; padding: 5px; text-align:right;">
                    <h6>{{ $total_m3 }}</h6>
                </td>
                <td colspan="1" style="background-color: #999999; padding: 5px;text-align:right;">
                    <h6>{{ $total_berat }}</h6>
                </td>
                <td colspan="3" style="background-color: #999999; padding: 5px;text-align:right;">
                </td>
            </tr>
        </tbody>
    </table>


    <table style="margin-top: 40px" class="footer">
        <tr>
            <td style="padding:5px">
                <div style="border: 2px solid #000; text-align:center; padding: 15px;">
                    <h6 style="text-decoration: underline">ISI KIRIMAN TIDAK DIPERIKSA</h6>
                    <p style="margin-top:5px;">Claim tidak dapat diproses sesudah barang diterima serta surat jalan &
                        packing list sudah
                        ditanda tangan</p>
                </div>
            </td>
            <td style="padding:5px">
                <div style="border: 2px solid #000; text-align:center; padding: 5px; background: black; color: white;">
                    <h6 style="">TANDA TANGAN & STEMPEL</h6>
                    <p style="margin-top:5px;">KEMBALI KE SURABAYA</p>
                </div>
            </td>
            <td style="padding:5px;text-align:right">
                <p style="font-size: 0.6em; color:#bbbbbb">Date of print:___________, by:___________</p>
                <p style="margin-top:65px;">Surabaya,_______________2024</p>
            </td>
        </tr>

    </table>

    <table style="text-align: center; margin-top:10px" class="footer">
        <tr>
            <td>
                <h6>DIKIRIM OLEH:</h6>
                <p>Vendor</p>
                <p style="margin-top:80px;color:#bbbbbb">( ______<span style="text-decoration: underline;">Nama
                        Terang</span>______ )
                </p>

            </td>
            <td>
                <h6>MENGETAHUI:</h6>
                <p>Ekspedisi</p>
                <p style="margin-top:80px;color:#bbbbbb">( <span style="text-decoration: underline;">Nama Lengkap &
                        Stempel</span> )
                </p>
            </td>
            <td>
                <h6>DITERIMA OLEH:</h6>
                <p>(Stempel & Tanda Tangan)</p>
                <p style="margin-top:80px;color:#bbbbbb">( <span style="text-decoration: underline;">Nama Lengkap &
                        Stempel</span> )
                </p>
            </td>
        </tr>

    </table>


</body>

</html>
