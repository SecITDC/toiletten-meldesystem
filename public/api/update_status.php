<?php
// Hausmeister setzt eine Meldung auf "Repariert" (oder zurück auf "Offen"). Nur mit Login.

require __DIR__ . '/../../src/bootstrap.php';

hausmeister_pruefen();
$daten = json_eingabe();

$id = filter_var($daten['id'] ?? null, FILTER_VALIDATE_INT);
$status = $daten['status'] ?? '';
if ($id === false || !in_array($status, STATUS, true)) {
    antwort(400, ['fehler' => 'Ungültige Daten']);
}

$stmt = db()->prepare('UPDATE meldungen SET status = ? WHERE id = ?');
$stmt->execute([$status, $id]);
if ($stmt->rowCount() === 0) {
    antwort(404, ['fehler' => 'Meldung nicht gefunden']);
}

antwort(200, ['ok' => true]);
