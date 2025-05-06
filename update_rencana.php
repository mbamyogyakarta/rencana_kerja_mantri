<?php
session_start();

if (!isset($_SESSION['pn'])) {
    header("Location: login.php");
    exit;
}

$pn = $_SESSION['pn'];
$role = $_SESSION['role'];
$unit_kerja = $_SESSION['unit_kerja'];

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: dashboard.php");
    exit;
}

$id = $_POST['id'];
$file = 'data_rencana.csv';
$temp_file = 'data_rencana_temp.csv';

if (!file_exists($file)) {
    header("Location: dashboard.php");
    exit;
}

$rows = array_map('str_getcsv', file($file));
$header = array_map('trim', $rows[0]);
$found = false;

$fp = fopen($temp_file, 'w');
fputcsv($fp, $header);

for ($i = 1; $i < count($rows); $i++) {
    $entry = array_combine($header, $rows[$i]);

    if ($entry['id'] === $id) {
        $isOwner = ($entry['pn'] === $pn);
        $sameUnit = ($entry['unit_kerja'] === $unit_kerja);

        if ($role === 'marketing' && !$isOwner) {
            fclose($fp);
            unlink($temp_file);
            header("Location: dashboard.php");
            exit;
        }

        if ($role === 'pimpinan' && !$sameUnit) {
            fclose($fp);
            unlink($temp_file);
            header("Location: dashboard.php");
            exit;
        }

        // Update data
        foreach ($header as $col) {
            if (isset($_POST[$col])) {
                $entry[$col] = $_POST[$col];
            }
        }

        $found = true;
        fputcsv($fp, array_map(function ($key) use ($entry) {
            return $entry[$key] ?? '';
        }, $header));
    } else {
        fputcsv($fp, $rows[$i]);
    }
}

fclose($fp);

if ($found) {
    rename($temp_file, $file);
}

header("Location: dashboard.php");
exit;
