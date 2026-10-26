<!-- yang ini masih saya pelajari dan juga kembangkan lagi
Adelia-250030069 -->
<?php

// CLASS BUKU
class Buku {
    private $kode;
    private $judul;
    private $penulis;
    private $stok;

    public function __construct($kode, $judul, $penulis, $stok) {
        $this->kode = $kode;
        $this->judul = $judul;
        $this->penulis = $penulis;
        $this->stok = max(0, (int) $stok);
    }

    public function pinjamBuku() {
        if ($this->stok <= 0) {
            return false;
        }

        $this->stok--;
        return true;
    }

    public function kembalikanBuku() {
        $this->stok++;
    }

    public function getKode() {
        return $this->kode;
    }

    public function getJudul() {
        return $this->judul;
    }

    public function getPenulis() {
        return $this->penulis;
    }

    public function getStok() {
        return $this->stok;
    }
}


// CLASS ANGGOTA
class Anggota {
    private $nomorAnggota;
    private $nama;

    public function __construct($nomorAnggota, $nama) {
        $this->nomorAnggota = $nomorAnggota;
        $this->nama = $nama;
    }

    public function getNomorAnggota() {
        return $this->nomorAnggota;
    }

    public function getNama() {
        return $this->nama;
    }
}


// CLASS PEMINJAMAN
class Peminjaman {
    private $kode;
    private $buku;
    private $anggota;
    private $tanggalPinjam;
    private $lamaPinjam;
    private $status;

    public function __construct(
        $kode,
        Buku $buku,
        Anggota $anggota,
        $lamaPinjam
    ) {
        $this->kode = $kode;
        $this->buku = $buku;
        $this->anggota = $anggota;
        $this->tanggalPinjam = date("d-m-Y");
        $this->lamaPinjam = max(1, (int) $lamaPinjam);
        $this->status = "Belum dipinjam";
    }

    public function pinjam() {
        if ($this->status == "Dipinjam") {
            return "Buku ini sudah dipinjam.";
        }

        if ($this->status == "Dikembalikan") {
            return "Transaksi ini sudah selesai.";
        }

        if (!$this->buku->pinjamBuku()) {
            $this->status = "Stok habis";
            return "Peminjaman gagal. Stok buku habis.";
        }

        $this->status = "Dipinjam";
        return "Peminjaman berhasil.";
    }

    public function kembalikan() {
        if ($this->status != "Dipinjam") {
            return "Buku tidak sedang dipinjam.";
        }

        $this->buku->kembalikanBuku();
        $this->status = "Dikembalikan";

        return "Buku berhasil dikembalikan.";
    }

    public function getKode() {
        return $this->kode;
    }

    public function getBuku() {
        return $this->buku;
    }

    public function getAnggota() {
        return $this->anggota;
    }

    public function getTanggalPinjam() {
        return $this->tanggalPinjam;
    }

    public function getLamaPinjam() {
        return $this->lamaPinjam;
    }

    public function getStatus() {
        return $this->status;
    }
}


// DATA AWAL
$buku1 = new Buku("B001", "Laskar Pelangi", "Andrea Hirata", 3);
$buku2 = new Buku("B002", "Bumi", "Tere Liye", 2);
$buku3 = new Buku("B003", "Laut Bercerita", "Leila S. Chudori", 1);

$anggota1 = new Anggota("A001", "Adelia");
$anggota2 = new Anggota("A002", "Made");

$buku = [$buku1, $buku2, $buku3];
$anggota = [$anggota1, $anggota2];
$peminjaman = [];

$pesan = "";
$jenisPesan = "sukses";


