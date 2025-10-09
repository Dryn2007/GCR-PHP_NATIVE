<?php
require_once '../includes/header.php';

// ... (kode validasi akses siswa Anda yang sudah ada) ...
$kelas_id = $_GET['id'];
$siswa_id = $_SESSION['user_id'];
$kelas_info = $conn->query("SELECT nama_kelas FROM kelas WHERE id = $kelas_id")->fetch_assoc();

// Ambil semua mapel yang ada di kelas ini
$mapel_list = $conn->query("SELECT id, nama_mapel FROM mapel WHERE kelas_id = $kelas_id ORDER BY nama_mapel ASC");
?>

<a href="dashboard.php" class="inline-block bg-gray-500 hover:bg-gray-600 text-white font-semibold py-2 px-4 rounded mb-6">&larr; Kembali</a>
<h2 class="text-3xl font-bold text-gray-800 mb-6">Materi & Tugas Kelas: <?php echo htmlspecialchars($kelas_info['nama_kelas']); ?></h2>

<div class="space-y-8">
    <?php while ($mapel = $mapel_list->fetch_assoc()): ?>
        <?php
        $mapel_id = $mapel['id'];
        // Ambil materi & tugas untuk mapel ini
        $materi_list = $conn->query("SELECT * FROM materi WHERE mapel_id = $mapel_id ORDER BY created_at DESC");
        $tugas_list = $conn->query("SELECT * FROM tugas WHERE mapel_id = $mapel_id ORDER BY deadline DESC");
        ?>
        <div class="bg-white p-6 rounded-lg shadow-md border border-gray-200">
            <h3 class="text-2xl font-bold text-blue-700 border-b pb-3 mb-4"><?php echo htmlspecialchars($mapel['nama_mapel']); ?></h3>

            <div class="mb-6">
                <h4 class="text-xl font-semibold text-gray-700 mb-3">Materi</h4>
                <ul class="space-y-2">
                    <?php if ($materi_list->num_rows > 0): ?>
                        <?php while ($m = $materi_list->fetch_assoc()): ?>
                            <li class="flex justify-between items-center bg-gray-100 p-2 rounded">
                                <span><?php echo htmlspecialchars($m['judul']); ?></span>
                                <a href="../uploads/materi/<?php echo htmlspecialchars($m['file']); ?>" download class="bg-blue-500 hover:bg-blue-600 text-white text-sm font-semibold py-1 px-3 rounded">Download</a>
                            </li>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <li class="text-gray-500">Belum ada materi.</li>
                    <?php endif; ?>
                </ul>
            </div>

            <div>
                <h4 class="text-xl font-semibold text-gray-700 mb-3">Tugas</h4>
                <div class="space-y-4">
                    <?php if ($tugas_list->num_rows > 0): ?>
                        <?php while ($t = $tugas_list->fetch_assoc()): ?>
                            <div class="border p-4 rounded-lg">
                                <h5 class="font-semibold"><?php echo htmlspecialchars($t['judul']); ?></h5>
                                <p class="text-sm text-gray-600"><?php echo nl2br(htmlspecialchars($t['deskripsi'])); ?></p>
                                <p class="text-sm text-red-600 font-semibold mt-2">Deadline: <?php echo date('d M Y, H:i', strtotime($t['deadline'])); ?></p>

                                <div class="mt-4 pt-4 border-t">
                                    <?php
                                    // Cek status pengumpulan tugas ini
                                    $stmt_status = $conn->prepare("SELECT * FROM pengumpulan WHERE tugas_id = ? AND siswa_id = ?");
                                    $stmt_status->bind_param("ii", $t['id'], $siswa_id);
                                    $stmt_status->execute();
                                    $result_status = $stmt_status->get_result();

                                    // ==========================================================
                                    // BAGIAN INI TELAH DIGANTI DENGAN KODE BARU ANDA
                                    // ==========================================================
                                    if ($result_status->num_rows > 0) {
                                        // Jika sudah mengumpulkan, ambil datanya
                                        $pengumpulan = $result_status->fetch_assoc();

                                        // Cek apakah sudah ada nilai atau belum
                                        if ($pengumpulan['nilai'] !== null) {
                                            // JIKA SUDAH DINILAI
                                    ?>
                                            <div class="bg-blue-50 border border-blue-200 p-4 rounded-lg">
                                                <h6 class="font-bold text-blue-800">Tugas Sudah Dinilai</h6>
                                                <p class="text-2xl font-bold text-gray-800 my-1">Nilai: <?php echo htmlspecialchars($pengumpulan['nilai']); ?></p>
                                                <?php if (!empty($pengumpulan['komentar'])): ?>
                                                    <div class="mt-2 pt-2 border-t border-blue-200">
                                                        <p class="text-sm font-semibold text-gray-700">Komentar dari Guru:</p>
                                                        <p class="text-sm text-gray-600 italic">"<?php echo nl2br(htmlspecialchars($pengumpulan['komentar'])); ?>"</p>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        <?php
                                        } else {
                                            // JIKA SUDAH DIKUMPULKAN TAPI BELUM DINILAI
                                            echo '<div class="bg-green-100 text-green-800 p-3 rounded font-semibold">Tugas sudah dikumpulkan, menunggu penilaian dari guru.</div>';
                                        }
                                    } else {
                                        // ... (sisa kode Anda untuk menampilkan form jika belum mengumpulkan dan belum deadline)
                                        // Bagian ini tidak perlu diubah.
                                        $deadline = new DateTime($t['deadline']);
                                        $sekarang = new DateTime();

                                        if ($sekarang > $deadline) {
                                            echo '<div class="bg-red-100 border border-red-300 text-red-800 p-3 rounded text-sm font-semibold">Waktu pengumpulan tugas telah berakhir.</div>';
                                        } else {
                                        ?>
                                            <form action="../proses/siswa_proses.php" method="POST" enctype="multipart/form-data">
                                                <input type="hidden" name="action" value="kumpul_tugas">
                                                <input type="hidden" name="tugas_id" value="<?php echo $t['id']; ?>">
                                                <input type="hidden" name="kelas_id" value="<?php echo $kelas_id; ?>">
                                                <div class="mb-2">
                                                    <label class="block text-sm font-medium">Jawaban Teks (Opsional)</label>
                                                    <textarea name="jawaban_teks" rows="3" class="w-full p-2 border rounded"></textarea>
                                                </div>
                                                <div class="mb-2">
                                                    <label class="block text-sm font-medium">Atau Upload File (Opsional)</label>
                                                    <input type="file" name="file_jawaban" class="w-full">
                                                </div>
                                                <button type="submit" class="w-full bg-blue-600 text-white py-2 px-4 rounded mt-2">Kumpulkan</button>
                                            </form>
                                    <?php
                                        }
                                    } // Tutup dari if-else utama
                                    // ==========================================================
                                    // AKHIR DARI BAGIAN YANG DIGANTI
                                    // ==========================================================
                                    ?>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <p class="text-gray-500">Belum ada tugas.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    <?php endwhile; ?>
</div>

<?php require_once '../includes/footer.php'; ?>