<?php
// Login für den Hausmeister.

require __DIR__ . '/../../src/bootstrap.php';

$daten = json_eingabe();
session_starten();

// Das Passwort selbst steht nirgends, nur sein Hash. password_verify prüft, ob es passt.
if (!password_verify((string)($daten['passwort'] ?? ''), $config['hausmeister_passwort_hash'])) {
    sleep(1); // bremst Angreifer, die viele Passwörter durchprobieren
    antwort(401, ['fehler' => 'Falsches Passwort']);
}

session_regenerate_id(true); // neue Sitzungs-ID nach dem Login (Schutz gegen Session-Fixation)
$_SESSION['eingeloggt'] = true;
antwort(200, ['ok' => true]);
