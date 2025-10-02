<?php
require_once __DIR__ . '/../config/db.php';

// Cek apakah user sudah login
if (!isset($_SESSION['user_id'])) {
    header("Location: /elearning_php_native/login.php"); // Sesuaikan path jika perlu
    exit();
}
?>
<!DOCTYPE html>
<html>

<head>
    <title>E-Learning</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="#">E-Learning</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <span class="navbar-text me-3">
                            Selamat datang, <?php echo htmlspecialchars($_SESSION['nama']); ?>! (<?php echo htmlspecialchars($_SESSION['role']); ?>)
                        </span>
                    </li>
                    <li class="nav-item">
                        <a class="btn btn-danger" href="/gcr/logout.php">Logout</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-4">