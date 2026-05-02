# Kletterdom Check-in System

Webapp für den Check-in-Betrieb im Kletterdom mit Rollen für Hallendienst und Admin. Das System deckt Registrierung, Zutrittsprüfung, Check-in per Button oder QR-Code, Kulanzfälle, Minderjährigen-Logik sowie Mitgliederverwaltung per CSV-Import ab.[file:1]

## Funktionen

- Öffentliche Registrierung für Mitglieder und Gäste mit QR-Code-Verifizierung.[file:1]
- Hallendienst-Ansicht mit Suche, manuellem Check-in, QR-Scanner, Kulanz und Sammel-Checkout.[file:1]
- Automatische Regeln für Schnuppergäste, unbestätigte Mitglieder und Minderjährige.[file:1]
- Admin-Dashboard mit Kennzahlen, Auslastung, CSV-Import, Export und Datenbereinigung.[file:1]

## Rollen

### Hallendienst

Der Hallendienst prüft Personen vor Ort, führt Check-ins durch, scannt QR-Codes, vergibt bei Bedarf Kulanz und bestätigt Einverständniserklärungen für Jugendliche.[file:1]

### Admin

Admins verwalten Mitgliederdaten per CSV, exportieren Check-ins, sehen Kennzahlen im Dashboard und können Registrierungen oder inaktive Mitglieder bereinigen.[file:1]

## Technik

Das Projekt basiert auf Laravel und verwendet MySQL, Nginx, PHP-FPM sowie Docker für das Deployment.[file:1][file:8]

## Einsatz

Gedacht für den einfachen und nachvollziehbaren Hallenbetrieb mit klaren Freigaben, Statusfarben und getrennten Bereichen für operative Nutzung und Verwaltung.[file:1]
