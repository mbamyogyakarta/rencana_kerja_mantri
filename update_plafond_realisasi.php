<?php
session_start();
if (!isset($_SESSION['pn']) || $_SESSION['role'] !== 'pimpinan') {
    header("Location: login.php");
    exit;
}

$id_rencana = $_GET['id_rencana'] ?? '';
if (empty($id_rencana)) {
    header("Location: lihat_rencana_realisasi.php?error=id_tidak_valid");
    exit;
}

// Proses update plafond jika form disubmit
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['plafond_realisasi'])) {
    $plafond_realisasi = $_POST['plafond_realisasi'];
    $file_gabungan = 'data_rencana_realisasi.csv';
    $data_gabungan = [];
    $header_gabungan = ['Jenis Data', 'ID Rencana', 'Tanggal Rencana', 'Kode BO', 'Branch Office', 'Kode Unit', 'BRI Unit', 'PN', 'Nama Mantri', 'Nama Deb', 'Sumber Deb', 'CIF', 'Produk', 'Plafond', 'Pemutus', 'Tanggal Realisasi', 'Hasil Kunjungan', 'Status Realisasi', 'Tindak Lanjut', 'Plafond Realisasi'];

    if (file_exists($file_gabungan)) {
        $rows = array_map('str_getcsv', file($file_gabungan));
        $header_existing = array_shift($rows);
        foreach ($rows as $row) {
            if (count($row) === count($header_existing)) {
                $entry = array_combine($header_existing, $row);
                if ($entry['ID Rencana'] === $id_rencana) {
                    $entry['Plafond Realisasi'] = $plafond_realisasi;
                }
                $data_gabungan[] = $entry;
            }
        }

        $fp = fopen($file_gabungan, 'w');
        fputcsv($fp, $header_gabungan);
        foreach ($data_gabungan as $fields) {
            fputcsv($fp, $fields);
        }
        fclose($fp);

        header("Location: lihat_rencana_realisasi.php?update_plafond_success=1");
        exit;
    } else {
        header("Location: lihat_rencana_realisasi.php?update_plafond_failed=1");
        exit;
    }
}

// Ambil data rencana realisasi berdasarkan ID untuk ditampilkan di form
$data_rencana = null;
$file_gabungan = 'data_rencana_realisasi.csv';
if (file_exists($file_gabungan)) {
    $rows = array_map('str_getcsv', file($file_gabungan));
    $header = array_shift($rows);
    foreach ($rows as $row) {
        if (count($row) === count($header)) {
            $entry = array_combine($header, $row);
            if ($entry['ID Rencana'] === $id_rencana) {
                $data_rencana = $entry;
                break;
            }
        }
    }
}

?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Update Plafond Realisasi</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container py-5">
        <h2>Update Plafond Realisasi</h2>
        <?php if ($data_rencana): ?>
            <form method="POST" action="">
                <div class="mb-3">
                    <label for="id_rencana" class="form-label">ID Rencana</label>
                    <input type="text" class="form-control" id="id_rencana" name="id_rencana" value="<?php echo htmlspecialchars($data_rencana['ID Rencana']); ?>" readonly>
                </div>
                <div class="mb-3">
                    <label for="nama_deb" class="form-label">Nama Debitur</label>
                    <input type="text" class="form-control" id="nama_deb" value="<?php echo htmlspecialchars($data_rencana['Nama Deb'] ?? '-'); ?>" readonly>
                </div>
                <div class="mb-3">
                    <label for="plafond_rencana" class="form-label">Plafond Rencana</label>
                    <input type="text" class="form-control" id="plafond_rencana" value="<?php echo htmlspecialchars($data_rencana['Plafond'] ?? '-'); ?>" readonly>
                </div>
                <div class="mb-3">
                    <label for="plafond_realisasi" class="form-label">Plafond Realisasi</label>
                    <input type="text" class="form-control" id="plafond_realisasi" name="plafond_realisasi" required>
                </div>
                <button type="submit" class="btn btn-primary">Simpan Plafond Realisasi</button>
                <a href="lihat_rencana_realisasi.php" class="btn btn-secondary">Batal</a>
            </form>
        <?php else: ?>
            <p class="text-danger">Data rencana tidak ditemukan.</p>
            <a href="lihat_rencana_realisasi.php" class="btn btn-secondary">Kembali</a>
        <?php endif; ?>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>