// MEMPROSES FORM
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $aksi = $_POST["aksi"] ?? "";

    if ($aksi == "pinjam") {
        $kodeBuku = $_POST["buku"] ?? "";
        $kodeAnggota = $_POST["anggota"] ?? "";
        $lama = (int) ($_POST["lama"] ?? 0);

        $bukuDipilih = null;
        $anggotaDipilih = null;

        foreach ($buku as $item) {
            if ($item->getKode() == $kodeBuku) {
                $bukuDipilih = $item;
                break;
            }
        }

        foreach ($anggota as $item) {
            if ($item->getNomorAnggota() == $kodeAnggota) {
                $anggotaDipilih = $item;
                break;
            }
        }

        if ($bukuDipilih === null || $anggotaDipilih === null) {
            $pesan = "Buku atau anggota tidak ditemukan.";
            $jenisPesan = "gagal";
        } elseif ($lama < 1 || $lama > 30) {
            $pesan = "Lama pinjam harus antara 1 sampai 30 hari.";
            $jenisPesan = "gagal";
        } else {
            $nomor = count($peminjaman) + 1;

            $transaksi = new Peminjaman(
                "P" . str_pad($nomor, 3, "0", STR_PAD_LEFT),
                $bukuDipilih,
                $anggotaDipilih,
                $lama
            );

            $pesan = $transaksi->pinjam();

            if ($transaksi->getStatus() == "Dipinjam") {
                $peminjaman[] = $transaksi;
            } else {
                $jenisPesan = "gagal";
            }
        }
    }

    if ($aksi == "kembali") {
        $kodePinjam = $_POST["kode_pinjam"] ?? "";
        $ditemukan = false;

        foreach ($peminjaman as $transaksi) {
            if ($transaksi->getKode() == $kodePinjam) {
                $pesan = $transaksi->kembalikan();
                $ditemukan = true;

                if ($transaksi->getStatus() != "Dikembalikan") {
                    $jenisPesan = "gagal";
                }

                break;
            }
        }

        if (!$ditemukan) {
            $pesan = "Data peminjaman tidak ditemukan.";
            $jenisPesan = "gagal";
        }
    }
}


// MENGHITUNG DATA
$totalStok = 0;

foreach ($buku as $item) {
    $totalStok += $item->getStok();
}

$totalDipinjam = 0;

foreach ($peminjaman as $item) {
    if ($item->getStatus() == "Dipinjam") {
        $totalDipinjam++;
    }
}

function e($nilai) {
    return htmlspecialchars((string) $nilai, ENT_QUOTES, "UTF-8");
}

?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistem Peminjaman Buku</title>

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            padding: 30px 18px;
            font-family: Arial, sans-serif;
            background: #f3f6f8;
            color: #183047;
        }

        .container {
            max-width: 1050px;
            margin: auto;
        }

        h1 {
            margin-bottom: 8px;
        }

        .subtitle {
            color: #687b8a;
            margin-bottom: 25px;
        }

        .cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(190px, 1fr));
            gap: 16px;
            margin-bottom: 22px;
        }

        .card, .panel {
            background: white;
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0 3px 12px rgba(20, 50, 70, 0.05);
        }

        .label {
            color: #687b8a;
            font-size: 14px;
        }

        .number {
            font-size: 28px;
            font-weight: bold;
            margin-top: 10px;
        }

        .panel {
            margin-bottom: 22px;
            overflow-x: auto;
        }

        h2 {
            margin-top: 0;
            font-size: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 14px;
        }

        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #e5ebef;
        }

        th {
            background: #eaf1f5;
        }

        input, select {
            width: 100%;
            padding: 11px;
            margin: 7px 0 15px;
            border: 1px solid #ccd6de;
            border-radius: 7px;
        }

        button {
            border: none;
            padding: 11px 16px;
            background: #285e19;
            color: white;
            border-radius: 7px;
            cursor: pointer;
        }

        button:hover {
            background: #1d4812;
        }

        .btn-kembali {
            background: #244d69;
        }

        .pesan {
            padding: 13px;
            border-radius: 8px;
            margin-bottom: 20px;
            background: #e2f5e7;
            color: #216438;
        }

        .pesan.gagal {
            background: #fde8e7;
            color: #a52e2e;
        }

        .status {
            font-weight: bold;
        }

        .kosong {
            color: #a52e2e;
        }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(190px, 1fr));
            gap: 12px;
        }

        @media (max-width: 600px) {
            body {
                padding: 20px 10px;
            }

            th, td {
                padding: 9px;
                font-size: 13px;
            }
        }
    </style>
</head>

