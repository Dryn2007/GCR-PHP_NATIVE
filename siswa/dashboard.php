<?php
// Asumsi 'header.php' sudah memuat setup Tailwind CSS
require_once '../includes/header.php';

// Pastikan hanya siswa yang bisa mengakses halaman ini
if ($_SESSION['role'] !== 'Siswa') {
    header("Location: ../index.php");
    exit();
}
$siswa_id_untuk_status = $_SESSION['user_id'];
$status_query = $conn->query("SELECT status_guru FROM users WHERE id = $siswa_id_untuk_status");
$user_status = $status_query->fetch_assoc();

$siswa_id = $_SESSION['user_id'];
$message = '';

// Logika untuk bergabung ke kelas
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['join_kelas'])) {
    $kode_kelas = $_POST['kode_kelas'];

    // Cari kelas berdasarkan kode
    $stmt = $conn->prepare("SELECT id FROM kelas WHERE kode_kelas = ?");
    $stmt->bind_param("s", $kode_kelas);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $kelas = $result->fetch_assoc();
        $kelas_id = $kelas['id'];

        // Cek apakah siswa sudah terdaftar di kelas ini
        $stmt_check = $conn->prepare("SELECT id FROM kelas_siswa WHERE kelas_id = ? AND siswa_id = ?");
        $stmt_check->bind_param("ii", $kelas_id, $siswa_id);
        $stmt_check->execute();
        $result_check = $stmt_check->get_result();

        if ($result_check->num_rows == 0) {
            // Daftarkan siswa ke kelas
            $stmt_join = $conn->prepare("INSERT INTO kelas_siswa (kelas_id, siswa_id) VALUES (?, ?)");
            $stmt_join->bind_param("ii", $kelas_id, $siswa_id);
            if ($stmt_join->execute()) {
                $message = '<div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">Berhasil bergabung ke kelas!</div>';
            }
        } else {
            $message = '<div class="bg-yellow-100 border border-yellow-400 text-yellow-700 px-4 py-3 rounded relative mb-4" role="alert">Anda sudah terdaftar di kelas ini.</div>';
        }
    } else {
        $message = '<div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">Kode kelas tidak ditemukan.</div>';
    }
}

// Ambil daftar kelas yang diikuti oleh siswa ini
$stmt = $conn->prepare("SELECT k.id, k.nama_kelas, u.nama AS nama_guru FROM kelas k JOIN users u ON k.guru_id = u.id JOIN kelas_siswa ks ON k.id = ks.kelas_id WHERE ks.siswa_id = ? ORDER BY k.nama_kelas ASC");
$stmt->bind_param("i", $siswa_id);
$stmt->execute();
$result_kelas_diikuti = $stmt->get_result();
?>

<div class="mb-6 pb-4 border-b border-gray-300">
    <h2 class="text-3xl font-bold text-gray-800">Dashboard Siswa</h2>
</div>

<?php echo $message; // Tampilkan pesan sukses/error 
?>

<div class="bg-white shadow-md rounded-lg overflow-hidden mb-8">
    <div class="bg-gray-50 px-6 py-4 border-b border-gray-200">
        <h3 class="text-lg font-semibold text-gray-700">Gabung Kelas Baru</h3>
    </div>
    <div class="p-6">
        <form method="POST">
            <div class="flex">
                <input type="text" class="flex-grow w-full px-4 py-2 text-gray-700 bg-white border border-gray-300 rounded-l-md focus:outline-none focus:ring-2 focus:ring-blue-500" name="kode_kelas" placeholder="Masukkan Kode Kelas" required>
                <button class="px-6 py-2 text-white font-semibold bg-blue-600 rounded-r-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2" type="submit" name="join_kelas">Gabung</button>
            </div>
        </form>
    </div>
</div>
<div class="bg-white shadow-md rounded-lg p-6 mb-8 border border-gray-200">
    <h3 class="text-lg font-semibold text-gray-700">Status Akun Guru</h3>
    <?php if ($user_status['status_guru'] == 'none'): ?>
        <p class="text-gray-600 my-2">Ingin membuat kelas dan materi sendiri? Ajukan diri Anda untuk menjadi Guru.</p>
        <a href="../proses/ajukan_guru_proses.php" class="inline-block bg-blue-600 hover:bg-blue-70h-700 text-white font-semibold py-2 px-4 rounded transition duration-200">
            Ajukan Menjadi Guru
        </a>
    <?php elseif ($user_status['status_guru'] == 'pending'): ?>
        <p class="text-yellow-600 my-2 font-medium">Pengajuan Anda sedang ditinjau oleh Admin. Harap tunggu.</p>
    <?php elseif ($user_status['status_guru'] == 'approved'): ?>
        <p class="text-green-600 my-2 font-medium">Selamat! Anda adalah seorang Guru. Silakan logout dan login kembali untuk mengakses dashboard Guru.</p>
    <?php endif; ?>
</div>

<div>
    <h3 class="text-2xl font-bold text-gray-800 mb-4">Daftar Kelas Anda</h3>
    <div class="space-y-4">
        <?php if ($result_kelas_diikuti->num_rows > 0): ?>
            <?php while ($row = $result_kelas_diikuti->fetch_assoc()): ?>
                <a href="kelas.php?id=<?php echo $row['id']; ?>" class="block p-6 bg-white border border-gray-200 rounded-lg shadow-sm hover:shadow-md hover:bg-gray-50 transition-all duration-200">
                    <h4 class="text-xl font-semibold text-blue-700"><?php echo htmlspecialchars($row['nama_kelas']); ?></h4>
                    <p class="text-sm text-gray-600 mt-1">Guru: <?php echo htmlspecialchars($row['nama_guru']); ?></p>
                </a>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="bg-blue-50 border border-blue-200 text-blue-800 px-4 py-3 rounded-md">
                <p>Anda belum bergabung dengan kelas manapun. Silakan gunakan formulir di atas untuk bergabung.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php
// Asumsi 'footer.php' sudah dikonversi ke Tailwind dan menutup tag-tag yang diperlukan
require_once '../includes/footer.php';
?>