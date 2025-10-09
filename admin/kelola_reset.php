<?php
require_once '../includes/header.php';
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Admin') {
    die("Akses ditolak.");
}

$user_list = $conn->query("SELECT id, nama, email, role FROM users WHERE reset_request = 1");
?>
<h2 class="text-3xl font-bold text-gray-800 mb-6">Kelola Reset Password</h2>

<?php if (isset($_SESSION['flash_message'])): ?>
    <div class="bg-green-100 text-green-700 p-3 rounded mb-4"><?php echo $_SESSION['flash_message']; ?></div>
    <?php unset($_SESSION['flash_message']); ?>
<?php endif; ?>

<div class="bg-white shadow-md rounded-lg overflow-hidden">
    <table class="min-w-full">
        <thead>
            <tr class="bg-gray-50">
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nama</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Email</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Aksi</th>
            </tr>
        </thead>
        <tbody class="divide-y">
            <?php while ($user = $user_list->fetch_assoc()): ?>
                <tr>
                    <td class="px-6 py-4"><?php echo $user['nama']; ?></td>
                    <td class="px-6 py-4"><?php echo $user['email']; ?></td>
                    <td class="px-6 py-4">
                        <a href="../proses/admin_proses.php?action=reset_password&id=<?php echo $user['id']; ?>"
                            onclick="return confirm('Yakin ingin mereset password pengguna ini?')"
                            class="text-red-600 hover:text-red-800 font-semibold">Reset Password</a>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>
<?php require_once '../includes/footer.php'; ?>