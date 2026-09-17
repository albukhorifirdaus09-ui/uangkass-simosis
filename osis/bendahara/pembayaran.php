<?php

require_once '../../config/auth.php';
require_once '../../config/database.php';

requireOsisTreasurer();

$year = max(
    2020,
    min(2100, (int) ($_GET['tahun'] ?? date('Y')))
);

$frekuensi = $_GET['frekuensi'] ?? 'mingguan';

if (!in_array($frekuensi, ['mingguan', 'bulanan'], true)) {
    $frekuensi = 'mingguan';
}

/*
|--------------------------------------------------------------------------
| Halaman pembayaran lama tidak digunakan lagi.
| Semua pembayaran sekarang dikelola langsung dari tabel uang_kas.php.
|--------------------------------------------------------------------------
*/

header(
    'Location: uang_kas.php?tahun=' .
    $year .
    '&frekuensi=' .
    urlencode($frekuensi)
);

exit;
?>