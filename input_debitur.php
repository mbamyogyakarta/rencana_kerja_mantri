<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $file = 'data_debitur.csv';
    $data = [
        $_POST['no'] ?? '',
        $_POST['kode_bo'] ?? '',
        $_POST['branch_office'] ?? '',
        $_POST['kode_unit'] ?? '',
        $_POST['bri_unit'] ?? '',
        $_POST['pn'] ?? '',
        $_POST['nama_mantri'] ?? '',
        $_POST['nama_deb'] ?? '',
        $_POST['sumber_deb'] ?? '',
        $_POST['cif'] ?? '',
        $_POST['produk'] ?? '',
        $_POST['plafond'] ?? '',
        $_POST['pemutus'] ?? ''
    ];
    
    $fp = fopen($file, 'a');
    fputcsv($fp, $data);
    fclose($fp);
    $msg = "Data berhasil disimpan.";
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Input Data Debitur</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-5">
    <h4 class="mb-4">Form Input Data Debitur</h4>

    <?php if (!empty($msg)): ?>
        <div class="alert alert-success"><?= $msg ?></div>
    <?php endif; ?>

    <form method="POST" action="">
        <div class="row g-3">
            <?php
            $fields = [
                'no' => 'NO',
                'kode_bo' => 'KODE BO',
                'branch_office' => 'BRANCH OFFICE',
                'kode_unit' => 'KODE UNIT',
                'bri_unit' => 'BRI UNIT',
                'pn' => 'PN',
                'nama_mantri' => 'NAMA MANTRI',
                'nama_deb' => 'NAMA DEB',
                'sumber_deb' => 'SUMBER DEB',
                'cif' => 'CIF',
                'produk' => 'PRODUK',
                'plafond' => 'PLAFOND',
                'pemutus' => 'PEMUTUS'
            ];

            foreach ($fields as $name => $label) {
                echo '<div class="col-md-6">
                        <label class="form-label">' . $label . '</label>
                        <input type="text" name="' . $name . '" class="form-control" required>
                      </div>';
            }
            ?>
        </div>
        <div class="mt-4">
            <button type="submit" class="btn btn-primary">Simpan</button>
            <a href="dashboard.php" class="btn btn-secondary">Kembali</a>
        </div>
    </form>
</div>
</body>
</html>
