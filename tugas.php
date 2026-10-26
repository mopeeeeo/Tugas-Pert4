<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tugas 4</title>
</head>
<body>

<!-- Adelia -250030069     -->
<?php

class Buku {
    public $judul;
    public $penulis;
    public $stok;

    public function __construct($judul, $penulis, $stok) {
        $this->judul = $judul;
        $this->penulis = $penulis;
        $this->stok = $stok;
    }

    public function tampilkanInfo() {
        echo "Judul Buku : " . $this->judul . "<br>";
        echo "Penulis : " . $this->penulis . "<br>";
        echo "Stok : " . $this->stok . "<br>";
    }
    public function pinjamBuku() {
        if($this->stok > 0) {
            $this->stok--;
            return true;
        }
        return false;
    }
    public function bukuKembali(){
        $this->stok++;
    }
}

class Anggota {
    public $nama;
    public $noAnggota;

    public function __construct($nama, $noAnggota) {
        $this->nama = $nama;
        $this->noAnggota = $noAnggota;
    }

    public function tampilkanInfo() {
        echo "Nama Anggota : " . $this->nama . "<br>";
        echo "Nomor Anggota : " . $this->noAnggota . "<br>";
    }
}

class Peminjaman {
    public $buku;
    public $anggota;
    public $lamaPinjam;
    public $status;

    public function __construct($buku, $anggota, $lamaPinjam) {
        $this->buku = $buku;
        $this->anggota = $anggota;
        $this->lamaPinjam = $lamaPinjam;
        $this->status = "belum dipinjam";
    }
    public function pinjam(){
        if ($this->status == "Dipinjam") {
            return "Buku sudah dipinjam sebelumnya.";
        }

        if ($this->status == "Dikembalikan") {
            return "Peminjaman sudah selesai.";
        }

        if ($this->buku->pinjamBuku()) {
            $this->status = "Dipinjam";
            return "Peminjaman berhasil.";
        }

        $this->status = "Gagal";
        return "Peminjaman gagal, stok buku habis.";
    }
    public function kembalikan() {
        if ($this->status != "Dipinjam") {
            return "Buku tidak sedang dipinjam.";
        }

        $this->buku->bukuKembali();
        $this->status = "Dikembalikan";

        return "Buku berhasil dikembalikan.";
    }


    public function tampilPeminjaman() {
        echo "<h3>Data Peminjaman</h3>";

        $this->anggota->tampilkanInfo();

        echo "<br>";

        $this->buku->tampilkanInfo();

        echo "Lama Pinjam : " . $this->lamaPinjam . " hari <br>";
        echo "Status Peminjaman : " . $this->status . "<br>";
    }
}

$buku1 = new Buku("Bumi Manusia", "Pramoedya Ananta Toer", 5);
$anggota1 = new Anggota("MopMop", "A001");
$pinjam1 = new Peminjaman($buku1, $anggota1, 7);
echo "<h3>Proses Peminjaman</h3>";
echo $pinjam1->pinjam() . "<br><br>";


$pinjam1->tampilPeminjaman();

echo "<h3>Proses Pengembalian</h3>";
echo $pinjam1->kembalikan() . "<br><br>";

$pinjam1->tampilPeminjaman();

?>

</body>
</html>