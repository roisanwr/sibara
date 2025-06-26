<?php
// Selalu mulai sesi di baris paling atas
session_start();

// Jika pengguna sudah login, langsung arahkan ke halaman home.php
if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true) {
    header('location: home.php');
    exit;
}

// Sertakan file koneksi.php
require_once 'koneksi.php';

// Definisikan variabel dan inisialisasi dengan nilai kosong
$username = $password = "";
$username_err = $password_err = $login_err = "";

// Proses data form ketika form di-submit
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Cek jika username kosong
    if (empty(trim($_POST["username"]))) {
        $username_err = "Silakan masukkan username.";
    } else {
        $username = trim($_POST["username"]);
    }

    // Cek jika password kosong
    if (empty(trim($_POST["password"]))) {
        $password_err = "Silakan masukkan password Anda.";
    } else {
        $password = trim($_POST["password"]);
    }

    // Validasi kredensial
    if (empty($username_err) && empty($password_err)) {
        // Siapkan statement SELECT
        $sql = "SELECT id_pengguna, username, password, peran FROM pengguna WHERE username = ?";

        if ($stmt = mysqli_prepare($conn, $sql)) {
            // Ikat variabel ke statement sebagai parameter
            mysqli_stmt_bind_param($stmt, "s", $param_username);
            $param_username = $username;

            // Eksekusi statement
            if (mysqli_stmt_execute($stmt)) {
                // Simpan hasil
                mysqli_stmt_store_result($stmt);

                // Cek jika username ada, jika ya, verifikasi password
                if (mysqli_stmt_num_rows($stmt) == 1) {
                    // Ikat hasil variabel. $db_password akan berisi password dari database
                    mysqli_stmt_bind_result($stmt, $id, $username, $db_password, $peran);
                    if (mysqli_stmt_fetch($stmt)) {
                        
                        // ===== PERUBAHAN DI SINI =====
                        // Bandingkan password yang diinput dengan yang ada di database (tanpa hash)
                        if ($password === $db_password) {
                            // Password benar, sesi sudah dimulai di atas
                            
                            // Simpan data dalam session
                            $_SESSION["loggedin"] = true;
                            $_SESSION["id_pengguna"] = $id;
                            $_SESSION["username"] = $username;
                            $_SESSION["peran"] = $peran;

                            // Arahkan ke halaman home
                            header("location: home.php");
                        } else {
                            // Password tidak benar
                            $login_err = "Username atau password salah.";
                        }
                    }
                } else {
                    // Username tidak ditemukan
                    $login_err = "Username atau password salah.";
                }
            } else {
                echo "Oops! Terjadi kesalahan. Silakan coba lagi nanti.";
            }

            // Tutup statement
            mysqli_stmt_close($stmt);
        }
    }

    // Tutup koneksi
    mysqli_close($conn);
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Sistem Gudang</title>
    <style>
        /* CSS Digabung di Sini */
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap');

        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f0f2f5;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }

        .login-container {
            background-color: #ffffff;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 400px;
            box-sizing: border-box;
        }

        h2 {
            text-align: center;
            color: #333;
            margin-bottom: 25px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #555;
            font-weight: 500;
        }

        .form-group input {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 8px;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
            transition: border-color 0.3s;
        }

        .form-group input:focus {
            outline: none;
            border-color: #007bff;
        }

        .btn-login {
            width: 100%;
            padding: 12px;
            background-color: #007bff;
            border: none;
            border-radius: 8px;
            color: white;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        .btn-login:hover {
            background-color: #0056b3;
        }

        .error-message {
            color: #dc3545;
            background-color: #f8d7da;
            border: 1px solid #f5c6cb;
            padding: 10px;
            border-radius: 5px;
            text-align: center;
            margin-bottom: 20px;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <h2>Login Sistem Gudang</h2>

        <?php
        if (!empty($login_err)) {
            echo '<div class="error-message">' . $login_err . '</div>';
        }
        ?>

        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" value="<?php echo $username; ?>">
                <span style="color:red; font-size:12px;"><?php echo $username_err; ?></span>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password">
                <span style="color:red; font-size:12px;"><?php echo $password_err; ?></span>
            </div>
            <div class="form-group">
                <button type="submit" class="btn-login">Login</button>
            </div>
        </form>
    </div>
</body>
</html>
