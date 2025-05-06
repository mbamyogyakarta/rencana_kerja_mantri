<?php
session_start();
if (!isset($_SESSION['pn']) || $_SESSION['role'] !== 'pimpinan') {
    header("Location: login.php");
    exit;
}

$nama = $_SESSION['nama'] ?? $_SESSION['pn'];
$kode_unit = $_SESSION['kode_unit'] ?? '-';

// Daftar jenis rencana kunjungan sesuai form input
$daftar_jenis_rencana = [
    "" => "-- Pilih --",
    "eksisting" => "Kunjungan pipeline pinjaman eksisting",
    "canvasing" => "Kunjungan Calon Nasabah Pinjaman Baru (Canvasing)",
    "restru" => "Periksa Ulang / Negosiasi Restruk",
    "somasi" => "Mengantar SP / Surat Somasi / Pemasangan Sticker",
    "sml1" => "Penagihan SML 1",
    "sml2" => "Penagihan SML 2",
    "sml3" => "Penagihan SML 3",
    "npl" => "Penagihan NPL",
    "dh" => "Penagihan DH",
    "simpanan" => "Kunjungan Nasabah Simpanan",
    "pickup" => "Pick Up Service Nasabah Simpanan",
    "edc" => "Kunjungan Penawaran / Pemasangan EDC / QRIS",
    "brilink" => "Klaster Usaha / Agen Brilink",
];

// Baca file data mantri
$file_mantri = 'data_mantri_with_password.csv';
$data_mantri = [];
if (file_exists($file_mantri)) {
    $rows_mantri = array_map('str_getcsv', file($file_mantri));
    $header_mantri = array_map('trim', array_shift($rows_mantri));
    $pn_index = array_search('pn', $header_mantri); // Menggunakan 'pn' (huruf kecil)
    $nama_index = array_search('nama', $header_mantri);

    if ($pn_index !== false && $nama_index !== false) {
        foreach ($rows_mantri as $row_mantri) {
            if (isset($row_mantri[$pn_index]) && isset($row_mantri[$nama_index])) {
                $data_mantri[trim($row_mantri[$pn_index])] = trim($row_mantri[$nama_index]);
            }
        }
    }
}

// Fungsi untuk menghasilkan data CSV
function generateCSV($data, $header) {
    $output = fopen('php://output', 'w');
    fputcsv($output, $header);
    foreach ($data as $row) {
        fputcsv($output, $row);
    }
    fclose($output);
}

// Proses ekspor CSV jika tombol ditekan
if (isset($_GET['export']) && $_GET['export'] === 'csv') {
    $file = 'data_rencana.csv';
    if (file_exists($file)) {
        $rows = array_map('str_getcsv', file($file));
        $header = array_map('trim', array_shift($rows));
        $data_untuk_csv = [];

        $filter_tanggal = $_GET['tanggal'] ?? '';
        $filter_jenis = $_GET['jenis_rencana'] ?? '';

        foreach ($rows as $row) {
            if (count($row) !== count($header)) continue;
            $entry = array_combine($header, array_map('trim', $row));
            if (($entry['Kode Unit'] ?? '') !== $kode_unit) continue;
            if ($filter_tanggal && ($entry['Tanggal'] ?? '') !== $filter_tanggal) continue;
            if ($filter_jenis && ($entry['Jenis Rencana'] ?? '') !== $filter_jenis) continue;

            $pn_rencana = $entry['PN'] ?? '';
            $nama_marketing = $data_mantri[$pn_rencana] ?? '-';
            $entry['Nama Marketing'] = $nama_marketing;
            $data_untuk_csv[] = $entry;
        }

        // Modifikasi header untuk CSV
        $csv_header = ['Tanggal', 'Nama Marketing', 'PN', 'Jenis Rencana', 'Kode Unit', 'Nama'];
        // Tambahkan kolom detail ke header CSV
        $detail_keys = [];
        if (!empty($data_untuk_csv)) {
            foreach ($data_untuk_csv[0] as $key => $value) {
                if (!in_array($key, ['PN', 'pn', 'nama', 'Tanggal', 'Jenis Rencana', 'Kode Unit', 'ID', 'Nama'])) {
                    $csv_header[] = $key;
                    $detail_keys[] = $key;
                }
            }
        }

        // Siapkan data untuk CSV termasuk detail
        $csv_data = [];
        foreach ($data_untuk_csv as $item) {
            $csv_row = [
                $item['Tanggal'] ?? '-',
                $item['Nama Marketing'] ?? '-',
                $item['PN'] ?? '-',
                $item['Jenis Rencana'] ?? '-',
                $item['Kode Unit'] ?? '-',
                $item['Nama'] ?? '-',
            ];
            foreach ($detail_keys as $key) {
                $csv_row[] = $item[$key] ?? '-';
            }
            $csv_data[] = $csv_row;
        }

        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="rencana_kunjungan_' . date('YmdHis') . '.csv"');
        generateCSV($csv_data, $csv_header);
        exit();
    } else {
        echo "<script>alert('File data_rencana.csv tidak ditemukan.'); window.location.href='dashboard_pimpinan.php';</script>";
        exit();
    }
}

