<?php
// Setzt das Hausmeister-Passwort. Auf dem Server ausführen mit:
//   php tools/passwort_setzen.php
// Legt config/config.php an, falls es noch nicht existiert.

if (PHP_SAPI !== 'cli') {
    exit("Nur in der Kommandozeile ausführen.\n");
}

$datei = __DIR__ . '/../config/config.php';
if (!file_exists($datei)) {
    copy(__DIR__ . '/../config/config.example.php', $datei);
}
$config = require $datei;

echo 'Neues Hausmeister-Passwort (mind. 10 Zeichen): ';
$verstecken = PHP_OS_FAMILY !== 'Windows' && stream_isatty(STDIN);
if ($verstecken) {
    shell_exec('stty -echo'); // Passwort beim Tippen nicht anzeigen
}
$passwort = trim((string)fgets(STDIN));
if ($verstecken) {
    shell_exec('stty echo');
    echo "\n";
}
$passwort = preg_replace('/^\x{FEFF}/u', '', $passwort); // unsichtbares BOM-Zeichen entfernen
if (mb_strlen($passwort) < 10) {
    fwrite(STDERR, "Zu kurz, nichts geändert.\n");
    exit(1); // Fehlercode, damit install.sh abbricht statt ohne Passwort weiterzumachen
}

$config['hausmeister_passwort_hash'] = password_hash($passwort, PASSWORD_DEFAULT);
if (empty($config['geheimnis'])) {
    $config['geheimnis'] = bin2hex(random_bytes(32));
}

$inhalt = file_get_contents($datei);
foreach (['hausmeister_passwort_hash', 'geheimnis'] as $schluessel) {
    $inhalt = preg_replace_callback(
        "/('$schluessel'\s*=>\s*)'[^']*'/",
        fn($m) => $m[1] . var_export($config[$schluessel], true),
        $inhalt
    );
}
file_put_contents($datei, $inhalt);
echo "Gespeichert.\n";
