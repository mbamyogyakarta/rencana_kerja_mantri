<?php
session_start();
if (!isset($_SESSION['pn'])) {
    header("Location: login.php");
    exit;
}

$filename = 'data_rencana.csv';
$tempfile = 'data_rencana_temp.csv';

$id = $_POST['id'] ?? '';
if (empty($id)) {
    echo "ID tidak ditemukan.";
    exit;
}

// Ambil data dari form
$pn = $_SESSION['pn'];
$nama = $_SESSION['nama'];
$tanggal = date("Y-m-d H:i:s");
$jenis = $_POST['Jenis Rencana'] ?? '';

$data_baru = [
    'ID' => $id,
    'PN' => $pn,
    'Nama' => $nama,
    'Tanggal' => $tanggal,
    'Jenis Rencana' => $jenis,
    'Nama Debitur' => $_POST['Nama Debitur'] ?? '',
    'No Rekening' => $_POST['No Rekening'] ?? '',
    'OS' => $_POST['OS'] ?? ''
];

// Baca dan simpan semua data kecuali yang diedit
$rows = [];
$header = [];
if (($handle = fopen($filename, 'r')) !== FALSE) {
    $header = fgetcsv($handle);
    while (($row = fgetcsv($handle)) !== FALSE) {
        if (trim($row[0]) !== trim($id)) {
            $rows[] = $row;
        }
    }
    fclose($handle);
}

// Tulis ulang ke file
if (($handle = fopen($filename, 'w')) !== FALSE) {
    if (empty($header)) {
        $header = array_keys($data_baru);
    }
    fputcsv($handle, $header);
    foreach ($rows as $row) {
        fputcsv($handle, $row);
    }

    // Tulis data baru dengan urutan sesuai header
    $ordered = [];
    foreach ($header as $key) {
        $ordered[] = $data_baru[$key] ?? '';
    }
    fputcsv($handle, $ordered);
    fclose($handle);
}

header("Location: dashboard.php?success=1");
exit;
