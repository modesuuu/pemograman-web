<?php
session_start();
require 'koneksi.php';

// halaman ini ikut sistem login yang sama, belum login ya balik ke login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// edit data itu aksi ubah data, jadi khusus admin aja
// user biasa read only, langsung balik ke daftar
if (($_SESSION['role'] ?? 'user') !== 'admin') {
    header("Location: mata_kuliah.php?pesan=readonly");
    exit();
}

$id = $_GET['id'] ?? null;
if (!$id) {
    header("Location: mata_kuliah.php");
    exit();
}

// simpan perubahan pas form disubmit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $kode     = trim($_POST['kode'] ?? '');
    $nama     = trim($_POST['nama'] ?? '');
    $semester = $_POST['semester'] ?? '';
    $sks      = $_POST['sks'] ?? null;

    if ($kode !== '' && $nama !== '' && $semester !== '') {
        $stmt = $connection->prepare("UPDATE mata_kuliah SET kode = :kode, nama = :nama, semester = :semester, sks = :sks WHERE id = :id");
        $stmt->bindParam(':kode', $kode);
        $stmt->bindParam(':nama', $nama);
        $stmt->bindParam(':semester', $semester);
        $stmt->bindParam(':sks', $sks);
        $stmt->bindParam(':id', $id);
        $stmt->execute();

        header("Location: mata_kuliah.php");
        exit();
    }
}

// ambil data lamanya buat ngisi form
$stmt = $connection->prepare("SELECT * FROM mata_kuliah WHERE id = :id");
$stmt->bindParam(':id', $id);
$stmt->execute();
$data = $stmt->fetch();

if (!$data) {
    header("Location: mata_kuliah.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="id" data-theme="light">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Mata Kuliah</title>
    <link rel="stylesheet" href="dist/output.css">
</head>

<body class="p-8 bg-base-200 min-h-screen flex justify-center items-center">

    <div class="card w-full max-w-md bg-base-100 shadow-xl p-6">
        <h2 class="card-title mb-4">Edit Mata Kuliah</h2>

        <form action="" method="POST" class="space-y-4">
            <div class="form-control">
                <label class="label">
                    <span class="label-text">Kode Mata Kuliah</span>
                </label>
                <input type="text" name="kode" class="input input-bordered w-full" value="<?= htmlspecialchars($data['kode']) ?>" required />
            </div>

            <div class="form-control">
                <label class="label">
                    <span class="label-text">Nama Mata Kuliah</span>
                </label>
                <input type="text" name="nama" class="input input-bordered w-full" value="<?= htmlspecialchars($data['nama']) ?>" required />
            </div>

            <div class="form-control">
                <label class="label">
                    <span class="label-text">Semester</span>
                </label>
                <input type="number" name="semester" class="input input-bordered w-full" value="<?= htmlspecialchars($data['semester']) ?>" required />
            </div>

            <div class="form-control">
                <label class="label">
                    <span class="label-text">SKS</span>
                </label>
                <input type="number" name="sks" class="input input-bordered w-full" value="<?= htmlspecialchars($data['sks'] ?? '') ?>" />
            </div>

            <div class="flex justify-end gap-2 mt-6">
                <a href="mata_kuliah.php" class="btn btn-ghost">Batal</a>
                <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
            </div>
        </form>
    </div>

</body>

</html>
