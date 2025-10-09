<?php
// Pastikan file ini terhubung ke database
require_once __DIR__ . '/../config/db.php';

// Cek apakah user sudah login
if (!isset($_SESSION['user_id'])) {
    header("Location: /gcr/login.php"); // Sesuaikan dengan path login Anda
    exit();
}

// [DISEDERHANAKAN & DIBUAT AMAN] Blok Logika Notifikasi
$notifikasi_list = [];
$unread_count = 0;
if (isset($_SESSION['role']) && $_SESSION['role'] == 'Siswa') {
    $siswa_id_notif = $_SESSION['user_id'];

    // Query aman untuk menghitung notifikasi yang belum dibaca untuk siswa ini
    $stmt_count = $conn->prepare("SELECT COUNT(id) as unread_count FROM notifikasi WHERE siswa_id = ? AND is_read = 0");
    $stmt_count->bind_param("i", $siswa_id_notif);
    $stmt_count->execute();
    $result_count = $stmt_count->get_result();
    if ($result_count) {
        $unread_count = $result_count->fetch_assoc()['unread_count'];
    }
    $stmt_count->close();

    // Query aman untuk mengambil 5 notifikasi terbaru untuk siswa ini
    $stmt_notif = $conn->prepare("SELECT * FROM notifikasi WHERE siswa_id = ? ORDER BY created_at DESC LIMIT 5");
    $stmt_notif->bind_param("i", $siswa_id_notif);
    $stmt_notif->execute();
    $result_notif = $stmt_notif->get_result();
    if ($result_notif) {
        while ($row = $result_notif->fetch_assoc()) {
            $notifikasi_list[] = $row;
        }
    }
    $stmt_notif->close();
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>E-Learning</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-100">

    <nav class="bg-white shadow-md">
        <div class="container mx-auto px-6 py-3">
            <div class="flex justify-between items-center">
                <a class="text-2xl font-bold text-gray-800" href="/gcr/index.php">E-Learning</a>

                <div class="flex items-center space-x-4">
                    <span class="text-gray-700">
                        Selamat datang, <?php echo htmlspecialchars($_SESSION['nama']); ?>!
                        <span class="font-medium">(<?php echo htmlspecialchars($_SESSION['role']); ?>)</span>
                    </span>

                    <?php
                    $profil_link = '';
                    if ($_SESSION['role'] == 'Siswa') {
                        $profil_link = '/gcr/siswa/profil.php'; // Sesuaikan '/gcr/' dengan path folder proyek Anda
                    } elseif ($_SESSION['role'] == 'Guru') {
                        $profil_link = '/gcr/guru/profil.php'; // Sesuaikan '/gcr/' dengan path folder proyek Anda
                    }
                    ?>
                    <?php if ($profil_link != ''): ?>
                        <a href="<?php echo $profil_link; ?>" class="text-sm text-blue-600 hover:underline">Edit Profil</a>
                    <?php endif; ?>

                    <?php if ($_SESSION['role'] == 'Siswa'): ?>
                        <div class="relative" id="notif-container">
                            <button id="notif-button" class="relative text-gray-600 hover:text-gray-800 focus:outline-none">
                                <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                                </svg>
                                <?php if ($unread_count > 0): ?>
                                    <span class="absolute -top-2 -right-2 h-5 w-5 rounded-full bg-red-500 text-white text-xs flex items-center justify-center">
                                        <?php echo $unread_count; ?>
                                    </span>
                                <?php endif; ?>
                            </button>

                            <div id="notif-panel" class="hidden absolute right-0 mt-2 w-80 bg-white rounded-lg shadow-xl overflow-hidden z-20">
                                <div class="py-2 px-4 text-sm font-semibold text-gray-700 border-b">Notifikasi Terbaru</div>
                                <div class="divide-y">
                                    <?php if (!empty($notifikasi_list)): ?>
                                        <?php foreach ($notifikasi_list as $notif): ?>
                                            <a href="<?php echo htmlspecialchars($notif['link']); ?>" class="block py-3 px-4 hover:bg-gray-100">
                                                <p class="text-sm text-gray-800"><?php echo htmlspecialchars($notif['pesan']); ?></p>
                                                <p class="text-xs text-gray-500 mt-1"><?php echo date('d M Y, H:i', strtotime($notif['created_at'])); ?></p>
                                            </a>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <p class="py-4 px-4 text-sm text-gray-500 text-center">Tidak ada notifikasi baru.</p>
                                    <?php endif; ?>
                                </div>
                                <a href="#" class="block bg-gray-50 text-center py-2 text-sm font-medium text-blue-600 hover:bg-gray-100">Lihat Semua Notifikasi</a>
                            </div>
                        </div>
                    <?php endif; ?>
                    <a class="bg-red-500 hover:bg-red-600 text-white font-semibold py-2 px-4 rounded" href="/gcr/logout.php">Logout</a>
                </div>
            </div>
        </div>
    </nav>

    <main class="container mx-auto p-6">

        <script>
            // Hanya jalankan skrip jika elemen notifikasi ada (untuk siswa)
            const notifContainer = document.getElementById('notif-container');

            if (notifContainer) {
                const notifButton = document.getElementById('notif-button');
                const notifPanel = document.getElementById('notif-panel');

                // Tampilkan/sembunyikan panel saat ikon lonceng diklik
                notifButton.addEventListener('click', (event) => {
                    event.stopPropagation(); // Mencegah event 'click' menyebar ke window
                    notifPanel.classList.toggle('hidden');
                });

                // Sembunyikan panel saat area di luar panel diklik
                window.addEventListener('click', (event) => {
                    // Cek apakah yang diklik bukan bagian dari container notifikasi
                    if (!notifContainer.contains(event.target)) {
                        notifPanel.classList.add('hidden');
                    }
                });
            }
        </script>