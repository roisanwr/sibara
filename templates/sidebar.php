<?php
// templates/sidebar.php

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

function is_active($page_name) {
    if (strpos($_SERVER['SCRIPT_NAME'], $page_name) !== false) {
        return 'active';
    }
    return '';
}

$peran = isset($_SESSION['peran']) ? $_SESSION['peran'] : '';

// Jika BASE_URL belum didefinisikan (untuk jaga-jaga), definisikan di sini.
if (!defined('BASE_URL')) {
    define('BASE_URL', '/sibara/');
}

?>
<aside class="sidebar h-screen sticky top-0 left-0 overflow-y-auto soft-shadow-lg flex flex-col transition-all duration-300">
    <!-- Sidebar Header -->
    <div class="p-4 border-b" style="border-color: var(--border-color);">
        <a href="<?php echo BASE_URL; ?>home.php" class="flex items-center gap-3">
             <svg class="w-8 h-8 text-orange-500" viewBox="0 0 24 24" fill="currentColor">
                <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8zm-1-13h2v6h-2zm0 8h2v2h-2z"/>
            </svg>
            <span class="sidebar-menu-text font-bold text-lg whitespace-nowrap" style="color: var(--text-primary);">SIBara</span>
        </a>
    </div>

     <!-- Sidebar Menu -->
    <nav class="flex-1 p-4 space-y-2">
        <!-- Menu Utama -->
        <p class="px-3 py-2 text-xs font-bold uppercase" style="color: var(--text-muted);">Menu Utama</p>
        
        <a href="<?php echo BASE_URL; ?>home.php" class="sidebar-menu-item <?php echo is_active('home.php'); ?> flex items-center gap-4 p-3 rounded-lg">
            <svg class="w-6 h-6 menu-icon-fill" viewBox="0 0 24 24" fill="currentColor"><path d="M3 13h8V3H3v10zm0 8h8v-6H3v6zm10 0h8V11h-8v10zm0-18v6h8V3h-8z"/></svg>
            <span class="sidebar-menu-text font-medium whitespace-nowrap">Dashboard</span>
        </a>

        <!-- Menu Dinamis Berdasarkan Peran -->
        <?php if ($peran == 'karyawan-toko'): ?>
            <!-- ================= MENU KARYAWAN TOKO ================= -->
            <a href="<?php echo BASE_URL; ?>permintaan/permintaan-lihat.php" class="sidebar-menu-item <?php echo is_active('permintaan-'); ?> flex items-center gap-4 p-3 rounded-lg">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path class="menu-icon-path" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path></svg>
                <span class="sidebar-menu-text font-medium whitespace-nowrap">Verifikasi Permintaan</span>
            </a>
            <!-- <a href="<?php echo BASE_URL; ?>laporan/laporan-lihat.php" class="sidebar-menu-item <?php echo is_active('laporan-'); ?> flex items-center gap-4 p-3 rounded-lg">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path class="menu-icon-path" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V7a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                <span class="sidebar-menu-text font-medium whitespace-nowrap">Laporan</span>
            </a> -->

        <?php elseif ($peran == 'staff-gudang'): ?>
            <!-- ================= MENU STAFF GUDANG ================= -->
            <p class="px-3 py-2 text-xs font-bold uppercase" style="color: var(--text-muted);">Manajemen Gudang</p>
            
            <a href="<?php echo BASE_URL; ?>permintaan/permintaan-lihat.php" class="sidebar-menu-item <?php echo is_active('permintaan-'); ?> flex items-center gap-4 p-3 rounded-lg">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path class="menu-icon-path" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                <span class="sidebar-menu-text font-medium whitespace-nowrap">Verifikasi Permintaan</span>
            </a>
            
            <!-- PERUBAHAN: Menu baru untuk Riwayat Transaksi ditambahkan di sini -->
            <a href="<?php echo BASE_URL; ?>transaksi/transaksi-lihat.php" class="sidebar-menu-item <?php echo is_active('transaksi-'); ?> flex items-center gap-4 p-3 rounded-lg">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path class="menu-icon-path" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"></path></svg>
                <span class="sidebar-menu-text font-medium whitespace-nowrap">Penerimaan Barang</span>
            </a>

             <a href="<?php echo BASE_URL; ?>barang/barang-lihat.php" class="sidebar-menu-item <?php echo is_active('barang-'); ?> flex items-center gap-4 p-3 rounded-lg">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path class="menu-icon-path" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"></path></svg>
                <span class="sidebar-menu-text font-medium whitespace-nowrap">Data Barang</span>
            </a>
            <a href="<?php echo BASE_URL; ?>laporan/laporan-lihat.php" class="sidebar-menu-item <?php echo is_active('laporan-'); ?> flex items-center gap-4 p-3 rounded-lg">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path class="menu-icon-path" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V7a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                <span class="sidebar-menu-text font-medium whitespace-nowrap">Laporan</span>
            </a>
        <?php endif; ?>

    </nav>
</aside>
