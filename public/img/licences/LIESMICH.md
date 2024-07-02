Das `licences` Verzeichnis kann Lizenz-relevante Ressourcen wie z.B. Lizenzlogos enthalten.



## Anzeigen von Lizenzlogos in PDF-Deckblättern

Bei der Verwendung von PDF-Deckblättern kann zu einer mit einem Dokument verknüpften Lizenz ein
entsprechendes Lizenzlogo im PDF-Deckblatt angezeigt werden.

Alle verwendeten Lizenzlogo-Dateien müssen derzeit lokal im System verfügbar sein. Standardmäßig
sucht die Anwendung im Verzeichnis `public/img/licences` nach Lizenzlogos. Über die
Konfigurationsoption `licences.logos.path` kann aber auch ein anderes Verzeichnis angegeben werden:

    licences.logos.path = APPLICATION_PATH "/public/img/licences"

Innerhalb des angegebenen Verzeichnisses erwartet die Anwendung die Lizenzlogo-Dateien in einer
Verzeichnisstruktur, welche der Pfadstruktur der URL entspricht, die für die Lizenz in der
entsprechenden Datenbanktabelle angegeben ist. Angenommen die Spalte `link_logo` in der
OPUS-Datenbanktabelle `document_licences` enthält folgende Logo-URL:

    https://licensebuttons.net/l/by-sa/4.0/88x31.png

Dann erwartet OPUS 4 eine lokale Kopie dieses Lizenzlogos unter folgendem Verzeichnispfad:

    public/img/licences/l/by-sa/4.0/88x31.png
