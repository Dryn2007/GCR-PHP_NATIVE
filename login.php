<!DOCTYPE html>
<html>

<head>
    <title>Login - E-Learning</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-5">
                <div class="card">
                    <div class="card-header">
                        <h3 class="text-center">Login</h3>
                    </div>
                    <div class="card-body">
                        <?php if (isset($_GET['status']) && $_GET['status'] == 'gagal'): ?>
                            <div class="alert alert-danger">Email atau password salah.</div>
                        <?php endif; ?>
                        <?php if (isset($_GET['status']) && $_GET['status'] == 'sukses_register'): ?>
                            <div class="alert alert-success">Registrasi berhasil! Silakan login.</div>
                        <?php endif; ?>
                        <form action="auth/login_process.php" method="POST">
                            <div class="mb-3">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" class="form-control" id="email" name="email" required>
                            </div>
                            <div class="mb-3">
                                <label for="password" class="form-label">Password</label>
                                <input type="password" class="form-control" id="password" name="password" required>
                            </div>
                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary">Login</button>
                            </div>
                        </form>
                        <p class="mt-3 text-center">Belum punya akun? <a href="register.php">Register di sini</a></p>
                        <p class="mt-1 text-center"><a href="lupa_password.php">Lupa Password?</a></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>

</html>