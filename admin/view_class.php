<?php
require_once '../includes/header.php';

// Auth Guard: Pastikan hanya Admin yang bisa akses
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Admin') {
    die("Akses ditolak.");
}

// Validasi ID Kelas dari URL
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("ID Kelas tidak valid.");
}
$kelas_id = $_GET['id'];

// Ambil info kelas dan guru
$kelas_info_q = $conn->prepare("SELECT k.nama_kelas, u.nama AS nama_guru FROM kelas k JOIN users u ON k.guru_id = u.id WHERE k.id = ?");
$kelas_info_q->bind_param("i", $kelas_id);
$kelas_info_q->execute();
$kelas_info = $kelas_info_q->get_result()->fetch_assoc();
if (!$kelas_info) {
    die("Kelas tidak ditemukan.");
}

// Ambil daftar materi
$materi_list = $conn->query("SELECT judul, file FROM materi WHERE kelas_id = $kelas_id ORDER BY created_at DESC");
// Ambil daftar tugas
$tugas_list = $conn->query("SELECT id, judul, deadline FROM tugas WHERE kelas_id = $kelas_id ORDER BY deadline DESC");
// Ambil daftar siswa yang terdaftar
$siswa_list_q = $conn->prepare("SELECT u.nama, u.email FROM kelas_siswa ks JOIN users u ON ks.siswa_id = u.id WHERE ks.kelas_id = ? ORDER BY u.nama ASC");
$siswa_list_q->bind_param("i", $kelas_id);
$siswa_list_q->execute();
$siswa_list = $siswa_list_q->get_result();
?>

<a href="manage_classes.php" class="inline-block bg-gray-500 hover:bg-gray-600 text-white font-semibold py-2 px-4 rounded mb-6 transition duration-200">&larr; Kembali ke Manajemen Kelas</a>

<div class="border-b border-gray-300 pb-4 mb-6">
    <h2 class="text-3xl font-bold text-gray-800">Detail Kelas: <?php echo htmlspecialchars($kelas_info['nama_kelas']); ?></h2>
    <p class="text-md text-gray-600">Guru: <?php echo htmlspecialchars($kelas_info['nama_guru']); ?></p>
</div>

<div class="flex flex-col lg:flex-row lg:space-x-8">
    <div class="w-full lg:w-2/3">
        <h3 class="text-xl font-bold text-gray-800 mb-4">Daftar Materi</h3>
        <ul class="space-y-3 mb-8">
            <?php if ($materi_list->num_rows > 0): ?>
                <?php while ($materi = $materi_list->fetch_assoc()): ?>
                    <li class="flex justify-between items-center p-4 bg-white border border-gray-200 rounded-lg shadow-sm">
                        <span><?php echo htmlspecialchars($materi['judul']); ?></span>
                        <a href="../uploads/materi/<?php echo htmlspecialchars($materi['file']); ?>" target="_blank" class="text-sm bg-sky-500 hover:bg-sky-600 text-white font-semibold py-1 px-3 rounded-md">Lihat</a>
                    </li>
                <?php endwhile; ?>
            <?php else: ?>
                <li class="p-4 bg-white border rounded-lg text-gray-500">Belum ada materi.</li>
            <?php endif; ?>
        </ul>

        <h3 class="text-xl font-bold text-gray-800 mb-4">Daftar Tugas</h3>
        <ul class="space-y-3">
            <?php if ($tugas_list->num_rows > 0): ?>
                <?php while ($tugas = $tugas_list->fetch_assoc()): ?>
                    <li class="block p-4 bg-white border border-gray-200 rounded-lg shadow-sm">
                        <h5 class="font-semibold text-blue-700"><?php echo htmlspecialchars($tugas['judul']); ?></h5>
                        <small class="text-sm text-red-600">Deadline: <?php echo date('d M Y, H:i', strtotime($tugas['deadline'])); ?></small>
                    </li>
                <?php endwhile; ?>
            <?php else: ?>
                <li class="p-4 bg-white border rounded-lg text-gray-500">Belum ada tugas.</li>
            <?php endif; ?>
        </ul>
    </div>

    <div class="w-full lg:w-1/3 mt-10 lg:mt-0">
        <h3 class="text-xl font-bold text-gray-800 mb-4">Siswa Terdaftar (<?php echo $siswa_list->num_rows; ?>)</h3>
        <div class="bg-white p-4 rounded-lg shadow-md border border-gray-200">
            <ul class="divide-y divide-gray-200">
                <?php if ($siswa_list->num_rows > 0): ?>
                    <?php while ($siswa = $siswa_list->fetch_assoc()): ?>
                        <li class="py-3">
                            <p class="font-medium text-gray-800"><?php echo htmlspecialchars($siswa['nama']); ?></p>
                            <p class="text-sm text-gray-500"><?php echo htmlspecialchars($siswa['email']); ?></p>
                        </li>
                    <?php endwhile; ?>
                <?php else: ?>
                    <li class="py-3 text-gray-500">Belum ada siswa yang bergabung.</li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>