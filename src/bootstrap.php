<?php
// Wird von jeder API-Datei als Erstes geladen.
// Liegt AUSSERHALB von public/, damit niemand diese Datei im Browser aufrufen kann.

declare(strict_types=1);

$config = require __DIR__ . '/../config/config.php';

// Fehler nie an Besucher zeigen (sie verraten Infos über den Server), nur ins Log schreiben.
ini_set('display_errors', '0');
error_reporting(E_ALL);

header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');

// Erlaubte Werte. Alles andere lehnt der Server ab, auch wenn jemand die Seite manipuliert.
const GEBAEUDE = ['A', 'B', 'C', 'D', 'L', 'Metallwerkstatt', 'Holzwerkstatt', 'Forum',
                  'Bauhalle 1', 'Bauhalle 2', 'Sporthalle 1', 'Sporthalle 2'];
const TOILETTEN = ['Männer Toilette', 'Frauen Toilette', 'Behinderten Toilette'];
const PROBLEME = ['Tür kaputt', 'Toilette defekt', 'Toilette beschädigt', 'Licht kaputt',
                  'Fenster kaputt', 'Heizung defekt', 'Sonstiges'];
const STATUS = ['Offen', 'Repariert'];
const MAX_BESCHREIBUNG = 500;

function db(): PDO
{
    static $pdo = null;
    if ($pdo) {
        return $pdo;
    }
    global $config;
    if (!is_dir(dirname($config['datenbank']))) {
        mkdir(dirname($config['datenbank']), 0750, true);
    }
    $pdo = new PDO('sqlite:' . $config['datenbank']);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    $pdo->exec('PRAGMA journal_mode = WAL');

    // Tabellen beim ersten Start automatisch anlegen.
    $pdo->exec(file_get_contents(__DIR__ . '/../datenbank.sql'));
    return $pdo;
}

function antwort(int $code, array $daten): never
{
    http_response_code($code);
    echo json_encode($daten, JSON_UNESCAPED_UNICODE);
    exit;
}

// Liest den JSON-Inhalt einer POST-Anfrage.
// Verlangt Content-Type application/json: ein fremde Webseite kann so etwas nicht
// heimlich im Namen des Hausmeisters schicken (Schutz gegen CSRF).
function json_eingabe(): array
{
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        antwort(405, ['fehler' => 'Nur POST erlaubt']);
    }
    if (!str_starts_with($_SERVER['CONTENT_TYPE'] ?? '', 'application/json')) {
        antwort(415, ['fehler' => 'Falsches Format']);
    }
    $daten = json_decode(file_get_contents('php://input') ?: '', true);
    if (!is_array($daten)) {
        antwort(400, ['fehler' => 'Ungültige Daten']);
    }
    return $daten;
}

function session_starten(): void
{
    global $config;
    session_name('hausmeister');
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'secure' => $config['nur_https'],
        'httponly' => true,      // JavaScript kommt nicht an das Cookie
        'samesite' => 'Strict',  // Cookie wird nicht von fremden Seiten mitgeschickt
    ]);
    session_start();
}

function hausmeister_pruefen(): void
{
    session_starten();
    if (empty($_SESSION['eingeloggt'])) {
        antwort(401, ['fehler' => 'Nicht angemeldet']);
    }
}
