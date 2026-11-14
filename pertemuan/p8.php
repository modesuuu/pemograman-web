<?php
// p8.php — Pertemuan 8
// Form upload file + daftar hasil upload + preview.
// File yang diupload kesimpen di folder "uploads".

session_start(); // buat nitip pesan antar redirect (lihat bawah)

// --- pengaturan ---
$folderUpload  = __DIR__ . '/uploads/';  // file disimpen di sini
$batasUkuran   = 8 * 1024 * 1024;        // 8 MB per file
$ekstensiBoleh = [                       // cuma tipe ini yang boleh masuk
    'jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp', 'svg',
    'pdf',
    'mp4', 'webm', 'mov',
    'mp3', 'wav', 'ogg',
    'txt', 'csv', 'log',
    'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx',
    'zip', 'rar',
];

// --- fungsi bantuan ---

// bersihin nama file: buang karakter aneh biar aman
function namaAman($nama) {
    $nama = basename($nama);
    $nama = preg_replace('/[^A-Za-z0-9._-]/', '_', $nama);
    return $nama !== '' ? $nama : 'file';
}

// ubah byte jadi KB / MB biar gampang dibaca
function ukuranRapi($byte) {
    if ($byte >= 1048576) return number_format($byte / 1048576, 2) . ' MB';
    if ($byte >= 1024)    return number_format($byte / 1024, 1) . ' KB';
    return $byte . ' B';
}

// kelompokin file berdasarkan ekstensinya, buat nentuin preview-nya
function tipeFile($ekstensi) {
    $ekstensi = strtolower($ekstensi);
    if (in_array($ekstensi, ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp', 'svg'])) return 'gambar';
    if (in_array($ekstensi, ['mp4', 'webm', 'mov'])) return 'video';
    if (in_array($ekstensi, ['mp3', 'wav', 'ogg']))  return 'audio';
    if ($ekstensi === 'pdf') return 'pdf';
    return 'lain';
}

// balikin nama asli file (prefix uniknya dibuang)
function namaAsli($tersimpan) {
    return preg_replace('/^[0-9a-f]{13}_/', '', $tersimpan);
}

$notif = []; // pesan buat ditampilin ke user: ['ok'|'err', 'teksnya']

// ambil pesan titipan dari redirect sebelumnya (habis upload/hapus), sekali pakai aja
if (!empty($_SESSION['notif'])) {
    $notif = $_SESSION['notif'];
    unset($_SESSION['notif']);
}

// --- kalau ada yang klik "Unduh" ---
if (isset($_GET['unduh'])) {
    $file = $folderUpload . basename($_GET['unduh']);
    if (is_file($file)) {
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . namaAsli(basename($file)) . '"');
        header('Content-Length: ' . filesize($file));
        readfile($file);
        exit;
    }
    $notif[] = ['err', 'File-nya nggak ketemu, mungkin udah dihapus.'];
}

// --- kalau form-nya di-submit ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // tombol hapus dipencet
    if (isset($_POST['hapus'])) {
        $target = $folderUpload . basename($_POST['hapus']);
        if (is_file($target) && @unlink($target)) {
            $notif[] = ['ok', '"' . namaAsli(basename($target)) . '" udah dihapus.'];
        } else {
            $notif[] = ['err', 'File-nya nggak bisa dihapus.'];
        }
    }

    // ada file yang diupload (bisa banyak sekaligus)
    if (!empty($_FILES['berkas']['name'][0])) {
        if (!is_dir($folderUpload)) mkdir($folderUpload, 0755, true);

        $total = count($_FILES['berkas']['name']);
        for ($i = 0; $i < $total; $i++) {
            $nama = $_FILES['berkas']['name'][$i];
            $kode = $_FILES['berkas']['error'][$i];
            $size = $_FILES['berkas']['size'][$i];
            $tmp  = $_FILES['berkas']['tmp_name'][$i];

            if ($kode !== UPLOAD_ERR_OK) {
                $notif[] = ['err', "\"$nama\" gagal ke-upload (kode $kode)."];
                continue;
            }
            if ($size > $batasUkuran) {
                $notif[] = ['err', "\"$nama\" kegedean, maksimal 8 MB ya."];
                continue;
            }
            $ekstensi = strtolower(pathinfo($nama, PATHINFO_EXTENSION));
            if (!in_array($ekstensi, $ekstensiBoleh)) {
                $notif[] = ['err', "\"$nama\": file .$ekstensi nggak dibolehin."];
                continue;
            }

            // kasih nama unik biar nggak nimpa file lain yang namanya sama
            $simpan = $folderUpload . uniqid() . '_' . namaAman($nama);
            if (move_uploaded_file($tmp, $simpan)) {
                $notif[] = ['ok', "\"$nama\" berhasil di-upload."];
            } else {
                $notif[] = ['err', "\"$nama\" gagal kesimpen di server."];
            }
        }
    }

    // titip pesannya ke session, terus lempar balik ke halaman ini sebagai GET.
    // ini yang bikin kalau halamannya di-refresh, file-nya nggak ke-upload dua kali
    // dan browser nggak nampilin alert "confirm form resubmission" lagi.
    $_SESSION['notif'] = $notif;
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit;
}

