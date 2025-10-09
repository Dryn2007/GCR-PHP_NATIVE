<?php
require_once '../includes/header.php';

// Auth & Validasi
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Guru') {
    die("Akses ditolak.");
}
if (!isset($_GET['id'])) {
    die("ID Kelas tidak ditemukan.");
}
$kelas_id = $_GET['id'];
$guru_id = $_SESSION['user_id'];
$kelas_info = $conn->query("SELECT nama_kelas FROM kelas WHERE id = $kelas_id")->fetch_assoc();

// Query baru: Ambil SEMUA mapel di kelas ini, dan cari tahu siapa pengajarnya (jika ada)
$semua_mapel_q = $conn->prepare("
    SELECT m.id, m.nama_mapel, p.guru_id AS id_pengajar, u.nama AS nama_pengajar
    FROM mapel m
    LEFT JOIN pengajar p ON m.id = p.mapel_id
    LEFT JOIN users u ON p.guru_id = u.id
    WHERE m.kelas_id = ?
    ORDER BY m.nama_mapel ASC
");
$semua_mapel_q->bind_param("i", $kelas_id);
$semua_mapel_q->execute();
$semua_mapel_result = $semua_mapel_q->get_result();
?>

<a href="dashboard.php" class="inline-block bg-gray-500 hover:bg-gray-600 text-white font-semibold py-2 px-4 rounded mb-6">&larr; Kembali</a>
<h2 class="text-3xl font-bold text-gray-800 mb-6">Detail Kelas: <?php echo htmlspecialchars($kelas_info['nama_kelas']); ?></h2>

<div class="space-y-8">
    <?php while ($mapel = $semua_mapel_result->fetch_assoc()): ?>
        <?php
        $mapel_id = $mapel['id'];
        // Ambil materi & tugas untuk mapel ini
        $materi_list = $conn->query("SELECT * FROM materi WHERE mapel_id = $mapel_id ORDER BY created_at DESC");
        $tugas_list = $conn->query("SELECT * FROM tugas WHERE mapel_id = $mapel_id ORDER BY deadline DESC");
        ?>
        <div class="bg-white p-6 rounded-lg shadow-md border border-gray-200">
            <div class="border-b pb-3 mb-4">
                <h3 class="text-2xl font-bold text-blue-700"><?php echo htmlspecialchars($mapel['nama_mapel']); ?></h3>
                <p class="text-sm text-gray-600">
                    Pengajar:
                    <strong class="font-semibold"><?php echo $mapel['nama_pengajar'] ?? 'Belum ada'; ?></strong>
                </p>
            </div>

            <?php
            // ==========================================================
            // INI BAGIAN PALING PENTING
            // Tombol dan form hanya akan muncul jika mapel ini diajar oleh guru yang sedang login
            // ==========================================================
            if ($mapel['id_pengajar'] == $guru_id):
            ?>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <h4 class="text-lg font-semibold text-gray-700 mb-2">Kelola Materi</h4>
                        <div id="form-materi-<?php echo $mapel_id; ?>" style="display:none;" class="bg-gray-50 p-4 rounded-lg mb-3">
                            <form action="../proses/guru_proses.php" method="POST" enctype="multipart/form-data">
                                <input type="hidden" name="action" value="tambah_materi">
                                <input type="hidden" name="kelas_id" value="<?php echo $kelas_id; ?>">
                                <input type="hidden" name="mapel_id" value="<?php echo $mapel_id; ?>">
                                <input type="text" name="judul" placeholder="Judul Materi" class="w-full p-2 border rounded mb-2" required>
                                <input type="file" name="file_materi" class="w-full p-2 border rounded mb-2" required>
                                <button type="submit" class="bg-blue-600 text-white py-2 px-4 rounded">Upload</button>
                            </form>
                        </div>
                        <button onclick="toggleForm('form-materi-<?php echo $mapel_id; ?>')" class="bg-blue-500 hover:bg-blue-600 text-white font-semibold py-2 px-3 rounded-md text-sm mb-3">+ Tambah Materi</button>
                    </div>
                    <div>
                        <h4 class="text-lg font-semibold text-gray-700 mb-2">Kelola Tugas</h4>
                        <div id="form-tugas-<?php echo $mapel_id; ?>" style="display:none;" class="bg-gray-50 p-4 rounded-lg mb-3">
                            <form action="../proses/guru_proses.php" method="POST">
                                <input type="hidden" name="action" value="tambah_tugas">
                                <input type="hidden" name="kelas_id" value="<?php echo $kelas_id; ?>">
                                <input type="hidden" name="mapel_id" value="<?php echo $mapel_id; ?>">
                                <input type="text" name="judul_tugas" placeholder="Judul Tugas" class="w-full p-2 border rounded mb-2" required>
                                <textarea name="deskripsi" placeholder="Deskripsi Tugas" class="w-full p-2 border rounded mb-2"></textarea>
                                <input type="datetime-local" name="deadline" class="w-full p-2 border rounded mb-2" required>
                                <button type="submit" class="bg-green-600 text-white py-2 px-4 rounded">Buat Tugas</button>
                            </form>
                        </div>
                        <button onclick="toggleForm('form-tugas-<?php echo $mapel_id; ?>')" class="bg-green-500 hover:bg-green-600 text-white font-semibold py-2 px-3 rounded-md text-sm mb-3">+ Tambah Tugas</button>
                    </div>
                </div>
            <?php endif; // Akhir dari blok khusus pengajar 
            ?>

            <div class="mt-6 grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <h4 class="text-lg font-semibold text-gray-700 mb-2">Daftar Materi</h4>
                    <ul class="space-y-2 text-sm">
                        <?php while ($m = $materi_list->fetch_assoc()): ?>
                            <li class="bg-gray-100 p-2 rounded"><?php echo htmlspecialchars($m['judul']); ?></li>
                        <?php endwhile; ?>
                    </ul>
                </div>
                <div>
                    <h4 class="text-lg font-semibold text-gray-700 mb-2">Daftar Tugas</h4>
                    <ul class="space-y-2 text-sm">
                        <?php while ($t = $tugas_list->fetch_assoc()): ?>
                            <li class="flex justify-between items-center bg-gray-100 p-2 rounded">
                                <span><?php echo htmlspecialchars($t['judul']); ?></span>
                                <?php if ($mapel['id_pengajar'] == $guru_id): // Tombol nilai hanya untuk pengajar mapel 
                                ?>
                                    <a href="penilaian.php?tugas_id=<?php echo $t['id']; ?>" class="bg-yellow-500 hover:bg-yellow-600 text-white font-semibold py-1 px-3 rounded text-xs">Nilai</a>
                                <?php endif; ?>
                            </li>
                        <?php endwhile; ?>
                    </ul>
                </div>
            </div>
        </div>
    <?php endwhile; ?>
</div>

<div class="mt-8 pt-6 border-t">
    <a href="../proses/guru_proses.php?action=keluar_kelas&id=<?php echo $kelas_id; ?>"
        onclick="return confirm('Anda yakin ingin keluar dari kelas ini? Semua materi dan tugas yang Anda buat akan tetap ada tetapi Anda tidak akan bisa mengelolanya lagi.')"
        class="bg-red-600 hover:bg-red-700 text-white font-semibold py-2 px-4 rounded-lg transition duration-200">
        Keluar dari Kelas Ini
    </a>
</div>

<script>
    // Fungsi simpel untuk menampilkan/menyembunyikan form
    function toggleForm(formId) {
        const form = document.getElementById(formId);
        if (form.style.display === 'none') {
            form.style.display = 'block';
        } else {
            form.style.display = 'none';
        }
    }
</script>
<?php require_once '../includes/footer.php'; ?>