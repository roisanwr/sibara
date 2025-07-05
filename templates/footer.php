<?php
// templates/footer.php

// Jika BASE_URL belum didefinisikan (untuk jaga-jaga), definisikan di sini.
if (!defined('BASE_URL')) {
    define('BASE_URL', '/sibara/');
}
?>
            <!-- Footer -->
            <footer class="p-4 lg:px-8 mt-auto">
                <div class="container mx-auto text-sm flex flex-col md:flex-row justify-between items-center" style="color: var(--text-secondary);">
                    <p>© <?php echo date("Y"); ?>, SIBARA - Sistem Informasi Barang</p>
                    <div class="flex gap-6 mt-4 md:mt-0">
                       <a href="#" class="hover:text-orange-500">Tentang Kami</a>
                       <a href="#" class="hover:text-orange-500">Bantuan</a>
                    </div>
                </div>
            </footer>
        </div> <!-- Penutup .main-content -->
    </div> <!-- Penutup #app-container -->

    <!-- PERBAIKAN: Menggunakan BASE_URL agar path selalu benar dari root -->
    <script src="<?php echo BASE_URL; ?>assets/js/index.js"></script>

</body>
</html>
