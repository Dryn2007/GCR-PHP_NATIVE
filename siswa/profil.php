<?php require_once '../includes/header.php'; ?>

<h2 class="text-3xl font-bold text-gray-800 mb-6">Profil Saya</h2>

<div class="bg-white p-8 rounded-lg shadow-md max-w-lg mx-auto">
    <?php if (isset($_GET['status']) && $_GET['status'] == 'sukses'): ?>
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4">
            Nama berhasil diperbarui!
        </div>
    <?php endif; ?>

    <form action="../proses/profil_proses.php" method="POST">
        <div class="mb-4">
            <label for="nama" class="block text-gray-700 font-semibold mb-2">Nama Lengkap</label>
            <input type="text" id="nama" name="nama" value="<?php echo htmlspecialchars($_SESSION['nama']); ?>" class="w-full px-4 py-2 border rounded-lg" required>
        </div>
        <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg">
            Simpan Perubahan
        </button>
    </form>
</div>

<?php require_once '../includes/footer.php'; ?>