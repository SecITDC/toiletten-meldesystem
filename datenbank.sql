-- Die Datenbank ist eine einzige Datei (SQLite). Kein extra Datenbank-Server nötig.

CREATE TABLE IF NOT EXISTS meldungen (
    id           INTEGER PRIMARY KEY AUTOINCREMENT,
    gebaeude     TEXT NOT NULL,
    toilette     TEXT NOT NULL,
    problem      TEXT NOT NULL,
    beschreibung TEXT NOT NULL DEFAULT '',
    status       TEXT NOT NULL DEFAULT 'Offen',
    zeit         TEXT NOT NULL DEFAULT (strftime('%Y-%m-%dT%H:%M:%SZ', 'now'))
);

-- Schutz gegen Spam: merkt sich kurz, wie oft ein Gerät gemeldet hat.
-- Gespeichert wird nur ein Hash (eine Art Fingerabdruck) der IP, nicht die IP selbst,
-- und nach einer Stunde wird der Eintrag gelöscht. So bleiben Meldungen anonym.
CREATE TABLE IF NOT EXISTS sperre (
    ip_hash TEXT NOT NULL,
    zeit    INTEGER NOT NULL
);
