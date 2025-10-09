<?php
require_once '../includes/header.php';
// Auth & Validasi ID Kelas
// ... (kode validasi Anda sudah bagus) ...
$kelas_id = $_GET['id'];

// Ambil info kelas saja (tanpa join guru)
$kelas_info = $conn->query("SELECT nama_kelas FROM kelas WHERE id = $kelas_id")->fetch_assoc();

// [BARU] Ambil daftar mapel untuk kelas ini
$mapel_list = $conn->query("SELECT id, nama_mapel FROM mapel WHERE kelas_id = $kelas_id");

// [BARU] Ambil daftar guru yang sudah mengajar di kelas ini
$pengajar_list_q = $conn->prepare("
    SELECT u.nama, m.nama_mapel
    FROM pengajar p
    JOIN users u ON p.guru_id = u.id
    JOIN mapel m ON p.mapel_id = m.id
    WHERE m.kelas_id = ?
");
$pengajar_list_q->bind_param("i", $kelas_id);
$pengajar_list_q->execute();
$pengajar_list = $pengajar_list_q->get_result();


?>

<h2 class="text-3xl font-bold text-gray-800">Detail Kelas: <?php echo htmlspecialchars($kelas_info['nama_kelas']); ?></h2>
<hr class="my-4">

<div class="flex flex-col lg:flex-row gap-8">

    <div class="w-full lg:w-1/2">
        <div class="bg-white p-6 rounded-lg shadow-md mb-6">
            <h3 class="text-xl font-bold text-gray-800 mb-4">Kelola Mata Pelajaran</h3>
            <form action="../proses/admin_kelas_proses.php" method="POST" class="flex gap-2">
                <input type="hidden" name="action" value="tambah_mapel">
                <input type="hidden" name="kelas_id" value="<?php echo $kelas_id; ?>">
                <input type="text" name="nama_mapel" placeholder="Nama Mapel Baru" class="flex-grow w-full px-4 py-2 border rounded-lg" required>
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg">Tambah</button>
            </form>
            <ul class="mt-4 space-y-2">
                <?php while ($mapel = $mapel_list->fetch_assoc()): ?>
                    <li class="flex justify-between items-center bg-gray-50 p-2 rounded">
                        <span><?php echo htmlspecialchars($mapel['nama_mapel']); ?></span>
                        <a href="../proses/admin_kelas_proses.php?action=hapus_mapel&id=<?php echo $mapel['id']; ?>&kelas_id=<?php echo $kelas_id; ?>" class="text-red-500 hover:text-red-700 text-sm">Hapus</a>
                    </li>
                <?php endwhile; ?>
            </ul>
        </div>

        
    </div>

    <div class="w-full lg:w-1/2">
        <div class="bg-white p-6 rounded-lg shadow-md">
            <h3 class="text-xl font-bold text-gray-800 mb-4">Daftar Guru Pengajar</h3>
            <ul class="divide-y divide-gray-200">
                <?php if ($pengajar_list->num_rows > 0): ?>
                    <?php while ($pengajar = $pengajar_list->fetch_assoc()): ?>
                        <li class="py-3">
                            <p class="font-medium text-gray-800"><?php echo htmlspecialchars($pengajar['nama']); ?></p>
                            <p class="text-sm text-gray-500">Mengajar: <?php echo htmlspecialchars($pengajar['nama_mapel']); ?></p>
                        </li>
                    <?php endwhile; ?>
                <?php else: ?>
                    <li class="py-3 text-gray-500">Belum ada guru yang ditugaskan.</li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>