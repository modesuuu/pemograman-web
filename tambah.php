<?php
session_start();
require 'koneksi.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// halaman tambah data ini khusus admin, user biasa balik ke daftar
if (($_SESSION['role'] ?? 'user') !== 'admin') {
    header("Location: mata_kuliah.php?pesan=readonly");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nim = $_POST['kode_mata_kuliah '];
    $nama = $_POST['nama'];
    $prodi = $_POST['semester'];

    $stmt = $connection->prepare("INSERT INTO mata_kuliah (KODE, NAMA, SEMESTER) VALUES (:kode, :nama, :semester)");
    $stmt->bindParam(':kode', $nim);
    $stmt->bindParam(':nama', $nama);
    $stmt->bindParam(':semester', $prodi);
    $stmt->execute();

    header("Location: index.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="id" data-theme="light">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Data</title>
    <link href="./dist/output.css" rel="stylesheet">
</head>

<body class="p-8 bg-base-200 min-h-screen flex justify-center items-center">

    <div class="card w-full max-w-md bg-base-100 shadow-xl p-6">
        <h2 class="card-title mb-4">Tambah Mata Kuliah</h2>

        <form action="" method="POST" class="space-y-4">
            <div class="form-control">
                <label class="label">
                    <span class="label-text">Kode Mata Kuliah</span>
                </label>
                <input type="text" name="nama" placeholder="Masukkan kode mata kuliah" class="input input-bordered w-full" required />
            </div>

            <div class="form-control">
                <label class="label">
                    <span class="label-text">Semester</span>
                </label>
                <input type="number" name="semester" placeholder="Masukkan semester" class="input input-bordered w-full" required />
            </div>

            <div class="flex justify-end gap-2 mt-6">
                <a href="index.php" class="btn btn-ghost">Batal</a>
                <button type="submit" class="btn btn-primary">Simpan</button>
            </div>
        </form>
    </div>

</body>

</html>