// Pagination settings
$dataPerHalaman = 10;
$halamanSaatIni = $_GET['halaman'] ?? 1;
$file_rencana = 'data_rencana.csv';
$data_rencana_all = [];

if (file_exists($file_rencana)) {
    $rows_rencana = array_map('str_getcsv', file($file_rencana));
    $header_rencana = array_map('trim', array_shift($rows_rencana));

    $filter_tanggal_tampil = $_GET['tanggal'] ?? '';
    $filter_jenis_tampil = $_GET['jenis_rencana'] ?? '';

    foreach ($rows_rencana as $row_rencana) {
        if (count($row_rencana) !== count($header_rencana)) continue;
        $entry_rencana = array_combine($header_rencana, array_map('trim', $row_rencana));
        if (($entry_rencana['Kode Unit'] ?? '') !== $kode_unit) continue;
        if ($filter_tanggal_tampil && ($entry_rencana['Tanggal'] ?? '') !== $filter_tanggal_tampil) continue;
        if ($filter_jenis_tampil && ($entry_rencana['Jenis Rencana'] ?? '') !== $filter_jenis_tampil) continue;

        $pn_rencana_tampil = $entry_rencana['PN'] ?? '';
        $nama_marketing_tampil = $data_mantri[$pn_rencana_tampil] ?? '-';
        $entry_rencana['Nama Marketing'] = $nama_marketing_tampil;
        $data_rencana_all[] = $entry_rencana;
    }
}

$totalData = count($data_rencana_all);
$jumlahHalaman = ceil($totalData / $dataPerHalaman);
$halamanSaatIni = max(1, min($halamanSaatIni, $jumlahHalaman));
$offset = ($halamanSaatIni - 1) * $dataPerHalaman;
$data_rencana_halaman_ini = array_slice($data_rencana_all, $offset, $dataPerHalaman);
$total_rencana_halaman_ini = count($data_rencana_halaman_ini);

