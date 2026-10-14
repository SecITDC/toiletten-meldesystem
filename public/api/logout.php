<?php
require __DIR__ . '/../../src/bootstrap.php';

json_eingabe();
session_starten();
$_SESSION = [];
session_destroy();
antwort(200, ['ok' => true]);
