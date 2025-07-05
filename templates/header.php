<?php
// templates/header.php

// Pastikan BASE_URL sudah didefinisikan
if (!defined('BASE_URL')) {
    define('BASE_URL', '/sibara/');
}

// Menyiapkan variabel-variabel dinamis dengan nilai default
// Halaman yang memanggil file ini bisa menimpanya (override)
$page_title = isset($page_title) ? $page_title : 'Dashboard';
$breadcrumbs = isset($breadcrumbs) ? $breadcrumbs : 'Pages';
$action_button = isset($action_button) ? $action_button : ''; // Defaultnya tidak ada tombol aksi

// Mengambil data pengguna dari sesi (harus ada dari auth_check.php)
$username = isset($_SESSION['username']) ? $_SESSION['username'] : 'Pengguna';
$peran = isset($_SESSION['peran']) ? $_SESSION['peran'] : '';

?>
<!DOCTYPE html>
<html lang="id" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($page_title); ?> - SIBARA</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/index.css">
</head>
<body class="bg-gray-100" style="--bg-main: #f0f2f5;">
    <div id="app-container" class="relative min-h-screen flex">
        <div id="mobile-overlay" class="mobile-overlay"></div>
        
        <?php require_once 'sidebar.php'; // Sidebar sekarang dipanggil dari sini ?>

        <!-- Konten Utama Dimulai di sini, membungkus Header dan Main -->
        <div class="main-content flex-1 flex flex-col">
            
            <!-- Header Halaman (Top Bar) yang sekarang terpusat -->
            <header class="sticky top-0 z-10 p-4 lg:px-8">
                <div class="header w-full container mx-auto rounded-lg soft-shadow-lg p-4 flex justify-between items-center transition-colors duration-300">
                    
                    <!-- Sisi Kiri: Tombol & Judul Dinamis -->
                    <div class="flex items-center gap-4">
                        <button id="sidebar-toggle-button" class="p-2 rounded-lg theme-aware-hover">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7"></path></svg>
                        </button>
                        <div>
                            <p class="text-sm" style="color: var(--text-secondary);"><?php echo htmlspecialchars($breadcrumbs); ?></p>
                            <h1 class="text-lg font-bold" style="color: var(--text-primary);"><?php echo htmlspecialchars($page_title); ?></h1>
                        </div>
                    </div>

                    <!-- Sisi Kanan: Tombol Aksi Dinamis & Profil -->
                    <div class="flex items-center gap-4">
                        <?php echo $action_button; // Mencetak tombol aksi jika ada ?>
                        
                        <div class="relative">
                            <button id="profile-button" class="flex items-center gap-2">
                               <img src="https://i.pravatar.cc/150?u=<?php echo $username; ?>" class="w-10 h-10 rounded-full" alt="Avatar">
                               <div class="hidden md:block text-right">
                                   <p class="font-semibold text-sm" style="color: var(--text-primary);"><?php echo htmlspecialchars($username); ?></p>
                                   <p class="text-xs" style="color: var(--text-secondary);"><?php echo htmlspecialchars(ucwords(str_replace('-', ' ', $peran))); ?></p>
                               </div>
                            </button>
                            <div id="profile-dropdown" class="dropdown absolute right-0 mt-2 w-48 rounded-lg soft-shadow p-2" style="background-color: var(--bg-card);">
                                <a href="<?php echo BASE_URL; ?>logout.php" class="block px-4 py-2 text-sm text-red-500 rounded-lg theme-aware-hover">Keluar</a>
                            </div>
                        </div>
                    </div>
                </div>
            </header>
