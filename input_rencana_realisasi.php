<?php
session_start();
// Set zona waktu default untuk Indonesia (WIB)
date_default_timezone_set('Asia/Jakarta');

if (!isset($_SESSION['pn'])) {
    header("Location: login.php");
    exit;
}
$nama = $_SESSION['nama'];
$unit_kerja_session = $_SESSION['unit_kerja'] ?? '-';
$pn_login = $_SESSION['pn'];
$kode_unit_session = $_SESSION['kode_unit'] ?? ''; // Ambil kode unit dari session
$role = $_SESSION['role'] ?? 'marketing';

// Ambil kode_kanca, kanca, dan unit_kerja dari data_mantri_with_password.csv
$kode_bo_otomatis = '';
$branch_office_otomatis = '';
$bri_unit_otomatis = '';
$file_mantri = 'data_mantri_with_password.csv';
if (file_exists($file_mantri)) {
    $rows_mantri = array_map('str_getcsv', file($file_mantri));
    $header_mantri = array_shift($rows_mantri);
    foreach ($rows_mantri as $row_mantri) {
        if (count($row_mantri) === count($header_mantri)) {
            $entry_mantri = array_combine($header_mantri, $row_mantri);
            if ($entry_mantri['pn'] === $pn_login) {
                $kode_bo_otomatis = $entry_mantri['kode_kanca'] ?? '';
                $branch_office_otomatis = $entry_mantri['kanca'] ?? '';
                $bri_unit_otomatis = $entry_mantri['unit_kerja'] ?? '';
                break;
            }
        }
    }
}

// Proses hapus rencana realisasi
if (isset($_GET['hapus_id'])) {
    $id_hapus = $_GET['hapus_id'];
    $file_gabungan = 'data_rencana_realisasi.csv';
    $data_gabungan = [];
    $header_gabungan = ['Jenis Data', 'ID Rencana', 'Tanggal Rencana', 'Kode BO', 'Branch Office', 'Kode Unit', 'BRI Unit', 'PN', 'Nama Mantri', 'Nama Deb', 'Sumber Deb', 'CIF', 'Produk', 'Plafond', 'Pemutus', 'Tanggal Realisasi', 'Hasil Kunjungan', 'Status Realisasi', 'Tindak Lanjut', 'Plafond Realisasi']; // Tambahkan 'Plafond Realisasi'

    if (file_exists($file_gabungan)) {
        $rows = array_map('str_getcsv', file($file_gabungan));
        $header_existing = array_shift($rows);
        foreach ($rows as $row) {
            if (count($row) === count($header_existing)) {
                $entry = array_combine($header_existing, $row);
                if ($entry['ID Rencana'] !== $id_hapus) {
                    $data_gabungan[] = $entry;
                }
            }
        }

        $fp = fopen($file_gabungan, 'w');
        fputcsv($fp, $header_gabungan);
        foreach ($data_gabungan as $fields) {
            fputcsv($fp, $fields);
        }
        fclose($fp);

        header("Location: input_rencana_realisasi.php?hapus_success=1");
        exit;
    } else {
        header("Location: input_rencana_realisasi.php?hapus_failed=1");
        exit;
    }
}

