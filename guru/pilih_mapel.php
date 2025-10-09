<?php
require_once '../includes/header.php';

// Auth & Validasi
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Guru' || !isset($_GET['id'])) {
    die("Akses ditolak.");
}
$kelas_id = $_GET['id'];
$guru_id = $_SESSION['user_id'];
$kelas_info = $conn->query("SELECT nama_kelas FROM kelas WHERE id = $kelas_id")->fetch_assoc();

$mapel_tersedia_q = $conn->prepare("
    SELECT m.id, m.nama_mapel 
    FROM mapel m
    LEFT JOIN pengajar p ON m.id = p.mapel_id
    WHERE m.kelas_id = ? AND p.id IS NULL
");
// Query ini sekarang hanya butuh 1 parameter: id kelas
$mapel_tersedia_q->bind_param("i", $kelas_id);
$mapel_tersedia_q->execute();
$mapel_tersedia = $mapel_tersedia_q->get_result();
?>

<div class="container mx-auto max-w-2xl mt-10">
    <div class="bg-white p-8 rounded-lg shadow-md">
        <h2 class="text-2xl font-bold text-gray-800">Selamat Datang di Kelas "<?php echo htmlspecialchars($kelas_info['nama_kelas']); ?>"</h2>
        <p class="text-gray-600 mb-6">Untuk melanjutkan, silakan pilih mata pelajaran yang akan Anda ajar di kelas ini.</p>

        <?php if ($mapel_tersedia->num_rows > 0) : ?>
            <div class="space-y-3">
                <?php while ($mapel = $mapel_tersedia->fetch_assoc()) : ?>
                    <a href="../proses/guru_proses.php?action=pilih_mapel&mapel_id=<?php echo $mapel['id']; ?>&kelas_id=<?php echo $kelas_id; ?>"
                        class="block w-full text-center bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 px-4 rounded-lg transition duration-200">
                        Pilih Mapel: <?php echo htmlspecialchars($mapel['nama_mapel']); ?>
                    </a>
                <?php endwhile; ?>
            </div>
        <?php else : ?>
            <div class="bg-yellow-100 border-l-4 border-yellow-500 text-yellow-700 p-4" role="alert">
                <p class="font-bold">Informasi</p>
                <p>Saat ini tidak ada mata pelajaran baru yang bisa Anda pilih di kelas ini.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>