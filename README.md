# Toiletten-Meldesystem (BBS Meppen)

Schüler scannen einen QR-Code, melden einen Schaden an einer Toilette, und der Hausmeister sieht alle Meldungen auf seiner eigenen Seite.

```
Schüler-Handy ──Meldung──▶  Ubuntu-Server (nginx + PHP + SQLite)  ◀──abrufen── Hausmeister-Seite
```

## Aufbau

| Ordner / Datei | Inhalt |
|---|---|
| `public/index.html` | Schüler-Seite (Formular) |
| `public/hausmeister.html` | Hausmeister-Seite mit Login |
| `public/api/` | PHP-Dateien, die Meldungen speichern, abrufen und ändern |
| `src/bootstrap.php` | Gemeinsamer Code: Datenbank, Sicherheit, erlaubte Werte |
| `datenbank.sql` | Aufbau der Datenbank (SQLite) |
| `config/config.example.php` | Vorlage für die Einstellungen (`config.php` kommt nicht auf GitHub) |
| `tools/passwort_setzen.php` | Hausmeister-Passwort setzen |
| `deploy/install.sh` | Installiert alles auf einem Ubuntu-Server |

## Installation auf Ubuntu

```bash
sudo apt update && sudo apt install -y git
git clone https://github.com/SecITDC/toiletten-meldesystem.git
cd toiletten-meldesystem
sudo bash deploy/install.sh
```

Update nach Änderungen: `git pull` und dann wieder `sudo bash deploy/install.sh`.

## QR-Code

Der Link kann Gebäude und Toilette vorauswählen, z. B.
`http://<server>/?gebaeude=B&toilette=Frauen%20Toilette`

## Sicherheit

- Prepared Statements gegen SQL-Injection
- Ausgabe mit `textContent` gegen XSS
- Hausmeister-Login mit Passwort-Hash, HttpOnly- und SameSite-Cookie
- Server prüft alle Eingaben gegen feste Listen
- Spam-Schutz (5 Meldungen pro Gerät in 10 Minuten), ohne IP-Adressen zu speichern
- Login-Bremse in nginx (5 Versuche pro Minute)
- Config, Datenbank und Code liegen außerhalb des öffentlichen Ordners
