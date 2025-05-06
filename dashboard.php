<?php
session_start();


// Sisa kode Anda ...
if (!isset($_SESSION['pn'])) {
    header("Location: login.php");
    exit;
}
$nama = $_SESSION['nama'];
$unit_kerja = $_SESSION['unit_kerja'] ?? '-';
$pn_login = $_SESSION['pn'];
$kode_unit = $_SESSION['kode_unit'] ?? '';
$role = $_SESSION['role'] ?? 'marketing';
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Dashboard Mantri</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>.form-section { display: none; }</style>
</head>
<body class="bg-light">

<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4>Selamat datang, <?php echo htmlspecialchars($nama); ?>!</h4>
            <small class="text-muted">Unit Kerja: <?php echo htmlspecialchars($unit_kerja); ?></small>
        </div>
        <div>
           <a href="input_rencana_realisasi.php" class="btn btn-info btn-sm me-2">Form Input dan Rencana Realisasi</a>
            <a href="logout.php" class="btn btn-outline-danger btn-sm">Logout</a>
        </div>
    </div>

    <div class="card shadow p-4">
        <h4 class="mb-4">RENCANA KERJA HARIAN MANTRI WILAYAH MBAM</h4>

        <?php if (isset($_GET['success'])): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                ✅ Rencana berhasil disimpan.
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <form method="POST" action="simpan_rencana.php">
            <div class="mb-3">
                <label for="jenis_kunjungan" class="form-label">Jenis Rencana Kunjungan</label>
                <select id="jenis_kunjungan" name="jenis_kunjungan" class="form-select" required>
                    <option value="">-- Pilih --</option>
                    <option value="eksisting">Kunjungan pipeline pinjaman eksisting</option>
                    <option value="canvasing">Kunjungan Calon Nasabah Pinjaman Baru (Canvasing)</option>
                    <option value="restru">Periksa Ulang / Negosiasi Restruk</option>
                    <option value="somasi">Mengantar SP / Surat Somasi / Pemasangan Sticker</option>
                    <option value="sml1">Penagihan SML 1</option>
                    <option value="sml2">Penagihan SML 2</option>
                    <option value="sml3">Penagihan SML 3</option>
                    <option value="npl">Penagihan NPL</option>
                    <option value="dh">Penagihan DH</option>
                    <option value="simpanan">Kunjungan Nasabah Simpanan</option>
                    <option value="pickup">Pick Up Service Nasabah Simpanan</option>
                    <option value="edc">Kunjungan Penawaran / Pemasangan EDC / QRIS</option>
                    <option value="brilink">Klaster Usaha / Agen Brilink</option>
                </select>
            </div>

            <?php
            $fields = [
                'input_nama' => 'Nama',
                'input_rekening' => 'Nomor Rekening / Pinjaman',
                'input_os' => 'OS Saat Ini',
                'input_jabatan_atasan' => [
                    'label' => 'Jabatan Atasan',
                    'type' => 'select',
                    'options' => ['', 'KaUnit', 'MBM', 'MBAM']
                ],
                'input_potensi' => 'Potensi Plafond Pinjaman Baru',
                'input_pemutus' => [
                    'label' => 'Pemutus Pinjaman Baru',
                    'type' => 'select',
                    'options' => ['', 'KaUnit', 'MBM', 'MBAM']
                ],
                'input_giro' => 'Giro / Tabungan / Deposito',
                'input_edc' => 'EDC / QRIS',
                'input_klaster' => 'Nama Klaster / Agen Brilink'
            ];
            foreach ($fields as $id => $info) {
                echo "<div id=\"$id\" class=\"form-section mb-3\">";
                if (is_array($info)) {
                    echo "<label class=\"form-label\">" . htmlspecialchars($info['label']) . "</label>";
                    if ($info['type'] === 'select') {
                        echo "<select name=\"" . str_replace('input_', '', $id) . "\" class=\"form-select\">";
                        foreach ($info['options'] as $option_value) {
                            echo "<option value=\"" . htmlspecialchars($option_value) . "\">" . htmlspecialchars($option_value ?: '-- Pilih --') . "</option>";
                        }
                        echo "</select>";
                    } else {
                        echo "<input type=\"text\" name=\"" . str_replace('input_', '', $id) . "\" class=\"form-control\">";
                    }
                } else {
                    echo "<label class=\"form-label\">" . htmlspecialchars($info) . "</label>";
                    echo "<input type=\"text\" name=\"" . str_replace('input_', '', $id) . "\" class=\"form-control\">";
                }
                echo "</div>";
            }
            ?>

            <button type="submit" class="btn btn-success mt-3">Simpan</button>
        </form>

        <hr class="my-5">

        <h5>Data Rencana <?php echo $role === 'pimpinan' ? 'Unit Anda' : 'Anda'; ?></h5>
        <div class="table-responsive mt-3">
            <table class="table table-bordered table-striped">
                <thead class="table-light">
                    <tr>
                        <th>Tanggal</th>
                        <th>Jenis Rencana</th>
                        <th>Unit Kerja</th>
                        <th>Detail</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $file = 'data_rencana.csv';
                    if (file_exists($file)) {
                        $rows = @array_map('str_getcsv', @file($file));
                        if ($rows === false) {
                            echo "<tr><td colspan='5' class='text-center text-danger'>Gagal membaca file data_rencana.csv.</td></tr>";
                        } elseif (!empty($rows)) {
                            $header = array_shift($rows);
                            foreach ($rows as $row) {
                                if (count($row) !== count($header)) continue;
                                $entry = array_combine($header, array_map('trim', $row));
                                if (
                                    ($role === 'marketing' && ($entry['PN'] ?? '') === $pn_login) ||
                                    ($role === 'pimpinan' && ($entry['Kode Unit'] ?? '') === $kode_unit)
                                ) {
                                    echo "<tr>";
                                    echo "<td>" . htmlspecialchars($entry['Tanggal'] ?? '-') . "</td>";
                                    echo "<td>" . htmlspecialchars($entry['Jenis Rencana'] ?? '-') . "</td>";
                                    echo "<td>" . htmlspecialchars($entry['Kode Unit'] ?? '-') . "</td>";
                                    echo "<td>";
                                    foreach ($entry as $key => $val) {
                                        if (!in_array($key, ['PN', 'Nama', 'Tanggal', 'Jenis Rencana', 'ID', 'Kode Unit']) && !empty($val)) {
                                            echo "<strong>" . htmlspecialchars($key) . ":</strong> " . htmlspecialchars($val) . "<br>";
                                        }
                                    }
                                    echo "</td>";
                                    echo "<td>";
                                    if (($entry['PN'] ?? '') === $pn_login || $role === 'pimpinan') {
                                        echo "<a href='edit_rencana.php?id=" . urlencode($entry['ID'] ?? '') . "' class='btn btn-sm btn-warning me-1'>Edit</a>";
                                        echo "<a href='hapus_rencana.php?id=" . urlencode($entry['ID'] ?? '') . "' class='btn btn-sm btn-danger' onclick=\"return confirm('Yakin ingin menghapus data ini?');\">Hapus</a>";
                                    }
                                    echo "</td>";
                                    echo "</tr>";
                                }
                            }
                        } else {
                            echo "<tr><td colspan='5' class='text-center'>Tidak ada data rencana.</td></tr>";
                        }
                    } else {
                        echo "<tr><td colspan='5' class='text-center text-danger'>File data_rencana.csv tidak ditemukan.</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
const fieldMap = {
    eksisting: ['input_nama', 'input_rekening', 'input_os', 'input_potensi', 'input_pemutus', 'input_jabatan_atasan'],
    canvasing: ['input_nama', 'input_potensi', 'input_pemutus', 'input_jabatan_atasan'],
    restru: ['input_nama', 'input_rekening', 'input_jabatan_atasan', 'input_os'],
    somasi: ['input_nama', 'input_rekening', 'input_os', 'input_jabatan_atasan'],
    sml1: ['input_nama', 'input_rekening', 'input_os', 'input_jabatan_atasan'],
    sml2: ['input_nama', 'input_rekening', 'input_os', 'input_jabatan_atasan'],
    sml3: ['input_nama', 'input_rekening', 'input_os', 'input_jabatan_atasan'],
    npl: ['input_nama', 'input_rekening', 'input_os', 'input_jabatan_atasan'],
    dh: ['input_nama', 'input_rekening', 'input_os', 'input_jabatan_atasan'],
    simpanan: ['input_nama', 'input_giro', 'input_jabatan_atasan'],
    pickup: ['input_nama', 'input_giro', 'input_jabatan_atasan'],
    edc: ['input_nama', 'input_edc', 'input_jabatan_atasan'],
    brilink: ['input_klaster', 'input_jabatan_atasan']
};

const select = document.getElementById("jenis_kunjungan");
const allInputs = document.querySelectorAll(".form-section");

select.addEventListener("change", function () {
    allInputs.forEach(el => el.style.display = "none");
    const selected = select.value;
    if (fieldMap[selected]) {
        fieldMap[selected].forEach(id => {
            const el = document.getElementById(id);
            if (el) el.style.display = "block";
        });
    }
});
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>