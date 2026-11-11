<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Surat Pernyataan Tidak Buta Warna</title>
    <style>
        @page { margin: 2.5cm 2.5cm 2.5cm 2.5cm; }
        
        body { 
            font-family: "Times New Roman", Times, serif; 
            font-size: 12pt; 
            line-height: 1.8; 
            color: #000;
        }
        
        .text-center { text-align: center; }
        .text-justify { text-align: justify; }
        .font-bold { font-weight: bold; }
        
        .judul {
            font-size: 14pt;
            font-weight: bold;
            text-align: center;
            margin-bottom: 30px;
            text-decoration: underline;
        }

        table.form-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
            margin-bottom: 20px;
        }
        
        table.form-table td {
            vertical-align: top;
            padding: 4px 0; 
        }
        
        .col-label { width: 170px; }
        .col-titik { width: 15px; text-align: center; }

        .ttd-table {
            width: 100%;
            margin-top: 30px; 
            text-align: center;
            border-collapse: collapse;
        }
        
        /* 🔥 FIX ALIGNMENT TANDA TANGAN 🔥 */
        .ttd-table td {
            vertical-align: top;
            line-height: 1.5; /* Memangkas jarak bawaan line-height */
        }

        .tanggal-row td {
            padding-bottom: 2px; /* Margin bawah sangat rapat */
        }
    </style>
</head>
<body>

    <div class="judul">
        SURAT PERNYATAAN TIDAK BUTA WARNA
    </div>

    <div>Yang bertanda tangan di bawah ini :</div>

    <table class="form-table">
        <tr>
            <td class="col-label">Nama</td>
            <td class="col-titik">:</td>
            <td class="text-justify"><strong>{{ $siswa['nama'] }}</strong></td>
        </tr>
        <tr>
            <td class="col-label">Tempat/Tanggal Lahir</td>
            <td class="col-titik">:</td>
            <td class="text-justify">{{ $siswa['tempat_lahir'] }}, {{ $siswa['tanggal_lahir'] }}</td>
        </tr>
        <tr>
            <td class="col-label">Nomor KTP / NIK</td>
            <td class="col-titik">:</td>
            <td class="text-justify">{{ $siswa['nik'] }}</td>
        </tr>
        <tr>
            <td class="col-label">Alamat</td>
            <td class="col-titik">:</td>
            <td class="text-justify">{{ $siswa['alamat'] }}</td>
        </tr>
    </table>

    <div class="text-justify" style="margin-top: 15px;">
        Sehubungan dengan pendaftaran Seleksi Penerimaan Murid Baru (SPMB) SMK Negeri 1 Adiwerna Tahun Pelajaran 2026/2027, dengan ini menyatakan bahwa saya tidak memiliki riwayat buta warna baik parsial maupun total.
    </div>

    <div class="text-justify" style="margin-top: 10px;">
        Apabila di kemudian hari ternyata ditemukan bahwa surat pernyataan yang saya tandatangani tidak sesuai dengan keadaan yang sebenarnya, Saya siap menerima sanksi tanpa mengajukan keberatan.
    </div>

    <div class="text-justify" style="margin-top: 10px;">
        Demikian pernyataan ini saya buat dengan sebenar-benarnya dan bersedia memper-Tanggung jawabkannya sesuai dengan mekanisme peraturan perundangan dan hukum yang berlaku.
    </div>

    <table class="ttd-table" style="margin-top: 30px; width: 100%; border-collapse: collapse;">
        <tr>
            <td style="width: 50%; vertical-align: top; text-align: left; padding-top: 30px; padding-left: 50px;">
                Mengetahui Orang Tua,<br>
                <br><br><br>
                ( <strong>{{ $ortu_nama }}</strong> )
            </td>
            
            <td style="width: 50%; vertical-align: top; text-align: left; padding-top: 7px; padding-left: 50px;">
                {{ $lokasi_cetak }}, {{ $tanggal_cetak }}<br>
                Yang membuat pernyataan,<br>
                <br><br><br>
                ( <strong>{{ $siswa['nama'] }}</strong> )
            </td>
        </tr>
    </table>

</body>
</html>