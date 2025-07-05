<?php
session_start();
// Pastikan untuk memanggil koneksi SEBELUM logika apapun yang butuh database.
include 'koneksi.php';

// Jika pengguna sudah login, langsung arahkan ke halaman home
if (isset($_SESSION['status']) && $_SESSION['status'] == "login") {
    header("location:home.php");
    exit;
}

$error = ''; // Variabel untuk menyimpan pesan error

// Proses login ketika form disubmit
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Amankan input username
    $username = mysqli_real_escape_string($koneksi, $_POST['username']);
    // Ambil password apa adanya untuk perbandingan
    $password_input = $_POST['password'];

    $sql = "SELECT * FROM pengguna WHERE username='$username'";
    $result = mysqli_query($koneksi, $sql);
    
    if ($result && mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        
        // Perbandingan password langsung (TIDAK AMAN, hanya untuk development)
        if ($password_input == $row['password']) { 
            // Jika login berhasil
            $_SESSION['id_pengguna'] = $row['id_pengguna']; // <-- PERUBAHAN DI SINI
            $_SESSION['username'] = $username;
            $_SESSION['peran'] = $row['peran']; 
            $_SESSION['status'] = "login";
            header("location:home.php");
            exit;
        } else {
            // Jika password salah
            $error = "Password yang Anda masukkan salah.";
        }
    } else {
        // Jika username tidak ditemukan
        $error = "Username tidak ditemukan.";
    }
}
?>
<!DOCTYPE html>
<html lang="id" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - SIBARA</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="assets/css/index.css">
</head>
<body style="--bg-main: #f0f2f5;">
    <div class="min-h-screen flex items-center justify-center p-4">
        <main class="w-full max-w-md">
            <div class="stat-card p-8 rounded-xl soft-shadow-lg">
                <div class="text-center mb-8">
                    <h1 class="text-3xl font-bold" style="color: var(--text-primary);">Selamat Datang!</h1>
                    <p class="mt-2 text-sm" style="color: var(--text-secondary);">Silakan login untuk melanjutkan ke SIBARA</p>
                </div>

                <form action="login.php" method="POST" class="space-y-6">
                    <div>
                        <label for="username" class="block text-sm font-medium" style="color: var(--text-secondary);">Username</label>
                        <input type="text" id="username" name="username" required
                               class="mt-1 block w-full px-4 py-3 rounded-lg border focus:outline-none focus:ring-2 focus:ring-orange-400"
                               style="background-color: var(--bg-main); border-color: var(--border-color);"
                               placeholder="Masukkan username Anda">
                    </div>

                    <div>
                        <label for="password" class="block text-sm font-medium" style="color: var(--text-secondary);">Password</label>
                        <input type="password" id="password" name="password" required
                               class="mt-1 block w-full px-4 py-3 rounded-lg border focus:outline-none focus:ring-2 focus:ring-orange-400"
                               style="background-color: var(--bg-main); border-color: var(--border-color);"
                               placeholder="Masukkan password Anda">
                    </div>
                    
                    <?php if(!empty($error)): ?>
                        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg relative" role="alert">
                            <span class="block sm:inline"><?php echo $error; ?></span>
                        </div>
                    <?php endif; ?>

                    <div>
                        <button type="submit"
                                class="w-full text-white font-bold py-3 px-4 rounded-lg transition-colors duration-300"
                                style="background-color: var(--accent-orange); hover:opacity-90;">
                            Login
                        </button>
                    </div>
                </form>
            </div>
        </main>
    </div>
</body>
</html>
