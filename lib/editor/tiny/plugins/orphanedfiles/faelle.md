### Initiales Laden des Editors mit verw. Dateien
  
  - Der Nutzer lädt den Editor mit einer verwaisten Datei
  - Der Event "init" wird aufgerufen
  - force=True wird benötigt, da sich im Editor nichts geändert hat.

### Initiales Laden des Editors ohne verw. Dateien
  
  - Der Nutzer lädt den Editor mit Dateien, die nicht verwaist sind.
  - Der Event "init" wird aufgerufen
  - force=True wird benötigt, da sich im Editor nichts geändert hat.

### Im Editor wird ein Bild hinzugefügt
  - Der Nutzer lädt im Editor wählt im Editor
  - Es wird ein Fokus ausgeführt undnoch ein Change-Event ausgeführt

### Im Editor wird ein Bild gelöscht
- Der Nutzer löscht ein Bild im Editor mit ENTF oder mit Maus.
- Es wird zuerst ein Focus und dann ein Change-Event ausgeführt


### Änderung beim tippen
  - Der Nutzer löscht beim Tippen einen Link aus dem Editor
  - Beim Tippen von Enter wird zunächst ein input und dann ein change ausgeführt.

### Änderung durch Medienmanager
  - Der Nutzer fügt ein Bild im Dateimanager hinzu.
  - Zuerst wird ein Fokus-Event ausgeführt, im Editor ist allerdings noch der alte Code
  - Anschließend wird ein Change-Event ausgeführt.

### Änderung durch Medienmanager
  - Der Nutzer löscht ein Bild im Dateimanager.
  - Dann wird nur ein input-Event ausgeführt.
