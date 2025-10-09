<!DOCTYPE html>
<html>

<head>
    <title>Lupa Password - E-Learning</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-100 flex items-center justify-center h-screen">
    <div class="w-full max-w-md bg-white p-8 rounded-lg shadow-md">
        <h2 class="text-2xl font-bold text-center mb-6">Reset Password</h2>
        <p class="text-center text-gray-600 mb-6">Masukkan email Anda. Permintaan reset akan dikirimkan ke Admin untuk diproses.</p>

        <?php if (isset($_GET['status'])): ?>
            <div class="bg-green-100 text-green-700 p-3 rounded mb-4 text-center">Permintaan reset password Anda telah dikirim ke Admin.</div>
        <?php endif; ?>

        <form action="proses/reset_request_proses.php" method="POST">
            <div class="mb-4">
                <label for="email" class="block text-gray-700 font-semibold mb-2">Email</label>
                <input type="email" id="email" name="email" class="w-full px-4 py-2 border rounded-lg" required>
            </div>
            <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg">Kirim Permintaan</button>
        </form>
        <p class="mt-4 text-center"><a href="login.php" class="text-blue-600 hover:underline">&larr; Kembali ke Login</a></p>
    </div>
</body>

</html>