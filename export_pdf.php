<?php
require_once __DIR__ . '/vendor/autoload.php';
require 'db.php';

session_start();
if (!isset($_SESSION['logged_in'])) {
    header('Location: index.php');
    exit();
}

$nama = $_SESSION['nama'];
$unit = $_SESSION['unit_kerja'];

$result = $db->query("SELECT * FROM kegiatan WHERE nama = '$nama' ORDER BY tanggal DESC LIMIT 1");
$data = $result->fetchArray(SQLITE3_ASSOC);

if (!$data) {
    die("Data tidak ditemukan.");
}

$html = '
<h2 style="text-align:center;">📝 Rencana Kegiatan Mantri</h2>
<p><strong>Nama:</strong> ' . htmlspecialchars($data['nama']) . '</p>
<p><strong>Unit Kerja:</strong> ' . htmlspecialchars($unit) . '</p>
<p><strong>Tanggal:</strong> ' . $data['tanggal'] . '</p>
<p><strong>Alamat:</strong> ' . htmlspecialchars($data['alamat']) . '</p>
<pre style="background-color:#f3f3f3;padding:10px;border:1px solid #ccc;">' . htmlspecialchars($data['catatan']) . '</pre>
';

$mpdf = new \Mpdf\Mpdf();
$mpdf->WriteHTML($html);
$mpdf->Output("rencana_kegiatan_$nama.pdf", 'D'); // force download
