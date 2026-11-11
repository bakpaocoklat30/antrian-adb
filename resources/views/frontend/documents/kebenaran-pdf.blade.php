<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Surat Pernyataan Kebenaran Dokumen</title>
    <style>
        @page { margin: 1.5cm 2cm 1.5cm 2cm; }
        
        body { 
            font-family: "Times New Roman", Times, serif; 
            font-size: 11pt; 
            line-height: 1.5; 
            color: #000;
        }
        
        .text-center { text-align: center; }
        .text-justify { text-align: justify; }
        .font-bold { font-weight: bold; }
        
        .judul {
            font-size: 12pt;
            font-weight: bold;
            text-align: center;
            margin-bottom: 20px;
        }

        table.form-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            margin-bottom: 10px;
        }
        
        table.form-table td {
            vertical-align: top;
            padding: 2px 0; 
        }
        
        .col-label { width: 140px; }
        .col-titik { width: 15px; text-align: center; }

        .list-angka {
            margin-top: 5px;
            margin-bottom: 10px;
            padding-left: 20px;
        }
        
        .list-angka li {
            text-align: justify;
            margin-bottom: 5px; 
        }

        .ttd-table {
            width: 100%;
            margin-top: 20px; 
            text-align: center;
            border-collapse: collapse;
        }
        
        .ttd-table td {
            padding: 2px;
        }
        
        .materai-text {
            font-size: 8pt;
            margin-bottom: 40px; 
            display: block;
        }
        
        .catatan {
            margin-top: 20px;
            font-size: 10pt; 
            font-style: italic;
        }
    </style>
</head>
<body>

    <div class="judul">
        SURAT PERNYATAAN KEBENARAN DOKUMEN<br>
        PERSYARATAN SPMB SMA NEGERI DAN SMK NEGERI<br>
        PROVINSI JAWA TENGAH TAHUN AJARAN 2026/2027
    </div>

    <div>Yang bertanda tangan di bawah ini :</div>

    <table class="form-table">
        <tr>
            <td class="col-label">Nama</td>
            <td class="col-titik">:</td>
            <td><strong>{{ $ortu['nama'] }}</strong></td>
        </tr>
        <tr>
            <td class="col-label">Alamat</td>
            <td class="col-titik">:</td>
            <td>{{ $ortu['alamat'] }}</td>
        </tr>
        <tr>
            <td class="col-label">Pekerjaan</td>
            <td class="col-titik">:</td>
            <td>{{ $ortu['pekerjaan'] }}</td>
        </tr>
    </table>

    <div class="text-justify" style="margin-top: 10px;">
        Merupakan orang tua/wali*) dari Calon Murid yang mengikuti proses seleksi SPMB SMA Negeri/SMK Negeri Provinsi Jawa Tengah Tahun Ajaran 2026/2027:
    </div>

    <table class="form-table">
        <tr>
            <td class="col-label">Nama</td>
            <td class="col-titik">:</td>
            <td><strong>{{ $siswa['nama'] }}</strong></td>
        </tr>
        <tr>
            <td class="col-label">N I S N</td>
            <td class="col-titik">:</td>
            <td>{{ $siswa['nisn'] }}</td>
        </tr>
        <tr>
            <td class="col-label">Alamat</td>
            <td class="col-titik">:</td>
            <td>{{ $siswa['alamat'] }}</td>
        </tr>
        <tr>
            <td class="col-label">Asal Sekolah</td>
            <td class="col-titik">:</td>
            <td>{{ $siswa['asal_sekolah'] }}</td>
        </tr>
    </table>

    <div style="margin-top: 10px;">Menyatakan bahwa :</div>

    <ol class="list-angka">
        <li>Seluruh data/dokumen yang dipergunakan calon Murid tersebut di atas adalah benar dan dapat kami pertanggungjawabkan sesuai ketentuan yang berlaku, baik dalam proses perolehannya maupun hal-hal yang berkaitan dengan dokumen yang bersangkutan.</li>
        <li>Apabila kami terbukti melanggar Surat Pernyataan yang telah kami tanda tangani, maka kami bersedia menerima sanksi anak kami tersebut di atas dibatalkan/dinyatakan gugur sebagai peserta SPMB, dan bersedia pula mempertanggungjawabkan secara hukum.</li>
    </ol>

    <div class="text-justify" style="margin-top: 10px;">
        Demikian Surat Pernyataan ini kami buat dengan sebenar-benarnya, untuk dapat dipergunakan sebagaimana mestinya, dan kepada yang berkepentingan untuk menjadikan maklum.
    </div>

    <table class="ttd-table">
        <tr>
            <td colspan="2" style="text-align: center; padding-bottom: 5px;">
                {{ $lokasi_cetak }}, {{ $tanggal_cetak }}
            </td>
        </tr>
        <tr>
            <td style="width: 50%; text-align: center;">
                Calon Murid,
            </td>
            <td style="width: 50%; text-align: center;">
                Yang membuat,
            </td>
        </tr>
        <tr>
            <td style="height: 110px; vertical-align: bottom; text-align: center;">
                <strong>{{ $siswa['nama'] }}</strong>
            </td>
            <td style="height: 110px; vertical-align: bottom; text-align: center;">
                <span class="materai-text">Materai Rp. 10.000,-</span>
                <strong>{{ $ortu['nama'] }}</strong>
            </td>
        </tr>
    </table>

    <div class="catatan">
        Catatan :<br>
        1) *) pilih yang sesuai<br>
        2) Surat pernyataan boleh ditulis tangan atau diketik ulang dengan redaksi yang sama<br>
        &nbsp;&nbsp;&nbsp;&nbsp;(tidak melakukan perubahan redaksi)
    </div>

</body>
</html>