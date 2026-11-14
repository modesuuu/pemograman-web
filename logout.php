<?php
session_start();

// hapus semua data session, jadi index.php nggak bisa dibuka lagi tanpa login
session_destroy();

header("Location: login.php?pesan=logout");
exit();
?>
