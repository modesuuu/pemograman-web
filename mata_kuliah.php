<?php
session_start();
require 'koneksi.php';

// halaman ini ikut sistem login yang sama, belum login ya balik ke login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// role dipakai buat nentuin read only atau nggak
$roleUser = $_SESSION['role'] ?? 'user';
$isAdmin = $roleUser === 'admin';

// user biasa itu read only, percobaan ubah data langsung ditolak di server
if ((isset($_POST['aksi']) && $_POST['aksi'] === 'tambah' && !$isAdmin) || (isset($_GET['hapus']) && !$isAdmin)) {
    header("Location: mata_kuliah.php?pesan=readonly");
    exit();
}

// Add
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['aksi']) && $_POST['aksi'] === 'tambah') {
    $kode   = $_POST['kode'];
    $nama  = $_POST['nama'];
    $semester = $_POST['semester'];

    if (!empty($kode) && !empty($nama) && !empty($semester)) {
        $stmt = $connection->prepare("INSERT INTO mata_kuliah (kode, nama, semester) VALUES (:kode, :nama, :semester)");
        $stmt->bindParam(':kode', $kode);
        $stmt->bindParam(':nama', $nama);
        $stmt->bindParam(':semester', $semester);
        $stmt->execute();

        header("Location: mata_kuliah.php");
        exit();
    }
}

// Deleted
if (isset($_GET['hapus'])) {
    $id = $_GET['hapus'];
    $stmt = $connection->prepare("DELETE FROM mata_kuliah WHERE id = :id");
    $stmt->bindParam(':id', $id);
    $stmt->execute();

    header("Location: mata_kuliah.php");
    exit();
}

// (READ)
$stmt = $connection->query("SELECT * FROM mata_kuliah ORDER BY id asc");
$data = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="id" data-theme="light">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Mahasiswa</title>
    <link rel="stylesheet" href="dist/output.css">
</head>

<body class="p-8 bg-base-200 min-h-screen">
    <div class="max-w-6xl mx-auto mb-4"><a href="index.php" class="btn btn-ghost btn-sm">&larr; Kembali ke Dashboard</a></div>
<div class="max-w-6xl mx-auto overflow-x-auto rounded-box p-6 border border-base-content/5 bg-base-100 shadow-md">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-bold text-base-content">Daftar Mata Kuliah</h2>
            <?php if ($isAdmin): ?><button onclick="modal_tambah.showModal()" class="btn btn-primary btn-sm">+ Tambah Data</button><?php endif; ?>
        </div>

        <?php if (!$isAdmin): ?>
<div class="alert alert-info mb-4"><span>Kamu login sebagai user biasa (read only), jadi cuma bisa melihat data. Tambah, edit, dan hapus data hanya untuk admin.</span></div>
<?php endif; ?>
<?php if (isset($_GET['pesan']) && $_GET['pesan'] === 'readonly'): ?>
<div class="alert alert-warning mb-4"><span>Aksi ditolak: akun read only tidak bisa mengubah data.</span></div>
<?php endif; ?>
<table class="table table-zebra w-full">
            <thead>
                <tr>
                    <th>Id</th>
                    <th>Kode</th>
                    <th>Nama</th>
                    <th>Semester</th>
                    <th class="text-center">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($data) > 0): ?>
                    <?php foreach ($data as $row): ?>
                        <tr class="hover">
                            <td><?= htmlspecialchars($row['id']) ?></td>
                            <td><?= htmlspecialchars($row['kode']) ?></td>
                            <td><?= htmlspecialchars($row['nama']) ?></td>
                            <td><?= htmlspecialchars($row['semester']) ?></td>
                            <td class="text-center space-x-1">
                                <?php if ($isAdmin): ?><a href="edit.php?id=<?= $row['id']; ?>" class="btn btn-sm btn-warning">Edit</a>
                                <a href="mata_kuliah.php?hapus=<?= $row['id']; ?>" onclick="return confirm('Yakin ingin menghapus data ini?')" class="btn btn-sm btn-error text-white">Hapus</a><?php else: ?><span class="text-gray-400 text-sm">Read only</span><?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5" class="text-center py-6 text-gray-500">
                            Belum ada data mahasiswa.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- modal tambah -->
    <dialog id="modal_tambah" class="modal modal-bottom sm:modal-middle">
        <div class="modal-box">
            <h3 class="font-bold text-lg mb-4">Tambah Data</h3>

            <form action="" method="POST" class="space-y-4">
                <input type="hidden" name="aksi" value="tambah">

                <div class="form-control">
                    <label class="label">
                        <span class="label-text">Kode Mata Kuliah</span>
                    </label>
                    <input type="text" name="kode" placeholder="Masukkan Kode Mata Kuliah" class="input input-bordered w-full" required />
                </div>

                <div class="form-control">
                    <label class="label">
                        <span class="label-text">Nama Mata Kuliah</span>
                    </label>
                    <input type="text" name="nama" placeholder="Masukkan Nama Mata Kuliah" class="input input-bordered w-full" required />
                </div>

                <div class="form-control">
                    <label class="label">
                        <span class="label-text">Semester</span>
                    </label>
                    <input type="number" name="semester" placeholder="Masukkan Semester" class="input input-bordered w-full" required />
                </div>

                <div class="modal-action mt-6">
                    <button type="button" onclick="modal_tambah.close()" class="btn btn-ghost">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>

        <form method="dialog" class="modal-backdrop">
            <button>close</button>
        </form>
    </dialog>
</body>

</html>