<body>
<div class="container">

    <h1>Sistem Peminjaman Buku</h1>
    <p class="subtitle">
        Aplikasi perpustakaan sederhana menggunakan PHP OOP.
    </p>

    <?php if ($pesan != "") { ?>
        <div class="pesan <?= $jenisPesan == "gagal" ? "gagal" : "" ?>">
            <?= e($pesan) ?>
        </div>
    <?php } ?>

    <div class="cards">
        <div class="card">
            <div class="label">Jumlah Judul Buku</div>
            <div class="number"><?= count($buku) ?></div>
        </div>

        <div class="card">
            <div class="label">Total Stok Tersedia</div>
            <div class="number"><?= $totalStok ?></div>
        </div>

        <div class="card">
            <div class="label">Sedang Dipinjam</div>
            <div class="number"><?= $totalDipinjam ?></div>
        </div>

        <div class="card">
            <div class="label">Jumlah Anggota</div>
            <div class="number"><?= count($anggota) ?></div>
        </div>
    </div>


    <div class="panel">
        <h2>Daftar Buku</h2>

        <table>
            <tr>
                <th>Kode</th>
                <th>Judul Buku</th>
                <th>Penulis</th>
                <th>Stok</th>
                <th>Status</th>
            </tr>

            <?php foreach ($buku as $item) { ?>
                <tr>
                    <td><?= e($item->getKode()) ?></td>
                    <td><?= e($item->getJudul()) ?></td>
                    <td><?= e($item->getPenulis()) ?></td>
                    <td><?= $item->getStok() ?></td>
                    <td>
                        <?php if ($item->getStok() > 0) { ?>
                            Tersedia
                        <?php } else { ?>
                            <span class="kosong">Stok habis</span>
                        <?php } ?>
                    </td>
                </tr>
            <?php } ?>
        </table>
    </div>


    <div class="panel">
        <h2>Form Peminjaman</h2>

        <form method="POST">
            <input type="hidden" name="aksi" value="pinjam">

            <div class="form-grid">
                <div>
                    <label>Pilih Buku</label>
                    <select name="buku" required>
                        <?php foreach ($buku as $item) { ?>
                            <option value="<?= e($item->getKode()) ?>">
                                <?= e($item->getJudul()) ?>
                                (Stok: <?= $item->getStok() ?>)
                            </option>
                        <?php } ?>
                    </select>
                </div>

                <div>
                    <label>Pilih Anggota</label>
                    <select name="anggota" required>
                        <?php foreach ($anggota as $item) { ?>
                            <option value="<?= e($item->getNomorAnggota()) ?>">
                                <?= e($item->getNama()) ?>
                            </option>
                        <?php } ?>
                    </select>
                </div>

                <div>
                    <label>Lama Pinjam (hari)</label>
                    <input
                        type="number"
                        name="lama"
                        min="1"
                        max="30"
                        value="7"
                        required
                    >
                </div>
            </div>

            <button type="submit">Pinjam Buku</button>
        </form>
    </div>


    <div class="panel">
        <h2>Riwayat Peminjaman</h2>

        <table>
            <tr>
                <th>Kode Pinjam</th>
                <th>Anggota</th>
                <th>Buku</th>
                <th>Tanggal Pinjam</th>
                <th>Lama</th>
                <th>Status</th>
                <th>Aksi</th>
            </tr>

            <?php if (count($peminjaman) == 0) { ?>
                <tr>
                    <td colspan="7">Belum ada transaksi peminjaman.</td>
                </tr>
            <?php } ?>

            <?php foreach ($peminjaman as $item) { ?>
                <tr>
                    <td><?= e($item->getKode()) ?></td>
                    <td><?= e($item->getAnggota()->getNama()) ?></td>
                    <td><?= e($item->getBuku()->getJudul()) ?></td>
                    <td><?= e($item->getTanggalPinjam()) ?></td>
                    <td><?= $item->getLamaPinjam() ?> hari</td>
                    <td class="status"><?= e($item->getStatus()) ?></td>
                    <td>
                        <?php if ($item->getStatus() == "Dipinjam") { ?>
                            <form method="POST">
                                <input
                                    type="hidden"
                                    name="aksi"
                                    value="kembali"
                                >
                                <input
                                    type="hidden"
                                    name="kode_pinjam"
                                    value="<?= e($item->getKode()) ?>"
                                >
                                <button class="btn-kembali" type="submit">
                                    Kembalikan
                                </button>
                            </form>
                        <?php } else { ?>
                            -
                        <?php } ?>
                    </td>
                </tr>
            <?php } ?>
        </table>
    </div>

</div>
</body>
</html>