// Proses simpan rencana (tetap ada untuk input rencana awal)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['nama_deb']) && !isset($_POST['hasil_kunjungan'])) {
    $kode_bo = $_POST['kode_bo'] ?? $kode_bo_otomatis;
    $branch_office = $_POST['branch_office'] ?? $branch_office_otomatis;
    $kode_unit_rencana = $_POST['kode_unit'] ?? '';
    $bri_unit = $_POST['bri_unit'] ?? $bri_unit_otomatis;
    $pn = $_POST['pn'] ?? $pn_login;
    $nama_mantri = $_POST['nama_mantri'] ?? $nama;
    $nama_deb = $_POST['nama_deb'] ?? '';
    $sumber_deb = $_POST['sumber_deb'] ?? '';
    $cif = $_POST['cif'] ?? '';
    $produk = $_POST['produk'] ?? '';
    $plafond = $_POST['plafond'] ?? '';
    date_default_timezone_set('Asia/Jakarta'); // Set zona waktu ke WIB
    $tanggal_rencana = date('Y-m-d');
    $id_rencana = uniqid('rencana_'); // Prefix ID untuk rencana
    $jenis_data = 'rencana'; // Menandakan ini adalah data rencana

    $file_gabungan = 'data_rencana_realisasi.csv';
    $data_gabungan = [];
    $header_gabungan = ['Jenis Data', 'ID Rencana', 'Tanggal Rencana', 'Kode BO', 'Branch Office', 'Kode Unit', 'BRI Unit', 'PN', 'Nama Mantri', 'Nama Deb', 'Sumber Deb', 'CIF', 'Produk', 'Plafond', 'Pemutus', 'Tanggal Realisasi', 'Hasil Kunjungan', 'Status Realisasi', 'Tindak Lanjut', 'Plafond Realisasi']; // Tambahkan 'Plafond Realisasi'

    if (file_exists($file_gabungan)) {
        $rows = array_map('str_getcsv', file($file_gabungan));
        $header_existing = array_shift($rows);
        foreach ($rows as $row) {
            if (count($row) === count($header_existing)) {
                $data_gabungan[] = array_combine($header_existing, $row);
            }
        }
    }

    $new_data = array_combine($header_gabungan, [$jenis_data, $id_rencana, $tanggal_rencana, $kode_bo, $branch_office, $kode_unit_rencana, $bri_unit, $pn, $nama_mantri, $nama_deb, $sumber_deb, $cif, $produk, $plafond, $pemutus, '', '', '', '', '']); // Tambahkan '' untuk 'Plafond Realisasi'
    $data_gabungan[] = $new_data;

    $fp = fopen($file_gabungan, 'w');
    fputcsv($fp, $header_gabungan);
    foreach ($data_gabungan as $fields) {
        fputcsv($fp, $fields);
    }
    fclose($fp);

    header("Location: input_rencana_realisasi.php?rencana_success=1");
    exit;
}

// Proses simpan rencana (tetap ada untuk input rencana awal)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['nama_deb']) && !isset($_POST['hasil_kunjungan'])) {
    $kode_bo = $_POST['kode_bo'] ?? $kode_bo_otomatis;
    $branch_office = $_POST['branch_office'] ?? $branch_office_otomatis;
    $kode_unit_rencana = $_POST['kode_unit'] ?? '';
    $bri_unit = $_POST['bri_unit'] ?? $bri_unit_otomatis;
    $pn = $_POST['pn'] ?? $pn_login;
    $nama_mantri = $_POST['nama_mantri'] ?? $nama;
    $nama_deb = $_POST['nama_deb'] ?? '';
    $sumber_deb = $_POST['sumber_deb'] ?? '';
    $cif = $_POST['cif'] ?? '';
    $produk = $_POST['produk'] ?? '';
    $plafond = $_POST['plafond'] ?? '';
    $pemutus = $_POST['pemutus'] ?? '';
    $tanggal_rencana = $_POST['tanggal_rencana'] ?? date('Y-m-d'); // PERUBAHAN DI SINI
    $id_rencana = uniqid('rencana_'); // Prefix ID untuk rencana
    $jenis_data = 'rencana'; // Menandakan ini adalah data rencana

    $file_gabungan = 'data_rencana_realisasi.csv';
    $data_gabungan = [];
    $header_gabungan = ['Jenis Data', 'ID Rencana', 'Tanggal Rencana', 'Kode BO', 'Branch Office', 'Kode Unit', 'BRI Unit', 'PN', 'Nama Mantri', 'Nama Deb', 'Sumber Deb', 'CIF', 'Produk', 'Plafond', 'Pemutus', 'Tanggal Realisasi', 'Hasil Kunjungan', 'Status Realisasi', 'Tindak Lanjut', 'Plafond Realisasi']; // Tambahkan 'Plafond Realisasi'

    if (file_exists($file_gabungan)) {
        $rows = array_map('str_getcsv', file($file_gabungan));
        $header_existing = array_shift($rows);
        foreach ($rows as $row) {
            if (count($row) === count($header_existing)) {
                $data_gabungan[] = array_combine($header_existing, $row);
            }
        }
    }

    $new_data = array_combine($header_gabungan, [$jenis_data, $id_rencana, $tanggal_rencana, $kode_bo, $branch_office, $kode_unit_rencana, $bri_unit, $pn, $nama_mantri, $nama_deb, $sumber_deb, $cif, $produk, $plafond, $pemutus, '', '', '', '', '']); // Tambahkan '' untuk 'Plafond Realisasi'
    $data_gabungan[] = $new_data;

    $fp = fopen($file_gabungan, 'w');
    fputcsv($fp, $header_gabungan);
    foreach ($data_gabungan as $fields) {
        fputcsv($fp, $fields);
    }
    fclose($fp);

    header("Location: input_rencana_realisasi.php?rencana_success=1");
    exit;
}

