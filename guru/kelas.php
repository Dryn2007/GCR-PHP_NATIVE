<?php
require_once '../includes/header.php';

// Pastikan hanya guru yang bisa mengakses
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Guru') {
    header("Location: ../index.php");
    exit();
}

// Validasi ID kelas dari URL
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("ID Kelas tidak valid.");
}

$kelas_id = $_GET['id'];
$guru_id = $_SESSION['user_id'];

// Verifikasi apakah guru ini adalah pemilik kelas
$stmt = $conn->prepare("SELECT nama_kelas FROM kelas WHERE id = ? AND guru_id = ?");
$stmt->bind_param("ii", $kelas_id, $guru_id);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows === 0) {
    die("Anda tidak memiliki akses ke kelas ini.");
}
$kelas = $result->fetch_assoc();
$stmt->close();

$page_message = ''; // Variabel untuk menampung notifikasi

// --- LOGIKA UPLOAD MATERI ---
if (isset($_POST['upload_materi'])) {
    $judul = $_POST['judul'];
    $file = $_FILES['file_materi'];
    $fileName = time() . '_' . basename($file['name']);
    $filePath = '../uploads/materi/' . $fileName;

    if (move_uploaded_file($file['tmp_name'], $filePath)) {
        $stmt_materi = $conn->prepare("INSERT INTO materi (kelas_id, judul, file) VALUES (?, ?, ?)");
        $stmt_materi->bind_param("iss", $kelas_id, $judul, $fileName);
        $stmt_materi->execute();
        $stmt_materi->close();
        $page_message = "<div class='bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6' role='alert'>Materi berhasil diunggah.</div>";
    } else {
        $page_message = "<div class='bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6' role='alert'>Gagal mengunggah materi.</div>";
    }
}

// --- LOGIKA BUAT TUGAS ---
if (isset($_POST['buat_tugas'])) {
    $judul_tugas = $_POST['judul_tugas'];
    $deskripsi = $_POST['deskripsi'];
    $deadline = $_POST['deadline'];

    $conn->begin_transaction();
    try {
        // ... (Logika PHP untuk buat tugas dan kirim notifikasi tetap sama)
        $stmt_tugas = $conn->prepare("INSERT INTO tugas (kelas_id, judul, deskripsi, deadline) VALUES (?, ?, ?, ?)");
        $stmt_tugas->bind_param("isss", $kelas_id, $judul_tugas, $deskripsi, $deadline);
        $stmt_tugas->execute();
        $stmt_tugas->close();

        $stmt_siswa = $conn->prepare("SELECT siswa_id FROM kelas_siswa WHERE kelas_id = ?");
        $stmt_siswa->bind_param("i", $kelas_id);
        $stmt_siswa->execute();
        $result_siswa = $stmt_siswa->get_result();

        $pesan_notif = "Tugas baru '" . htmlspecialchars($judul_tugas) . "' di kelas " . htmlspecialchars($kelas['nama_kelas']);
        $link_notif = "../siswa/kelas.php?id=" . $kelas_id;

        $stmt_notif = $conn->prepare("INSERT INTO notifikasi (siswa_id, pesan, link) VALUES (?, ?, ?)");
        while ($siswa = $result_siswa->fetch_assoc()) {
            $stmt_notif->bind_param("iss", $siswa['siswa_id'], $pesan_notif, $link_notif);
            $stmt_notif->execute();
        }
        $stmt_notif->close();
        $stmt_siswa->close();

        $conn->commit();
        $page_message = "<div class='bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6' role='alert'>Tugas berhasil dibuat dan notifikasi telah dikirim.</div>";
    } catch (mysqli_sql_exception $exception) {
        $conn->rollback();
        $page_message = "<div class='bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6' role='alert'>Gagal membuat tugas: " . $exception->getMessage() . "</div>";
    }
}

// Ambil data materi dan tugas untuk ditampilkan
$materi_list = $conn->query("SELECT id, judul, file, created_at FROM materi WHERE kelas_id = $kelas_id ORDER BY created_at DESC");
$tugas_list = $conn->query("SELECT id, judul, deskripsi, deadline FROM tugas WHERE kelas_id = $kelas_id ORDER BY deadline DESC");
?>

<a href="dashboard.php" class="inline-block bg-gray-500 hover:bg-gray-600 text-white font-semibold py-2 px-4 rounded mb-6 transition duration-200">&larr; Kembali ke Dashboard</a>

<div class="border-b border-gray-300 pb-4 mb-6">
    <h2 class="text-3xl font-bold text-gray-800">Detail Kelas: <?php echo htmlspecialchars($kelas['nama_kelas']); ?></h2>
</div>

