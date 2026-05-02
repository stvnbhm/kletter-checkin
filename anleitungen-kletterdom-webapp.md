# Kletterdom Check-in System – Anleitungen

## 1. Anleitung für Hallendienst

Diese Anleitung erklärt die wichtigsten Funktionen der Webapp für den laufenden Hallenbetrieb. Der Hallendienst arbeitet im Bereich `Hallendienst`, kann Personen suchen, Check-ins durchführen, QR-Codes scannen, Kulanz vergeben, Einverständniserklärungen bestätigen, Mitglieder importieren und alle offenen Check-ins gesammelt auschecken.[file:1][file:10]

### Anmeldung und Start

1. Melde dich mit deinem Benutzerkonto an.[file:1]
2. Nach dem Login landest du automatisch im passenden Bereich; Hallendienst-Nutzer werden zur Staff-Ansicht weitergeleitet.[file:1]
3. In der Staff-Ansicht siehst du oben Kennzahlen wie heute eingecheckt, Gäste heute, Mitglieder heute und Registrierungen gesamt.[file:10]

### Aufbau der Staff-Ansicht

Die Liste zeigt registrierte Personen mit Name, Typ, Mitgliedsnummer, Zutrittsstatus, Hinweisen zu Minderjährigen und der passenden Check-in-Aktion.[file:10]

Die Zutrittsfarben bedeuten:

- Grün: Mitglied ok, direkter Check-in möglich.[file:1]
- Blau: Schnuppergast ok, direkter Check-in möglich.[file:1]
- Orange: Warnung, zuerst prüfen; oft ist Hallendienst-Aktion nötig.[file:1][file:10]
- Rot: Kein Zutritt, kein Check-in möglich.[file:1]

### Person suchen

1. Nutze das Suchfeld für Name oder Mitgliedsnummer.[file:10]
2. Klicke auf `Suchen`.[file:10]
3. Mit `Zurücksetzen` wird wieder die volle Liste angezeigt.[file:10]

### Check-in manuell durchführen

1. Suche die Person in der Liste.[file:10]
2. Prüfe den Zutrittsstatus und eventuelle Hinweise.[file:1][file:10]
3. Klicke auf `Check-in`, wenn die Person freigegeben ist.[file:10]
4. Bereits eingecheckte Personen werden entsprechend markiert und können nicht nochmals eingecheckt werden.[file:1][file:10]

Wichtig für die Praxis:

- Rot blockiert den Check-in immer.[file:1][file:10]
- Gäste mit erstem bereits verbrauchtem Schnupperbesuch brauchen vor dem nächsten Check-in eine Kulanzfreigabe durch den Hallendienst.[file:10]
- Nicht verifizierte Mitglieder ohne Treffer im Mitgliedersystem haben nur eine begrenzte Anzahl an Besuchen; danach setzt das System den Status auf rot.[file:1][file:10]

### QR-Code scannen

1. Öffne in der Staff-Ansicht den Button `QR-Code scannen`.[file:10]
2. Erlaube dem Browser den Kamerazugriff.[file:10]
3. Wähle bei Bedarf die richtige Kamera aus und klicke `Starten`.[file:10]
4. Halte den QR-Code der Person vor die Kamera.[file:10]
5. Die Webapp prüft den Code und zeigt direkt Erfolg oder Fehler an.[file:10]

Wichtig: Über QR können nur Personen mit grünem oder blauem Status direkt eingecheckt werden. Orange oder rot werden geblockt und müssen in der Staff-Ansicht geprüft werden.[file:1][file:10]

### Kulanz vergeben

Kulanz ist für Fälle gedacht, in denen ein weiterer Besuch ausnahmsweise erlaubt werden soll, zum Beispiel bei Schnuppergästen nach dem ersten Besuch.[file:10]

1. Suche die Person.[file:10]
2. Wenn statt `Check-in` ein Kulanz-Hinweis erscheint, trage einen Grund ein.[file:10]
3. Klicke auf `Kulanz gewähren`.[file:10]
4. Danach kann der Check-in in der Staff-Ansicht durchgeführt werden.[file:1][file:10]

Die Kulanz setzt den Status auf orange mit Begründung und gilt bis Tagesende.[file:10]

### Minderjährige prüfen

Die App kennzeichnet Minderjährige automatisch.[file:1][file:10]

- Unter 14 Jahren: Klettern nur unter Aufsicht; die Aufsicht muss bestätigt sein.[file:1][file:10]
- 14 bis 17 Jahre: Falls ohne Aufsicht geklettert wird, braucht es eine Einverständniserklärung der Eltern.[file:1][file:10]
- Wenn das Formular abgegeben wurde, klicke bei der Person auf `Formular abgegeben`, damit die Erklärung als geprüft markiert wird.[file:10]

### Mitglieder per CSV importieren

Auch der Hallendienst kann einen Mitgliederimport starten.[file:1]

1. CSV-Datei im Importbereich auswählen.[file:1][file:10]
2. Import starten.[file:1][file:10]
3. Nach dem Import erscheint eine Erfolg- oder Fehlermeldung.[file:10]

Der Import aktualisiert Mitgliedsdaten und kann Registrierungen mit dem aktuellen Mitgliederstatus synchronisieren.[file:1]

### Alle auschecken

