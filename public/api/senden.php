<?php
// Schüler schicken hierher ihre Meldung. Kein Login nötig.

require __DIR__ . '/../../src/bootstrap.php';

$daten = json_eingabe();

$gebaeude = $daten['gebaeude'] ?? '';
$toilette = $daten['toilette'] ?? '';
$problem = $daten['problem'] ?? '';
$beschreibung = trim((string)($daten['beschreibung'] ?? ''));

// Nur Werte aus den Listen sind erlaubt.
if (!in_array($gebaeude, GEBAEUDE, true) || !in_array($toilette, TOILETTEN, true)
    || !in_array($problem, PROBLEME, true)) {
    antwort(400, ['fehler' => 'Ungültige Auswahl']);
}
if (mb_strlen($beschreibung) > MAX_BESCHREIBUNG) {
    antwort(400, ['fehler' => 'Beschreibung ist zu lang (max. ' . MAX_BESCHREIBUNG . ' Zeichen)']);
}

// Spam-Schutz: zu viele Meldungen vom selben Gerät in kurzer Zeit?
$jetzt = time();
$ip_hash = hash('sha256', $config['geheimnis'] . ($_SERVER['REMOTE_ADDR'] ?? ''));
db()->prepare('DELETE FROM sperre WHERE zeit < ?')->execute([$jetzt - 3600]);
$zaehler = db()->prepare('SELECT COUNT(*) FROM sperre WHERE ip_hash = ? AND zeit > ?');
$zaehler->execute([$ip_hash, $jetzt - 600]);
if ((int)$zaehler->fetchColumn() >= $config['max_meldungen_pro_10_min']) {
    antwort(429, ['fehler' => 'Zu viele Meldungen. Bitte versuche es in ein paar Minuten erneut.']);
}
db()->prepare('INSERT INTO sperre (ip_hash, zeit) VALUES (?, ?)')->execute([$ip_hash, $jetzt]);

// Die "?" sind Platzhalter (Prepared Statement): so kann niemand über die Eingabe
// eigene SQL-Befehle einschleusen (Schutz gegen SQL-Injection). Das hattest du schon richtig!
db()->prepare('INSERT INTO meldungen (gebaeude, toilette, problem, beschreibung) VALUES (?, ?, ?, ?)')
    ->execute([$gebaeude, $toilette, $problem, $beschreibung]);

antwort(201, ['ok' => true]);