<?php echo $page_message; // Tampilkan pesan sukses atau error di sini 
?>

<div class="flex flex-col lg:flex-row lg:space-x-8">

    <div class="w-full lg:w-1/2">
        <h3 class="text-2xl font-bold text-gray-800 mb-4">Materi Pembelajaran</h3>

        <div class="bg-white p-6 rounded-lg shadow-md border border-gray-200 mb-8">
            <h4 class="text-xl font-semibold text-gray-700 border-b pb-3 mb-4">Unggah Materi Baru</h4>
            <form method="POST" enctype="multipart/form-data">
                <div class="mb-4">
                    <label for="judul" class="block mb-2 text-sm font-medium text-gray-700">Judul Materi</label>
                    <input type="text" name="judul" class="block w-full px-3 py-2 bg-white border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500" required>
                </div>
                <div class="mb-4">
                    <label for="file_materi" class="block mb-2 text-sm font-medium text-gray-700">Pilih File (PDF, PPT, dll)</label>
                    <input type="file" name="file_materi" class="block w-full text-sm text-gray-500 border border-gray-300 rounded-lg cursor-pointer bg-gray-50 focus:outline-none file:mr-4 file:py-2 file:px-4 file:rounded-l-lg file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100" required>
                </div>
                <button type="submit" name="upload_materi" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-md transition duration-200">Unggah Materi</button>
            </form>
        </div>

        <h4 class="text-xl font-semibold text-gray-700 mb-3">Daftar Materi Terunggah</h4>
        <ul class="space-y-3">
            <?php if ($materi_list && $materi_list->num_rows > 0): ?>
                <?php while ($materi = $materi_list->fetch_assoc()): ?>
                    <li class="flex justify-between items-center p-4 bg-white border border-gray-200 rounded-lg shadow-sm">
                        <span class="text-gray-800"><?php echo htmlspecialchars($materi['judul']); ?></span>
                        <a href="../uploads/materi/<?php echo htmlspecialchars($materi['file']); ?>" target="_blank" class="text-sm bg-sky-500 hover:bg-sky-600 text-white font-semibold py-1 px-3 rounded-md transition duration-200">Lihat</a>
                    </li>
                <?php endwhile; ?>
            <?php else: ?>
                <li class="p-4 bg-white border border-gray-200 rounded-lg shadow-sm text-gray-500">Belum ada materi yang diunggah.</li>
            <?php endif; ?>
        </ul>
    </div>

    <div class="w-full lg:w-1/2 mt-10 lg:mt-0">
        <h3 class="text-2xl font-bold text-gray-800 mb-4">Tugas</h3>

        <div class="bg-white p-6 rounded-lg shadow-md border border-gray-200 mb-8">
            <h4 class="text-xl font-semibold text-gray-700 border-b pb-3 mb-4">Buat Tugas Baru</h4>
            <form method="POST">
                <div class="mb-4">
                    <label for="judul_tugas" class="block mb-2 text-sm font-medium text-gray-700">Judul Tugas</label>
                    <input type="text" name="judul_tugas" class="block w-full px-3 py-2 bg-white border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500" required>
                </div>
                <div class="mb-4">
                    <label for="deskripsi" class="block mb-2 text-sm font-medium text-gray-700">Deskripsi</label>
                    <textarea name="deskripsi" rows="3" class="block w-full px-3 py-2 bg-white border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"></textarea>
                </div>
                <div class="mb-4">
                    <label for="deadline" class="block mb-2 text-sm font-medium text-gray-700">Deadline</p>
                        <input type="datetime-local" name="deadline" class="block w-full px-3 py-2 bg-white border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500" required>
                </div>
                <button type="submit" name="buat_tugas" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-md transition duration-200">Buat Tugas</button>
            </form>
        </div>

        <h4 class="text-xl font-semibold text-gray-700 mb-3">Daftar Tugas Aktif</h4>
        <div class="space-y-3">
            <?php if ($tugas_list && $tugas_list->num_rows > 0): ?>
                <?php while ($tugas = $tugas_list->fetch_assoc()): ?>
                    <a href="penilaian.php?tugas_id=<?php echo $tugas['id']; ?>" class="block p-4 bg-white border border-gray-200 rounded-lg shadow-sm hover:bg-gray-50 hover:shadow-md transition-all duration-200">
                        <h5 class="font-semibold text-blue-700"><?php echo htmlspecialchars($tugas['judul']); ?></h5>
                        <small class="text-sm text-red-600">Deadline: <?php echo date('d M Y, H:i', strtotime($tugas['deadline'])); ?></small>
                    </a>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="p-4 bg-white border border-gray-200 rounded-lg shadow-sm text-gray-500">Belum ada tugas yang dibuat.</div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>