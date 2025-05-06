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
    echo "File tidak ditemukan.";
    exit;
}

$rows = array_map('str_getcsv', file($file));
$header = array_shift($rows);
$new_rows = [];
$deleted = false;

foreach ($rows as $row) {
    if (count($row) !== count($header)) continue;
    $entry = array_combine($header, $row);
    if ($entry['ID'] === $id) {
        if (
            ($role === 'marketing' && $entry['PN'] !== $pn_login) ||
            ($role === 'pimpinan' && ($entry['Kode Unit'] ?? '') !== $kode_unit)
        ) {
            echo "Anda tidak memiliki akses untuk menghapus data ini.";
            exit;
        }
        $deleted = true;
        continue; // skip entry yang dihapus
    }
    $new_rows[] = $row;
}

if ($deleted) {
    $fp = fopen($file, 'w');
    fputcsv($fp, $header);
    foreach ($new_rows as $row) {
        fputcsv($fp, $row);
    }
    fclose($fp);
    header("Location: dashboard.php");
    exit;
} else {
    echo "Data tidak ditemukan.";
}
