<?php
require_once '../includes/header.php';

// Pastikan hanya siswa yang bisa mengakses
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Siswa') {
    header("Location: ../index.php");
    exit();
}

// Validasi ID kelas dari URL
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    // Tampilkan pesan error dengan gaya Tailwind
    echo '<div class="container mx-auto mt-10"><div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded" role="alert">ID Kelas tidak valid.</div></div>';
    require_once '../includes/footer.php';
    exit();
}

$kelas_id = $_GET['id'];
$siswa_id = $_SESSION['user_id'];

// Verifikasi apakah siswa terdaftar di kelas ini
$stmt = $conn->prepare("SELECT k.nama_kelas FROM kelas k JOIN kelas_siswa ks ON k.id = ks.kelas_id WHERE k.id = ? AND ks.siswa_id = ?");
$stmt->bind_param("ii", $kelas_id, $siswa_id);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows === 0) {
    echo '<div class="container mx-auto mt-10"><div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded" role="alert">Anda tidak terdaftar di kelas ini.</div></div>';
    require_once '../includes/footer.php';
    exit();
}
// INI BAGIAN YANG MENGISI VARIABEL $kelas
$kelas = $result->fetch_assoc();
$stmt->close();

$page_message = '';
// --- LOGIKA KUMPUL TUGAS (FILE ATAU TEKS) ---
if (isset($_POST['kumpul_tugas'])) {
    $tugas_id = $_POST['tugas_id'];
    $jawaban_teks = $_POST['jawaban_teks'] ?? null;
    $file = $_FILES['file_jawaban'];

    // Cek apakah sudah pernah mengumpulkan
    $stmt_cek = $conn->prepare("SELECT id FROM pengumpulan WHERE tugas_id = ? AND siswa_id = ?");
    $stmt_cek->bind_param("ii", $tugas_id, $siswa_id);
    $stmt_cek->execute();

    if ($stmt_cek->get_result()->num_rows > 0) {
        $page_message = "<div class='bg-yellow-100 border-l-4 border-yellow-500 text-yellow-700 p-4 mb-4' role='alert'>Anda sudah pernah mengumpulkan tugas ini.</div>";
    } else {
        // Validasi: Pastikan ada salah satu yang diisi (file atau teks)
        if ($file['error'] == UPLOAD_ERR_NO_FILE && empty(trim($jawaban_teks))) {
            $page_message = "<div class='bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4' role='alert'>Gagal: Anda harus mengunggah file atau mengisi jawaban teks.</div>";
        } else {
            $fileName = null;
            // Jika ada file yang diupload, proses file
            if ($file['error'] == UPLOAD_ERR_OK) {
                $fileName = time() . '_' . $siswa_id . '_' . basename($file['name']);
                $filePath = '../uploads/pengumpulan/' . $fileName;
                if (!move_uploaded_file($file['tmp_name'], $filePath)) {
                    $page_message = "<div class='bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4' role='alert'>Gagal mengunggah file.</div>";
                    $fileName = null;
                }
            }

            // Simpan ke database
            $stmt_kumpul = $conn->prepare("INSERT INTO pengumpulan (tugas_id, siswa_id, file, jawaban_teks) VALUES (?, ?, ?, ?)");
            $stmt_kumpul->bind_param("iiss", $tugas_id, $siswa_id, $fileName, $jawaban_teks);

            if ($stmt_kumpul->execute()) {
                $page_message = "<div class='bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4' role='alert'>Tugas berhasil dikumpulkan.</div>";
            } else {
                $page_message = "<div class='bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4' role='alert'>Gagal menyimpan data ke database.</div>";
            }
            $stmt_kumpul->close();
        }
    }
    $stmt_cek->close();
}

// INI BAGIAN YANG MENGISI VARIABEL $materi_list dan $tugas_list
$materi_list = $conn->query("SELECT id, judul, file FROM materi WHERE kelas_id = $kelas_id ORDER BY created_at DESC");
$tugas_list = $conn->query("SELECT id, judul, deskripsi, deadline FROM tugas WHERE kelas_id = $kelas_id ORDER BY deadline DESC");
?>

<a href="dashboard.php" class="inline-block bg-gray-500 hover:bg-gray-600 text-white font-semibold py-2 px-4 rounded mb-6 transition duration-200">&larr; Kembali ke Dashboard</a>
<div class="mb-8 pb-4 border-b border-gray-300">
    <h2 class="text-3xl font-bold text-gray-800">Kelas: <?php echo htmlspecialchars($kelas['nama_kelas']); ?></h2>
</div>

<?php echo $page_message; ?>