// --- ambil semua file yang udah diupload, yang terbaru paling atas ---
$daftar = [];
if (is_dir($folderUpload)) {
    foreach (glob($folderUpload . '*') as $path) {
        if (!is_file($path)) continue;
        $daftar[] = [
            'tersimpan' => basename($path),
            'asli'      => namaAsli(basename($path)),
            'ukuran'    => filesize($path),
            'waktu'     => filemtime($path),
            'tipe'      => tipeFile(pathinfo($path, PATHINFO_EXTENSION)),
        ];
    }
    usort($daftar, fn($a, $b) => $b['waktu'] <=> $a['waktu']);
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Upload File — Pertemuan 8</title>
<style>
  :root { --biru:#2563eb; --bg:#f1f5f9; --teks:#1e293b; --abu:#64748b; --garis:#e2e8f0; }
  * { box-sizing:border-box; }
  body { margin:0; font-family:system-ui,-apple-system,"Segoe UI",Roboto,sans-serif; background:var(--bg); color:var(--teks); }
  .wrap { max-width:980px; margin:32px auto; padding:0 16px; }
  h1 { font-size:26px; margin:0 0 4px; }
  .sub { color:var(--abu); margin:0 0 20px; font-size:14px; }
  .card { background:#fff; border:1px solid var(--garis); border-radius:14px; padding:22px; margin-bottom:20px; box-shadow:0 1px 3px rgba(15,23,42,.06); }
  .card h2 { margin:0 0 14px; font-size:18px; }
  #dropzone { border:2px dashed #b6c2d2; border-radius:12px; padding:36px 20px; text-align:center; cursor:pointer; color:var(--abu); transition:.15s; }
  #dropzone:hover, #dropzone.over { border-color:var(--biru); background:#eff6ff; color:var(--biru); }
  #dropzone input { display:none; }
  #dipilih { margin:10px 0 0; padding:0; list-style:none; font-size:14px; color:var(--teks); text-align:left; }
  #dipilih li { padding:4px 0; }
  .btn { display:inline-block; border:0; border-radius:9px; padding:10px 22px; font-size:15px; font-weight:600; cursor:pointer; text-decoration:none; }
  .btn-utama { background:var(--biru); color:#fff; margin-top:14px; }
  .btn-utama:hover { background:#1d4ed8; }
  .btn-kecil { padding:7px 12px; font-size:13px; border-radius:7px; }
  .btn-lihat { background:#e0eaff; color:#1d4ed8; }
  .btn-unduh { background:#e6f7ed; color:#15803d; }
  .btn-hapus { background:#fee2e2; color:#b91c1c; }
  .aksi { display:flex; gap:8px; margin-top:10px; flex-wrap:wrap; }
  .aksi form { margin:0; }
  .msg { border-radius:10px; padding:10px 14px; margin:0 0 10px; font-size:14px; }
  .msg.ok { background:#dcfce7; color:#166534; }
  .msg.err { background:#fee2e2; color:#991b1b; }
  .grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(220px,1fr)); gap:14px; }
  .file { border:1px solid var(--garis); border-radius:12px; overflow:hidden; background:#fff; }
  .thumb { height:140px; background:#f8fafc; display:flex; align-items:center; justify-content:center; overflow:hidden; }
  .thumb img { width:100%; height:100%; object-fit:cover; }
  .badge { font-size:12px; font-weight:700; text-transform:uppercase; letter-spacing:.06em; color:var(--abu); background:#f1f5f9; padding:6px 12px; border-radius:999px; }
  .info { padding:12px 14px 14px; }
  .nama { font-weight:600; font-size:14px; word-break:break-all; }
  .meta { font-size:12px; color:var(--abu); margin-top:4px; }
  .kosong { text-align:center; color:var(--abu); padding:28px 0; }
  .catatan { font-size:13px; color:var(--abu); margin-top:10px; }
  /* Modal preview */
  #modal { display:none; position:fixed; inset:0; background:rgba(15,23,42,.65); z-index:50; align-items:center; justify-content:center; padding:20px; }
  #modal.buka { display:flex; }
  #modalBox { background:#fff; border-radius:14px; max-width:860px; width:100%; max-height:88vh; overflow:auto; padding:18px; position:relative; }
  #modalIsi img, #modalIsi video, #modalIsi iframe { width:100%; border-radius:8px; }
  #modalIsi iframe { height:70vh; border:1px solid var(--garis); }
  #modalIsi audio { width:100%; margin-top:12px; }
  #modalTutup { position:absolute; top:10px; right:12px; border:0; background:#f1f5f9; border-radius:50%; width:34px; height:34px; font-size:16px; cursor:pointer; }
  #modalJudul { font-weight:700; margin:0 40px 12px 0; word-break:break-all; font-size:15px; }
</style>
</head>
<body>
<div class="wrap">

  <h1>Upload File</h1>
  <p class="sub">Tugas Pertemuan 8 — upload file, lihat hasilnya, sekalian preview.</p>

  <?php foreach ($notif as [$jenis, $teks]): ?>
    <div class="msg <?= $jenis ?>"><?= htmlspecialchars($teks) ?></div>
  <?php endforeach; ?>

  <!-- FORM UPLOAD -->
  <div class="card">
    <h2>Pilih file</h2>
    <form method="post" enctype="multipart/form-data">
      <div id="dropzone">
        <input type="file" name="berkas[]" id="berkas" multiple>
        <strong>Klik di sini atau seret file ke kotak ini</strong><br>
        <span style="font-size:13px">Bisa banyak file sekaligus kok</span>
        <ul id="dipilih"></ul>
      </div>
      <button type="submit" class="btn btn-utama">Upload</button>
      <p class="catatan">Maksimal 8 MB per file. Boleh: gambar, PDF, video, audio, dokumen, teks, arsip.</p>
    </form>
  </div>

  <!-- HASIL UPLOAD -->
  <div class="card">
    <h2>Hasil upload (<?= count($daftar) ?>)</h2>
    <?php if (!$daftar): ?>
      <p class="kosong">Belum ada file yang diupload. Coba upload dulu di atas.</p>
    <?php else: ?>
      <div class="grid">
        <?php foreach ($daftar as $f):
          $url = 'uploads/' . rawurlencode($f['tersimpan']);
        ?>
        <div class="file">
          <div class="thumb">
            <?php if ($f['tipe'] === 'gambar'): ?>
              <img src="<?= $url ?>" alt="" loading="lazy">
            <?php else: ?>
              <span class="badge"><?= $f['tipe'] ?></span>
            <?php endif; ?>
          </div>
          <div class="info">
            <div class="nama"><?= htmlspecialchars($f['asli']) ?></div>
            <div class="meta"><?= ukuranRapi($f['ukuran']) ?> · <?= date('d M Y H:i', $f['waktu']) ?></div>
            <div class="aksi">
              <button type="button" class="btn btn-kecil btn-lihat"
                onclick="bukaModal('<?= $url ?>','<?= $f['tipe'] ?>','<?= htmlspecialchars($f['asli'], ENT_QUOTES) ?>')">Lihat</button>
              <a class="btn btn-kecil btn-unduh" href="?unduh=<?= rawurlencode($f['tersimpan']) ?>">Unduh</a>
              <form method="post" onsubmit="return confirm('Yakin mau hapus file ini?')">
                <input type="hidden" name="hapus" value="<?= htmlspecialchars($f['tersimpan']) ?>">
                <button type="submit" class="btn btn-kecil btn-hapus">Hapus</button>
              </form>
            </div>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>

</div>

<!-- MODAL PREVIEW -->
<div id="modal">
  <div id="modalBox">
    <button id="modalTutup" onclick="tutupModal()">✕</button>
    <p id="modalJudul"></p>
    <div id="modalIsi"></div>
  </div>
</div>

<script>
// tampilin nama file yang dipilih / diseret
const dz = document.getElementById('dropzone');
const inp = document.getElementById('berkas');
const list = document.getElementById('dipilih');

function tampilkanDipilih() {
  list.innerHTML = '';
  [...inp.files].forEach(f => {
    const li = document.createElement('li');
    li.textContent = '📄 ' + f.name;
    list.appendChild(li);
  });
}
dz.addEventListener('click', () => inp.click());
inp.addEventListener('change', tampilkanDipilih);
dz.addEventListener('dragover', e => { e.preventDefault(); dz.classList.add('over'); });
dz.addEventListener('dragleave', () => dz.classList.remove('over'));
dz.addEventListener('drop', e => {
  e.preventDefault(); dz.classList.remove('over');
  inp.files = e.dataTransfer.files; tampilkanDipilih();
});

// buka-tutup modal preview
function bukaModal(src, tipe, nama) {
  document.getElementById('modalJudul').textContent = nama;
  const isi = document.getElementById('modalIsi');
  if (tipe === 'gambar')      isi.innerHTML = `<img src="${src}" alt="">`;
  else if (tipe === 'video')  isi.innerHTML = `<video src="${src}" controls></video>`;
  else if (tipe === 'audio')  isi.innerHTML = `<audio src="${src}" controls></audio>`;
  else if (tipe === 'pdf')    isi.innerHTML = `<iframe src="${src}"></iframe>`;
  else isi.innerHTML = `<p>Tipe file ini nggak bisa di-preview, unduh aja langsung.</p><a class="btn btn-kecil btn-unduh" href="${src}" download>Unduh file</a>`;
  document.getElementById('modal').classList.add('buka');
}
function tutupModal() {
  document.getElementById('modal').classList.remove('buka');
  document.getElementById('modalIsi').innerHTML = '';  // biar video/audio berhenti
}
document.getElementById('modal').addEventListener('click', e => {
  if (e.target.id === 'modal') tutupModal();
});
document.addEventListener('keydown', e => { if (e.key === 'Escape') tutupModal(); });
</script>
</body>
</html>
