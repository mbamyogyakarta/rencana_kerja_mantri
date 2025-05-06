<?php
session_start();
if (!isset($_SESSION['pn'])) {
    header("Location: login.php");
    exit;
}

$pn_login = $_SESSION['pn'];
$role = $_SESSION['role'] ?? 'marketing';
$kode_unit = $_SESSION['kode_unit'] ?? '';

$id = $_GET['id'] ?? '';
if (!$id) {
    echo "ID tidak ditemukan.";
    exit;
}

$file = 'data_rencana.csv';
if (!file_exists($file)) {
    echo "Data tidak ditemukan.";
    exit;
}

$rows = array_map('str_getcsv', file($file));
$header = array_shift($rows);
$data = null;

foreach ($rows as $row) {
    if (count($row) !== count($header)) continue;
    $entry = array_combine($header, $row);
    if ($entry['ID'] === $id) {
        if (
            ($role === 'marketing' && $entry['PN'] !== $pn_login) ||
            ($role === 'pimpinan' && ($entry['Kode Unit'] ?? '') !== $kode_unit)
        ) {
            echo "Anda tidak memiliki akses untuk mengedit data ini.";
            exit;
        }
        $data = $entry;
        break;
    }
}

if (!$data) {
    echo "Data tidak ditemukan.";
    exit;
}

$jenis = $data['Jenis Rencana'];
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Edit Rencana</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>.form-section { display: none; }</style>
</head>
<body class="bg-light">
<div class="container py-5">
    <h3>Edit Rencana Kunjungan</h3>
    <form method="POST" action="simpan_rencana.php">
        <input type="hidden" name="edit_id" value="<?php echo htmlspecialchars($id); ?>">

        <div class="mb-3">
            <label class="form-label">Jenis Rencana</label>
            <input type="text" name="jenis_kunjungan" class="form-control" value="<?php echo htmlspecialchars($jenis); ?>" readonly>
        </div>

        <?php
        $fields = [
    'input_nama_debitur' => ['label' => 'Nama', 'csv' => 'Nama'],
    'input_rekening' => ['label' => 'Nomor Rekening / Pinjaman', 'csv' => 'Rekening'],
    'input_os' => ['label' => 'OS Saat Ini', 'csv' => 'OS'],
    'input_jabatan_atasan' => ['label' => 'Jabatan Atasan', 'csv' => 'Jabatan Atasan'],
    'input_potensi' => ['label' => 'Potensi Plafond Pinjaman Baru', 'csv' => 'Potensi'],
    'input_pemutus' => ['label' => 'Pemutus Pinjaman Baru', 'csv' => 'Pemutus'],
    'input_giro' => ['label' => 'Giro / Tabungan / Deposito', 'csv' => 'Giro'],
    'input_edc' => ['label' => 'EDC / QRIS', 'csv' => 'EDC'],
    'input_klaster' => ['label' => 'Nama Klaster / Agen Brilink', 'csv' => 'Klaster']
];

foreach ($fields as $id => $meta) {
    $label = $meta['label'];
    $csv_key = $meta['csv'];
    $value = isset($data[$csv_key]) ? $data[$csv_key] : '';

    echo "<div id=\"$id\" class=\"form-section mb-3\">
            <label class=\"form-label\">$label</label>
            <input type=\"text\" name=\"" . str_replace('input_', '', $id) . "\" class=\"form-control\" value=\"" . htmlspecialchars($value) . "\">
          </div>";
}

        ?>

        <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
        <a href="dashboard.php" class="btn btn-secondary">Batal</a>
    </form>
</div>

<script>
const fieldMap = {
    eksisting: ['input_nama', 'input_rekening', 'input_os', 'input_potensi', 'input_pemutus'],
    canvasing: ['input_nama', 'input_potensi', 'input_pemutus'],
    restru: ['input_nama', 'input_rekening', 'input_jabatan_atasan', 'input_os'],
    somasi: ['input_nama', 'input_rekening', 'input_os'],
    sml1: ['input_nama', 'input_rekening', 'input_os'],
    sml2: ['input_nama', 'input_rekening', 'input_os'],
    sml3: ['input_nama', 'input_rekening', 'input_os'],
    npl: ['input_nama', 'input_rekening', 'input_os'],
    dh: ['input_nama', 'input_rekening', 'input_os'],
    simpanan: ['input_nama', 'input_giro'],
    pickup: ['input_nama', 'input_giro'],
    edc: ['input_nama', 'input_edc'],
    brilink: ['input_klaster']
};

const jenis = "<?php echo strtolower($jenis); ?>";
const allInputs = document.querySelectorAll(".form-section");

allInputs.forEach(el => el.style.display = "none");
if (fieldMap[jenis]) {
    fieldMap[jenis].forEach(id => {
        const el = document.getElementById(id);
        if (el) el.style.display = "block";
    });
}
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
