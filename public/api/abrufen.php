<?php
// Liefert alle Meldungen an die Hausmeister-Seite. Nur mit Login.

require __DIR__ . '/../../src/bootstrap.php';

hausmeister_pruefen();

$meldungen = db()->query(
    "SELECT * FROM meldungen ORDER BY status = 'Repariert', zeit DESC"
)->fetchAll();

antwort(200, ['meldungen' => $meldungen]);
