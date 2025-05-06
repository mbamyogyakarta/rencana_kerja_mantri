<?php
session_start();
$error = '';

// Fungsi untuk membaca data mantri dari CSV
function getMantriData($file) {
    if (!file_exists($file)) {
        return "File data tidak ditemukan.";
    }
    $rows = array_map('str_getcsv', file($file));
    if (count($rows) < 2) {
        return "Data CSV kosong atau tidak valid.";
    }
    $header = array_map('trim', $rows[0]);
    $data = [];
    for ($i = 1; $i < count($rows); $i++) {
        if (count($rows[$i]) === count($header)) {
            $data[] = array_combine($header, array_map('trim', $rows[$i]));
        }
    }
    return $data;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $pn = trim($_POST["pn"]);
    $password = trim($_POST["password"]);
    $file = 'data_mantri_with_password.csv';

    $mantri_data = getMantriData($file);

    if (is_string($mantri_data)) {
        $error = $mantri_data;
    } else {
        $login_berhasil = false;
        foreach ($mantri_data as $mantri) {
            // Login: password sama dengan PN
            if (trim($mantri["pn"] ?? '') === $pn && trim($password) === $pn) {
                // Set session lengkap
                $_SESSION["logged_in"] = true;
                $_SESSION["pn"] = trim($mantri["pn"]);
                $_SESSION["nama"] = $mantri["nama"] ?? trim($mantri["pn"]);
                $_SESSION["kode_unit"] = $mantri["kode_unit"] ?? $mantri["unit_kerja"] ?? '-';
                $_SESSION["role"] = strtolower(trim($mantri["role"] ?? "marketing"));

                // Redirect berdasarkan role
                if ($_SESSION["role"] === "pimpinan") {
                    header("Location: dashboard_pimpinan.php");
                } else {
                    header("Location: dashboard.php");
                }
                $login_berhasil = true;
                exit;
            }
        }

        if (!$login_berhasil) {
            $error = "❌ Personal Number salah atau belum terdaftar.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Login Rencana Mantri</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-5">
            <div class="card shadow-lg p-4">
                <h4 class="text-center mb-4">🔐 Login ke Rencana Mantri</h4>

                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
                <?php endif; ?>

                <form method="POST">
                    <div class="mb-3">
                        <label for="pn" class="form-label">Personal Number (PN)</label>
                        <input type="text" class="form-control" name="pn" id="pn" required>
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">Kata Sandi (PN juga)</label>
                        <input type="password" class="form-control" name="password" id="password" required>
                    </div>
                    <button type="submit" class="btn btn-success w-100">Login</button>
                </form>
            </div>
        </div>
    </div>
</div>

</body>
</html>