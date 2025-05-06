<?php
session_start();
if (!isset($_SESSION['pn'])) {
    header("Location: login.php");
    exit;
}

date_default_timezone_set('Asia/Jakarta'); // Atur zona waktu (penting untuk konsistensi)

$pn = $_SESSION['pn'];
$nama = $_SESSION['nama'];
$kode_unit = $_SESSION['kode_unit'] ?? '-';
$tanggal = date('Y-m-d');
$id_edit = $_POST['edit_id'] ?? null;

$jenis = $_POST['jenis_kunjungan'] ?? '';
$form_fields = [
    'nama' => $_POST['nama'] ?? '',
    'rekening' => $_POST['rekening'] ?? '',
    'os' => $_POST['os'] ?? '',
    'jabatan_atasan' => $_POST['jabatan_atasan'] ?? '',
    'potensi' => $_POST['potensi'] ?? '',
    'pemutus' => $_POST['pemutus'] ?? '',
    'giro' => $_POST['giro'] ?? '',
    'edc' => $_POST['edc'] ?? '',
    'klaster' => $_POST['klaster'] ?? ''
];

// Siapkan header CSV
$file = 'data_rencana.csv';
$header = ['ID', 'Tanggal', 'PN', 'Nama', 'Kode Unit', 'Jenis Rencana', 'Nama', 'Rekening', 'OS', 'Jabatan Atasan', 'Potensi', 'Pemutus', 'Giro', 'EDC', 'Klaster'];

// Check if the file exists, create if it doesn't, then open for writing
if (!file_exists($file)) {
    $fp = fopen($file, 'w');
    if ($fp === false) {
        die("Failed to create file: $file");
    }
    fputcsv($fp, $header);
    fclose($fp);
}

$rows = array_map('str_getcsv', file($file));
$existing_header = array_shift($rows);

// Jika edit
if ($id_edit) {
    $new_rows = [];
    foreach ($rows as $row) {
        if (count($row) !== count($existing_header)) continue;
        $entry = array_combine($existing_header, $row);
        if ($entry['ID'] === $id_edit) {
            $entry['Tanggal'] = $tanggal;
            $entry['Jenis Rencana'] = $jenis;
            $entry['Nama'] = $form_fields['nama'];
            $entry['Rekening'] = $form_fields['rekening'];
            $entry['OS'] = $form_fields['os'];
            $entry['Jabatan Atasan'] = $form_fields['jabatan_atasan'];
            $entry['Potensi'] = $form_fields['potensi'];
            $entry['Pemutus'] = $form_fields['pemutus'];
            $entry['Giro'] = $form_fields['giro'];
            $entry['EDC'] = $form_fields['edc'];
            $entry['Klaster'] = $form_fields['klaster'];
            $row = [];
            foreach ($header as $col) {
                $row[] = $entry[$col] ?? '';
            }
        }
        $new_rows[] = $row;
    }

    $fp = fopen($file, 'w');
    if ($fp === false) {
        die("Failed to open file for writing: $file");
    }
    fputcsv($fp, $header);
    foreach ($new_rows as $r) {
        fputcsv($fp, $r);
    }
    fclose($fp);

    header("Location: dashboard.php");
    exit;
}

// Jika tambah baru
$id_baru = uniqid('rnc_');
$new_entry = [
    $id_baru,
    $tanggal,
    $pn,
    $nama,
    $kode_unit,
    $jenis,
    $form_fields['nama'],
    $form_fields['rekening'],
    $form_fields['os'],
    $form_fields['jabatan_atasan'],
    $form_fields['potensi'],
    $form_fields['pemutus'],
    $form_fields['giro'],
    $form_fields['edc'],
    $form_fields['klaster']
];

$fp = fopen($file, 'a');
if ($fp === false) {
    die("Failed to open file for appending: $file");
}
fputcsv($fp, $new_entry);
fclose($fp);

header("Location: dashboard.php?success=1");
exit;
?>
