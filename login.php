<?php
session_start();
require 'koneksi.php';

// udah login? langsung lempar ke dashboard, nggak usah login dua kali
if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$error = "";
$sukses = "";

// habis logout diarahkan ke sini lagi, kasih tau biar jelas prosesnya selesai
if (isset($_GET['pesan']) && $_GET['pesan'] === 'logout') {
    $sukses = "Kamu sudah logout. Sampai jumpa!";
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username === '' || $password === '') {
        $error = "Username dan password wajib diisi.";
    } else {
        // cari usernya dulu, passwordnya dicek terpisah pakai password_verify
        // karena di database yang disimpan itu hash, bukan password aslinya
        $stmt = $connection->prepare("SELECT * FROM users WHERE username = :username");
        $stmt->bindParam(':username', $username);
        $stmt->execute();
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id']  = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['nama']     = $user['nama'];
            $_SESSION['role']     = $user['role'];

            header("Location: index.php");
            exit();
        } else {
            // pesannya disamakan aja mau username atau password yang salah,
            // biar nggak ketahuan bagian mana yang keliru
            $error = "Username atau password salah.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id" data-theme="light">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Login — Edu-Center</title>
<link rel="icon" href="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 32 32'%3E%3Crect width='32' height='32' rx='8' fill='%235D5FEF'/%3E%3Ctext x='16' y='22' font-family='sans-serif' font-size='18' font-weight='700' fill='white' text-anchor='middle'%3EE%3C/text%3E%3C/svg%3E">
<script src="https://cdn.tailwindcss.com"></script>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:ital,wght@0,400;0,500;0,600;0,700;0,800;1,400&display=swap" rel="stylesheet">
<style>
  :root, [data-theme="light"] {
    --brand: #5D5FEF;
    --brand-ink: #ffffff;
    --page-bg: #f5f7fb;
    --card-bg: #ffffff;
    --card-border: #e6ebf2;
    --ink: #0f172a;
    --muted: #64748b;
    --focus: #5D5FEF;
    --input-bg: #ffffff;
  }
  body {
    font-family: "Plus Jakarta Sans", ui-sans-serif, system-ui, sans-serif;
    background: var(--page-bg);
    color: var(--ink);
  }
  :is(a, button, input):focus-visible {
    outline: 3px solid var(--focus);
    outline-offset: 2px;
    border-radius: 6px;
  }
  .card {
    background: var(--card-bg);
    border: 1px solid var(--card-border);
    border-radius: 1rem;
  }
  .btn-brand {
    background: var(--brand);
    color: var(--brand-ink);
    border-radius: 0.75rem;
    font-weight: 700;
    transition: filter 150ms ease, transform 150ms ease;
  }
  .btn-brand:hover { filter: brightness(1.07); }
  .btn-brand:active { transform: translateY(1px); }
  .field {
    background: var(--input-bg);
    border: 1px solid var(--card-border);
    border-radius: 0.75rem;
    color: var(--ink);
    width: 100%;
  }
  .field::placeholder { color: var(--muted); opacity: 1; }
</style>
</head>
<body class="min-h-screen flex items-center justify-center p-4">

  <main class="card w-full max-w-md p-8 shadow-sm">
    <div class="flex items-center gap-3 mb-2">
      <div class="w-11 h-11 rounded-xl flex items-center justify-center text-white text-xl font-extrabold" style="background: var(--brand);">E</div>
      <div>
        <h1 class="text-xl font-extrabold leading-tight">Edu-Center</h1>
        <p class="text-sm" style="color: var(--muted);">Dashboard</p>
      </div>
    </div>

    <h2 class="text-2xl font-bold mt-6">Masuk</h2>
    <p class="text-sm mt-1 mb-6" style="color: var(--muted);">Pakai akun yang sudah terdaftar untuk melanjutkan.</p>

    <?php if ($error !== ""): ?>
      <div class="mb-4 rounded-xl px-4 py-3 text-sm font-semibold text-red-700 bg-red-50 border border-red-200" role="alert">
        <?= htmlspecialchars($error) ?>
      </div>
    <?php endif; ?>

    <?php if ($sukses !== ""): ?>
      <div class="mb-4 rounded-xl px-4 py-3 text-sm font-semibold text-green-700 bg-green-50 border border-green-200" role="status">
        <?= htmlspecialchars($sukses) ?>
      </div>
    <?php endif; ?>

    <form method="POST" action="">
      <label for="username" class="block text-sm font-semibold mb-2">Username</label>
      <input type="text" id="username" name="username" class="field px-4 py-3 mb-4" placeholder="Masukkan username" autocomplete="username" value="<?= htmlspecialchars($_POST['username'] ?? '') ?>">

      <label for="password" class="block text-sm font-semibold mb-2">Password</label>
      <input type="password" id="password" name="password" class="field px-4 py-3" placeholder="Masukkan password" autocomplete="current-password">

      <label class="flex items-center gap-2 mt-3 text-sm cursor-pointer" style="color: var(--muted);">
        <input type="checkbox" id="lihat-password" class="rounded">
        Lihat password
      </label>

      <button type="submit" class="btn-brand w-full py-3 mt-6">Masuk</button>
    </form>

    <div class="mt-6 rounded-xl px-4 py-3 text-sm" style="background: var(--page-bg); color: var(--muted);">
      Akun demo untuk mencoba:<br>
      Admin (akses penuh): <strong style="color: var(--ink);">admin</strong> / <strong style="color: var(--ink);">admin123</strong><br>
      User biasa (read only): <strong style="color: var(--ink);">user</strong> / <strong style="color: var(--ink);">user123</strong>
    </div>
  </main>

<script>
  // checkbox kecil buat intip password, biar nggak salah ketik pas demo
  document.getElementById('lihat-password').addEventListener('change', function () {
    document.getElementById('password').type = this.checked ? 'text' : 'password';
  });
</script>
</body>
</html>
