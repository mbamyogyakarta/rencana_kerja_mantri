<?php
function is_logged_in() {
    return isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;
}

function login($pn, $password, $db) {
    $stmt = $db->prepare("SELECT * FROM mantri WHERE pn = :pn");
    $stmt->bindValue(':pn', $pn, SQLITE3_TEXT);
    $result = $stmt->execute()->fetchArray(SQLITE3_ASSOC);

    if ($result && $result['password'] == $password) {
        $_SESSION['logged_in'] = true;
        $_SESSION['nama'] = $result['nama'];
        $_SESSION['unit_kerja'] = $result['unit_kerja'];
        return true;
    }
    return false;
}
?>
