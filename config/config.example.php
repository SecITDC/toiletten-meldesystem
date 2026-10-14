<?php
// Vorlage. Kopiere diese Datei zu config.php und trage die echten Werte ein.
// config.php kommt NICHT auf GitHub (steht in .gitignore), weil dort Geheimnisse drinstehen.

return [
    // Wo die Datenbank-Datei liegt (außerhalb von public/).
    'datenbank' => __DIR__ . '/../data/meldungen.sqlite',

    // Passwort-Hash für die Hausmeister-Seite. Erzeugen mit:
    //   php tools/passwort_setzen.php
    'hausmeister_passwort_hash' => '',

    // Ein langer Zufallswert für den Spam-Schutz. Wird von tools/passwort_setzen.php erzeugt.
    'geheimnis' => '',

    // true, sobald die Seite über https:// läuft.
    'nur_https' => false,

    // Spam-Schutz: höchstens so viele Meldungen pro Gerät in 10 Minuten.
    'max_meldungen_pro_10_min' => 5,
];
