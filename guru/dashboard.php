<?php require_once '../includes/header.php'; ?>

<?php
// Pastikan hanya guru yang bisa mengakses halaman ini
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Guru') {
    header("Location: ../index.php");
    exit();
}

$guru_id = $_SESSION['user_id'];
$message = '';

// Logika untuk membuat kelas baru
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['buat_kelas'])) {
    $nama_kelas = htmlspecialchars(trim($_POST['nama_kelas']));
    if (!empty($nama_kelas)) {
        // Generate kode kelas unik
        $kode_kelas = strtoupper(substr(md5(time() . $nama_kelas), 0, 6));

        $stmt = $conn->prepare("INSERT INTO kelas (nama_kelas, kode_kelas, guru_id) VALUES (?, ?, ?)");
        $stmt->bind_param("ssi", $nama_kelas, $kode_kelas, $guru_id);
        if ($stmt->execute()) {
            $message = "<div class='bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6' role='alert'>Kelas baru berhasil dibuat!</div>";
        }
        $stmt->close();
    }
}

// GANTI DENGAN KODE BARU YANG BENAR INI

$guru_id = $_SESSION['user_id']; // Pastikan variabel ini ada

// Query ini mengambil semua kelas unik dimana guru ini mengajar setidaknya satu mapel.
$stmt = $conn->prepare("
    SELECT k.id, k.nama_kelas, k.kode_kelas
    FROM kelas k
    JOIN mapel m ON k.id = m.kelas_id
    JOIN pengajar p ON m.id = p.mapel_id
    WHERE p.guru_id = ?
    GROUP BY k.id
    ORDER BY k.nama_kelas ASC
");
$stmt->bind_param("i", $guru_id);
$stmt->execute();
$result = $stmt->get_result();
?>
<div class="mb-6 pb-4 border-b border-gray-300">
    <h2 class="text-3xl font-bold text-gray-800">Dashboard Guru</h2>
</div>

<?php echo $message; // Menampilkan pesan sukses setelah membuat kelas 
?>

<div class="bg-white shadow-md rounded-lg overflow-hidden mb-8">
    <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
        <h3 class="text-lg font-semibold text-gray-700">Gabung ke Kelas</h3>
    </div>
    <div class="p-6">
        <form action="../proses/guru_proses.php" method="POST">
            <input type="hidden" name="action" value="join_kelas">
            <div class="flex">
                <input type="text" class="flex-grow w-full px-4 py-2 text-gray-700 bg-white border-gray-300 rounded-l-md focus:outline-none focus:ring-2 focus:ring-blue-500" name="kode_kelas" placeholder="Masukkan Kode Kelas dari Admin" required>
                <button class="px-6 py-2 text-white font-semibold bg-blue-600 rounded-r-md hover:bg-blue-700 focus:outline-none" type="submit">
                    Cari & Gabung
                </button>
            </div>
        </form>
    </div>
</div>

<div>
    <h3 class="text-2xl font-bold text-gray-800 mb-4">Daftar Kelas Anda</h3>
    <div class="space-y-4">
        <?php if ($result && $result->num_rows > 0): ?>
            <?php while ($row = $result->fetch_assoc()): ?>
                <a href="kelas.php?id=<?php echo $row['id']; ?>" class="block p-6 bg-white border border-gray-200 rounded-lg shadow-sm hover:shadow-md hover:bg-gray-50 transition-all duration-200">
                    <h4 class="text-xl font-semibold text-blue-700"><?php echo htmlspecialchars($row['nama_kelas']); ?></h4>
                    <div class="flex items-center space-x-2 mt-1">
                        <p class="text-sm text-gray-600">Kode Kelas:</p>
                        <strong id="kode-<?php echo $row['id']; ?>" class="font-mono bg-gray-200 text-gray-800 py-1 px-2 rounded-md"><?php echo $row['kode_kelas']; ?></strong>

                        <button onclick="salinKode(event, 'kode-<?php echo $row['id']; ?>', this)" class="p-1 rounded-md hover:bg-gray-300 focus:outline-none" title="Salin Kode">
                            <svg class="h-5 w-5 text-gray-600 copy-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                            </svg>
                            <svg class="h-5 w-5 text-green-600 success-icon hidden" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                        </button>
                    </div>
                </a>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="bg-blue-50 border border-blue-200 text-blue-800 px-4 py-3 rounded-md">
                <p>Anda belum membuat kelas. Silakan gunakan formulir di atas untuk membuat kelas pertama Anda.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
    function salinKode(event, elementId, buttonElement) {
        // 1. Mencegah link utama (tag <a>) terbuka saat ikon diklik
        event.stopPropagation();
        event.preventDefault();

        // 2. Ambil teks dari elemen strong
        const teksKode = document.getElementById(elementId).innerText;

        // 3. Salin teks ke clipboard
        navigator.clipboard.writeText(teksKode).then(() => {
            // 4. Beri feedback visual (tukar ikon)
            const copyIcon = buttonElement.querySelector('.copy-icon');
            const successIcon = buttonElement.querySelector('.success-icon');

            copyIcon.classList.add('hidden');
            successIcon.classList.remove('hidden');

            // 5. Kembalikan ke ikon semula setelah 2 detik
            setTimeout(() => {
                successIcon.classList.add('hidden');
                copyIcon.classList.remove('hidden');
            }, 2000);
        }).catch(err => {
            console.error('Gagal menyalin teks: ', err);
        });
    }
</script>


<?php require_once '../includes/footer.php'; ?>