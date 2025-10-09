<?php
require_once '../includes/header.php';

// Auth & Validasi
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Guru' || !isset($_GET['tugas_id'])) {
    die("Akses ditolak atau ID Tugas tidak valid.");
}
$tugas_id = $_GET['tugas_id'];
$guru_id = $_SESSION['user_id'];

// Ambil info tugas dan verifikasi apakah tugas ini milik guru yang login
$tugas_info_q = $conn->prepare("
    SELECT t.judul, t.kelas_id FROM tugas t
    JOIN mapel m ON t.mapel_id = m.id
    JOIN pengajar p ON m.id = p.mapel_id
    WHERE t.id = ? AND p.guru_id = ?
");
$tugas_info_q->bind_param("ii", $tugas_id, $guru_id);
$tugas_info_q->execute();
$tugas_info_res = $tugas_info_q->get_result();
if ($tugas_info_res->num_rows == 0) {
    die("Anda tidak memiliki akses ke tugas ini.");
}
$tugas_info = $tugas_info_res->fetch_assoc();
$kelas_id_redirect = $tugas_info['kelas_id'];

// Ambil daftar siswa yang sudah mengumpulkan
$pengumpulan_q = $conn->prepare("
    SELECT p.id, p.file, p.jawaban_teks, p.nilai, p.komentar, u.nama as nama_siswa
    FROM pengumpulan p
    JOIN users u ON p.siswa_id = u.id
    WHERE p.tugas_id = ? ORDER BY u.nama ASC
");
$pengumpulan_q->bind_param("i", $tugas_id);
$pengumpulan_q->execute();
$pengumpulan_list = $pengumpulan_q->get_result();
?>

<a href="kelas.php?id=<?php echo $kelas_id_redirect; ?>" class="inline-block bg-gray-500 hover:bg-gray-600 text-white font-semibold py-2 px-4 rounded mb-6">&larr; Kembali ke Kelas</a>
<h2 class="text-3xl font-bold text-gray-800">Penilaian Tugas</h2>
<h3 class="text-xl text-gray-700 mb-6">"<?php echo htmlspecialchars($tugas_info['judul']); ?>"</h3>

<?php
// ==========================================================
// KODE UNTUK MENAMPILKAN PESAN STATUS DIMASUKKAN DI SINI
// ==========================================================
if (isset($_SESSION['flash_message'])) {
    $msg = $_SESSION['flash_message'];
    $alert_class = $msg['type'] == 'success'
        ? 'bg-green-100 border-green-400 text-green-700'
        : 'bg-red-100 border-red-400 text-red-700';

    echo "<div class='border px-4 py-3 rounded relative mb-4 {$alert_class}' role='alert'>
            <span class='block sm:inline'>{$msg['message']}</span>
          </div>";

    // Hapus pesan setelah ditampilkan
    unset($_SESSION['flash_message']);
}
?>

<div class="bg-white shadow-md rounded-lg overflow-hidden">
    <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nama Siswa</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Jawaban</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase w-24">Nilai (0-100)</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Komentar</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase w-28">Aksi</th>
            </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
            <?php if ($pengumpulan_list->num_rows > 0): ?>
                <?php while ($item = $pengumpulan_list->fetch_assoc()): ?>
                    <tr>
                        <td class="px-6 py-4"><?php echo htmlspecialchars($item['nama_siswa']); ?></td>
                        <td class="px-6 py-4">
                            <?php if (!empty($item['file'])): ?>
                                <a href="../uploads/pengumpulan/<?php echo htmlspecialchars($item['file']); ?>" target="_blank" class="text-blue-600 hover:underline">Lihat File</a>
                            <?php elseif (!empty($item['jawaban_teks'])): ?>
                                <div class="text-sm text-gray-700"><?php echo nl2br(htmlspecialchars($item['jawaban_teks'])); ?></div>
                            <?php else: ?>
                                <span class="text-gray-400">--</span>
                            <?php endif; ?>
                        </td>
                        <form action="../proses/penilaian_proses.php" method="POST">
                            <input type="hidden" name="pengumpulan_id" value="<?php echo $item['id']; ?>">
                            <input type="hidden" name="tugas_id" value="<?php echo $tugas_id; ?>">
                            <td class="px-6 py-4">
                                <input type="number" name="nilai" value="<?php echo $item['nilai']; ?>" class="w-full border rounded p-1" min="0" max="100">
                            </td>
                            <td class="px-6 py-4">
                                <textarea name="komentar" rows="2" class="w-full border rounded p-1"><?php echo htmlspecialchars($item['komentar']); ?></textarea>
                            </td>
                            <td class="px-6 py-4">
                                <button type="submit" class="bg-green-600 hover:bg-green-700 text-white font-semibold py-1 px-3 rounded text-sm">Simpan</button>
                            </td>
                        </form>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="5" class="text-center py-4 text-gray-500">Belum ada siswa yang mengumpulkan tugas.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php require_once '../includes/footer.php'; ?>