?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Dashboard Pimpinan</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4>Dashboard Pimpinan - <?= htmlspecialchars($nama); ?></h4>
            <small class="text-muted">Kode Unit: <?= htmlspecialchars($kode_unit); ?></small>
        </div>
        <div>
            <a href="lihat_rencana_realisasi.php" class="btn btn-primary btn-sm me-2">Lihat Rencana & Realisasi</a>
            <a href="logout.php" class="btn btn-outline-danger btn-sm">Logout</a>
        </div>
    </div>

    <div class="card shadow p-4">
        <h5 class="mb-4">Data Rencana Kunjungan Seluruh Mantri Unit</h5>

        <form method="GET" class="row g-3 mb-4">
            <div class="col-md-3">
                <label for="tanggal" class="form-label">Tanggal</label>
                <input type="date" name="tanggal" id="tanggal" class="form-control" value="<?= htmlspecialchars($_GET['tanggal'] ?? '') ?>">
            </div>
            <div class="col-md-3">
                <label for="jenis_rencana" class="form-label">Jenis Rencana</label>
                <select name="jenis_rencana" id="jenis_rencana" class="form-select">
                    <option value="">Semua</option>
                    <?php foreach ($daftar_jenis_rencana as $value => $label): ?>
                        <?php if ($value !== ''): ?>
                            <option value="<?= htmlspecialchars($value) ?>" <?= ($_GET['jenis_rencana'] ?? '') === $value ? 'selected' : '' ?>><?= htmlspecialchars($label) ?></option>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3 d-flex align-items-end">
                <button type="submit" class="btn btn-success me-2">Filter</button>
                <a href="dashboard_pimpinan.php" class="btn btn-secondary me-2">Reset</a>
                <button type="submit" class="btn btn-info" name="export" value="csv">Ekspor CSV</button>
            </div>
        </form>

        <?php if ($totalData > 0): ?>
            <p class="mb-2"><strong>Total Rencana Kunjungan:</strong> <?= htmlspecialchars($totalData); ?></p>
        <?php elseif (isset($_GET['tanggal']) || isset($_GET['jenis_rencana'])): ?>
            <p class="mb-2 text-muted">Tidak ada rencana kunjungan yang sesuai dengan filter.</p>
        <?php elseif (!file_exists($file_rencana)): ?>
            <p class="mb-2 text-danger">File data_rencana.csv tidak ditemukan.</p>
        <?php else: ?>
            <p class="mb-2 text-muted">Belum ada rencana kunjungan yang dibuat.</p>
        <?php endif; ?>

        <div class="table-responsive">
            <table class="table table-bordered table-striped">
                <thead class="table-light">
                    <tr>
                        <th>Tanggal</th>
                        <th>Nama Marketing</th>
                        <th>PN</th>
                        <th>Jenis Rencana</th>
                        <th>Unit</th>
                        <th>Nama</th>
                        <th>Detail</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if (file_exists($file_rencana) && !empty($data_rencana_halaman_ini)) {
                        foreach ($data_rencana_halaman_ini as $entry) {
                            echo "<tr>";
                            echo "<td>" . htmlspecialchars($entry['Tanggal'] ?? '-') . "</td>";
                            echo "<td>" . htmlspecialchars($entry['Nama Marketing']) . "</td>";
                            echo "<td>" . htmlspecialchars($entry['PN'] ?? '-') . "</td>";
                            echo "<td>" . htmlspecialchars($entry['Jenis Rencana'] ?? '-') . "</td>";
                            echo "<td>" . htmlspecialchars($entry['Kode Unit'] ?? '-') . "</td>";
                            echo "<td>" . htmlspecialchars($entry['Nama'] ?? '-') . "</td>"; // Menggunakan 'Nama' (huruf kapital 'N')
                            echo "<td>";
                            // Tampilkan Nama jika ada
                            if (isset($entry['Nama']) && !empty($entry['Nama'])) {
                                echo "<strong>Nama:</strong> " . htmlspecialchars($entry['Nama']) . "<br>";
                            }
                            foreach ($entry as $key => $val) {
                                if (!in_array($key, ['PN', 'pn', 'nama', 'Tanggal', 'Jenis Rencana', 'Kode Unit', 'ID', 'Nama']) && !empty($val)) { // Menambahkan 'pn' ke array pengecualian
                                    echo "<strong>" . htmlspecialchars($key) . ":</strong> " . htmlspecialchars($val) . "<br>";
                                }
                            }
                            echo "</td>";
                            echo "</tr>";
                        }
                    } elseif (file_exists($file_rencana) && empty($data_rencana_halaman_ini) && ($totalData > 0)) {
                        echo "<tr><td colspan='7' class='text-center text-muted'>Tidak ada data pada halaman ini.</td></tr>";
                    } elseif (!file_exists($file_rencana)) {
                        echo "<tr><td colspan='7' class='text-center text-danger'>File data_rencana.csv tidak ditemukan.</td></tr>";
                    } else {
                        echo "<tr><td colspan='7' class='text-center text-muted'>Belum ada rencana kunjungan.</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>

        <?php if ($jumlahHalaman > 1): ?>
            <nav aria-label="Halaman data">
                <ul class="pagination justify-content-center mt-4">
                    <?php if ($halamanSaatIni > 1): ?>
                        <li class="page-item">
                            <a class="page-link" href="?halaman=<?= $halamanSaatIni - 1 ?><?= isset($_GET['tanggal']) ? '&tanggal=' . $_GET['tanggal'] : '' ?><?= isset($_GET['jenis_rencana']) ? '&jenis_rencana=' . $_GET['jenis_rencana'] : '' ?>" aria-label="Previous">
                                <span aria-hidden="true">&laquo;</span>
                            </a>
                        </li>
                    <?php endif; ?>
                    <?php for ($i = 1; $i <= $jumlahHalaman; $i++): ?>
                        <li class="page-item <?= ($i == $halamanSaatIni) ? 'active' : '' ?>">
                            <a class="page-link" href="?halaman=<?= $i ?><?= isset($_GET['tanggal']) ? '&tanggal=' . $_GET['tanggal'] : '' ?><?= isset($_GET['jenis_rencana']) ? '&jenis_rencana=' . $_GET['jenis_rencana'] : '' ?>"><?= $i ?></a>
                        </li>
                    <?php endfor; ?>
                    <?php if ($halamanSaatIni < $jumlahHalaman): ?>
                        <li class="page-item">
                            <a class="page-link" href="?halaman=<?= $halamanSaatIni + 1 ?><?= isset($_GET['tanggal']) ? '&tanggal=' . $_GET['tanggal'] : '' ?><?= isset($_GET['jenis_rencana']) ? '&jenis_rencana=' . $_GET['jenis_rencana'] : '' ?>" aria-label="Next">
                                <span aria-hidden="true">&raquo;</span>
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
            </nav>
        <?php endif; ?>

    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>