<div class="flex flex-col lg:flex-row lg:space-x-8">

    <div class="w-full lg:w-5/12">
        <h3 class="text-2xl font-bold text-gray-800 mb-4">Materi Pembelajaran</h3>
        <ul class="space-y-3">
            <?php if ($materi_list && $materi_list->num_rows > 0): ?>
                <?php while ($materi = $materi_list->fetch_assoc()): ?>
                    <li class="bg-white p-4 rounded-lg shadow-sm flex justify-between items-center border border-gray-200">
                        <span class="text-gray-700"><?php echo htmlspecialchars($materi['judul']); ?></span>
                        <a href="../uploads/materi/<?php echo htmlspecialchars($materi['file']); ?>" download class="bg-blue-500 hover:bg-blue-600 text-white text-sm font-semibold py-1 px-3 rounded transition duration-200">Download</a>
                    </li>
                <?php endwhile; ?>
            <?php else: ?>
                <li class="bg-white p-4 rounded-lg shadow-sm text-gray-500 border border-gray-200">Belum ada materi.</li>
            <?php endif; ?>
        </ul>
    </div>

    <div class="w-full lg:w-7/12 mt-10 lg:mt-0">
        <h3 class="text-2xl font-bold text-gray-800 mb-4">Daftar Tugas</h3>
        <div class="space-y-6">
            <?php if ($tugas_list && $tugas_list->num_rows > 0): ?>
                <?php while ($tugas = $tugas_list->fetch_assoc()): ?>
                    <div class="bg-white p-6 rounded-lg shadow-md border border-gray-200">
                        <h4 class="text-xl font-semibold text-gray-800"><?php echo htmlspecialchars($tugas['judul']); ?></h4>
                        <div class="text-gray-600 mt-2 space-y-2"><?php echo nl2br(htmlspecialchars($tugas['deskripsi'])); ?></div>
                        <p class="text-sm text-red-600 font-semibold mt-3">Deadline: <?php echo date('d M Y, H:i', strtotime($tugas['deadline'])); ?></p>

                        <div class="mt-4 pt-4 border-t border-gray-200">
                            <?php
                            // Cek status pengumpulan tugas ini
                            $stmt_status = $conn->prepare("SELECT file, jawaban_teks, nilai, komentar FROM pengumpulan WHERE tugas_id = ? AND siswa_id = ?");
                            $stmt_status->bind_param("ii", $tugas['id'], $siswa_id);
                            $stmt_status->execute();
                            $result_status = $stmt_status->get_result();

                            if ($result_status->num_rows > 0) {
                                // Jika sudah mengumpulkan
                                $status = $result_status->fetch_assoc();
                                echo '<div class="bg-green-50 border border-green-200 text-green-800 p-4 rounded-md">';
                                echo '<strong class="font-semibold block">Anda sudah mengumpulkan.</strong>';
                                if (!empty($status['file'])) {
                                    echo '<span class="text-sm">File: ' . htmlspecialchars($status['file']) . '</span><br>';
                                }
                                if (!empty($status['jawaban_teks'])) {
                                    echo '<p class="text-sm mt-2 p-2 bg-gray-100 rounded">Jawaban Teks: <br>' . nl2br(htmlspecialchars($status['jawaban_teks'])) . '</p>';
                                }
                                if ($status['nilai'] !== null) {
                                    echo '<strong class="font-semibold mt-2 block">Nilai: ' . $status['nilai'] . '</strong>';
                                    echo '<span class="text-sm">Komentar Guru: ' . htmlspecialchars($status['komentar']) . '</span>';
                                } else {
                                    echo '<i class="text-sm mt-2 block">Belum dinilai oleh guru.</i>';
                                }
                                echo '</div>';
                            } else {
                                // Jika belum mengumpulkan
                                if (new DateTime() > new DateTime($tugas['deadline'])) {
                                    echo '<div class="bg-red-50 border border-red-200 text-red-800 p-4 rounded-md text-sm font-semibold">Waktu pengumpulan sudah lewat.</div>';
                                } else {
                            ?>
                                    <form method="POST" enctype="multipart/form-data">
                                        <input type="hidden" name="tugas_id" value="<?php echo $tugas['id']; ?>">
                                        <div class="mb-4">
                                            <label class="block mb-2 text-sm font-medium text-gray-900" for="jawaban_teks_<?php echo $tugas['id']; ?>">Kirim Jawaban Teks (Opsional)</label>
                                            <textarea name="jawaban_teks" id="jawaban_teks_<?php echo $tugas['id']; ?>" rows="4" class="block w-full text-sm text-gray-900 bg-gray-50 rounded-lg border border-gray-300 focus:ring-blue-500 focus:border-blue-500"></textarea>
                                        </div>
                                        <div class="mb-4">
                                            <label class="block mb-2 text-sm font-medium text-gray-900" for="file_jawaban_<?php echo $tugas['id']; ?>">Atau Upload File (Opsional)</label>
                                            <input type="file" name="file_jawaban" id="file_jawaban_<?php echo $tugas['id']; ?>" class="block w-full text-sm text-gray-500 border border-gray-300 rounded-lg cursor-pointer bg-gray-50 focus:outline-none file:mr-4 file:py-2 file:px-4 file:rounded-l-lg file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                                        </div>
                                        <button type="submit" name="kumpul_tugas" class="w-full px-4 py-2 text-white font-semibold bg-blue-600 rounded-lg hover:bg-blue-700 focus:outline-none">Kumpulkan</button>
                                    </form>
                            <?php
                                }
                            }
                            $stmt_status->close();
                            ?>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="bg-white p-6 rounded-lg shadow-md border border-gray-200">
                    <p class="text-gray-500">Belum ada tugas di kelas ini.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>