1. Nutze die Funktion zum Sammel-Checkout, wenn der Hallenbetrieb endet.[file:1]
2. Dadurch werden alle offenen Check-ins geschlossen.[file:1]
3. Bei Gästen oder begrenzten Sonderfällen kann das System danach automatisch den Zutrittsstatus anpassen, wenn ein Limit erreicht wurde.[file:1]

### Typische Hinweise

- `Bereits eingecheckt`: Die Person ist noch aktiv in der Halle.[file:1][file:10]
- `Kein Zutritt`: Kein Check-in möglich.[file:1]
- `Kulanz erforderlich`: Erst Grund eintragen und Kulanz gewähren.[file:10]
- `Formular abgegeben`: Elternerklärung wurde vom Hallendienst bestätigt.[file:10]

---

## 2. Anleitung für Admin

Diese Anleitung erklärt die Bedienung des Admin-Bereichs. Admins haben Zugriff auf Dashboard, Statistiken, Mitgliederimport, Check-in-Export sowie Löschfunktionen für Registrierungen und inaktive Mitglieder.[file:1]

### Anmeldung und Zugriff

1. Melde dich mit einem Admin-Konto an.[file:1]
2. Admin-Nutzer werden nach dem Login in den Admin-Bereich weitergeleitet.[file:1]
3. Nur Benutzer mit Admin-Recht dürfen diesen Bereich öffnen; andere werden zurück in den Hallendienst geleitet.[file:1]

### Aufbau des Admin-Dashboards

Das Dashboard zeigt zentrale Kennzahlen für den Betrieb:[file:1]

- Heute eingecheckt.[file:1]
- Registrierungen gesamt.[file:1]
- Aktive Mitglieder.[file:1]
- Inaktive Mitglieder.[file:1]

Zusätzlich gibt es ein Diagramm zur Hallenauslastung der letzten 30 Tage auf Basis der täglichen Check-ins.[file:1]

### Mitglieder per CSV importieren

Der CSV-Import ist eine der wichtigsten Admin-Funktionen. Er aktualisiert oder legt Mitglieder an und synchronisiert ihren Status mit bestehenden Registrierungen.[file:1]

#### So funktioniert der Import

1. Öffne den Importbereich im Admin-Dashboard.[file:1]
2. Wähle die Mitglieder-CSV aus.[file:1]
3. Starte den Import.[file:1]
4. Prüfe die Rückmeldung nach Abschluss.[file:1]

#### Was der Import macht

- Mitgliederdaten werden aktualisiert oder neu angelegt.[file:1]
- Aktive, bezahlte Mitglieder können wieder auf grün gesetzt werden.[file:1]
- Mitglieder mit offenem Beitrag können auf orange gesetzt werden.[file:1]
- Inaktive Mitglieder werden auf rot gesetzt.[file:1]

#### Fehlende Mitglieder bestätigen

Wenn bei einem neuen Import bisher bekannte Mitglieder in der CSV fehlen, stoppt die App den Vorgang zunächst und verlangt eine explizite Bestätigung der Anzahl fehlender Mitglieder.[file:1]

Vorgehen:

1. Lies die Warnung im Importbereich.[file:1]
2. Trage die geforderte Anzahl in das Bestätigungsfeld ein.[file:1]
3. Starte den Import erneut.[file:1]

So wird verhindert, dass Mitglieder versehentlich als inaktiv markiert werden.[file:1]

### Check-ins exportieren

1. Öffne im Admin-Bereich die Exportfunktion.[file:1]
2. Starte den Export der Check-in-Daten.[file:1]
3. Die Datei kann anschließend für Auswertung oder Archivierung verwendet werden.[file:1]

Der Export enthält Check-in-Daten inklusive wesentlicher Registrierungsinformationen.[file:1]

### Registrierungen löschen

Admins können einzelne Registrierungen vollständig entfernen.[file:1]

1. Suche im Admin-Bereich die gewünschte Registrierung.[file:1]
2. Starte die Löschaktion.[file:1]
3. Die zugehörigen Check-ins werden mit gelöscht.[file:1]

Diese Funktion sollte nur genutzt werden, wenn ein Datensatz wirklich entfernt werden muss.[file:1]

### Inaktive Mitglieder löschen

Admins können zusätzlich alle als inaktiv markierten Mitglieder bereinigen.[file:1]

1. Starte die Funktion `Inaktive Mitglieder löschen`.[file:1]
2. Die App entfernt die betroffenen Mitgliederdatensätze.[file:1]
3. Zugehörige Registrierungen und Check-ins dieser inaktiven Mitglieder werden ebenfalls gelöscht.[file:1]

Diese Funktion ist nur für echte Bereinigungen gedacht und sollte mit Vorsicht verwendet werden.[file:1]

### Wichtige Admin-Hinweise

- Der Admin-Bereich ist nur für Benutzer mit Admin-Recht freigegeben.[file:1]
- Ein fehlerhafter CSV-Import wird mit klaren Meldungen gestoppt, zum Beispiel wenn die Spalte `Mitgliedsnummer` fehlt.[file:1]
- Der Import schützt vor versehentlichen Massenänderungen durch die Bestätigung fehlender Mitglieder.[file:1]

### Empfohlener Admin-Ablauf

1. Regelmäßig Mitglieder-CSV importieren.[file:1]
2. Dashboard-Kennzahlen und Hallenauslastung prüfen.[file:1]
3. Bei Bedarf Check-ins exportieren.[file:1]
4. Nur nach Prüfung Registrierungen oder inaktive Mitglieder löschen.[file:1]
