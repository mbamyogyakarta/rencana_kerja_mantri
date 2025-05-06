<?php
session_start();
if (!isset($_SESSION['pn']) || $_SESSION['role'] !== 'pimpinan') {
    header("Location: login.php");
    exit;
}

$nama_pimpinan = $_SESSION['nama'] ?? $_SESSION['pn'];
$kode_unit_pimpinan = $_SESSION['kode_unit'] ?? '-';

// Filter tanggal
$filter_tanggal = $_GET['tanggal'] ?? '';

// Baca data dari file gabungan
$file_gabungan = 'data_rencana_realisasi.csv';
$data_rencana_realisasi = [];
$header_realisasi = [];

if (file_exists($file_gabungan)) {
    $rows = array_map('str_getcsv', file($file_gabungan));
    if (!empty($rows)) {
        $header_realisasi = array_shift($rows);
        foreach ($rows as $row) {
            if (count($row) === count($header_realisasi)) {
                $entry = array_combine($header_realisasi, $row);
                // Hanya tampilkan data untuk unit kerja pimpinan dan sesuai filter tanggal
                if (($entry['Kode Unit'] ?? '') === $kode_unit_pimpinan &&
                    (!$filter_tanggal || ($entry['Tanggal Rencana'] ?? '') === $filter_tanggal)) {
                    $data_rencana_realisasi[] = $entry;
                }
            }
        }
    }
}

// Cek apakah kolom 'Plafond Realisasi' ada di header
$plafond_realisasi_exists = in_array('Plafond Realisasi', $header_realisasi);

// Proses ekspor CSV
if (isset($_POST['export_csv'])) {
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="rencana_realisasi_' . date('YmdHis') . '.csv"');
    $output = fopen('php://output', 'w');
    if ($output !== false) { // Tambahkan pengecekan keberhasilan fopen
        fputcsv($output, $header_realisasi); // Output header
        foreach ($data_rencana_realisasi as $row) {
            fputcsv($output, $row); // Output data
        }
        fclose($output);
        exit();
    } else {
        die("Gagal membuka output stream untuk ekspor CSV."); // Lebih baik menggunakan die()
    }
}

// Inisialisasi variabel total
$total_plafond_rencana = 0;
$total_plafond_realisasi = 0;
// Hitung total plafond rencana dan realisasi
foreach ($data_rencana_realisasi as $data) {
    $total_plafond_rencana += floatval($data['Plafond'] ?? 0);
    if ($plafond_realisasi_exists && isset($data['Plafond Realisasi'])) {
        $total_plafond_realisasi += floatval($data['Plafond Realisasi'] ?? 0);
    }
}

?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Data Rencana & Realisasi</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4>Data Rencana & Realisasi</h4>
            <small class="text-muted">Unit Kerja: <?php echo htmlspecialchars($kode_unit_pimpinan); ?></small>
        </div>
        <div>
            <form method="POST" class="d-inline me-2">
                <button type="submit" name="export_csv" class="btn btn-success btn-sm">Ekspor CSV</button>
            </form>
            <a href="dashboard_pimpinan.php" class="btn btn-secondary btn-sm me-2">Kembali ke Dashboard</a>
            <a href="logout.php" class="btn btn-outline-danger btn-sm">Logout</a>
        </div>
    </div>

    <div class="card shadow p-4">
        <h5 class="mb-4">Daftar Rencana & Realisasi Kunjungan</h5>

        <form method="GET" class="row g-3 mb-4">
            <div class="col-md-3">
                <label for="tanggal" class="form-label">Filter Tanggal Rencana</label>
                <input type="date" name="tanggal" id="tanggal" class="form-control" value="<?= htmlspecialchars($filter_tanggal) ?>">
            </div>
            <div class="col-md-3 d-flex align-items-end">
                <button type="submit" class="btn btn-success me-2">Filter</button>
                <a href="lihat_rencana_realisasi.php" class="btn btn-secondary">Reset Filter</a>
            </div>
        </form>

        <div class="mt-4">
            <h5>Rekapitulasi Total</h5>
            <p><strong>Total Plafond Rencana:</strong> <?php echo number_format($total_plafond_rencana, 0, ',', '.'); ?></p>
            <p><strong>Total Plafond Realisasi:</strong> <?php echo number_format($total_plafond_realisasi, 0, ',', '.'); ?></p>
        </div>

        <div class="table-responsive mt-3">
            <table class="table table-bordered table-striped">
                <thead class="table-light">
                    <tr>
                        <th>Tanggal Rencana</th>
                        <th>Nama Mantri</th>
                        <th>Nama Debitur</th>
                        <th>Produk</th>
                        <th>Plafond</th>
                        <th>Tanggal Realisasi</th>
                        <th>Hasil Kunjungan</th>
                        <th>Status Realisasi</th>
                        <th>Tindak Lanjut</th>
                        <?php if ($plafond_realisasi_exists): ?>
                            <th>Plafond Realisasi</th>
                        <?php endif; ?>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($data_rencana_realisasi)): ?>
                        <tr><td colspan="<?php echo $plafond_realisasi_exists ? '11' : '10'; ?>" class="text-center">Tidak ada data rencana dan realisasi untuk unit ini<?php echo $filter_tanggal ? " pada tanggal " . htmlspecialchars($filter_tanggal) : ""; ?>.</td></tr>
                    <?php else: ?>
                        <?php foreach ($data_rencana_realisasi as $data): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($data['Tanggal Rencana'] ?? '-'); ?></td>
                                <td><?php echo htmlspecialchars($data['Nama Mantri'] ?? '-'); ?></td>
                                <td><?php echo htmlspecialchars($data['Nama Deb'] ?? '-'); ?></td>
                                <td><?php echo htmlspecialchars($data['Produk'] ?? '-'); ?></td>
                                <td><?php echo number_format($data['Plafond'] ?? 0, 0, ',', '.'); ?></td>
                                <td><?php echo htmlspecialchars($data['Tanggal Realisasi'] ?? '-'); ?></td>
                                <td><?php echo htmlspecialchars($data['Hasil Kunjungan'] ?? '-'); ?></td>
                                <td><?php echo htmlspecialchars($data['Status Realisasi'] ?? '-'); ?></td>
                                <td><?php echo htmlspecialchars($data['Tindak Lanjut'] ?? '-'); ?></td>
                                <?php if ($plafond_realisasi_exists): ?>
                                    <td><?php echo number_format(floatval($data['Plafond Realisasi'] ?? 0), 0, ',', '.'); ?></td>
                                <?php endif; ?>
                                <td>
                                    <a href="update_plafond_realisasi.php?id_rencana=<?php echo urlencode($data['ID Rencana'] ?? ''); ?>" class="btn btn-sm btn-warning">Update Plafond</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
