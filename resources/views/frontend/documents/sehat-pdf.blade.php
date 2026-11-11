<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Surat Pernyataan Sehat</title>
    <style>
        @page { margin: 2cm 2.5cm 2cm 2.5cm; } 
        
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
            margin-bottom: 30px;
            text-decoration: underline;
        }

        table.form-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
            margin-bottom: 15px;
        }
        
        table.form-table td {
            vertical-align: top;
            padding: 3px 0; 
        }
        
        .col-label { width: 120px; }
        .col-titik { width: 15px; text-align: center; }

        .ttd-table {
            width: 100%;
            margin-top: 30px; 
            text-align: center;
            border-collapse: collapse;
        }
        
        .ttd-table td {
            padding: 0; 
        }

        /* 🔥 FIX POSISI & SPACE: Memberikan space longgar di bawah tanggal sebelum teks "Yang membuat," 🔥 */
        .tanggal-row td {
            padding-bottom: 20px; 
        }
        
        .catatan {
            margin-top: 40px;
            font-size: 10pt;
            font-style: italic;
        }
    </style>
</head>
<body>

    <div class="judul">
        SURAT PERNYATAAN SEHAT
    </div>

    <div>Yang bertanda tangan di bawah ini :</div>

    <table class="form-table">
        <tr>
            <td class="col-label">Nama</td>
            <td class="col-titik">:</td>
            <td class="text-justify"><strong>{{ $siswa['nama'] }}</strong></td>
        </tr>
        <tr>
            <td class="col-label">N I S N</td>
            <td class="col-titik">:</td>
            <td class="text-justify">{{ $siswa['nisn'] }}</td>
        </tr>
        <tr>
            <td class="col-label">Alamat</td>
            <td class="col-titik">:</td>
            <td class="text-justify">
                Jalan {{ $alamat['jalan'] }}, RT {{ $alamat['rt'] }} / RW {{ $alamat['rw'] }}, Kelurahan/Desa {{ $alamat['desa'] }}, Kecamatan {{ $alamat['kecamatan'] }}, Kab/Kota {{ $alamat['kota'] }}
            </td>
        </tr>
    </table>

    <div class="text-justify" style="margin-top: 15px;">
        Dengan ini menyatakan bahwa : <br>
        Saya dalam kondisi sehat untuk pemenuhan aspek kesehatan yang dipersyaratkan dalam SPMB SMK Negeri pada pilihan program keahlian <strong>{{ $program_keahlian }}</strong>.**)
    </div>

    <div class="text-justify" style="margin-top: 15px;">
        Demikian Surat Pernyataan ini kami buat dengan sesungguhnya dan penuh tanggungjawab, dan apabila dikemudian hari diperoleh kesimpulan bahwa Surat Pernyataan ini tidak benar, maka saya siap menerima sanksi dibatalkan sebagai peserta SPMB meskipun saya dinyatakan diterima dalam seleksi SPMB Tahun Ajaran 2026/2027.
    </div>

    <table class="ttd-table">
        <tr class="tanggal-row">
            <td style="width: 50%;"></td>
            <td style="width: 50%;">
                {{ $lokasi_cetak }}, {{ $tanggal_cetak }}
            </td>
        </tr>
        <tr>
            <td style="width: 50%;">
                Mengetahui<br>
                Orangtua/Wali
            </td>
            <td style="width: 50%; vertical-align: bottom;">
                Yang membuat,
            </td>
        </tr>
        <tr>
            <td style="height: 80px; vertical-align: bottom;">
                <strong>{{ $ortu_nama }}</strong>
            </td>
            <td style="height: 80px; vertical-align: bottom;">
                <strong>{{ $siswa['nama'] }}</strong>
            </td>
        </tr>
    </table>

    <div class="catatan">
        *) pilih yang sesuai <br>
        *) Surat pernyataan dapat diketik atau ditulis tangan pada kertas polos/bergaris <br>
        **) Khusus Calon Murid SMK Negeri
    </div>

</body>
</html>