// Proses simpan realisasi (update data yang ada, bukan tambah baru)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['hasil_kunjungan'])) {
    $id_rencana_realisasi = $_POST['id_rencana_realisasi'];
    $tanggal_realisasi = $_POST['tanggal_realisasi'];
    $hasil_kunjungan = $_POST['hasil_kunjungan'];
    $status_realisasi = $_POST['status_realisasi'];
    $tindak_lanjut = $_POST['tindak_lanjut'];
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
                if ($entry['ID Rencana'] === $id_rencana_realisasi) {
                    // Update data realisasi
                    $entry['Tanggal Realisasi'] = $tanggal_realisasi;
                    $entry['Hasil Kunjungan'] = $hasil_kunjungan;
                    $entry['Status Realisasi'] = $status_realisasi;
                    $entry['Tindak Lanjut'] = $tindak_lanjut;
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

        header("Location: input_rencana_realisasi.php?realisasi_success=1");
        exit;
    } else {
        // File tidak ditemukan, handle error (mungkin redirect ke halaman error)
        header("Location: input_rencana_realisasi.php?realisasi_failed=1"); // Atau halaman error lainnya
        exit;
    }
}


// Baca data dari file gabungan
$file_gabungan = 'data_rencana_realisasi.csv';
$data_rencana_arr = [];
$data_realisasi_arr = [];
if (file_exists($file_gabungan)) {
    $rows = array_map('str_getcsv', file($file_gabungan));
    $header = array_shift($rows);
    foreach ($rows as $row) {
        if (count($row) === count($header)) {
            $entry = array_combine($header, $row);
            if ($entry['Jenis Data'] === 'rencana') {
                if (($role === 'marketing' && $entry['PN'] === $pn_login) || ($role === 'pimpinan' && ($entry['Kode Unit'] ?? '') === $kode_unit_session)) {
                    $data_rencana_arr[] = $entry;
                }
            } elseif ($entry['Jenis Data'] === 'realisasi') {
                // Kita tidak perlu array terpisah untuk realisasi saat ini
                $data_realisasi_arr[] = $entry;
            }
        }
    }
}

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Input Rencana Realisasi</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4>Input Rencana Realisasi</h4>
            <small class="text-muted">Unit Kerja: <?php echo htmlspecialchars($unit_kerja_session); ?></small>
        </div>
        <a href="dashboard.php" class="btn btn-secondary btn-sm me-2">Kembali ke Dashboard</a>
        <a href="logout.php" class="btn btn-outline-danger btn-sm">Logout</a>
    </div>

    <?php if (isset($_GET['rencana_success'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            Rencana berhasil disimpan.
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if (isset($_GET['realisasi_success'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            Realisasi berhasil disimpan.
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if (isset($_GET['hapus_success'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            Data rencana dan realisasi berhasil dihapus.
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if (isset($_GET['hapus_failed'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            Gagal menghapus data. File tidak ditemukan.
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="card shadow p-4 mb-4">
        <h4>Form Input Rencana</h4>
        <form method="POST" action="">
            <div class="mb-3">
                <label for="kode_bo" class="form-label">Kode BO</label>
                <input type="text" class="form-control" id="kode_bo" name="kode_bo" value="<?php echo htmlspecialchars($kode_bo_otomatis); ?>" readonly>
                <small class="text-muted">Terisi otomatis sesuai data Anda.</small>
            </div>
            <div class="mb-3">
                <label for="branch_office" class="form-label">Branch Office</label>
                <input type="text" class="form-control" id="branch_office" name="branch_office" value="<?php echo htmlspecialchars($branch_office_otomatis); ?>" readonly>
                <small class="text-muted">Terisi otomatis sesuai data Anda.</small>
            </div>
            <div class="mb-3">
                <label for="kode_unit" class="form-label">Kode Unit</label>
                <input type="text" class="form-control" id="kode_unit" name="kode_unit" value="<?php echo htmlspecialchars($kode_unit_session); ?>" readonly>
                <small class="text-muted">Terisi otomatis sesuai unit kerja Anda.</small>
            </div>
            <div class="mb-3">
                <label for="bri_unit" class="form-label">BRI Unit</label>
                <input type="text" class="form-control" id="bri_unit" name="bri_unit" value="<?php echo htmlspecialchars($bri_unit_otomatis); ?>" readonly>
                <small class="text-muted">Terisi otomatis sesuai data Anda.</small>
            </div>
            <div class="mb-3">
                <label for="pn" class="form-label">PN</label>
                <input type="text" class="form-control" id="pn" name="pn" value="<?php echo htmlspecialchars($pn_login); ?>" readonly>
                <small class="text-muted">Terisi otomatis sesuai PN Anda.</small>
            </div>
            <div class="mb-3">
                <label for="nama_mantri" class="form-label">Nama Mantri</label>
                <input type="text" class="form-control" id="nama_mantri" name="nama_mantri" value="<?php echo htmlspecialchars($nama); ?>" readonly>
                <small class="text-muted">Terisi otomatis sesuai nama Anda.</small>
            </div>
             <div class="mb-3">
                <label for="tanggal_rencana" class="form-label">Tanggal Rencana</label>
                <input type="date" class="form-control" id="tanggal_rencana" name="tanggal_rencana" value="<?php echo date('Y-m-d'); ?>">
                <small class="text-muted">Pilih tanggal rencana kunjungan.</small>
            </div>
            <div class="mb-3">
                <label for="nama_deb" class="form-label">Nama Deb</label>
                <input type="text" class="form-control" id="nama_deb" name="nama_deb" required>
            </div>
            <div class="mb-3">
                <label for="sumber_deb" class="form-label">Sumber Debitur</label>
                <select class="form-select" id="sumber_deb" name="sumber_deb">
                    <option value="">-- Pilih Sumber --</option>
                    <option value="Pipeline">Pipeline</option>
                    <option value="Canvasing">Canvasing</option>
                </select>
            </div>
            <div class="mb-3">
                <label for="cif" class="form-label">CIF</label>
                <input type="text" class="form-control" id="cif" name="cif">
            </div>
            <div class="mb-3">
                <label for="produk" class="form-label">Produk</label>
                <select class="form-select" id="produk" name="produk">
                    <option value="">-- Pilih Produk --</option>
                    <option value="KUR">KUR</option>
                    <option value="Kupedes">Kupedes</option>
                     <option value
                    <option value="Briguna">Briguna</option>
                </select>
            </div>
            <div class="mb-3">
                <label for="plafond" class="form-label">Plafond</label>
                <input type="text" class="form-control" id="plafond" name="plafond">
            </div>
            <div class="mb-3">
                <label for="pemutus" class="form-label">Pemutus</label>
                <select class="form-select" id="pemutus" name="pemutus">
                    <option value="">-- Pilih Pemutus --</option>
                    <option value="Ka Unit">KaUnit</option>
                    <option value="MBM">MBM</option>
                    <option value="MBAM">MBAM</option>
                </select>
            </div>
            <button type="submit" class="btn btn-success">Simpan Rencana Realisasi</button>
        </form>
    </div>

    <div class="card shadow p-4 mb-4">
         <div class="table-responsive mt-3">
            <h4>Daftar Rencana Kunjungan</h4>
            <table class="table table-bordered table-striped">
                <thead class="table-light">
                    <tr>
                        <th>ID</th>
                         <th>Tanggal</th>
                        <th>Kode BO</th>
                        <th>Branch Office</th>
                        <th>Kode Unit</th>
                        <th>BRI Unit</th>
                        <th>PN</th>
                        <th>Nama Mantri</th>
                        <th>Nama</th>
                        <th>Sumber Deb</th>
                        <th>CIF</th>
                        <th>Produk</th>
                        <th>Plafond</th>
                        <th>Pemutus</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($data_rencana_arr)): ?>
                        <tr><td colspan="15" class="text-center">Tidak ada rencana kunjungan.</td></tr>
                    <?php else: ?>
                        <?php foreach ($data_rencana_arr as $rencana): ?>
                            <tr>
                                <td><?php echo htmlspecialchars(str_replace('rencana_', '', $rencana['ID Rencana'])); ?></td>
                                 <td><?php echo htmlspecialchars($rencana['Tanggal Rencana']); ?></td>
                                <td><?php echo htmlspecialchars($rencana['Kode BO']); ?></td>
                                <td><?php echo htmlspecialchars($rencana['Branch Office']); ?></td>
                                <td><?php echo htmlspecialchars($rencana['Kode Unit']); ?></td>
                                <td><?php echo htmlspecialchars($rencana['BRI Unit']); ?></td>
                                <td><?php echo htmlspecialchars($rencana['PN']); ?></td>
                                <td><?php echo htmlspecialchars($rencana['Nama Mantri']); ?></td>
                                <td><?php echo htmlspecialchars($rencana['Nama Deb']); ?></td>
                                <td><?php echo htmlspecialchars($rencana['Sumber Deb']); ?></td>
                                <td><?php echo htmlspecialchars($rencana['CIF']); ?></td>
                                <td><?php echo htmlspecialchars($rencana['Produk']); ?></td>
                                <td><?php echo htmlspecialchars($rencana['Plafond']); ?></td>
                                <td><?php echo htmlspecialchars($rencana['Pemutus']); ?></td>
                                <td>
                                    <button type="button" class="btn btn-sm btn-primary btn-tambah-realisasi" data-id="<?php echo htmlspecialchars($rencana['ID Rencana']); ?>">
                                        Hasil Kunjungan
                                    </button>
                                    <a href="input_rencana_realisasi.php?hapus_id=<?php echo urlencode($rencana['ID Rencana']); ?>" class="btn btn-sm btn-danger ms-1" onclick="return confirm('Apakah Anda yakin ingin menghapus rencana ini?')">
                                        Hapus
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="card shadow p-4">
       
        <form method="POST" action="" id="form-realisasi" style="display: none;">
            <div class="mb-3">
                <label for="id_rencana_realisasi" class="form-label">ID Rencana</label>
                <input type="text" class="form-control" id="id_rencana_realisasi" name="id_rencana_realisasi" readonly>
            </div>
             <div class="mb-3">
                 <label for="tanggal_realisasi" class="form-label">Tanggal Realisasi</label>
                <input type="date" class="form-control" id="tanggal_realisasi" name="tanggal_realisasi" value="<?php echo date('Y-m-d'); ?>">
            </div>
            <div class="mb-3">
                <label for="hasil_kunjungan" class="form-label">Hasil Kunjungan</label>
                <textarea class="form-control" id="hasil_kunjungan" name="hasil_kunjungan" rows="3"></textarea>
            </div>
            <div class="mb-3">
                <label for="status_realisasi" class="form-label">Status Realisasi</label>
                <select class="form-select" id="status_realisasi" name="status_realisasi">
                    <option value="">-- Pilih Status --</option>
                    <option value="Berhasil">Berhasil</option>
                    <option value="Belum Berhasil">Belum Berhasil</option>
                    <option value="Ditunda">Ditunda</option>
                </select>
            </div>
            <div class="mb-3">
                <label for="tindak_lanjut" class="form-label">Tindak Lanjut</label>
                <textarea class="form-control" id="tindak_lanjut" name="tindak_lanjut" rows="3"></textarea>
            </div>
            <div class="mb-3">
                <label for="plafond_realisasi" class="form-label">Plafond Realisasi</label>
                <input type="text" class="form-control" id="plafond_realisasi" name="plafond_realisasi">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-batal-realisasi" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-primary">Simpan Realisasi</button>
            </div>
        </form>

         <div class="table-responsive mt-3">
            <h4>Update Hasil Kunjungan</h4>
            <table class="table table-bordered table-striped">
                <thead class="table-light">
                    <tr>
                        <th>ID Rencana</th>
                         <th>Tanggal Realisasi</th>
                        <th>Hasil Kunjungan</th>
                        <th>Status Realisasi</th>
                        <th>Tindak Lanjut</th>
                        <th>Plafond Realisasi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $realisasi_exist = false;
                    foreach ($data_rencana_arr as $rencana) {
                        if (!empty($rencana['Tanggal Realisasi'])) {
                            echo "<tr>";
                            echo "<td>" . htmlspecialchars(str_replace('rencana_', '', $rencana['ID Rencana'])) . "</td>";
                             echo "<td>" . htmlspecialchars($rencana['Tanggal Realisasi']) . "</td>";
                            echo "<td>" . htmlspecialchars($rencana['Hasil Kunjungan']) . "</td>";
                            echo "<td>" . htmlspecialchars($rencana['Status Realisasi']) . "</td>";
                            echo "<td>" . htmlspecialchars($rencana['Tindak Lanjut']) . "</td>";
                             echo "<td>" . htmlspecialchars($rencana['Plafond Realisasi']) . "</td>";
                            echo "</tr>";
                            $realisasi_exist = true;
                        }
                    }
                    if (!$realisasi_exist && !empty($data_rencana_arr)): ?>
                        <tr><td colspan="6" class="text-center">Belum ada data realisasi untuk rencana yang ada.</td></tr>
                    <?php elseif (empty($data_rencana_arr)): ?>
                        <tr><td colspan="6" class="text-center">Tidak ada data rencana.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const tombolTambahRealisasi = document.querySelectorAll('.btn-tambah-realisasi');
        const formRealisasi = document.getElementById('form-realisasi');
        const inputIdRencanaRealisasi = document.getElementById('id_rencana_realisasi');
        const tombolBatalRealisasi = document.querySelector('.btn-batal-realisasi');

        tombolTambahRealisasi.forEach(tombol => {
            tombol.addEventListener('click', function() {
                const idRencana = this.getAttribute('data-id');
                inputIdRencanaRealisasi.value = idRencana;
                formRealisasi.style.display = 'block';
            });
        });

        tombolBatalRealisasi.addEventListener('click', function() {
            formRealisasi.style.display = 'none';
            inputIdRencanaRealisasi.value = '';
            document.getElementById('hasil_kunjungan').value = '';
            document.getElementById('status_realisasi').value = '';
            document.getElementById('tindak_lanjut').value = '';
            document.getElementById('plafond_realisasi').value='';
        });
    });
</script>
</body>
</html>