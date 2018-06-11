# OPUS 4 Change Log

---

## Release 4.6.2 2018-06-11

### Feature Request

* [OPUSVIER-2942] - DOI-Vergabe implementieren

### Bugs

* [OPUSVIER-3893] - config.ini-Parameter <searchengine.solr.facetlimit.year_inverted> funktioniert nicht mehr richtig

### Aufgaben

* [OPUSVIER-3884] - Funktion, um Enrichment-Wert direkt abzufragen
* [OPUSVIER-3894] - OPUS 4.6.2 Release vorbereiten
* [OPUSVIER-1361] - URN sollen in der Administration zu Dokumenten hinzugefügt werden können
* [OPUSVIER-3830] - DataCite Client Library implementieren

### Dokumentation

* [OPUSVIER-3889] - Ergänzung der Dokumentation zum Thema Datenschutz

---

## Release 4.6.1 2018-02-26

### Bugs

* [OPUSVIER-3841] - Editieren von Personen ohne Dokumente produziert Fehler
* [OPUSVIER-3846] - Fehlerhaftes XML bei der OAI-Schnittstelle

## Aufgaben

* [OPUSVIER-2034] - Datenbank-Felder, die als NOT NULL definiert sind, haben z.T. NULL als Defaultwert
* [OPUSVIER-2385] - Validierung von ISBNs vornehmen
* [OPUSVIER-2386] - Validierung von ISSNs vornehmen
* [OPUSVIER-2494] - getAll-Abfragen im Zusammenhang mit dem Aufruf von setDefault-Methoden verhindern
* [OPUSVIER-3479] - Einschränkung der Coverage für Controller Testklassen
* [OPUSVIER-3480] - Zusammenfassungen, Titel, usw. mit Sprachattribut in Frontdoor HTML markieren
* [OPUSVIER-3483] - DINI Zertifikat: OAI-Anfrage Identify soll Beschreibung des Dienstes (Element description) in englischer Sprache liefern
* [OPUSVIER-3540] - MathJax über Composer installieren
* [OPUSVIER-3580] - Schriftenreihen im Dokumentenformular der Administration alphabetisch sortieren
* [OPUSVIER-3801] - Verbose Ausgabe beim Neuaufbau der Testdaten optional
* [OPUSVIER-3839] - Unveränderte Felder bei der Bestätigung des Bulk-Editing nur einmal mittig anzeigen
* [OPUSVIER-3842] - Gleiche Breite für alle DropDown-Felder in der erweiterten Suche
* [OPUSVIER-3844] - Package für OPUS 4 API und Core anlegen
* [OPUSVIER-3848] - Übersetzungen für ISBN/ISSN Validatoren
* [OPUSVIER-3849] - Handhabung von Export Parameter wie Content-Disposition erweitern
* [OPUSVIER-3856] - Sprache für Google Scholar Link soll OPUS-UI entsprechen
* [OPUSVIER-3858] - CSS @import von print.css entfernen und Meta-Tag verwenden

## Dokumentation

* [OPUSVIER-3013] - Dokumentieren wie XHTML DTD mit Hilfe von xmlcatalog lokal gecached werden kann

---

## Release 4.6 2017-08-14

### Bugs

* [OPUSVIER-1745] - im Browser darstellbare Dateitypen werden stets mit Content-Disposition=attachment ausgeliefert
* [OPUSVIER-2110] - Klasse Opus_View in library/View/View.php verhindert kein XSS
* [OPUSVIER-2112] - Frontdoor versucht Felder wie Abstract, Titel, etc. zu übersetzen
* [OPUSVIER-2167] - Open Search: Chrome erkennt nicht den OSDD
* [OPUSVIER-2195] - Collection-Zuweisungsformular in der Administration zeigt nicht bereits die mit dem Dokument verknüpften Collections an und erlaubt das dopplete Zuweisen
* [OPUSVIER-2246] - Reload des Publish-Formulars im zweiten Formularschritt wirft eine Exception
* [OPUSVIER-2522] - Opus_Document::delete() löscht eigenmächtig Dokument aus dem Index -- sollte eigentlich vom Plugin Opus_Document_Plugin_Index ausgeführt werden, sofern es aktiviert ist
* [OPUSVIER-2546] - Platzhalter-Mechanismus in Übersetzungsressourcen greift nicht auf Eingabe da
* [OPUSVIER-2562] - Suchanfragen mit zwei oder mehr escapten Zeichen werden nicht akzeptiert
* [OPUSVIER-2943] - Plugin-Methode preDelete wird für nicht gespeicherte Objekte ausgeführt
* [OPUSVIER-3115] - Klärung, welche DDC-Klassifikation in der Distribution ausgeliefert werden soll
* [OPUSVIER-3188] - Fehlermeldungen im Formular: Neuer IP-Bereich sind englisch
* [OPUSVIER-3401] - Frontdoor zeigt keinen Titel oder Abstrakt, wenn Dokument keine Sprache hat
* [OPUSVIER-3424] - Bug in Setup_Model_Abstract::getContent
* [OPUSVIER-3629] - Tippfehler im BibTeX-Icon
* [OPUSVIER-3667] - Namespace für Dokumenttyp Schema verwendet falsche Domain
* [OPUSVIER-3670] - Build Probleme mit OPUS 4.6 auf dem CI-System fixen
* [OPUSVIER-3671] - Funktion getMatchingSubjects nicht kompatible zu SQL-Mode ONLY_FULL_GROUP_BY
* [OPUSVIER-3675] - Option mergeFactor in Solr Config deprecated
* [OPUSVIER-3676] - Solr Option unlockOnStartup produziert Warnung
* [OPUSVIER-3681] - Optionale Patent-Felder sind als NOT NULL in Datenbank definiert
* [OPUSVIER-3682] - Testdokument 300 wird nicht angelegt
* [OPUSVIER-3685] - Unit Tests für Fehler bei Volltextindizierung unzuverlässig
* [OPUSVIER-3686] - Volltextcache wird nicht genutzt
* [OPUSVIER-3687] - Hash-Values für Dateien werden immer wieder berechnet
* [OPUSVIER-3689] - Speichern eines Dokuments löst drei Indizierungen aus
* [OPUSVIER-3696] - SolrIndexBuilder-Skript mit Angabe einer oder mehrerer OPUS-IDs löscht alle anderen Dokumente aus dem Solr-Index
* [OPUSVIER-3697] - SolrIndexBuilder interpretiert Start (und End) ID falsch wenn Option -c verwendet wird
* [OPUSVIER-3702] - Volltextcache schreibt noch nicht extrahierte Dateien als Fehler ins Log
* [OPUSVIER-3710] - Lange Dateinamen werden im Dateimanager nicht umgebrochen
* [OPUSVIER-3713] - Datumsangabe aus dem Publishformular wird bei englischer Oberfläche falsch abgespeichert
* [OPUSVIER-3716] - Klick auf Suchergebnis öffnet falsches Dokument in Frontdoor
* [OPUSVIER-3725] - Nach Aufruf von opus4/crawlers kann nicht mehr im OPUS navigiert werden
* [OPUSVIER-3753] - Verwendung des Optionen-Formulars bricht Export Links
* [OPUSVIER-3754] - Exception beim Speichern im FileManager ohne SortOrder Wert
* [OPUSVIER-3755] - Breadcrumb für Dokumente ohne Breadcrumb sieht seltsam aus
* [OPUSVIER-3756] - DisplayBrowsing-Änderung für Sammlungen ändert ServerDateModified
* [OPUSVIER-3757] - DisplayFrontdoor-Änderung führt zu ServerDateModified Aktualisierung
* [OPUSVIER-3758] - Plugin InvalidateDocumentCache liest Filter-Konfiguration immer wieder ein
* [OPUSVIER-3761] - Sichtbarkeits-Einstellungen für Sammlungen ändern ServerDateModified
* [OPUSVIER-3762] - Feld Visible von Schriftenreihen führt zu ServerDateModified Änderung
* [OPUSVIER-3763] - OAI Error Code
* [OPUSVIER-3765] - Feld Visible von Opus_Collection sollte ServerDateModified nicht ändern
* [OPUSVIER-3802] - Frage zur Installation der 4.0 Lizenzen wird beim Update nicht angezeigt
* [OPUSVIER-3823] - Nutzerrolle "guest" mit Zugriff auf Modul "export" zeigt bei allen anderen Rollen Administrationsrechte an
* [OPUSVIER-3824] - Überprüfen des Warnhinweises beim Speichern einer Datei im Adminbereich
* [OPUSVIER-3826] - Update-Script ändert "server_date_modified" für alle Dokumente, auch wenn sich diese nicht geändert haben
* [OPUSVIER-3829] - Übersetzungen mit HTML-Tags müssen in CDATA eingefasst werden
* [OPUSVIER-3831] - Namen mit führenden Leerzeichen werden nicht zusammengefasst
* [OPUSVIER-3836] - Anwendungsfehler beim Anlegen einer neuen Sammlung
* [OPUSVIER-3837] - Personen Bulk-Formular nur öffnen wenn Person gefunden wird

### Feature Requests

* [OPUSVIER-2415] - Browsing-URLs sind nicht Google-Scholar-Freundlich
* [OPUSVIER-2768] - Export von Ergebnislisten in Bibtex
* [OPUSVIER-2933] - Anzeige von eingeklappten Abstracts auf der Frontdoor: Wörter nicht abschneiden und "..." direkt hinter dem Text
* [OPUSVIER-3071] - Wunsch nach kurzen URLs für die Frontdoor
* [OPUSVIER-3246] - Zurückspringen an die Stelle in einer Sammlung, an der ein Eintrag hinzugefügt wurde.
* [OPUSVIER-3496] - Suchtrefferanzeige soll in Abhängigkeit der englischen Oberfläche bevorzugt englische Titel und Abstracts anzeigen (konfigurierbar)
* [OPUSVIER-3504] - Datum der letzten Änderung in der Dokumentenliste im Admin anzeigen
* [OPUSVIER-3505] - Anzeige aller Dokumente unabhängig vom Dokumentstatus im Adminbereich
* [OPUSVIER-3572] - Konfigurationsmöglichkeit für Reply-To und des Return-Path bei E-Mails aus Opus
* [OPUSVIER-3573] - Version 4 der CC-Lizenzen in OPUS aufnehmen
* [OPUSVIER-3622] - Redirect auf vorherige Seite nach Login
* [OPUSVIER-3630] - Ausgabe Lizenzen über OAI
* [OPUSVIER-3654] - Veröffentlichungsformulare ergänzen um SubjectDDC (3-stellig durch Definition "CollectionLeaf")
* [OPUSVIER-3655] - Grafische Sichtbarkeit von Open-Access-Publikationen in Trefferlisten
* [OPUSVIER-3680] - Verlinkung zu ORCID für Autoren in Frontdoor

### Aufgaben

* [OPUSVIER-152] - Batch Processing von zu indexierenden Dokumenten
* [OPUSVIER-157] - Unschöne URLs
* [OPUSVIER-766] - "Allgemeine Suchoptionen" sollen auch nach der einfachen Suche zur Verfügung stehen
* [OPUSVIER-918] - Ingest-Schnittstelle für Dokumente
* [OPUSVIER-1108] - Browsing nach dem Jahr der Veröffentlichung
* [OPUSVIER-1292] - Citation-Export: Aufruf von Opus_Model_Filter ersetzen
* [OPUSVIER-1662] - Matheon-spezifische Felder in solr.xslt und schema.xml entfernen
* [OPUSVIER-1760] - Symbol für Volltexte bei der Suchanzeige anbieten
* [OPUSVIER-1813] - Lizenzen überarbeiten
* [OPUSVIER-1828] - Änderung des Environment für die Skripte
* [OPUSVIER-1870] - Import von Metadaten
* [OPUSVIER-1952] - Request Parameter von abgelehnten Zugriffen ans Login-Formular weiterreichen
* [OPUSVIER-2039] - Unit-Test für Abhängigkeiten von UnixTimestamp schreiben
* [OPUSVIER-2293] - xMetaDiss aus der Auslieferung entfernen
* [OPUSVIER-2588] - Prüfen ob TMX Dateien valide sind (xmllint)
* [OPUSVIER-2743] - Verknüpfung mit Sammlungen (CollectionRole) überarbeiten
* [OPUSVIER-3068] - Definition der Datenbankparameter an einer einzigen Stelle
* [OPUSVIER-3154] - DDC-Notation für Elemente der ersten und zweiten Ebene bei der XMetaDissPlus-Ausgabe
* [OPUSVIER-3324] - Beim Anhängen der Export-Parameter in der SolrSearch entsteht eine url-Mischform
* [OPUSVIER-3411] - Export Plugins ermöglichen
* [OPUSVIER-3412] - Modul Crawlers mit Unit Tests abdecken und dokumentieren
* [OPUSVIER-3418] - OpenAire - Project-Identifier-Ausgabe in OAI-DC klären
* [OPUSVIER-3463] - Erweitertes Suchformular auf Zend_Form umstellen
* [OPUSVIER-3484] - OAI Anfrage Identify muss adminEmail Element mit gültigem Wert enthalten
* [OPUSVIER-3485] - DINI Zertifikat: Maschinenlesbare Angaben zur Rechtesituation in den Metadaten der veröffentlichten Dokumente (Frontdoor und OAI)
* [OPUSVIER-3516] - PHP Version von createdb.sh Skript
* [OPUSVIER-3529] - Webpräsenz von OPUS 4 überprüfen
* [OPUSVIER-3577] - Skript für notwendige Schritte nach Git-Update
* [OPUSVIER-3592] - Funktionen für XSLT aus Frontdoor Controller in Modelklasse verschieben
* [OPUSVIER-3660] - Ausgabe der Zugriffsinformationen auf den Volltext über OAI_DC
* [OPUSVIER-3664] - Export von Ergebnislisten im CSV-Format
* [OPUSVIER-3665] - Export von Ergebnislisten im RIS-Format
* [OPUSVIER-3691] - Leere Elemente wie "abstracts" im Import XML sollten toleriert werden
* [OPUSVIER-3704] - Module Management Seite in der Administration
* [OPUSVIER-3705] - SWORD Authentifizierung mit Account Management integrieren
* [OPUSVIER-3706] - Links für Seitennavigation durch Icons ersetzen
* [OPUSVIER-3708] - Export Links in Frontdoor dynamisch erzeugen
* [OPUSVIER-3709] - Verwendung von custom.css ohne Änderungen an common.phtml
* [OPUSVIER-3711] - OPUS Logo durch CSS bestimmen
* [OPUSVIER-3714] - Home (Logo) Link konfigurierbar
* [OPUSVIER-3717] - Auswahl der Icons für die Dateien in der Frontdoor mit CSS
* [OPUSVIER-3720] - Home (Logo) LinkTitle konfigurierbar
* [OPUSVIER-3722] - Neuer Personenbereich in der Administration mit eingeschränktem Zugriff
* [OPUSVIER-3723] - Datenbankabfrage für Autoren (Personen)
* [OPUSVIER-3724] - Auflistung der Personen in der Administration
* [OPUSVIER-3726] - Filterung der Personen nach Rollen
* [OPUSVIER-3727] - Anzeige der Dokumente für eine Person
* [OPUSVIER-3728] - Filterung der Personen nach dem Namen
* [OPUSVIER-3729] - Editieren von Personen für alle verknüpften Dokumente
* [OPUSVIER-3730] - Anzeige der Rollen einer Person
* [OPUSVIER-3731] - Hervorheben des Filter-Strings in den Namen
* [OPUSVIER-3739] - Anzeige der Uhrzeit der letzten Änderung in der Metadatenübersicht
* [OPUSVIER-3741] - Registrierung von Export-Plugins im Bootstrap von Modulen
* [OPUSVIER-3743] - Flexible Updates der Datenbank für 4.6
* [OPUSVIER-3748] - Export Pulldown für Suchergebnisse nur anzeigen wenn Exports möglich
* [OPUSVIER-3749] - Export von Suchen muss selben Code verwenden wie Suche
* [OPUSVIER-3750] - Änderung der DOI-Resolving-URL
* [OPUSVIER-3751] - Änderung des URN-Resolver-Links
* [OPUSVIER-3752] - Redirect Funktionen in angepasste Redirector-Klasse verschieben
* [OPUSVIER-3766] - Export begrenzen für normale Nutzer
* [OPUSVIER-3767] - Export Formate alphabetisch sortieren
* [OPUSVIER-3768] - Multibranch-Pipeline Build für Nutzerdokumentation
* [OPUSVIER-3769] - Multibranch-Pipeline Build für Entwicklerdokumentation
* [OPUSVIER-3770] - Multibranch-Pipeline Build für OPUS 4 Homepage auf GitHub
* [OPUSVIER-3775] - Tabelle "schema_version" vereinfachen
* [OPUSVIER-3776] - Explizite Verwendung von SQL Schema Datei in Applikation entfernen
* [OPUSVIER-3777] - Administrationsbereich "Einstellungen" hinzufügen
* [OPUSVIER-3778] - Verlinkung zur GND von Autoren in der Frontdoor
* [OPUSVIER-3791] - Feld "name" für kurze Lizenzbezeichnungen hinzufügen
* [OPUSVIER-3792] - Entfernen der führenden Nullen der GND-IDs
* [OPUSVIER-3794] - Kurznamen von Lizenzen in Übersicht anzeigen
* [OPUSVIER-3795] - Option, um Update-Schritte einzeln bestätigen zu lassen (außer Datenbank)
* [OPUSVIER-3796] - Unit Test für die korrekten Versionen der Datenbank im Framework
* [OPUSVIER-3797] - Vorbereitungen für OPUS 4.6 Release
* [OPUSVIER-3798] - Setzen der OPUS Version in Masterdaten in eigene Datei auslagern
* [OPUSVIER-3799] - Gesamte Breite des Browsers in Administration nutzen
* [OPUSVIER-3804] - Link zum "schema" Verzeichnis und "createdb.sh" entfernen
* [OPUSVIER-3806] - Opus_Person um interne ID erweitern
* [OPUSVIER-3811] - Status von Dokumenten in Liste anzeigen
* [OPUSVIER-3814] - Keyboard Shortcut für ID Feld in Dokumenten-Verwaltung
* [OPUSVIER-3815] - Funktion "intdiv" für PHP 5
* [OPUSVIER-3825] - Formularelement "Combobox" um Werte auswählen und eingeben zu können
* [OPUSVIER-3827] - Datenbank Update-Funktion für mehrere Personen
* [OPUSVIER-3828] - Übersicht und Bestätigung der Änderungen beim Bulk-Editing von Personen
* [OPUSVIER-3832] - Führende und nachfolgende Leerzeichen beim Speichern entfernen
* [OPUSVIER-3835] - Verhindern, dass mehrere Personen-Formulare sich beeinflussen
* [OPUSVIER-3838] - Fehlermeldungen für Suche nach Schriftenreihen und Sammlungen verbessern

### Dokumentation

* [OPUSVIER-2181] - Spezifikation der DefaultField-Types in der Dokumenttypdefinition
* [OPUSVIER-2214] - Datentyp opus:validfieldname existiert nicht mehr in documenttype.xsd
* [OPUSVIER-2416] - Verwendung von robots.txt dokumentieren

---

## Release 4.5 2016-12-06

### Bugs

* [OPUSVIER-1341] - Abspeichern von unzulässigen Werten für Feld server_state
* [OPUSVIER-1561] - Performance-Probleme in Solrsearch_Model_CollectionRoles (wird auch von Publish-Modul benutzt!)
* [OPUSVIER-1691] - Anzeige der zugewiesenen CollectionRoles auf der Frontdoor irreführend
* [OPUSVIER-1860] - Block "Metadaten exportieren" in der Frontdoor mit zu geringer Höhe
* [OPUSVIER-2196] - Script "opus-create-export-xml.php" lässt sich nicht aufrufen
* [OPUSVIER-2275] - UnitTest Solrsearch_IndexControllerTest::testLastPageUrlEqualsNextPageUrlDocTypeArticle erfordert genau 20 Dokumente
* [OPUSVIER-2286] - Layoutfehler in der erweiterter Suche: Dropdowns unterschiedlich lang
* [OPUSVIER-2357] - OAI-Set wird nur ausgegeben, wenn es ein Subset hat, an dem mindestens ein Dokument im Serverstate published hängt
* [OPUSVIER-2358] - Irreführende Ausgabe bei leeren OAI-Subsets
* [OPUSVIER-2375] - Fehler und Empfehlungen in der OAI-Schnittstelle
* [OPUSVIER-2412] - Module "crawlers" und Guest-Zugriff darauf in Dokumentation erläutern
* [OPUSVIER-2511] - Host- und Instancename in der Testumgebung sind leer
* [OPUSVIER-2547] - DB-Schema hält Rolle 'owner' vor, die über das Framework nicht abgebildet wird.
* [OPUSVIER-2615] - Nach dem zweimal hintereinander auf Login geklickt wurde, erscheint nach dem erfolgreichen Login das leere Login-Formular
* [OPUSVIER-2660] - Problem mit Encoding auf dem CI-System
* [OPUSVIER-3110] - Verzögerter Selenium Build Break in Jenkins
* [OPUSVIER-3214] - Fataler Fehler, wenn User gelöscht wird, der noch eine Session besitzt
* [OPUSVIER-3232] - Inkonsistentes Verhalten des Admin-Formulars bei Änderung von Sammlungseinträgen
* [OPUSVIER-3347] - Unit Test Home_IndexControllerTest::testStartPageContainsTotalNumOfDocs läuft nicht mehr durch
* [OPUSVIER-3381] - Unit Tests modifizieren Testdokument 146, so daß sich ServerDateModified ändert
* [OPUSVIER-3472] - Sortierung Anzeige von Schriftenreihen auf der Frontdoor
* [OPUSVIER-3477] - opus4ci.zib.de verschickt Mails an ungültige Mail-Adresse
* [OPUSVIER-3508] - OAI-Schnittstelle liefert Volltext aus, bei gesetztem EmbargoDate
* [OPUSVIER-3542] - OPUS-Update-Check funktioniert nicht mehr
* [OPUSVIER-3599] - SolrIndexBuilder indiziert letzte (<16) Dokumente nicht
* [OPUSVIER-3600] - Opus_UserRoleTest::testAccessDocumentsInsertRemove gebrochen
* [OPUSVIER-3601] - opus4current.sql nicht korrekt verlinkt
* [OPUSVIER-3602] - Search Engine Settings in config.ini.template falsch definiert
* [OPUSVIER-3605] - Publish-Modul verwendet boolval (PHP 5.5) Funktion
* [OPUSVIER-3606] - Verknüpfte Metadaten fehlen in Datenbank (RHEL, PHP 5.4, MariaDB)
* [OPUSVIER-3609] - OPUS 4 Handbuch Job gebrochen auf CI-System
* [OPUSVIER-3610] - Fehler beim Aufbau der Datenbank werden nicht angezeigt
* [OPUSVIER-3611] - Optionale SQL Felder für Accounts dürfen nicht NULL sein und haben kein Defaultwert
* [OPUSVIER-3614] - NOT NULL ohne DEFAULT in SQL Schema
* [OPUSVIER-3617] - Fehler weil SQL für Testdokumente "server_date_created" nicht setzt
* [OPUSVIER-3618] - Foreign Key Constraints Fehler beim Löschen von Sammlungen
* [OPUSVIER-3619] - Speichern von VisibleInOAI = "false" für Opus_File schlägt fehl
* [OPUSVIER-3620] - Fehler bei der Fehlerbehandlung in Oai_Model_Error Klasse
* [OPUSVIER-3621] - SQL Fehler beim Abruf der ListSets in OAI
* [OPUSVIER-3637] - Falsche Sortierung von Autoren mit führenden Leerzeichen im Namen
* [OPUSVIER-3640] - Pfade für Dateien in Frontdoor META-Tags fehlerhaft
* [OPUSVIER-3641] - Sortierreihenfolge von Dateien in Frontdoor falsch
* [OPUSVIER-3643] - ORDER BY ist nicht in GROUP BY Fehler in SQL Statements
* [OPUSVIER-3644] - ORDER BY ist nicht in SELECT list
* [OPUSVIER-3645] - Warnung beim Speichern von zu langen Werten in der Datenbank
* [OPUSVIER-3649] - Setzen von Admin Passwort bei der Installation schlägt fehl
* [OPUSVIER-3651] - XMetaDissPlus soll Dokumente im Embargo nicht ausliefern

### Features

* [OPUSVIER-3230] - Lexikographische Sortierung der Facette Autor (Author) nach dem Nachnamen
* [OPUSVIER-3563] - Erweiterung der Frontdoor um die Angabe "Gehört zur Bibliographie " (BelongsToBibliography)
* [OPUSVIER-3575] - Repositorium spezifische Titel von RSS Feeds
* [OPUSVIER-3636] - Autoren in Suchergebnissen als "Nachname, Vorname" anzeigen
* [OPUSVIER-3646] - Navigation in Frontdoor mit Links/Rechts Cursortasten
* [OPUSVIER-3652] - Vorschlagsfunktion für Schlagwörter (GND) in Administration (aus der Datenbank)

### Aufgaben

* [OPUSVIER-231]  - Entfernen von nicht mehr unterstützten Layouts inkl. MenuHelper aus dem System
* [OPUSVIER-533]  - Init-Skript für Selenium-RC Server erstellen
* [OPUSVIER-889]  - Aufnahme des Admin-Accounts für die Weboberfläche in des Install-Skript
* [OPUSVIER-922]  - Validierung von Username/Passwort an zu vielen Stellen
* [OPUSVIER-1427] - Update Bibliotheken wie zum Beispiel Solr-Client, ZEND, usw.
* [OPUSVIER-1473] - chmod im workspace Verzeichnis selektiver ausführen
* [OPUSVIER-1617] - Installations-Script: Apache-Neustart am Ende des Install-Scripts auslagern
* [OPUSVIER-1677] - Catchable Fatal errors entsprechend behandeln
* [OPUSVIER-2313] - Anleitung zum Neustart des Selenium Servers
* [OPUSVIER-2384] - Migration des CI-Systems auf leistungsfähige Hardware
* [OPUSVIER-2578] - Zugriff auf Mailing-Listen Archiv
* [OPUSVIER-2782] - Editieren von Collections im Metadaten-Formular
* [OPUSVIER-2788] - Allgemeine Formularklassen für neues Metadaten-Formular
* [OPUSVIER-3012] - XHTML DTDs lokal cachen um schnelle Validierungen zu ermöglichen
* [OPUSVIER-3015] - Seiten im Modul Home durch XHTML Validierung abdecken
* [OPUSVIER-3038] - Änderungen an Unit Tests sollten keinen Selenium Build triggern
* [OPUSVIER-3070] - Server in Jenkins integrieren
* [OPUSVIER-3073] - Automatische Konfiguration der mail.opus Einstellungen für CI System
* [OPUSVIER-3074] - Framework Fast Target in Jenkins einrichten
* [OPUSVIER-3077] - Selenium Target in Jenkins einrichten
* [OPUSVIER-3078] - Migration Target in Jenkins einrichten
* [OPUSVIER-3079] - Startup Script für SOLR auf dem CI-System
* [OPUSVIER-3080] - Startup Script für OPUS SMTP Dumpserver auf dem CI-System
* [OPUSVIER-3081] - CodeBrowser in Jenkins installieren (Problem mit Dependencies)
* [OPUSVIER-3082] - CI-System verschickt Testmessages nach draußen
* [OPUSVIER-3083] - Running Ant produziert "Unable to locate tools.jar" Meldung
* [OPUSVIER-3084] - Jenkins URL auf dem CI-System von / nach /jenkins verschieben
* [OPUSVIER-3085] - Jenkins Jobs auf Extended Email Plugin umstellen
* [OPUSVIER-3086] - Regeln für parallele Builds definieren und dokumentieren
* [OPUSVIER-3087] - Integrate Jenkins mit JIRA
* [OPUSVIER-3088] - Automatisches Update der Demo-Instanz
* [OPUSVIER-3093] - Setup Jenkins für automatischen Start nach opus4ci.zib.de boot
* [OPUSVIER-3096] - Selenium Tests auf neuem CI-System fixen
* [OPUSVIER-3097] - OPUS 4 Testserver von opus4mig nach opus4web umziehen
* [OPUSVIER-3366] - Umstellung auf Solr-4.8
* [OPUSVIER-3495] - Update Solr Schnittstelle
* [OPUSVIER-3517] - Update Solr auf opus4web zu Version 4.8 oder neuer
* [OPUSVIER-3519] - Version von Schema in Datenbank speichern
* [OPUSVIER-3524] - Update Selenium Instanz von GitHub Sourcen
* [OPUSVIER-3531] - composer.phar für Build auf CI System verwenden
* [OPUSVIER-3550] - Nacharbeiten zum Solr Update
* [OPUSVIER-3552] - CI-System auf GitHub Entwicklung umstellen
* [OPUSVIER-3593] - Alte SQL Dateien vom Framework entfernen
* [OPUSVIER-3594] - Anzeigen mit welchem APPLICATION_ENV OPUS läuft
* [OPUSVIER-3597] - Composer nicht mit "sudo" ausführen
* [OPUSVIER-3604] - Selenium Tests fixen
* [OPUSVIER-3612] - Ablauf des Embargo muss server_date_modified aktualisieren
* [OPUSVIER-3615] - Skript für Datenbankveränderungen
* [OPUSVIER-3616] - Accounts "user" und "referee" aus Masterdaten entfernen
* [OPUSVIER-3638] - Funktion zum Löschen des Dokumentcaches hinzufügen
* [OPUSVIER-3647] - .gitignore in ansonsten leeren Workspace-Verzeichnissen entfernen
* [OPUSVIER-3648] - Konfigurationsdatei console.ini hinzufügen
* [OPUSVIER-3650] - Datenbank Update für 4.5 Schema
* [OPUSVIER-3658] - Workspace Verzeichnisse für Tests bei der Installation anlegen
* [OPUSVIER-3659] - Fehlermeldungen bei der Ausführung von SQL Skripten
* [OPUSVIER-3662] - Composer Installation ohne SUDO ausführen

### Dokumentation

* [OPUSVIER-476]  - Dokumentation der Datei- und Verzeichnisrechte
* [OPUSVIER-828]  - Platzierung von Dokumenten ohne Titel in Suchergebnissen erläutern
* [OPUSVIER-1419] - Dokumentation der Datumsfelder
* [OPUSVIER-1618] - Installations-Dokumentation: Apache-Einrichtung sollte *nach* Install-Script erklärt werden
* [OPUSVIER-2453] - OPUS4-Webseite neu machen
* [OPUSVIER-2559] - URN-Vergabe: vorgesehenen Dokument-Workflow dokumentieren
* [OPUSVIER-2917] - Anleitung wie man Selenium lokal ohne Behinderung laufen lassen kann
* [OPUSVIER-3410] - Kapitel 12.1 zum Import enthält falsche Informationen zum XML Schema
* [OPUSVIER-3461] - jQuery UI Einbindung klären
* [OPUSVIER-3551] - Installations- und Updateanleitung für 4.5

---

## Release 4.5.0-RC1 2016-04-25

### Bugs

* [OPUSVIER-1364] - Sprache einer Seite ist Deutsch, XHTML-Tags behaupten aber Englisch.
* [OPUSVIER-1543] - Anlegen einer leeren Collection wird nicht verhindert
* [OPUSVIER-1585] - Indexing funktioniert mit Version r60 der Client Library nicht mehr
* [OPUSVIER-2304] - isValid-Tracking auf Linked-Models tested NICHT das verlinkte Model
* [OPUSVIER-2311] - Default-Collation sollte beim Aufruf des MySQL-Clients in install.sh und update-db.sh übergeben werden
* [OPUSVIER-2315] - SeriesSearch-Tests laufen auf dem CI-System nicht durch
* [OPUSVIER-2503] - MIME-Type für leere Dateien hat sich geändert (Unit Test Fails)
* [OPUSVIER-2572] - unregisterPlugin sollte keine Opus_Model_Exception werfen, wenn das angegebene Plugin nicht registriert ist
* [OPUSVIER-2731] - Ausgabe von Opus_Date für Zend_Date fehlerhaft
* [OPUSVIER-2973] - Admin-Menu-Eintrag ist aktiviert, obwohl alle Untereinträge deaktiviert sind
* [OPUSVIER-3229] - Translate-Mechanismus provoziert Exception, wenn der zu übersetzende Wert leer ist
* [OPUSVIER-3404] - Falsche Übergabe des Sprachcodes im Element dc:title über XMetaDissPlus
* [OPUSVIER-3415] - Anzeige von Nummer und Name für Collections schlägt im Browsing fehl
* [OPUSVIER-3423] - Schriftenreihen-Administration hat ein LI-Element mit einem versteckten Input-Field
* [OPUSVIER-3430] - GND-ID lässt sich bei den Personen im Adminbereich nicht abspeichen
* [OPUSVIER-3458] - Fehlende deutsche Übersetzung von Language und Type in der Administration (Dokumente, Metadatenformular)
* [OPUSVIER-3459] - Schreibfehler in opus4/modules/publish/language/field_header.tmx
* [OPUSVIER-3462] - Option numberOfDefaultSearchResults wirkt bei den meisten Suchanfragen nicht
* [OPUSVIER-3471] - Fehler mit Umlauten im IE10 und Chrome
* [OPUSVIER-3488] - Frontdoor-Anzeige: Formatierung der Bemerkung (Note) unterscheidet sich von den übgrigen Metadaten
* [OPUSVIER-3501] - Fehlerhafte Ausgabe der OAI-Anfrage für xMetaDissPlus mit einem DNB-Set
* [OPUSVIER-3503] - Minor Bug bei der Datumsanzeige in der Administration
* [OPUSVIER-3514] - Überprüfen, ob im XMetaDissPlus.xslt der Feldinhalt eines Feldes aus meheren Feldern zusammengebaut werden kann
* [OPUSVIER-3536] - Fehlende DocId  im Apache-Logfile bei Zugriffen auf die Frontdoor aus der Trefferliste
* [OPUSVIER-3545] - Sprachauswahl sollte nur unterstützte Sprachen anzeigen
* [OPUSVIER-3546] - Sichtbarkeit von CollectionRole beim Zuweisen zu Dokument nicht korrekt angezeigt
* [OPUSVIER-3547] - Sichtbarkeit von CollectionRole "institutes" wird in der Facette nicht berücksichtigt
* [OPUSVIER-3558] - Fehler im Publishformular, wenn keine Datei hochgeladen wird
* [OPUSVIER-3562] - Fehler in Lizenzbeschreibung korrigieren
* [OPUSVIER-3565] - Fehler beim Speichern des Pubhlishprozess bei Verwendung von PersonOther
* [OPUSVIER-3579] - FAQ-Änderungen im Adminbereich lassen sich nicht abspeichern
* [OPUSVIER-3584] - Bug in der Administration der Zugriffskontrolle - Rolle und Module werden nicht korrekt angezeigt ('%1$s' )
* [OPUSVIER-3596] - Umlaute werden mit Opus_Database nicht korrekt importiert

### Feature Request

* [OPUSVIER-1727] - Den Abschnitt "Einräumung eines einfachen Nutzungsrechts" auf der ersten Seite des Publish-Formulars konfigurierbar machen
* [OPUSVIER-2786] - Blättern zwischen Titeln aus einer Trefferübersicht

### Stories

* [OPUSVIER-1101] - Überarbeitung der offiziellen Opus4-Webseite
* [OPUSVIER-1414] - installationsort der OPUS4-Instanz im Dateisystem
* [OPUSVIER-1496] - Wofür ist der opus4-User da?
* [OPUSVIER-1615] - Install-Script: opus4-user sollte keine Shell bekommen
* [OPUSVIER-1641] - Installations-Script: Apache-Config wird automatisch erstellt
* [OPUSVIER-2406] - DINI-Zertifikat
* [OPUSVIER-2620] - Verwaltung der Abhängigkeiten zu externen Software-Komponenten

### Tasks

* [OPUSVIER-2717] - Bibtex-Eintragstyp "PhDThesis" um um Entstehungsort erweitern
* [OPUSVIER-2770] - Unit/Selenium Test für verstecken des Delete Links für Accounts
* [OPUSVIER-2818] - getrennte Konfigurationsmöglichkeit für Suche und Indexierung schaffen
* [OPUSVIER-2921] - einheitliche Dateiendung für XSLT-Stylesheets verwenden: xsl oder xslt
* [OPUSVIER-2946] - Überarbeitung der Opus_Date-Klasse
* [OPUSVIER-3023] - Code für "Available_Languages" aus Bootstrap entfernen und Handling überarbeiten
* [OPUSVIER-3065] - Composer als Dependency Manager für die Installation der Opus-Entwicklungsumgebung
* [OPUSVIER-3067] - Framework Entwicklung sollte nicht von createdb.sh in Server abhängen
* [OPUSVIER-3122] - Refactoring / Umbenennung der Klassen unter server/library
* [OPUSVIER-3199] - Version von JQuery in Install-Skript aktualisieren
* [OPUSVIER-3268] - Übersetzungen in der Administration nicht mehr BETA
* [OPUSVIER-3295] - Mit Apache 2.4 funktioniert unsere Konfiguration nicht mehr
* [OPUSVIER-3355] - Migrationscode aus dem Hauptprojekt herauslösen
* [OPUSVIER-3356] - Authorization Unit Tests liegen momentan im Modules Verzeichnis
* [OPUSVIER-3409] - Vorbereitung von CHANGES.txt und RELEASE_NOTES.txt für das Web automatisieren
* [OPUSVIER-3421] - $_logger Variable in Controller_Action bereinigen
* [OPUSVIER-3422] - Coding Style Cleanup in Applikation
* [OPUSVIER-3431] - Validierung von ORCID verbessern
* [OPUSVIER-3442] - Erlaubte Zeichen für EnrichmentKeys einschränken
* [OPUSVIER-3460] - Grundlagen zum Editieren der Konfiguration in der Administration
* [OPUSVIER-3466] - Source Code Migration zu GitHUB
* [OPUSVIER-3470] - CC-Lizenz-Logo-URI in der Standardauslieferung auf HTTPS anpassen
* [OPUSVIER-3473] - CSS für Review Modul anpassen
* [OPUSVIER-3478] - Unit Tests für View Helper SeriesNumber
* [OPUSVIER-3487] - Build OPUS 4 Master von GitHUB
* [OPUSVIER-3489] - Korrekturen am  XMetaDissPlus-Format in der Standardauslieferung
* [OPUSVIER-3490] - Releases mit GitHUB gehosteten Sourcen ermöglichen
* [OPUSVIER-3491] - Collections Tabellen durch OPUS 4 Schema SQL erstellen
* [OPUSVIER-3493] - Feld "school" für BibTeX-Templates doctoralthesis und masterthesis
* [OPUSVIER-3497] - rebuilding_database.sh Skript für Tests SVN unabhängig machen
* [OPUSVIER-3500] - URN Resolver an vielen Stellen im Code spezifiziert
* [OPUSVIER-3502] - Refactor citationExport Modul
* [OPUSVIER-3506] - OPUS 4 Framework durch Packagist.org verfügbar machen
* [OPUSVIER-3507] - Merge von Solr Update mit Framework Master zurücknehmen
* [OPUSVIER-3510] - Installations Skript auf Composer umstellen
* [OPUSVIER-3520] - Framework von GitHub im CI System bauen
* [OPUSVIER-3521] - Application von GitHub im CI System bauen
* [OPUSVIER-3522] - OPUS 4 Coding Standard als Composer Paket
* [OPUSVIER-3523] - .gitignore für workspace Verzeichnisse anlegen
* [OPUSVIER-3526] - README und LICENCE Datei opus4/application Repository hinzufügen
* [OPUSVIER-3527] - Installationsskript zu Application hinzufügen
* [OPUSVIER-3534] - Installationsskripte in Application Repository verschieben
* [OPUSVIER-3538] - Base URL für OPUS bei Installation wählbar machen
* [OPUSVIER-3553] - Matheon Dateien aus Application Git Repository entfernen
* [OPUSVIER-3559] - Fix 'year_inverted' facet handling
* [OPUSVIER-3564] - Separate Übersetzung für Mouseover-Text auf erster Seite "Auswahl Dokumenttyp"
* [OPUSVIER-3569] - Inhalt von .htaccess in Apache Konfiguration verschieben
* [OPUSVIER-3570] - Code für Opus_Document_Plugin_SequenceNumber entfernt
* [OPUSVIER-3571] - BibTex Feld "type" mit Dokumenttyp füllen, anstelle von Enrichment "Type"
* [OPUSVIER-3574] - Schriftenreihen im Browsing alphabetisch sortieren
* [OPUSVIER-3576] - Schriftenreihen im Publish-Formular auf alphabetisch sortieren
* [OPUSVIER-3583] - Installationsskript für OPUS 4 mit Git überarbeiten
* [OPUSVIER-3586] - Solr Configuration als Composer Paket
* [OPUSVIER-3587] - HTML Tags im Seitenkopf (HEAD) auf separaten Zeilen
* [OPUSVIER-3588] - Packaging Skripte für Tarball entfernen
* [OPUSVIER-3589] - Netbeans Projektdateien entfernen
* [OPUSVIER-3590] - Verzeichnis scripts/install entfernen
* [OPUSVIER-3595] - Release 4.5-RC1

### Aufgabe

* [OPUSVIER-392] - Code Duplication zwischen Opus_Model_Xml_Version1 und Opus_Model_Xml_Version2
* [OPUSVIER-820] - Anzeige der Anzahl der Dokumente
* [OPUSVIER-856] - Update auf aktuellste Revision des PHP Solr-Clients
* [OPUSVIER-1085] - Passwörter im Install-Skript zweimal abfragen, um Tipfehler zu entdecken
* [OPUSVIER-1190] - Dokumenttypen "all", "demo", "preprintmatheon" und "talkzib" aus Standardauslieferung entfernen.
* [OPUSVIER-1482] - OAI-Schnittstelle gibt nicht gültige Werte für Attribut lang zurück
* [OPUSVIER-1672] - Welche Rechte/welcher Owner für workspace
* [OPUSVIER-1693] - Zend_Registry-Lookup nach Zend_Log in Controllern, die von Controller_Action abgeleitet sind, entfernen
* [OPUSVIER-1887] - Unit Tests für Übersetzungsresourcen in Modulen ermöglichen
* [OPUSVIER-2004] - Anlegen eines neuen Git-Repositories scripts
* [OPUSVIER-2382] - zwei Icons für Administration und FAQ in der Navigationsleiste hinzufügen
* [OPUSVIER-2581] - MARCXML-Import-XSL-Stylesheet in den Tarball aufnehmen
* [OPUSVIER-3069] - Parameter für rebuilding_database.sh aus tests/config.ini auslesen
* [OPUSVIER-3186] - Übersichtsseite Enrichments CSS anpassen
* [OPUSVIER-3467] - Organisation für OPUS 4 in GitHUB anlegen
* [OPUSVIER-3468] - GitHUB Repository für Framework anlegen
* [OPUSVIER-3469] - Framework Sourcen unabhängig von Server machen
* [OPUSVIER-3474] - DINI Zertifikat 2013: Anpassung Frontdoor mit Metatag für Persistent Identifier mit URL eines entsprechenden Resolver-Dienstes
* [OPUSVIER-3492] - Framework Testing using Composer

### Dokumentation

* [OPUSVIER-3261] - Konzept Dokumentation online
* [OPUSVIER-3434] - OPUS 4 Handbuch in Online-Version übertragen
* [OPUSVIER-3435] - Kapitel 6 - Manuelle Installation - übertragen
* [OPUSVIER-3436] - Kapitel 5 - Installation ohne Paketverwaltung - übertragen
* [OPUSVIER-3437] - Kapitel 3 - Voraussetzungen für die Installation - übertragen
* [OPUSVIER-3438] - Kapitel 7 - Parameter der config.ini - übertragen
* [OPUSVIER-3439] - Kapitel 12 - Import - übertragen
* [OPUSVIER-3440] - Anhang 13.1 - Dokumententypen - übertragen
* [OPUSVIER-3443] - Kapitel 2 - Begriffe und Funktionen in OPUS - übertragen
* [OPUSVIER-3445] - Kapitel 8 - Erweiterte Konfiguration - übertragen
* [OPUSVIER-3446] - Kapitel 9 - Administration - übertragen
* [OPUSVIER-3447] - Kapitel 10 - Migration OPUS3.x nach OPUS4 - übertragen
* [OPUSVIER-3448] - Kapitel 11 - Update - übertragen
* [OPUSVIER-3449] - Kapitel 13.2 - Bedeutung der Felder - übertragen
* [OPUSVIER-3450] - Kapitel 13.3 - Feldtypen für die Templates - übertragen
* [OPUSVIER-3451] - Kapitel 13.4-13.6 - Dokumententyp Mappings - übertragen
* [OPUSVIER-3453] - Kapitel 13.8 - Optionale PHP-Module - übertragen
* [OPUSVIER-3454] - Kapitel 13.9 - DINI-Zertifikat - übertragen

---

## Release 4.4.5 2014-10-30

### Bugs

* [OPUSVIER-2340] - Admin-Veröffentlichungsstatistik enthält in Select-Box leeren Eintrag, wenn Dokumente ohne ServerDatePublished existieren
* [OPUSVIER-3396] - Übersetzungsversuche von HTML Code tauchen im Log auf
* [OPUSVIER-3402] - DocInfo Balken in Administration enthält keinen Titel, wenn Dokumensprache NULL ist
* [OPUSVIER-3405] - Endlosschleife in Script fix-collections-sortorder.php beim Update
* [OPUSVIER-3407] - Tabellenüberschriften für Sammlungen in Administration übersetzen

### Aufgaben

* [OPUSVIER-1499] - Updatelog enthält DELETED Einträge für Verzeichnisse, die nicht gelöscht wurden
* [OPUSVIER-3000] - Metadaten-Formulare (DocumentXXX) umbenennen und in Order "Document" verschieben
* [OPUSVIER-3301] - Ausgeblendete Sammlungseinträge (Collections) ab der zweiten Ebene sind in der  Dokumentenadministration (Metadatenformular) farblich nicht markiert
* [OPUSVIER-3406] - Sortierfunktionen für Opus_Db_NestedSet hinzufügen
* [OPUSVIER-3408] - Sortierfunktionen für Sammlungen in Administration verfügbar machen

---

## Release 4.4.4 2014-10-13

### Bugs

* [OPUSVIER-1769] - Veröffentlichungsstatistik wertet auch Dokumente im ServerState != 'published' aus
* [OPUSVIER-1843] - Merkwürdiges Verhalten bei der Eingabe von validen Datumswerten in allen Datumsfelden innerhalb der Metadatenverwaltung
* [OPUSVIER-2113] - Dokument-Formular im Admin-Bereich übersetzt Felder doppelt
* [OPUSVIER-2210] - Publish_Model_Validation benutzt ein Model eines anderen Moduls
* [OPUSVIER-2247] - Der ausgelieferte OPUS4-Tarball ist eine Tar-Bombe
* [OPUSVIER-2332] - Legal Notice Checkbox im zweiten Formularschritt wird aktiviert, wenn zusätzliches Feld angefordert wird
* [OPUSVIER-2390] - Exception bei zweimaligem Validierungsfehler für CRUDAction Formulare
* [OPUSVIER-2414] - Google Scholar: citation_date or citation_publication_date not set
* [OPUSVIER-2451] - Name der RSS-Feeds immer "Latest Documents"
* [OPUSVIER-2515] - potentiell gefährliche Löschoperationen im Dateisystem in rebuilding_database.sh.template
* [OPUSVIER-2551] - RSS-Export erzeugt HTML-Output ohne Header, wenn der Solr-Server nicht erreichbar ist
* [OPUSVIER-2596] - Apache-Rewrite LogLevel unnötig hoch in Standard-Auslieferung
* [OPUSVIER-2624] - ausbleibende Rückfrage beim Löschen von Lizenzen und Sprachen
* [OPUSVIER-2673] - Test Opus_Model_Xml_Version1Test::testSerializingInvalidUTF8Chars erfordert phpunit 3.6
* [OPUSVIER-2726] - Beim Einfügen neuer Collections ist die Sortierung nicht richtig
* [OPUSVIER-2742] - Export "Neueste Dokumente" liefert alle Dokumente, wenn Modul solrsearch verwendet und rows-Parameter gesetzt wird
* [OPUSVIER-2815] - Nutzer mit Rechten zur Dokumentenverwaltung kann von nicht publizierten Dokumenten die Frontdoor nicht ansehen
* [OPUSVIER-3028] - Fehler beim Update von Version <= 4.2.2 auf >= 4.4.0 in Zusammenhang mit dem Verschieben der PHTML-Dokumenttyp-Templates
* [OPUSVIER-3100] - Breadcrumbs Tests funktioniert nicht, wenn mit anderen Tests ausgeführt
* [OPUSVIER-3167] - Fehlermeldung beim Update auf 4.4.1 zu altem import-Folder "opus4/import"
* [OPUSVIER-3168] - Bug beim Installieren von OPUS 4.4.1
* [OPUSVIER-3173] - Fehlerhafte Ausgabe im Migrationsscript: Opus3InstituteImport: No Faculty with Opus3-Id '6
* [OPUSVIER-3176] - Meldungen beim Löschen von Schriftenreihen
* [OPUSVIER-3177] - Hinweis bei Löschen von Sprachen
* [OPUSVIER-3178] - Konkrete Meldung beim Löschen von DNB-Instituten
* [OPUSVIER-3183] - Formular DNB-Institute ist Englisch statt Deutsch
* [OPUSVIER-3204] - MetadataImport zerstört Dokumente, wenn Exception beim Speichern auftritt
* [OPUSVIER-3210] - Notification bei "Publication" - Dokument wurde freigeschaltet - an Autor und Einsteller funktioniert nicht
* [OPUSVIER-3223] - Originaldateien sollen nach Migration nicht in OAI sichtbar sein
* [OPUSVIER-3226] - Erste Seite Veröffentlichen: Dokumente können unbemerkt ohne Dokumenttyp erzeugt werden
* [OPUSVIER-3228] - Cache-Invalidierung bei Update einer Instanz
* [OPUSVIER-3233] - Die Attribute "RoleVisibleFrontdoor" und "visible" funktionieren nur für ausgewählte Collections
* [OPUSVIER-3234] - Link zur Frontdoor fehlt auf der letzten Seite nach erfolgreichem Publishprozess bei eingeschränkten Adminrechten
* [OPUSVIER-3244] - Anzeige Abstract in Ergebnisliste und Frontdoor ist fehlerhaft bei neuen OPUS 4.4.2 Instanzen
* [OPUSVIER-3256] - Übertragung von Seleniumtests auf PHPUnittests
* [OPUSVIER-3265] - Umbau XMetaDissPlus: dc:source soll nicht aus TitleParent sondern TitleSource kommen
* [OPUSVIER-3267] - Skript für die Migration des Enrichments "SourceTitle" nach TitleSource
* [OPUSVIER-3269] - OPUS über HTTPS: Fehler bei Suche nach Strings mit /
* [OPUSVIER-3273] - Veröffentlichungsstatistik liefert falsche Zahlen
* [OPUSVIER-3277] - Abfangen der Exception im Admin-File-Upload
* [OPUSVIER-3281] - Fehlerhafte Zugriffskontrolle auf Dateien in Oai_Model_Container
* [OPUSVIER-3289] - Bibtex-Export-Stylesheets bauen year-Angabe fehlerhaft zusammen weshalb die year-Angabe in bestimmten Fällen in der Ausgabe fehlt
* [OPUSVIER-3291] - Übersetzungen für Enrichments in der Administration greifen nicht (immer?)
* [OPUSVIER-3293] - Fehlender Übersetzungsschlüssel mail_toauthor_subject, wenn über die Frontdoor eine Email an den Autor geschickt wird
* [OPUSVIER-3300] - Darstellung einer Latex-Formel im Abstract & Title im Chrome-Browser
* [OPUSVIER-3306] - Rolle ungleich Administrator hat kein Änderungsrecht bei Schriftenreihen trotz Zugriff auf Modul "admin" und auf den Bereich "Schriftenreihen verwalten"
* [OPUSVIER-3311] - Datei kann heruntergeladen werden, obwohl sie nicht in der Frontdoor sichtbar ist
* [OPUSVIER-3313] - Dateien von Embargoed Dokumenten können heruntergeladen werden
* [OPUSVIER-3322] - Update auf 4.4.3 schreibt Dokumenttyp-Templates (PHTML-Dateien) unter / (root-Verzeichnis)
* [OPUSVIER-3334] - Falsche Reihenfolge der Suchergebnisse im Browsing
* [OPUSVIER-3336] - Sortierungsparameter werden beim Export nicht berücksichtigt
* [OPUSVIER-3337] - cron-php-runner.sh Skript kann nicht zu /dev/stdout schreiben
* [OPUSVIER-3341] - Unterscheidung des akademischen Grades beim Typ "masterthesis" fehlt in der XmetadissPlus-Schnittstelle
* [OPUSVIER-3344] - Ändern der DDC-Auswahl im Publishformular "all" auf CollectionLeaf
* [OPUSVIER-3350] - Zeitabhängigkeit in Unit Test für Opus_File beheben
* [OPUSVIER-3363] - Error Logging beim Indexieren von Volltexten erweitern
* [OPUSVIER-3364] - Schreibfehler in opus4/modules/publish/language/errors.tmx korrigieren
* [OPUSVIER-3379] - @depends Deklaration für Regression2543Test stimmt nicht
* [OPUSVIER-3382] - ServerDateModified von Dokument 146 ändert sich beim Löschen von Collection(Role)s
* [OPUSVIER-3383] - Löschen von CollectionRole löscht unter Umständen falsche Collections
* [OPUSVIER-3390] - Link zur PACS-Klassifikation in den Hilfetexten ändern
* [OPUSVIER-3391] - Host und Instanz-URI für Export XSLT verfügbar machen
* [OPUSVIER-3392] - Dokument-Cache wird unter Umständen nicht aktualisiert
* [OPUSVIER-3397] - Link zur Frontdoor eines Dokuments enthält ID Parameter, der in der Administration verwendet wird
* [OPUSVIER-3398] - Fehler bei der Anzeige eines Dokuments in der Administration, wenn Sprache einer Datei NULL ist
* [OPUSVIER-3403] - Test für Seite mit Versionsinformation schlägt fehl, wenn Server mit aktueller Versionsnummer nicht erreichbar ist

### Feature Request

* [OPUSVIER-2429] - Sprachauswahl über ein Konfigschlüssel abschaltbar machen
* [OPUSVIER-2447] - MathJax für Formeln auf der Frontdoor
* [OPUSVIER-2471] - Sprache der Datei soll auf Frontdoor angezeigt werden
* [OPUSVIER-3043] - Ungültige Institute ausblenden im Publish, nicht jedoch auf Frontdoor und beim Browsen/Suchen
* [OPUSVIER-3061] - Hochladeversuch von nicht erlaubten Dateiformaten sollte sofort einen Fehler bringen, nicht erst nach langer Bearbeitungszeit
* [OPUSVIER-3190] - Datum für das Hinzufügen einer Datei
* [OPUSVIER-3211] - Metadaten-Import sollte bei Update ausgewählte Felder nicht löschen
* [OPUSVIER-3221] - Link auf die Sammlung (Collection) in der Frontdoor
* [OPUSVIER-3222] - Kompletten Sammlungsnamen (Collection-Namen) in der Frontdoor anzeigen
* [OPUSVIER-3251] - Ausgabe eines Enrichments als "note" in Bibtex-Export
* [OPUSVIER-3253] - Statistik: Gesamtbestand zum 31.12. eines Jahres anzeigen

### Stories

* [OPUSVIER-126] - Solr-Anbindung
* [OPUSVIER-128] - Abklärung der lizenzrechtlichen Belange
* [OPUSVIER-167] - Administration UI issues/bugs/tasks
* [OPUSVIER-197] - Configuration handling in application
* [OPUSVIER-508] - Frontdoor Überarbeitung
* [OPUSVIER-524] - UI Testing
* [OPUSVIER-582] - CSS und web design
* [OPUSVIER-772] - Editieren der Metadaten von Dokumenten im Administrationsmodul
* [OPUSVIER-907] - Distribution
* [OPUSVIER-972] - OPUS 3.x-Migration
* [OPUSVIER-1046] - Sichtbarkeit in Google-Scholar
* [OPUSVIER-1110] - OpenSearch Support
* [OPUSVIER-1269] - Jira-Projekt OPUSVIER aufräumen
* [OPUSVIER-1340] - Unit-Tests hinzufügen, die das Editieren der Metadaten eines Dokumentes abdecken
* [OPUSVIER-1386] - OPUS-Update-Check
* [OPUSVIER-1413] - Alten Form-Builder-Code entfernen, wenn komplett umgestiegen
* [OPUSVIER-1416] - Update Skript
* [OPUSVIER-1451] - Sortierbarkeit von Dateien eines Dokument
* [OPUSVIER-1713] - Anzahl der Anzeige der Facetten erhöhen
* [OPUSVIER-1754] - OpenAIRE Compliance herstellen
* [OPUSVIER-1775] - Sprachzuordnung bei SWD-Schlagwörter entfernen, da diese nur in deutscher Sprache vorliegen
* [OPUSVIER-2747] - Überarbeitung der Gestaltung der Administration durch Grafiker
* [OPUSVIER-2772] - Neues Metadaten-Formular für Dokumente

### Tasks

* [OPUSVIER-2708] - Anzahl der Suchtreffer pro Seite sollte instanzabhängig über die Konfiguration überschrieben werden können
* [OPUSVIER-2758] - Soll phpinfo Ausgabe erhalten bleiben?
* [OPUSVIER-2853] - Konzeption und Umsetzung der Fehlerbehandlung für den Fall, dass beim Neuaufbau eines Cache-Eintrags die Reindexierung des Dokuments nicht erfolgreich war
* [OPUSVIER-2926] - View Helper für ServerUrl und BaseUrl einsetzen
* [OPUSVIER-2950] - Basisklasse ControllerTestCase um eine Methode erweitern, die ein Testdokument erzeugt und Referenz darauf zurückgibt
* [OPUSVIER-3021] - In sprachabhängigen Unit Tests die Sprache setzen
* [OPUSVIER-3022] - Deprecated Funktionen setUpEnglish und setUpGerman von ControllerTestCase entfernen
* [OPUSVIER-3048] - Support Klasse fuer Unit Test in *support* Ordner verschieben
* [OPUSVIER-3138] - Entfernen von Collections erst nach Speichern wirksam
* [OPUSVIER-3144] - Authorization Tests als Unit Tests ermöglichen
* [OPUSVIER-3158] - Jahreszahlen auf Maximalwert validieren
* [OPUSVIER-3219] - Abhängigkeit vom Framework zur Applikation durch Funktion setThemesPath in Opus_Collection beheben
* [OPUSVIER-3237] - Anlegen eines neuen Dokumenttyp "PeriodicalPart" für die Standardauslieferung
* [OPUSVIER-3247] - Anlegen von EnrichmentKeys
* [OPUSVIER-3250] - Trennung von Model und Controller in der Veröffentlichungsstatistik
* [OPUSVIER-3258] - Erweiterung von isPartOf um Band oder Heftnummer für Zeitschriften (XMetaDissPlus)
* [OPUSVIER-3262] - Datenbank Update Skript für 4.4.3 erweitern
* [OPUSVIER-3270] - FullText bis Ablauf eines Embargos nicht zugänglich machen
* [OPUSVIER-3274] - Frontdoor Selenium Tests in Unit Tests umwandeln
* [OPUSVIER-3279] - createTestFile nach ControllerTestCase auslagern
* [OPUSVIER-3288] - Lokale XSLT-Datei für Frontdoor ermöglich
* [OPUSVIER-3292] - Anzeige der Publikationsstatistik in der Administration verbessern
* [OPUSVIER-3305] - Anlegen eines Feldes zum Einbinden von HTML-Dateien in der Frontdoor
* [OPUSVIER-3308] - Analoge Funktion zu Opus_Collection::getVisibleChildren() für das Feld visiblePublish
* [OPUSVIER-3309] - Name des Dokumententypes auf Validierungsseite mit anzeigen
* [OPUSVIER-3310] - Datenbank Update Skript für OPUS 4.4.4
* [OPUSVIER-3312] - Sichtbarkeit in Frontdoor steuert Zugriff auf Dateien
* [OPUSVIER-3314] - Ausrichten der Dateiliste in Frontdoor
* [OPUSVIER-3315] - Dezentere Anzeige der Dateisprache in Frontdoor ohne Bild
* [OPUSVIER-3316] - Bei allen Titeln sollten die in der Dokumentsprache zuerst angezeigt werden
* [OPUSVIER-3323] - Beim Metadaten-Import sind Personen mit der Role "other" nicht erlaubt
* [OPUSVIER-3326] - Defaultrolle für neue Dateien konfigurierbar machen
* [OPUSVIER-3327] - Test der Installation und des Updates für Opus-4.4.4
* [OPUSVIER-3339] - Regeln für OAI Zugriff auf Dateien publizierter Dokumente klären
* [OPUSVIER-3342] - Dateien aus HTML-Ordner im Migrationsscript wie Dateien aus Original-Ordner behandeln
* [OPUSVIER-3352] - Umzug der Selenium-Instanz in neue VM
* [OPUSVIER-3353] - OPUS 4 Demo Instanz muss in neuer VM aufgesetzt werden
* [OPUSVIER-3358] - Klasse View_Filter_RemoveWhitespaces entfernen
* [OPUSVIER-3359] - Button/Link für XML-Export für Dokumente
* [OPUSVIER-3360] - Export eines Dokuments verwendet SolrIndex unnötig
* [OPUSVIER-3361] - Klasse View_Helper_Hostname löschen
* [OPUSVIER-3368] - OPUS Entwicklung auf PHPUnit 4.x umstellen
* [OPUSVIER-3369] - title-Attribute für Facet-Extender Links
* [OPUSVIER-3370] - OPUS für PHP 5.5.9 anpassen
* [OPUSVIER-3373] - Write Unit Test de prüft, daß keine Short-Tags ("<? ") in PHTML vorkommen
* [OPUSVIER-3380] - Remove Matheon Selenium Tests von SVN
* [OPUSVIER-3385] - Language Unit Test gebrochen, weil DTD nicht erreichbar
* [OPUSVIER-3387] - Validierungsklassen für Klassifikationen entfernen
* [OPUSVIER-3388] - Zend Framework auf Version 1.12.9 aktualisieren

### Aufgaben

* [OPUSVIER-1334] - falsche NOT NULL-Constraints in server_date_* Feldern
* [OPUSVIER-1342] - Validierung für alle Felder von Opus_Document zu Metadaten Formularen hinzufügen
* [OPUSVIER-1752] - Bei der Anzeige von Titeln und Zusammenfassungen, soll der Wert in der Dokumentsprache zuerst angezeigt werden
* [OPUSVIER-1831] - Suche nach unterschiedlichen Nicht-Autoren-Personen in erweiterter Suche ermöglichen
* [OPUSVIER-1982] - Verwaltung von Schriftenreihen in der Administration
* [OPUSVIER-2061] - Verhalten des Publish-Formulars bei Verwendung unzulässiger EnrichmentKeys testen
* [OPUSVIER-2076] - Opus3ImportLogger entfernen und den Standard-PHP-Logger verwenden
* [OPUSVIER-2090] - Logmeldungen im CI-Target opus4framework-fast: Konfigurationsparameter searchengine.index.host kann nicht gefunden werden
* [OPUSVIER-2194] - Google Scholar Link hinter dem Button auf der Dokument-Frontdoor um weitere Metadatenfelder erweitern
* [OPUSVIER-2219] - Übersicht über den Validierungsstatus für die einzelnen Dokumenttypdefinitionen in der Administration anzeigen
* [OPUSVIER-2346] - OpenAire-Compliance: Set "ec_fundedresources"
* [OPUSVIER-2347] - OpenAire-Compliance: Plugin, um die benötigten spez. Metadaten zu erzeugen
* [OPUSVIER-2411] - Verhindern von Indexieren durch Crawler
* [OPUSVIER-2489] - Export eines Dokuments sollte direkt durch das Anhängen von Parametern an die Frontdoor-URL unterstützt werden
* [OPUSVIER-2558] - Migration zur neuesten Version von PHPUnit (> 3.6)
* [OPUSVIER-2571] - Regression-Test für OPUSVIER-2570 schreiben
* [OPUSVIER-2773] - Formular(e) zum Verwalten der Personen eines Dokuments
* [OPUSVIER-2795] - Zugriffskontrolle für neues Formular anpassen/überprüfen
* [OPUSVIER-2805] - Thesis Grantor und Publisher editieren im Metadaten-Formular
* [OPUSVIER-2871] - Rendering von Formular-Elementen
* [OPUSVIER-2879] - Änderungen am Form-Builder um HTML Output zu vereinheitlichen
* [OPUSVIER-2893] - Implementierung eines Modells für das Export-Modul
* [OPUSVIER-2895] - Prüfung der XML-Wohlgeformtheit im Framework
* [OPUSVIER-2951] - Hinzufügen von ein oder mehreren Personen zu einem Dokument
* [OPUSVIER-2952] - Editieren von Personen, die mit einem Dokument verknüpft sind
* [OPUSVIER-2992] - Formularklasse für Opus_DnbInstitute implementieren
* [OPUSVIER-2993] - Formularklasse für Opus_Language implementieren
* [OPUSVIER-2995] - Formularklasse für Opus_Series implementieren
* [OPUSVIER-2996] - Formularklasse für Opus_CollectionRole implementieren
* [OPUSVIER-2997] - Formularklasse für Opus_Collection implementieren
* [OPUSVIER-3002] - Anzeige im FileManager überarbeiten
* [OPUSVIER-3041] - Unterscheidung zwischen Grantor-Institution und Grantor-Department im Datenmodel
* [OPUSVIER-3180] - Übersichtsseite Schriftenreihe CSS anpassen
* [OPUSVIER-3181] - Übersichtsseite Sprache CSS anpassen
* [OPUSVIER-3182] - Übersichtsseite DNB-Institute CSS anpassen
* [OPUSVIER-3217] - Rewrite Rule von OPUS3 nach OPUS4 anpassen
* [OPUSVIER-3252] - Anzeige eines Enrichments im Bibtex-Export konfigurierbar
* [OPUSVIER-3254] - Jahreszahl in Veröffentlichungsstatistik
* [OPUSVIER-3275] - Test für Embargodate
* [OPUSVIER-3276] - EmbargoDate über Oai ausgeben
* [OPUSVIER-3290] - Latex-Formel im Abstract & Title
* [OPUSVIER-3304] - Neue Person Identifier Felder (z.B. GND) im Publishmodul
* [OPUSVIER-3307] - Validierung der ID Felder für Personen im Dokument-Admin-Bereich
* [OPUSVIER-3317] - Online OpenAire Compliance Tests mit dem Validator
* [OPUSVIER-3319] - Änderung des Typ von Feld "sort_order" in Tabelle "document_files" zu INT
* [OPUSVIER-3320] - bei Auf- und Zuklappen der Facette springt man an den Seitenanfang
* [OPUSVIER-3321] - Dateien im FileManager in der Framework Reihenfolge anzeigen
* [OPUSVIER-3349] - Dateien aus dem Ordner 'original' im Migrationsscript die Zugriffsrolle 'guest' entziehen
* [OPUSVIER-3351] - 'more'-link sollte versteckt werden, wenn Facette ausgewählt ist
* [OPUSVIER-3371] - Short-Tags "<? " von PHTML Dateien entfernen
* [OPUSVIER-3374] - Fix "Array to String Conversion" Fehler in Unit Tests
* [OPUSVIER-3375] - Konfiguration von Datenbank Admin Zugang provoziert "Array to String Conversion" Fehler
* [OPUSVIER-3376] - preg_replace(): /e modifier is deprecated in PHP 5.5.9
* [OPUSVIER-3377] - Framework Tests für Plugin schlagen fehlt
* [OPUSVIER-3378] - Skipped Tests werden in Selenium mit PHPUnit 4.x als Fehler angezeigt
* [OPUSVIER-3384] - Zwei Unit Tests in Opus_Model_Xml_Version1Test gebrochen

### Dokumentation

* [OPUSVIER-949] - "interne" Datumsfelder server_date_*
* [OPUSVIER-2141] - Hinweis zu OpenSearch-Unterstützung in die Dokumentation aufnehmen
* [OPUSVIER-2714] - Möglichkeit zum Editieren des eigenen Accounts dokumentieren
* [OPUSVIER-3242] - Überarbeitung der Dokumentation für die Anpassung des Feldes "ThesisPublisher" in den Dokumenttypen
* [OPUSVIER-3243] - Beschreibung des neuen Dokumenttyps "periodicalpart" in der Dokumentation
* [OPUSVIER-3260] - Dokumentation Release OPUS 4.4.3
* [OPUSVIER-3284] - Anpassung der Dokumentation bzgl. der Konvertierung von Tarbomb zu Tarball
* [OPUSVIER-3325] - Anhängen der Export-Parameter für Suchergebnisse beim searchtype=latest
* [OPUSVIER-3343] - Konfiguration der Defaultrole für neue Dateien dokumentieren
* [OPUSVIER-3346] - Neue Dokumententypen für akademischen Grad dokumentieren
* [OPUSVIER-3389] - Dokumentation der OpenAire-Funktionalität
* [OPUSVIER-3394] - Dokumentation des Export-Buttons auf Frontdoor und Suche
* [OPUSVIER-3400] - Dokumentation des Embargo Date

---

## Release 4.4.3 2014-06-04

### Bugs

* [OPUSVIER-1486] - Datei-Downloads funktionieren nicht mit VirtualHosts (z.B. mit Standard-Apache unter Ubuntu)
* [OPUSVIER-1574] - OAI-Schnittstelle, epicur: Dokumente ohne URN dürfen nicht mit ausgegeben werden
* [OPUSVIER-2025] - Speichern eines "Dependent Models" propagiert nicht an das übergeordnete Model
* [OPUSVIER-2323] - Anpassung von form.first.show_rights_checkbox in config.ini.template
* [OPUSVIER-2709] - Anzeige der Veröffentlichungsstatistik in der Administration setzt Existenz der Collection Role institutes voraus
* [OPUSVIER-3212] - OPUS Update: solr.xslt wird ungefragt überschrieben, Anpassungen gehen verloren
* [OPUSVIER-3215] - Install-Script: Ordner public/js wird nicht erstellt
* [OPUSVIER-3218] - Inkonsistenz im Datenmodell bei Scope und Type von Opus_Language
* [OPUSVIER-3238] - Falsche Platzierung von neu eingefügten Sammlungseinträgen wenn "sort_order"!=0
* [OPUSVIER-3245] - Zugriffrechte auf DNB-Institution nicht vorhanden, obwohl gesetzt
* [OPUSVIER-3248] - XMetaDissPlus: Beim Publikationstyp "Sound" (dc:type) muss der erste Buchstabe groß geschrieben sein.
* [OPUSVIER-3263] - update script für die Sortierung der Collections benötigt
* [OPUSVIER-3264] - Anzeige des Dokumenttyps in der Zusammenfassung des Publish-Formulars
* [OPUSVIER-3278] - initLanguageList() nach Form / Element verschieben
* [OPUSVIER-3282] - Pfad zur confirmation-mail.template im MatheonModel anpassen
* [OPUSVIER-3283] - Aufruf von loginUser in Unit Test wirkt sich auf folgenden Test aus
* [OPUSVIER-3285] - Lokale Schema-Datei bei der Doctype-Validierung verwenden

### Feature Requests

* [OPUSVIER-1772] - Anzeige der Titel des Dokuments nur in der Veröffentlichungssprache
* [OPUSVIER-2472] - Dokumente (d.h. deren Metdaten) in der Ausgabe in der OAI-Schnittstelle verbergen

### Aufgaben

* [OPUSVIER-713] - Erstellung von Publikationslisten
* [OPUSVIER-901] - kaputtes Layout auf Fehlerseite; geeignetere Fehlermeldung
* [OPUSVIER-1017] - Frontdoor verwendet XML-Cache nicht
* [OPUSVIER-1133] - Anpassungen auf Startseite
* [OPUSVIER-1152] - Webdesign
* [OPUSVIER-1156] - Rendering von Latex in Titel und Abstract
* [OPUSVIER-1178] - Anzeige der zugeordneten Dokumente in der Sammlungsadministration
* [OPUSVIER-1575] - OAI-Schnittstelle, epicur: Unit-Test für Dokumente ohne URN
* [OPUSVIER-1667] - asynchronen Cache-Neuaufbau nach dem Löschen eines Cacheeintrags auslösen
* [OPUSVIER-1989] - Innerhalb des Browsing soll standardmäßig nach server_date_published sortiert werden (außer bei latest documents und bei dem Serienbrowsing)
* [OPUSVIER-2241] - EnrichmentKeys bzgl. Review-Modul und Migration
* [OPUSVIER-2272] - Nach dem Ausführen des Installationsskripts sollte das Excecutable-Bit auf createdb.sh sicherheitshalber entfernt werden
* [OPUSVIER-2479] - Versionsinfo als HTML-Kommentar in Seitenheader aufnehmen
* [OPUSVIER-2653] - benutzerspezifische index.xslt ermöglichen
* [OPUSVIER-2705] - CSS für Druckversion bereitstellen
* [OPUSVIER-2877] - Funktion der "Menu" Controller in der Administration in IndexController aufnehmen
* [OPUSVIER-3030] - Standardtexte anpassen auf GND (anstelle von SWD)
* [OPUSVIER-3146] - Selenium AccessModuleSetupAndAdmin und AccountSecurity11 in Unit Test umwandeln
* [OPUSVIER-3197] - XML Catalog Datei für RDFA und XHTML Validierungen anlegen
* [OPUSVIER-3225] - Funktion getDisplayName von Opus_Series sollte 'Title' zurückgeben
* [OPUSVIER-3240] - Eintragen des Feldes "ThesisPublisher" in allen Dokumentvorlagen der Standardauslieferung
* [OPUSVIER-3255] - Gesamtzahl der Dokumente
* [OPUSVIER-3272] - Rechtschreibfehler in error.tmx
* [OPUSVIER-3280] - Erweiterung von documentType.include in tests.ini um Wert 'masterthesis'
* [OPUSVIER-3302] - Felder des Personenformulars im Adminbereich anlegen

---

## Release 4.4.2 2013-11-22

### Bugs

* [OPUSVIER-3114] - Konfiguration der Felder, die beim Speichern eines Models kein Update des ServerDateModified bewirken sollen.
* [OPUSVIER-3129] - Keine einfache und erweiterte Suche unter IE8  und IE 9 möglich
* [OPUSVIER-3148] - Attribute "visible" für Collections wird in der Frontdoor nicht berücksichtigt
* [OPUSVIER-3153] - Update Skript bricht ab, wenn Zend nicht aktualisiert werden muss
* [OPUSVIER-3156] - Rechtschreibfehler im Account Formular
* [OPUSVIER-3159] - Ausgabe der titelverleihenden Institution auf der Frontdoor
* [OPUSVIER-3160] - Update-Skript löscht leere Unterverzeichnisse von .svn
* [OPUSVIER-3161] - Auf- und einklappen von Abstracts mit Chrome als Browser funktioniert nicht richtig
* [OPUSVIER-3162] - XMetaDissplus: Ausgabe von Sprachcodes wird von der DNB bemängelt
* [OPUSVIER-3163] - XMetaDissPluss: Reihenfolge der Elemente ist nicht korrekt
* [OPUSVIER-3168] - Bug beim Installieren von OPUS 4.4.1
* [OPUSVIER-3187] - Menuezeile oben Konto statt Account
* [OPUSVIER-3192] - Links für Dateidownload in der Administration ohne Pfadname für Instanz
* [OPUSVIER-3196] - Frontdoor sollte valides XHTML produzieren
* [OPUSVIER-3202] - Update Script berücksichtigt Benachrichtigungsfunktion nicht richtig - Ordner mail_templates wird nicht angelegt
* [OPUSVIER-3203] - InvalidateDocumentCache::preDelete nur ausführen, wenn Model-ID gesetzt

### Feature Request

* [OPUSVIER-2720] - Feldlänge von edition von varchar(25) auf edition varchar(255) erweitern

### Aufgaben

* [OPUSVIER-2846] - Opus_Document.ServerDateModified aktualisieren, wenn "dependent models", an denen das Dokument hängt, modifiziert oder gelöscht werden
* [OPUSVIER-3136] - Term SWD im Publishformular durch GND ersetzen
* [OPUSVIER-3155] - Längere Eingabefelder in Formularen im Adminbereich (Namen u.a.)
* [OPUSVIER-3157] - zusätzliche Prüfung auf Bash-Versionsnummer in Update-Skript einbauen
* [OPUSVIER-3172] - 'SWD' im Publish Formular  in 'GND' ändern (Übersetzungen)
* [OPUSVIER-3207] -  Beschreibung wie implizierte Felder als Pflichtfeld deklariert werden können

### Dokumentation

* [OPUSVIER-3206] - Hinweis auf Abhängigkeit von XMetaDissPlus auf Spracheinstellungen

---

## Release 4.4.1 2013-10-17

### Bugs

* [OPUSVIER-1542] - Administration-Tab im Menü ist nicht mehr aktiv, sofern eine Admin-Action ausgewählt wird
* [OPUSVIER-1773] - fehlende Übersetzung der CollectionRoles bei Zuweisung über bzw. Anzeige in Dokument-Metadatenverwaltung
* [OPUSVIER-1841] - Fehlermeldung bei der Anzeige des Geburtstages einer Person in der Metadatenverwaltung eines Dokuments
* [OPUSVIER-1890] - Nach dem Hinzufügen eines Patentes wird das Feld YearApplied mit dem Wert '0000' angezeigt
* [OPUSVIER-2163] - Sammlungsübersicht für ein Dokument zeigt CollectionRole-Verknüpfung nicht an, wenn gleichzeitig eine Verknüpfung für eine Collection der CollectionRole besteht
* [OPUSVIER-2178] - Layout: unschöner Umbruch des Links "Einen neuen Sammlungseintrag hier einfügen"
* [OPUSVIER-2318] - Typ (main, parent, ...) eines Titels kann nicht mehr geändert werden
* [OPUSVIER-2575] - Breadcrumb-Leiste verschwindet nach dem Abschicken eines leeren Formulars beim Anelgen einer neuen Serie
* [OPUSVIER-2814] - Nutzer mit Rechten zur Dokumentenverwaltung kann von nicht publizierten Dokumenten die Dateien nicht ansehen
* [OPUSVIER-2838] - Dokumentsprache ändert sich nicht, wenn Sprache des Abstracts und / oder Titels geändert wird
* [OPUSVIER-2971] - Fehlermeldung erscheint grün statt rot
* [OPUSVIER-2972] - POST aus Session bei der Metadatenadministration für ein Dokument nur verwenden, wenn es tatsächlich einen Rücksprung z.B. aus der PersonForm gab
* [OPUSVIER-2975] - Update Skript kommt mit Testdateien mit Spaces im Namen nicht klar
* [OPUSVIER-2982] - Hinzufügen eines Institutes zu einem Dokument modifiziert ServerDateModified bei anderem Dokument
* [OPUSVIER-2986] - OPUS4 Bayreuth: OAI Schnittstelle, XMetaDissPlus testen
* [OPUSVIER-2987] - OPUS 4 Bamberg: Test OAI-Schnittstelle
* [OPUSVIER-2998] - Funktion urlencode in modules/export/views/scripts/publist/default.xslt wird nicht im Controller registriert
* [OPUSVIER-2999] - Dateiausgabe ist auf PDF beschränkt in modules/export/views/scripts/publist/default.xslt
* [OPUSVIER-3010] - Navigation im Formular für "Sammlungseintrag hinzufügen" fehlerhaft
* [OPUSVIER-3036] - Aufruf der Lizenz-Seite auf dem Testserver produziert Fehlermeldung
* [OPUSVIER-3037] - Person mit SortOrder = Anzahl der vorhandenen Personen landet an letzter Stelle beim Hinzufügen
* [OPUSVIER-3044] - Schreibfehler in DDC-Klassifikation 620
* [OPUSVIER-3046] - Metadaten-Formular erlaubt keine alphanumerischen Werte für PageFirst, -Last, und -Number
* [OPUSVIER-3050] - Zend Framework Version 1.12.3 fehlt im Release-Tarball
* [OPUSVIER-3054] - CollectionRole ohne Root-Collection auf Assign-Collection Seite nicht anzeigen
* [OPUSVIER-3055] - Start Page für Collection Assignment verwendet sichtbarkeit von Root-Collection und nicht der Collection Role
* [OPUSVIER-3106] - Fehler beim Hinzufügen von neuen Unterformularen in Metadaten-Verwaltung
* [OPUSVIER-3107] - Beim Entfernen eines Unterformulars wird ODD/EVEN nicht aktualisiert
* [OPUSVIER-3109] - Breadcrumbs für CollectionRoles Formular werden nicht richtig angezeigt
* [OPUSVIER-3118] - Fehlerhafte Anzeige des Status eines Dokuments in der ActionBox für die Frontdoor
* [OPUSVIER-3120] - Speichern von Dokument 122 schlägt fehl
* [OPUSVIER-3126] - Fix aktuell gebrochene Server Unit Tests
* [OPUSVIER-3131] - fehlender Übersetzungsschlüssel solrsearch_title_invalidsearchterm
* [OPUSVIER-3142] - Über XMetaDissPlus nur Dokumente mit Volltexten ausliefern
* [OPUSVIER-3143] - Zugriffsrecht "guest" für eine Datei kann nicht entfernt werden
* [OPUSVIER-3145] - Bug beim Speichern geänderter Felder in Opus_Collection

### Feature Request

* [OPUSVIER-1818] - Anzeige einer Warnmeldung im Filemanager, wenn Hash-Ist und Hash-Soll nicht übereinstimmen

### Aufgaben

* [OPUSVIER-1742] - XML-Cache rauswerfen oder wieder aktivieren
* [OPUSVIER-3007] - OAI-Schnittstelle - Anforderungen der DNB umsetzen
* [OPUSVIER-2663] - Möglichkeit schaffen aufwendigeren Debug Code zu überspringen
* [OPUSVIER-2771] - Links für Dokumentenverwaltung direkt in Frontdoor
* [OPUSVIER-2774] - Bestätigungsseite für Statusänderungen optional machen
* [OPUSVIER-2863] - Ausgabe der Revision Nummer im Seitenfooter: Anpassung der Doku nach Veränderung des Verhaltens
* [OPUSVIER-2884] - Hinweis zur Verwendung von Cookies in FAQ und Hilfeseiten einbauen
* [OPUSVIER-2911] - Funktion zur Validierung von XHTML zu ControllerTestCase hinzufügen
* [OPUSVIER-2988] - View Scripte für "configuration" entfernen
* [OPUSVIER-2989] - Ungenutzte View Scripte für PersonController entfernen
* [OPUSVIER-3001] - Metadaten-Übersicht um Liste von Dateien ergänzen
* [OPUSVIER-3003] - Alle Option für die "Documents" Anzeige in der Administration in Session speichern
* [OPUSVIER-3005] - Textarea Felder so in der Übersicht rendern, daß Zeilenumbrüche erhalten bleiben
* [OPUSVIER-3006] - Vor Ausführung von 'xmllint' Tests prüfen, ob Kommando vorhanden ist
* [OPUSVIER-3009] - Logger Funktion zu Controller_ModuleAccess hinzufügen
* [OPUSVIER-3011] - Wiederverwendbares Confirmation Formular implementieren
* [OPUSVIER-3017] - Gezielte Auswahl der Sprache für Unit Tests ermöglichen
* [OPUSVIER-3018] - Klasse für das Management von konfigurierbaren Nachrichten implementieren
* [OPUSVIER-3019] - Code zum Laden von Übersetzungen für Module in separate Klasse auslagern
* [OPUSVIER-3026] - Formular für die Anzeige der Modelliste (indexAction) des CRUD Controllers
* [OPUSVIER-3029] - Standardtexte enthalten Schreibfehler - Rückmeldung Hosting-Kunde BBAW
* [OPUSVIER-3032] - Funktion zum Prüfen von FlashMessenger Nachrichten zu ControllerTestCase hinzufügen
* [OPUSVIER-3033] - Funktion zum Prüfen, ob ein Breadcrumb definiert wurde zu ControllerTestCase hinzufügen
* [OPUSVIER-3035] - Support für Prefix für automatische Label zu Application_Form_Abstract hinzufügen
* [OPUSVIER-3039] - Download links für Dateien in der Metadaten-Übersicht
* [OPUSVIER-3042] - Basisklasse für Modelle in Library anlegen (ersetzt Basisklasse in Admin Modul)
* [OPUSVIER-3045] - In Admin_Form_DocumentMultiSubForm Unterformulare ohne DisplayGroup rendern
* [OPUSVIER-3047] - Neuen Ordner fuer Support-Klasse fuer Unit Tests anlegen
* [OPUSVIER-3049] - Admin_Model_DocumentEditSession so erweitern, daß mehrere Posts gespeichert werden können
* [OPUSVIER-3051] - Update von ServerDateModified nur bei relevanten Änderungen an Dependent-Modellen
* [OPUSVIER-3052] - Controller Helper für Manipulation von Breadcrumbs implementieren
* [OPUSVIER-3053] - Admin_Model_FileImport und Controller_Helper_Files für Unit Tests modifizieren
* [OPUSVIER-3056] - Vor der Ausführung der Bibtex Import Tests prüfen, ob "bib2xml" Kommando verfügbar ist
* [OPUSVIER-3057] - Breadcrumb für ein Dokument kürzen
* [OPUSVIER-3058] - Formularelement für die Auswahl von Rollen implementieren
* [OPUSVIER-3059] - Mehrspaltige Ausgabe in Metadaten-Formular über HTML Tabellen realisieren
* [OPUSVIER-3064] - Verwende Pfad relativ zu APPLICATION_PATH für temporäre Dateien in Publish
* [OPUSVIER-3066] - Unit Test Fixes für neues CI-System
* [OPUSVIER-3102] - Fehlermeldung bei fehlenden Dateien
* [OPUSVIER-3103] - FileLink Element muss HIDDEN Feld erzeugen für populate-Funktion nach einem POST
* [OPUSVIER-3104] - Fehlermeldung für den Fall das der Hash nicht ermittelt werden konnte
* [OPUSVIER-3105] - FileHash und FileSize Elemente nach POST mit Werten setzen
* [OPUSVIER-3111] - Konfigurationsparameter zum Abschalten der Bestätigungsseite für Statusänderungen in Dokumentation aufnehmen
* [OPUSVIER-3112] - Alle Remove Buttons im Metadaten-Formular müssen übersetzt werden ('Entfernen')
* [OPUSVIER-3113] - Suche nach Dokument-ID mit Schriftenreihen Nummer
* [OPUSVIER-3116] - Tabellenzellen in Metadaten-Formular und Übersicht mit ID markieren um Tests zu vereinfachen
* [OPUSVIER-3127] - Selenium DeleteFileTest an neuen FileManager anpassen
* [OPUSVIER-3128] - Sofortiges Löschen von Dateien mit Nachfrage
* [OPUSVIER-3130] - Javascript theme.js für die Administration sollte nur gezielt eingebunden werden
* [OPUSVIER-3133] - Button zum Abbrechen zur Import Seite hinzufügen
* [OPUSVIER-3135] - Überarbeiten der Dokumentation
* [OPUSVIER-3152] - Manuelle Update Tests vor dem 4.4.1 Release
* [OPUSVIER-308] - Add little icon that shows that frontdoor view will be opened in different window in clearance module
* [OPUSVIER-1335] - Meldung nach dem Abspeichern von Änderungen
* [OPUSVIER-1533] - Felder ThesisPublisher und ThesisGrantor wiederholbar im Adminformular
* [OPUSVIER-1777] - Sprachzuordnung bei SWD-Schlagwörtern in der Metadatenverwaltung entfernen, da diese nur in deutscher Sprache vorliegen
* [OPUSVIER-1784] - Implementierung eines Standard-Layouts
* [OPUSVIER-1980] - Zuordnung von Dokumenten zu einer Schriftenreihe
* [OPUSVIER-2083] - Selenium Test der Probleme in OPSUVIER-1841 abdeckt
* [OPUSVIER-2161] - Unit Test für die Anzeige eines vollständig besetzten Dokumentes
* [OPUSVIER-2305] - Sortierung von Personen im Formular (verstecktes SortOrder Feld)
* [OPUSVIER-2320] - Eingabe von identischen Subjects (duplicate) sollte verhindert werden
* [OPUSVIER-2335] - Skript für das Tagging eines Releases erstellen
* [OPUSVIER-2360] - Hinzufügen von Titeln in einer bereits vergebenen Sprache verhindern
* [OPUSVIER-2396] - Usability beim Hinzufügen neuer Autoren im Admin-Formular
* [OPUSVIER-2741] - leeres DB-Update-Skript db/schema/update-4.4.0-to-4.4.1.sql anlegen
* [OPUSVIER-2748] - Metadaten Übersicht für einzelne Dokumente
* [OPUSVIER-2752] - Druckansicht für Metadaten Übersicht
* [OPUSVIER-2753] - Metadaten Editieren
* [OPUSVIER-2754] - CSS für Dateimanager
* [OPUSVIER-2755] - Druckansicht für Frontdoor
* [OPUSVIER-2757] - Anzeige der installierten Version in der Administration
* [OPUSVIER-2761] - DC-Ausgabe des Dokumenttyps für Habilitationen ist nicht standardkonform
* [OPUSVIER-2762] - fehlendes creator-Element in xMetaDiss-Ausgabe, wenn Datensatz keinen Autor, aber dafür andere Personen oder Körperschaften besitzt
* [OPUSVIER-2765] - Farben und evtl. Icon für Aktive/Inaktive oder Visible/Hidden Lizenzen, Collections, ...
* [OPUSVIER-2792] - Identifier eines Dokuments editieren
* [OPUSVIER-2793] - Metadaten in Sektionen anzeigen und navigieren
* [OPUSVIER-2794] - Code für alte Metadaten-Formulare entfernen
* [OPUSVIER-2798] - Notizen editieren
* [OPUSVIER-2799] - Zusammenfassungen (abstracts) editieren
* [OPUSVIER-2802] - Editieren von Enrichments im Metadaten-Formular
* [OPUSVIER-2803] - Bibliographische Informationen editieren im Metadaten-Formular
* [OPUSVIER-2804] - Schriftenreihen editieren im Metadaten-Formular
* [OPUSVIER-2806] - Schlagwörter editieren im Metadaten-Formular
* [OPUSVIER-2809] - Rendering des Metadaten-Formulars als statische Übersicht
* [OPUSVIER-2817] - Tests für altes Formular anpassen bzw. entfernen
* [OPUSVIER-2858] - Breadcrumbs in der Administration anpassen
* [OPUSVIER-2880] - Anpassungen an Seiten für Sammlungen in der Administration
* [OPUSVIER-2885] - Breadcrumbs für Filemanager anpassen
* [OPUSVIER-2890] - Anpassungen für Hauptseite Documents (Liste) in Administration
* [OPUSVIER-2983] - OPUS 4 Bamberg: DNB Hinweise zur OAI-Schnittstelle
* [OPUSVIER-2984] - OPUS4 Bayreuth: DNB Hinweise zur OAI-Schnittstelle
* [OPUSVIER-2991] - CRUDAction Controller so erweitern, daß das Formular explizit gesetzt werden kann
* [OPUSVIER-2994] - Formularklasse für Opus_Licence implementieren
* [OPUSVIER-3008] - Zeitschriften in Bayreuth
* [OPUSVIER-3040] - OPUS4 KU Eichstätt: DNB Hinweise zur OAI-Schnittstelle
* [OPUSVIER-3072] - OPUS4 TU Berlin: DNB Hinweise zur OAI-Schnittstelle
* [OPUSVIER-3089] - Ausgabe des FlashMessenger Containers verhindern wenn leer
* [OPUSVIER-3090] - Positionierung von Import und Upload Button im FileManager
* [OPUSVIER-3091] - HTML für Breadcrumbs-Leiste anpassen um Hilfslink zu integrieren
* [OPUSVIER-3092] - Legend Tags die als Überschriften (für Formulare) angezeigt werden sollen mit "headline" CSS-class markieren
* [OPUSVIER-3094] - Anzeige des direkten Aufrufs eines Dokuments in "Documents" ändern
* [OPUSVIER-3095] - Anzeige von Ist und Soll im Filemanager wenn ungleich
* [OPUSVIER-3099] - CSS Klassen für Tabellenspalten in Metadaten-Formular anpassen
* [OPUSVIER-3101] - Anzeige der Paginierung für Dokumente-Liste
* [OPUSVIER-3108] - Anpassung HTML für Sektionsfehlermeldungen

### Dokumentation

* [OPUSVIER-2891] - Kapitel 9 Administration grundlegend überarbeiten
* [OPUSVIER-3031] - Dokumentation 4.4 S. 193: "Contribution" anstelle von "Contribution to a Periodical"
* [OPUSVIER-1990] - Title werden jetzt in TextArea Feldern eingegeben
* [OPUSVIER-2960] - Kapitel Administration, 9.1 Dokumente
* [OPUSVIER-2961] - Kapitel Administration, 9.2 Freischalten
* [OPUSVIER-2962] - Kapitel Administration, 9.3 Sammlungen
* [OPUSVIER-2963] - Kapitel Administration, 9.4 Schriftenreihen
* [OPUSVIER-2964] - Kapitel Administration, 9.5 Lizenzen
* [OPUSVIER-2965] - Kapitel Administration, 9.6 Sprachen
* [OPUSVIER-2966] - Kapitel Administration, 9.7 Informationen
* [OPUSVIER-2967] - Kapitel Administration, 9.8 Zugriffskontrolle
* [OPUSVIER-2968] - Kapitel Administration, 9.9 Oberflächenanpassung
* [OPUSVIER-2969] - Kapitel Administration, 9.10 Systeminformationen
* [OPUSVIER-3119] - Dokumentation für Dateimanager anpassen
* [OPUSVIER-2856] - Dokumentation des Features Publikationslisten

### Spezifikation

* [OPUSVIER-12] - Bibtex-Import

---

## Release 4.4.0 2013-07-22

### Bugs

* [OPUSVIER-1666] - XML-Cache wird nach Collection/CollectionRole-Update (Umbenennung, Unsichtbarmachung, Löschung, Änderung von Eigenschaften) nicht geupdated
* [OPUSVIER-1681] - XML-Cache bekommt von Änderungen an Dateien eines Dokuments nichts mit
* [OPUSVIER-1687] - Design des XML-Cache überarbeiten: Cacheeintrag eines Dokuments wird nur aktualisiert, wenn nach Änderung store()-Methode auf dem Dokument gerufen wird -- indirekte Änderungen werden ignoriert
* [OPUSVIER-1688] - XML-Cache bekommt von den Änderungen in Sprache, Licence, Dateien und DNB-Institutionen nichts mit
* [OPUSVIER-1739] - Änderungen an Dateien über den Admin-Bereich aktualisiert XML-Cache nicht
* [OPUSVIER-1820] - Patentinformationen werden nicht auf der Frontdoor angezeigt
* [OPUSVIER-2124] - Dokumenttyp-Templates und View-Templates sollten nicht in einem Verzeichnis liegen: sonst sind die Dokumenttypbezeichnungen check und error nicht erlaubt
* [OPUSVIER-2611] - Fehler beim Löschen eines Browsingfeldes im Publish-Formular
* [OPUSVIER-2616] - OPUS4 kann sinnvoll nur mit Cookies benutzt werden (sowohl von Admin als auch normalem Benutzer)
* [OPUSVIER-2740] - Dokumentübersicht zeigt nur maximal eine titelverleihende bzw. veröffentlichende Stelle pro Dokument an
* [OPUSVIER-2759] - Auswahl bei einstufigen Collections im Publish-Formular wird nicht übernommen, wenn man "Browse Down"-Button verwendet
* [OPUSVIER-2760] - Performanz des Exports für Publikationslisten nicht ausreichend
* [OPUSVIER-2776] - Admin Eintrag im Hauptmenu wird für untergeordnete Seiten in der Administration nicht hervorgehoben
* [OPUSVIER-2783] - Nicht editierbare Felder im Publish-Formular werden nicht mit abgespeichert
* [OPUSVIER-2800] - Migration von unterschiedlichen Dokumenten mit gleichen Dateinamen zulassen, sonst gehen Daten verloren
* [OPUSVIER-2813] - Keine Lokalisierung des Ausgabeformats für Datumsfelder auf der Frontdoor
* [OPUSVIER-2819] - Fehlermeldung bei nicht gefüllten "required fields" wird nicht übersetzt
* [OPUSVIER-2824] - beim Anlegen eines Patents unter ausschließlicher Angabe einer Nummer wird automatisch für das YearApplied der Wert 0000 gespeichert
* [OPUSVIER-2835] - "Undefined index: TitleMainLanguage_1" bzw. "Undefined index: TitleAbstractLanguage_1", wenn das Publikationsformular manipuliert wird
* [OPUSVIER-2837] - Eintrag von Bandnummer ohne Auswahl Schriftenreihe im Publish-Formular erzeugt Anwendungsfehler nach Prüfung und Speichern
* [OPUSVIER-2839] - Auswahl einer Schriftenreihe ohne Angabe einer Bandnummer im Publish-Formular erzeugt keinen Validierungsfehler
* [OPUSVIER-2843] - Enthält eine Schriftenreihe eine Bandnummer, die mit der ID der Schriftenreihe übereinstimmt, so kann im Publish diese Schriftenreihe nicht mehr ausgewählt werden
* [OPUSVIER-2845] - CI-Target opus4servercontrolleronly zeigt Status "grün", obwohl zwei Tests fehlschlagen
* [OPUSVIER-2848] - Labelvergabe bei migrierten Dateien ist fehlerhaft
* [OPUSVIER-2850] - URN_Collision löst keinen Validierungsfehler aus, sondern tritt erst beim Versuch des Abspeicherns im dritten Formularschritt ein
* [OPUSVIER-2852] - Beim Abspeichern des Opus_Document im Formularschritt 3 (Deposit) erfolgt kein ExceptionHandling
* [OPUSVIER-2861] - keine Fehlerbehandlung beim Instanziieren von Model-Klassen im Publish_Model_Deposit
* [OPUSVIER-2882] - Ungeeignete und fehlende Übesersetzungen  beim Import von Bibtex-Daten
* [OPUSVIER-2897] - Auswertung der Konfigurationsschlüssel documentTypes.templates.* findet nicht statt
* [OPUSVIER-2908] - Fehler beim Bearbeiten der Homepage
* [OPUSVIER-2916] - Store auf einem Document mit Titel, ändert ServerDateModified für alle Dokumente
* [OPUSVIER-2920] - Ausgabe von XML-Kommentaren mit den Namen von unsichtbaren Collections bzw. Collection Roles in der Frontdoor: härten oder entfernen
* [OPUSVIER-2934] - Migrationtest Opus3Migration_CorruptXmlDumpTest::testCorruptXmlDump läuft auf CI-System nicht durch
* [OPUSVIER-2935] - Überschreiben der delete()-Methode in Opus_Collection(Role) verhindert korrektes Funktionieren des Plugin-Mechanismus
* [OPUSVIER-2936] - Revision History für die Dateien Opus3Migration_ICL.php und Opus3Migration_Documents.php wiederherstellen
* [OPUSVIER-2970] - ACL-Ressourcen für neu hinzugefügte Admin-Funktionalitäten konfigurieren
* [OPUSVIER-2976] - Opus_File::_createHashValues härten gegen den Fall, dass zwar in der Datenbank eine Referenz auf eine Datei angegeben wurde, diese aber nicht im Dateisystem existiert

### Aufgaben

* [OPUSVIER-2722] - server_date_published (Datum der Publikation (Server)) auf der Frontdoor anzeigen
* [OPUSVIER-2158] - Automatisches Testen des Migrationsskripts
* [OPUSVIER-2658] - Mechanismus zum Verwalten der Übersetzungsressourcen in der Administration spezifizieren
* [OPUSVIER-2729] - Codeanpassungen bei der Administration der Lizenzen eines Dokuments in Zusammenhang mit OPUSVIER-2727
* [OPUSVIER-2730] - Codeanpassungen bei der Administration der DNB-Institute eines Dokuments in Zusammenhang mit OPUSVIER-2728
* [OPUSVIER-2763] - Immer Aktivierungsstatus einer Lizenz auf der Ansichtsseite der Lizenz anzeigen
* [OPUSVIER-2766] - Klasse Review_Model_DocumentAdapter in library verschieben, da sie von zwei Modulen verwendet wird
* [OPUSVIER-2769] - Index Seite des AccountControllers mit weiteren Informationen anreichen
* [OPUSVIER-2797] - Einbindung eines Tracking-Codes dokumentieren
* [OPUSVIER-2810] - jpgraph zu den Build Target im CI System hinzufügen
* [OPUSVIER-2811] - Änderung des Übersetzungsschlüssels ServerDatePublished (für Administration und Frontdoor)
* [OPUSVIER-2812] - Anpassung der Dokumentation: Text und ggf. auch Screenshots
* [OPUSVIER-2821] - Konfiguration von nicht-editierbaren Default-Werten im Publikationsformular dokumentieren
* [OPUSVIER-2823] - CruiseControl-Konfigurationsdateien ins SVN laden
* [OPUSVIER-2831] - Konfigurierbarkeit des Sortierkriteriums für Facettenwerte
* [OPUSVIER-2832] - Spezialsortierkriterium für Facette year umsetzen, so dass absteigende Sortierung (mit dem neuesten Jahr zuerst) möglich ist
* [OPUSVIER-2833] - Konfiguration des Sortierkriteriums für die Facette year dokumentieren
* [OPUSVIER-2834] - Verwendung des Opus_Document-Cache aktivieren
* [OPUSVIER-2844] - Zusätzlichen Kommandozeilen-Parameter des Migrationsskriptes in die Doku aufnehmen.
* [OPUSVIER-2847] - Beim Neuanlegen eines Cache-Eintrags für ein Dokument gleichzeitig eine Reindexierung für das Dokument anfordern
* [OPUSVIER-2849] - Neuaufbau von fehlenden Cache-Einträgen im Hintergrund über Cronjob auslösen
* [OPUSVIER-2851] - Bereitstellung des MetadatenImports im Framework
* [OPUSVIER-2854] - Hinweis zum Feld IdentifierUrn im Publikationsformular in die Doku aufnehmen
* [OPUSVIER-2855] - Das Feld IdentiferUrn sollte aufgrund der Abhängigkeiten zur DNB nicht mehr in den Publish-Template benutzt werden
* [OPUSVIER-2856] - Dokumentation des Features Publikationslisten
* [OPUSVIER-2859] - Indexierung beim Aufruf von store() auf dem Dokument sollte immer synchron erfolgen
* [OPUSVIER-2860] - Ursache für das Brechen der Selenium-Tests 'testSyntaxInvalidDocIdErrorMessage' bzw. 'testFileDoesNotBelongToDocErrorMessage' auf dem CI-System ermitteln
* [OPUSVIER-2862] - Revision Number im Seitenfooter per CSS verstecken, wenn die Instanz im Production-Mode betrieben wird
* [OPUSVIER-2864] - Möglichkeit in Selenium-Tests schaffen, den Application-Mode abzufragen
* [OPUSVIER-2868] - einheitliche Fehlerbehandlung bei Model_Exceptions im Module_Publish sicherstellen
* [OPUSVIER-2869] - Dokumenttyp-Templates sollen aus modules-Verzeichnis in configs-Verzeichnis verschoben werden
* [OPUSVIER-2870] - Aktualisierung auf Zend 1.12.3 zum Update Script hinzufügen
* [OPUSVIER-2874] - Dokumentation Config-Parameter publist.stylesheet
* [OPUSVIER-2875] - Dokumentation Config-Parameter publist.groupby.completedyear
* [OPUSVIER-2881] - Overview Link in Documents Liste in Edit Link umwandeln
* [OPUSVIER-2898] - Verzeichnisbaum in der Dokumentation in Kap. 5.1 aktualisieren
* [OPUSVIER-2899] - Typo in der Dokumentation
* [OPUSVIER-2902] - Anpassung der Dokumentation in Zusammenhang mit der Verschiebung der PHTML-Template-Dateien
* [OPUSVIER-2903] - Erweiterung des Update-Skripts: Behandlung der Verschiebung der PHTML-Template-Files
* [OPUSVIER-2905] - Anpassung des roten Hinweiskasten in Kap. 8.4.6: Verwendung von Unterstrichen im Dokumenttypnamen
* [OPUSVIER-2906] - Veröffentlichung von Prüfsummen für den OPUS4-Release-Tarball
* [OPUSVIER-2907] - Kann die Datei pl.css aus layouts/opus4/css entfernt werden?
* [OPUSVIER-2913] - SolrIndexBuilder sollte unabhängig von der Konfiguration die Indexierung immer unmittelbar ausführen
* [OPUSVIER-2914] - Markierung der Bearbeitung von Übersetzungsressourcen als Beta
* [OPUSVIER-2932] - Diagnose für Test Home_IndexControllerTest::testStartPageContainsTotalNumOfDocs erleichtern
* [OPUSVIER-2947] - Aktualisierung des Install-Skripts auf das aktuelle Zend Framework
* [OPUSVIER-2957] - Watchdog für php_error_log auf opus4ci einrichten
* [OPUSVIER-2958] - Anpassung der Dokumentation: Abhängigkeit auf Version des Zend Frameworks hat sich verändert
* [OPUSVIER-343] - Add button to administration to trigger indexing of database
* [OPUSVIER-485] - Konfigurierbarkeit der Sortierreihenfolge von Facetten
* [OPUSVIER-851] - Anzahl der Dokumente pro Seite
* [OPUSVIER-1107] - Collection-Änderungen: Update aller zugeordneten Dokumente
* [OPUSVIER-1690] - Bei der Zuweisung von Collections zu einem Dokument im Adminbereich die Sichtbarkeit farblich kennzeichnen
* [OPUSVIER-1774] - Editierbarkeit der TMX-Sprachdateien innerhalb des Administrationsbereiches ermöglichen
* [OPUSVIER-1817] - FileManager sollte nachfragen bevor eine Datei gelöscht wird
* [OPUSVIER-1824] - Anforderungsanalyse für Publikationslisten
* [OPUSVIER-1903] - Klasse zum Lesen und Schreiben von TMX Dateien
* [OPUSVIER-2089] - PHP Warning im Zusammenhang mit der Library JpGraph in Logfile auf dem CI-System
* [OPUSVIER-2321] - Editieren der Lizenzen über eine Liste von Checkboxen
* [OPUSVIER-2371] - Administrations-Seite erstellen, die den Status der Dokumente im Index auflistet
* [OPUSVIER-2372] - neues Indexfeld server_date_modified einführen
* [OPUSVIER-2486] - neues mehrwertiges Indexfeld fulltext_indexed anlegen, in dem die Namen der Volltextdateien abgelegt werden, die mit nicht-leerem Ergebnis extrahiert wurde
* [OPUSVIER-2585] - Aktiv-Status der Lizenzen bereits auf Übersichtsseite farblich (rot/grün) kennzeichnen
* [OPUSVIER-2724] - leeres DB-Update-Skript db/schema/update-4.3.1-to-4.4.0.sql anlegen
* [OPUSVIER-2727] - Abfrage auf Tabelle document_licences beim Instanziieren eines Opus_Document eliminieren
* [OPUSVIER-2728] - zwei Abfragen auf Tabelle dnb_institutes beim Instanziieren eines Opus_Document eliminieren
* [OPUSVIER-2750] - Icons für die Sortierung von Personen, Schriftenreihen, ...
* [OPUSVIER-2751] - Hauptmenü für Administration neu gestalten
* [OPUSVIER-2778] - Url-Parameter-Check für Publikationslisten
* [OPUSVIER-2779] - Separate Stylesheet-Directories für den Export und die Publikationslisten
* [OPUSVIER-2780] - Mapping der URL-Parameter 'role' und 'number' auf Suchanfrage
* [OPUSVIER-2785] - Bearbeiten der Einträge auf der Hilfeseite
* [OPUSVIER-2789] - Lizenzen editieren
* [OPUSVIER-2790] - Patente editieren
* [OPUSVIER-2791] - Allgemeine Metadaten editieren
* [OPUSVIER-2796] - Bearbeitung der Einträge auf statischen Seiten
* [OPUSVIER-2801] - Titel editieren im neuen Metadaten-Formular
* [OPUSVIER-2827] - Implementierung eines MetadatenImport-Workers
* [OPUSVIER-2840] - Anzeige der Opus_Job-Queue in der Administration
* [OPUSVIER-2841] - Integration des Setup-Moduls in die Navigation
* [OPUSVIER-2866] - Auswahl des Stylesheets um Config-Parameter erweiteren
* [OPUSVIER-2867] - Gruppierung der Dokumente einer Publikationsliste konfigurierbar machen
* [OPUSVIER-2883] - Implementierung von Unit-Tests für den BibTeX-Import-Controller
* [OPUSVIER-2888] - Absolute Urls statt relativer Urls im Default-Layout
* [OPUSVIER-2889] - Prefix für attribute 'id' und 'class' sowie die Anker im Default-Layout einführen
* [OPUSVIER-2892] - Unterbindung von Namespaces im Default-Layout
* [OPUSVIER-2896] - Verschiebung der Job-Erzeugung beim BibTeX-Import vom Controller in das Modell
* [OPUSVIER-2900] - Import von BibTeX-Dateien mit deutschen Umlauten
* [OPUSVIER-2912] - Selenium Tests für neues HTML gefixt
* [OPUSVIER-2918] - Util-Klasse implementieren, die einen Konsistenzcheck bezüglich Datenbank und Suchindex durchführt und Inkonsistenzen auflöst
* [OPUSVIER-2919] - Job-Worker-Klasse und zugehöriges Cron-Skript für die Ausführung des Consistency Checks implementieren
* [OPUSVIER-2923] - Validierung von Titeln im Metadaten-Formular
* [OPUSVIER-2925] - Konsistenzprüfung in Administration
* [OPUSVIER-2937] - Beim implizites Löschen von Collection-Bäumen wird das server_date_modified nicht aktualisiert
* [OPUSVIER-2941] - Restlichen adminContainer von PHTML Skripten entfernen
* [OPUSVIER-2949] - Einführung eines spezifischen Konfigurationsparameters für die asynchrone Ausführung der Index-Maintenance
* [OPUSVIER-2954] - Dokumentation des neuen Features Konsistenzprüfung

### Dokumentation

* [OPUSVIER-2678] - Dokumentation der asynchronen Jobverarbeitung
* [OPUSVIER-2725] - Korrektur in Kapitel 5.1 (Workspace Permissions)
* [OPUSVIER-2735] - Direktverlinkungen in den FAQ
* [OPUSVIER-2829] - Cronjob zur Cache-Revalidierung
* [OPUSVIER-2830] - Bearbeitung von Übersetzungsressourcen und statischen Inhalten
* [OPUSVIER-2898] - Verzeichnisbaum in der Dokumentation in Kap. 5.1 aktualisieren

### Spezifikation

* [OPUSVIER-1684] - Hinzufügen von mehreren Informationen gleichen Types ist mühsam

---

## Release 4.3.1 2013-02-21

### Bugs

* [OPUSVIER-2328] - PersonEditor wird im RIS-Export nicht ausgegeben
* [OPUSVIER-2587] - Übersetzungsresource modules/admin/language/access.tmx ist nicht valide
* [OPUSVIER-2599] - ThesisGrantor/@Name wird in XMetaDissPlus.xslt zweimal ausgegeben
* [OPUSVIER-2701] - sichtbare Gruppierung (Zebrastreifen) von Sammlungseinträgen im Publikationsformular funktioniert nicht
* [OPUSVIER-2716] - BibTeX Export liefert leere Datei
* [OPUSVIER-2734] - Browsing-Felder (Collections) werden nicht abgespeichert, wenn man mehr als 2 Ebenen absteigt
* [OPUSVIER-2738] - Datenexport RIS: mehrere Werte des gleichen Typs separat ausgeben
* [OPUSVIER-2739] - Standardbezeichnungen für DDC im Publish anpassen

### Aufgaben

* [OPUSVIER-2407] - optionales Anlegen einer DINI-konformen DDC (nach DNB-Standard)
* [OPUSVIER-2642] - leeres DB-Update-Skript db/schema/update-4.3.0-to-4.3.1.sql anlegen

### Dokumentation

* [OPUSVIER-2723] - Anforderungen an Mail-Server (SMTP) in der Dokumentation präzisieren
* [OPUSVIER-2736] - Abhängigkeit zwischen PHTML-Template und XML-Dokumenttypdefinition: Schreibweise des Wertes des Attributs name

---

## Release 4.3.0 2012-12-20

### Bugs

* [OPUSVIER-1886] - Falscher Hinweis zur Zugehörigkeit des Dokuments zur Bibliographie
* [OPUSVIER-1933] - BK Collection führt zu weißer Seite im Publish
* [OPUSVIER-1988] - Migration der Tabelle "diss" aus OPUS3
* [OPUSVIER-2422] - Fehlerhafte Übersetzung bei einem neuen Dokumenttyp "patent" in der Auswahlliste auf der ersten Seite des Publish-Formulars
* [OPUSVIER-2443] - Upload Feld kann nicht ausgeblendet werden
* [OPUSVIER-2492] - Bezeichnungen der Collection Roles erscheinen nicht auf der Frontdoor
* [OPUSVIER-2538] - Sinn der PHTML-Templates unterhalb von server/modules/publish/views/scripts/form/expert klären
* [OPUSVIER-2580] - Update Skript berücksichtigt Ordner "public/xsl" nicht
* [OPUSVIER-2582] - Home_Model_HelpFiles verwendet getExtension Funktion, die es erst seit PHP 5.3.6 gibt
* [OPUSVIER-2583] - Migrationsskript sollte für den Fall gehärtet werden, dass für ein freigeschaltetes Dokument noch Einträge in den OPUS3-temp-Tabellen existieren
* [OPUSVIER-2584] - das bei Migrationsfehlern ausgegebene OPUS4-XML-Dokument enthält nur eine Teilmenge der Datenfelder
* [OPUSVIER-2586] - Umbenennung der Buttons "up" und "down" nur global möglich
* [OPUSVIER-2589] - Publish-Formular ruft bei der Generierung eines Response mehrfach (mindestens zweimal) den Translate-Mechanismus auf
* [OPUSVIER-2602] - Update erneuert Templates für Dokumenttypen trotz Angabe [K]eep modified file
* [OPUSVIER-2607] - XMetaDissPlus: Vornamen optional ausliefern
* [OPUSVIER-2613] - Auskommentieren der Revision-Nummer in common.phtml nicht mit HTML- sondern besser mit PHP-Kommentaren
* [OPUSVIER-2618] - Download von Solr 1.4.1 scheitert aufgrund geänderter URL
* [OPUSVIER-2635] - Fehlermeldung im dritten Formularschritt beim Dokumenttyp all, wenn alle Schriftenreihen in der Administration gelöscht wurden
* [OPUSVIER-2638] - Formular für das Ändern der Sammlungseinstellungen zeigt "OPUS 4 | Sammlung '%1$s' wurde erfolgreich bearbeitet" im Seitentitel
* [OPUSVIER-2639] - fehlende Übersetzungsschlüssel für PersonReferee, PersonEditor, PersonAdvisor, PersonTranslator, PersonContributor nachtragen
* [OPUSVIER-2640] - Übersetzungsschlüssel mit dem Suffix "1" in der Datei modules/publish/language/field_hints.tmx überprüfen
* [OPUSVIER-2645] - erste Seite des Publikationsformulars wird doppelt durch den Translate-Mechanismus geschleift
* [OPUSVIER-2646] - wenn form.first.bibliographie auf den Wert 0 gesetzt wird, kann dennoch das Dokument der Bibliographie zugeordnet werden, wenn das Formular manipuliert wird
* [OPUSVIER-2650] - fehlende Übersetzungen der header und hints für Identifier ergänzen
* [OPUSVIER-2651] - falsche Anzeige von Collections auf der Frontdoor, wenn die Anzeigeoption "Name, Number" für die zugehörige Collection Role ausgewählt wurde
* [OPUSVIER-2654] - Parameter "stylesheets" ändern in "stylesheet"
* [OPUSVIER-2655] - Assertions der Tests in Publish_FormControllerTest sind zu schwach
* [OPUSVIER-2657] - falscher Anzeigetitel "Wählen Sie eine weitere Datei zum Hochladen" im ersten Schritt des Publikationsformulars, wenn auf erster Seite ein Fehler auftritt
* [OPUSVIER-2664] - Publish-Formular verwirft die bereits hochgeladene Datei, wenn Button "Upload another file" und anschließend "Next Step" gedrückt wird
* [OPUSVIER-2676] - Publish-Formular geht nicht in Schritt 2, wenn anfängliches Formular kein ausgewählten Dokumenttyp hatte
* [OPUSVIER-2681] - zwei fehlende hint-Übersetzungsschlüssel nachtragen
* [OPUSVIER-2683] - Übersetzungsschlüssel der Hints für Browsing-Felder im zweiten Publikationsformular dürfen keine fortlaufende Nummer bekommen
* [OPUSVIER-2684] - Collection Role 'bk' wird im Dokumenttyp 'all' nicht verwendet und es existieren auch keine Übersetzungsschlüssel
* [OPUSVIER-2685] - Englische Standardfehlermeldung, wenn in deutscher Formularversion eine ungültige E-Mail-Adresse eingetragen wurde
* [OPUSVIER-2686] - Fehler bei der Migration von Volltexten in Verzeichnishierarchien
* [OPUSVIER-2688] - Downloadlink für Solr 1.4.1 veraltet
* [OPUSVIER-2697] - Verknüpfung eines Dokuments zu einer Collection (nicht zur obersten) in der Administration führt ggf. zu "Fatal error: Allowed memory size of XXX bytes exhausted"
* [OPUSVIER-2699] - mehrere Warnungen und Hinweise im PHP-Error-Log in Zusammenhang mit dem Test von Opus3FileImport.php
* [OPUSVIER-2712] - Nutzer können eigenen Account nicht editieren

### Tasks

* [OPUSVIER-2127] - Check-Seite im Publish-Modul: Anzeige des BelongsToBibliography-Status und der hochgeladenen Dateien sollte jeweils ausblendbar sein
* [OPUSVIER-2677] - Hinweis in die Release Notes aufnehmen, dass mit OPUS 4.3.0 kleine Anpassungen an den PHTML-Dokumenttyp-Templates vorgenommen wurden
* [OPUSVIER-2694] - neue Funktion hasVisibleChildren in Opus_Collection einführen
* [OPUSVIER-2695] - neue Funktion getVisibleChildren in Opus_Collection schaffen
* [OPUSVIER-2698] - neu eingeführte Funktion getDisplayNameForBrowsingContext in Opus_Collection sollte Kompatibilität der übergebenen Collection Role überprüfen
* [OPUSVIER-2719] - PersonController entfernen
* [OPUSVIER-491] - Mehrfach belegte Übersetzungs-Schlüssel prüfen
* [OPUSVIER-809] - alphabetische Sortierungen für Selects
* [OPUSVIER-1858] - automatische E-Mail an den Submitter/Autor eines Dokuments nach dessen Veröffentlichung
* [OPUSVIER-1947] - Datei modules/publish/documentation/publish.ger.txt und Verzeichnis modules/publish/documentation entfernen
* [OPUSVIER-1951] - Template-Definitionen in index.xslt in externe Dateien auslagern
* [OPUSVIER-1960] - Übersetzung der Namen von Modulen auf der Access Seite für Rollen
* [OPUSVIER-1961] - Erweiterung der Liste der möglichen Rechte auf der Access Seite für Rollen
* [OPUSVIER-1967] - Anpassung der Datenbank zur Speicherung der neuen Rechte für Rollen
* [OPUSVIER-1968] - Modifizieren der Klasse Opus_SecurityRealm um neue Rechte zu unterstützen
* [OPUSVIER-1969] - Filterung des Administrationsmenüs in Abhängigkeit von den Rechten eines Nutzers
* [OPUSVIER-1970] - Transparente Absicherung von Controllern implementieren
* [OPUSVIER-1971] - Einschränkung des Workflows in Abhängigkeit von Rollen umsetzen
* [OPUSVIER-2043] - Klasse für die Konfiguration der ACLs implementieren
* [OPUSVIER-2065] - Cleanup-Skript für das Entfernen von Dokumenten im Zustand temporary und deren Dateien
* [OPUSVIER-2297] - Migrationstest für die Collections anlegen
* [OPUSVIER-2298] - Migrationstest für die Volltexte anlegen
* [OPUSVIER-2299] - Migrationstest für die Lizenzen anlegen
* [OPUSVIER-2300] - Migrationstests für die fehlenden Dokumenttypen anlegen
* [OPUSVIER-2301] - Migrationstest für Enrichments anlegen
* [OPUSVIER-2557] - leeres DB-Update-Skript db/schema/update-4.2.2-to-4.3.0.sql anlegen
* [OPUSVIER-2565] - Formatierungen (z.B. Absätze) im Abstract ermöglichen
* [OPUSVIER-2576] - Migration der Subjects konfigurierbar machen: Tests nachreichen
* [OPUSVIER-2601] - Anzeige von Sammlungen mit Name und Nummer konfigurierbar ob in Administration oder im Browsing oder in beiden
* [OPUSVIER-2609] - Exportausgabe um zusätzliches Wurzelelement-Attribut queryhits erweitern, das Gesamttrefferanzahl der zugrundeliegenden Suchanfrage enthält
* [OPUSVIER-2610] - neuer Config-Parameter einführen searchengine.solr.facetlimit.FACETTE
* [OPUSVIER-2625] - Dokumentation der Print on Demand Funktionalität
* [OPUSVIER-2630] - Autor(en)/Submitter zum Verschicken der Benachrichtigung auswählbar machen
* [OPUSVIER-2631] - doppelten Versand von Emails verhindern, wenn Autor=Submitter
* [OPUSVIER-2633] - Benachrichtigung an jede Person einzeln verschicken anstatt eine E-Mail an alle Personen
* [OPUSVIER-2648] - Hinweis zu hochgeladenen Dateien im zweiten und dritten Formularschritt ausblenden, wenn File-Upload deaktiviert wurde
* [OPUSVIER-2649] - Unit Test schreiben, der mehrfach belegte Übersetzungsschlüssel detektiert
* [OPUSVIER-2659] - Umsetzung des Worker-Mechanismus'
* [OPUSVIER-2666] - Migrationstests für Zugriffsbeschränkungen auf Dateien
* [OPUSVIER-2667] - Link zum Editieren eines Dokumentes in der Frontdoor nur für Nutzer mit Zugriff auf "documents" anzeigen
* [OPUSVIER-2668] - Defaultvalues für das Mapping von Dokumentsprache und Dokumenttyp
* [OPUSVIER-2670] - Umsetzung des Workers für Mailversand
* [OPUSVIER-2679] - Probeme bei der Jobverarbeitung werden u.U. nicht sichtbar
* [OPUSVIER-2690] - Fehlerausgabe bei der Migration, wenn Dokumenttyp und Sprache nicht gemappt werden können
* [OPUSVIER-2692] - Systematische Tests für mehrere Titel mit  gleicher Sprache
* [OPUSVIER-2702] - Verzeichnis scripts/cron muss in Distribution-Tarball aufgenommen werden
* [OPUSVIER-2704] - Erzeugen von Dumps und Testklassen für die Konsistenzprüfung der temp-Klassen
* [OPUSVIER-2706] - Testaufbau für die asynchrone Jobverarbeitung
* [OPUSVIER-2710] - Tests für erweiterte Recht im Admin Modul hinzufügen

### Documentation

* [OPUSVIER-2626] - Kapitel "Dokumente verwalten" bzw. "Übersicht - Metadaten bearbeiten" überarbeiten
* [OPUSVIER-2627] - Kapitel "Lizenzen verwalten" überarbeiten
* [OPUSVIER-2628] - Kapitel "Sprachen verwalten" überarbeiten
* [OPUSVIER-2629] - Kapitel "DNB-Institution (Verbreitende Stelle)" überarbeiten
* [OPUSVIER-2634] - Dokumentation des E-Mail-Notifizierungsmechanismus
* [OPUSVIER-2647] - Dokumentation des neu eingeführten Konfigurationsschlüssels form.first.enable_upload
* [OPUSVIER-2661] - Kapitel "Installation mittels Paketverwaltung" temporär aus der Doku nehmen
* [OPUSVIER-2662] - Überschreiben des Default-Werts (von 10) für die angezeigten Werte pro Facette
* [OPUSVIER-2678] - Dokumentation der asynchronen Jobverarbeitung
* [OPUSVIER-2700] - zu ändernde Übersetzungsschlüssel beim Anlegen neuer Felder tabellarisch darstellen
* [OPUSVIER-2713] - Erweiterte Möglichkeiten der Rechteverwaltung dokumentieren
* [OPUSVIER-877] - Feinjustierung bei Opus3XMLImport
* [OPUSVIER-2671] - Kapitel 10.1: Mapping von Dokumenttypen anpassen
* [OPUSVIER-2672] - Kapitel 10.1: Mapping von Sprachen anpassen
* [OPUSVIER-2674] - Kapitel 10.1: Migration der Lizenzen

### Stories

* [OPUSVIER-2691] - Systematische Tests für Titel und Abstracts

---

## Release 4.2.2 2012-07-04

### Bugs

* [OPUSVIER-938]  - Import
* [OPUSVIER-1207] - FormController Unit-Tests schlagen fehl
* [OPUSVIER-1368] - Export wirft Fehler, wenn Solr-Index nicht up-to-date ist
* [OPUSVIER-1659] - Publish-Formular: Gedrückte Buttons werden nicht erkannt, wenn Sprache falsch gesetzt ist.
* [OPUSVIER-1726] - RSS stirbt, wenn dex Solr-Index Dokumente enthält, die nicht mehr in der Datenbank sind
* [OPUSVIER-1764] - Date-Attribut Timezone in XML-Ausgabe enthält ungültigen Inhalt
* [OPUSVIER-1919] - Suche hinter den RSS-Feeds wirft keinen 503er
* [OPUSVIER-1948] - Nach Angabe ab 10 der Keywords/Titeln erhält man bei "next step" eine weiße Seite
* [OPUSVIER-1999] - Enrichment-Feld mit "Title" im Namen läßt sich nicht anlegen
* [OPUSVIER-2051] - Validierung Titel <-> Dokumentsprache auf TitleMain beschränken
* [OPUSVIER-2077] - Document-XML enthält (für Opus_Model_Filter) nicht mehr die Dokument-ID
* [OPUSVIER-2240] - Indexierungsfehler des Solr-Servers werden im OPUS-Indexer-Code nicht erkannt
* [OPUSVIER-2337] - HTTP Response Code 503 senden, wenn Solr-Server keine Verbindung annimmt
* [OPUSVIER-2343] - Selenium-Server schliesst Firefox nicht automatisch
* [OPUSVIER-2354] - Update-Script überschreibt alle Standardhilfetexte wie z.B. Kontaktdatei und Imprintdatei sowie die Indexdatei help.phtml
* [OPUSVIER-2393] - erforderliche Minimalanpassungen für DINI-Compliance der OAI-Schnittstelle
* [OPUSVIER-2397] - Nutzer in Role 'reviewer' bekommt keine 'unpublished' Dokumente angezeigt
* [OPUSVIER-2398] - fehlender Übersetzungsschlüssel beim Zuweisen von Sammlungen
* [OPUSVIER-2409] - Datei zum Dokument wird beim Veröffentlichen mit der Sprache "Englisch" versehen, auch wenn Dokumentensprache "Deutsch" ist
* [OPUSVIER-2410] - Das Migrationsscript setzt für unpublish-Dokumente aus OPUS3 das Datum "server_date_published"
* [OPUSVIER-2413] - Unnötiges "string to lower" in Account-Administration sorgt für Probleme bei Mixed-Case-Usernamen
* [OPUSVIER-2417] - Sichtbarkeit und Lesezugriffsrecht von Dateien wird bei der Befüllung des Indexfeld has_fulltext ignoriert
* [OPUSVIER-2419] - Fehler in der Beschreibung des Anlegens von benutzerspezifischen Übersetzungsressourcen: Kap. 8.2
* [OPUSVIER-2420] - Fehler beim Umbenennen von bereits existierenden Enrichmentkeys
* [OPUSVIER-2424] - Admin kann Dateien von gelöschten Dokumenten nicht ansehen
* [OPUSVIER-2425] - Publish-Formular: Felder mit einfachen Quotes ' werden abgeschnitten
* [OPUSVIER-2426] - Anpassung der OPUS-Versionsnummer sowie der Link in der FAQ-Datei "dokumentation.de.txt"
* [OPUSVIER-2427] - Migrationsscript lässt Dateien von "eingeschränkten" Dokumenten aus OPUS3 für OAI zu
* [OPUSVIER-2431] - Fehler im Template für Anlegen eines neuen Gruppen-Feldes
* [OPUSVIER-2432] - Text zur Verlinkung zum "Kapitel 8.3.2 Felder umbenennen" ist an mehreren Stellen falsch
* [OPUSVIER-2433] - Verschiedene Sprachen für TitleParent und Dokument soll möglich sein
* [OPUSVIER-2434] - Solr-Searcher erzeugt Queries, die im Solr einen Parse-Error auslösen
* [OPUSVIER-2435] - Sonderzeichen (", \) in Autoren-Namen erzeugen ungültige Such-Links in der Frontdoor
* [OPUSVIER-2437] - Migrationsscript migriert nicht alle Datensätze (bei ungültiger Mail-Adresse in "verification")
* [OPUSVIER-2440] - Administration der IP-Ranges lässt nur alpha-numerische Beschreibungen zu
* [OPUSVIER-2441] - Administration der IP-Ranges darf nur auf IPs validieren
* [OPUSVIER-2444] - URLS mit Sonderzeichen sind in der  OAI-Schnittstelle nicht korrekt codiert
* [OPUSVIER-2448] - Fehler in der XMetaDissPlus-Ausgabe
* [OPUSVIER-2449] - Transfercontainer für ein Dokument nicht erzeugen, wenn dem Dokument nur eine in der OAI-Schnittstelle sichtbare Datei zugeordnet ist
* [OPUSVIER-2450] - Element ddb:fileNumber enthält Anzahl der Dateien für alle ausgelieferten Dokumente (falscher XPath-Selektor)
* [OPUSVIER-2454] - OAI-Schnittstelle parst ungültige Identifier nicht korrekt
* [OPUSVIER-2455] - Duplicate headers received from server (Multiple Content-Disposition headers)
* [OPUSVIER-2456] - Hinweis in die Installationsbeschreibung aufnehmen, dass allow_url_fopen auf On gesetzt sein muss
* [OPUSVIER-2461] - Metadatenimport-Skript wirft Fehlermeldung beim Versuch die Keywords eines Dokuments zu indexieren
* [OPUSVIER-2463] - Formatierung der Beispiel-XML-Snippets und der Lognachrichten im Kapitel 12 verbessern
* [OPUSVIER-2467] - Status-Code 500 bei schwerwiegenden Fehlern im Production-Mode ausliefern
* [OPUSVIER-2468] - bei Personen: nur Nachnamen als Pflichtfeld
* [OPUSVIER-2469] - bei Personennamen darf nur der Nachname verpflichtend sein
* [OPUSVIER-2470] - Personen werden nicht korrekt gespeichert
* [OPUSVIER-2476] - URLs zur DNB sind veraltet - bitte aktualisieren
* [OPUSVIER-2477] - Migrationsscript löscht Umlaute in Volltextdateien während der Migration
* [OPUSVIER-2482] - Fehler in der Dokumentation, das Feld "Journal" gibt es nicht mehr
* [OPUSVIER-2487] - Metadatenimport-Skript gibt sinnlose Meldungen aus, wenn es ohne Datei aufgerufen wird
* [OPUSVIER-2491] - Import von 8000 Datensätzen dauert mit 60 Minuten sehr lange
* [OPUSVIER-2493] - XML-Cache wird immer noch befüllt
* [OPUSVIER-2502] - Layout-Bug bei der Erweiterten Suche im Kontext der Suche nach einem Autoren
* [OPUSVIER-2508] - XMetaDissPlus zeigt auch Transfer-URLs für Dokumente an, die keine Volltexte besitzen
* [OPUSVIER-2509] - XMetaDissPlus: DDC-SG durch DDC ersetzen
* [OPUSVIER-2510] - XMetaDiss-Stylesheet enthält noch Abfragen auf Subject-DDC
* [OPUSVIER-2513] - Doppelklicks auf Submit-Buttons sollten abgefangen und zum Einfachklick transformiert werden
* [OPUSVIER-2518] - HTTP Response Code 400 statt 503, wenn Solr-Server keine Verbindung annimmt
* [OPUSVIER-2519] - Plugin Opus_Document_Plugin_SequenceNumber wird nicht mehr benötigt -- Plugin wird aber initialisiert
* [OPUSVIER-2523] - Element "ddb:contact" bei XMetaDissPlus nicht ausgeben falls ThesisPublisher leer
* [OPUSVIER-2524] - Element "thesis:grantor" bei XMetaDissPlus nicht ausgeben falls ThesisGrantor leer
* [OPUSVIER-2534] - Fehler, wenn RSS-Export mit Suchanfrage aufgerufen wird, die ein leeres Ergebnis zurückliefert
* [OPUSVIER-2535] - OAI-Schnittstelle: "idDoesNotExist" wenn Dokument nicht gefunden
* [OPUSVIER-2539] - PersonSubmitter sollte nicht in den Suchindex wandern
* [OPUSVIER-2540] - fehlende Übersetzungen in der Sammlungsadministration
* [OPUSVIER-2541] - Migration der Subjects konfigurierbar machen
* [OPUSVIER-2543] - fehlende Übersetzungen in der Sammlungsadministration für die Aktionen Move und ChangeVisibility
* [OPUSVIER-2564] - OAI-Schnittstelle: GetRecord liefert SetSpecs mit ungültigen Zeichen aus
* [OPUSVIER-2570] - boolesche Attribute "belongstobibliography" und "allowemailcontact" werden nicht korrekt importiert, wenn als Werte statt 0/1 die Werte false/true verwendet werden

### Tasks

* [OPUSVIER-2490] - OAI-Schnittstelle soll XSLT für Browser-Rendering übermitteln
* [OPUSVIER-645]  - Frontdoor: Abstracts einklappbar machen
* [OPUSVIER-1481] - Fehlende DC-Felder in Ausgabe der OAI-Schnittstelle
* [OPUSVIER-1770] - Aufruf von /admin/statistic/show resultiert in Exception
* [OPUSVIER-2072] - UNIQUE-Constraint für DocumentEnrichment-Tripel (document_id, key_name, value) über das Framework gewährleisten
* [OPUSVIER-2362] - Migrationsskript sollte WARNING ausgeben, wenn Dokumente mit mehreren Abstracts in gleicher Sprache migriert werden
* [OPUSVIER-2363] - Migrationsskript sollte WARNING ausgeben, wenn Dokument in Sprache eng vorliegt und title_en nicht leer ist
* [OPUSVIER-2364] - Migrationsskript sollte WARNING ausgeben, wenn OPUS4-Dokumente mit mehreren TitleMain/TitleAbstract-Feldern in der gleichen Sprache angelegt werden
* [OPUSVIER-2400] - Verwendung von ENTER-Taste im Edit-Formular für Autoren entfernt unter Umständen einen Autor
* [OPUSVIER-2445] - Zusätzliche Prüfung einführen: URN nur bei Volltext
* [OPUSVIER-2446] - setServerDatePublished nicht mehr "manuell" setzen
* [OPUSVIER-2475] - Alle Identifier mit in den Suchindex aufnehmen
* [OPUSVIER-2484] - copy field statements für alle Personen für die einfache Suche anlegen
* [OPUSVIER-2488] - XML-Export sollte auch Paginierung unterstützen
* [OPUSVIER-2520] - Migration nichtvalider E-Mail-Adressen bei PersonSubmitter
* [OPUSVIER-2521] - Neuer EnrichmentKey 'InvalidVerification' für die Migration
* [OPUSVIER-2526] - Neuen Parameter frontdoor.numOfShortAbstractChars abschaltbar machen
* [OPUSVIER-2532] - Ubuntu 12.04 LTS unterstützt "mysql-server-5.5" anstatt "5.1"
* [OPUSVIER-2544] - Migration der Subjects konfigurierbar machen: Implementation
* [OPUSVIER-2553] - Migration der Subjects konfigurierbar machen: Tests
* [OPUSVIER-1917] - Collection "open_access" und OAI-Set einrichten

### Documentation

* [OPUSVIER-2458] - Hinweis zur Dokumentsprache aufnehmen
* [OPUSVIER-2460] - Hinweis in die Checkliste für die Liveschaltung aufnehmen, dass uns nach der Liveschaltung die Instanz-URL mitgeteilt wird
* [OPUSVIER-2481] - Verlinkung zu Famfamfam bei Nutzung der Icons
* [OPUSVIER-2485] - Kapitel Einfache Suche aktualisieren
* [OPUSVIER-2495] - Klarstellung zu oldId und docId im Import-XML
* [OPUSVIER-2507] - neuer Konfigschlüssel frontdoor.numOfShortAbstractChars
* [OPUSVIER-2512] - Hilfedateien verschoben
* [OPUSVIER-2514] - Kapitel zur Benennung der Dokumenttypen anpassen
* [OPUSVIER-2517] - OPUS3 migrierte Benutzerdefinierte Felder - Hinweis zur Verwendung präzisieren
* [OPUSVIER-2525] - Export unterstützt jetzt auch Paginierung
* [OPUSVIER-2530] - Neuer EnrichmentKey 'InvalidVerification' in die Dokumentatuion aufnehmen
* [OPUSVIER-2545] - Migration der Subjects konfigurierbar machen: Dokumentation
* [OPUSVIER-2555] - Dokumentation der Abhängigkeiten (Dependencies) von OPUS aktualisieren
* [OPUSVIER-2566] - Kapitel 2.6 Export: Der Parameter heißt "stylesheets"
* [OPUSVIER-2567] - Hilfe Index wird in "help.ini" statt "help.phtml" konfiguriert
* [OPUSVIER-2569] - Nur noch "all-lower-case" Loginnamen verwenden

---

## Release 4.2.1 2012-03-01

### Bugs

* [OPUSVIER-1930] - Sprachabhängige Assertions in Unit-Tests entfernen
* [OPUSVIER-2281] - Fehlender Übersetzungsschlüssel admin_access_store
* [OPUSVIER-2341] - update-documenttypes.php ersetzt nur den Attributwert von datatype für das SubjectFeld
* [OPUSVIER-2348] - MIgrationsskript bricht ab, wenn es mit der Einstellung set -ex läuft
* [OPUSVIER-2350] - Migrationsskript prüft nicht (mehr), ob der einzulesende XML-Dump valide ist
* [OPUSVIER-2351] - Migrationsskript testet nur Existenz von Dateien / Verzeichnissen; es prüft aber nicht Lese- bzw. Schreibrechte
* [OPUSVIER-2353] - Exception für die Formulare zum Hinzufügen/Editieren von Abstrakten
* [OPUSVIER-2355] - Änderungen der DocSortOrder eines Dokumentes einer Schriftenreihe werden nicht abgespeichert
* [OPUSVIER-2356] - Migrationsskript bricht ab, wenn Sonderzeichen im Namen von zu importierenden Dateien auftritt
* [OPUSVIER-2361] - Anlegen von zwei Dokumenttiteln in Dokumentsprache während der Migration verhindern
* [OPUSVIER-2376] - CI-Target opus4migration wird nicht automatisch ausgeführt
* [OPUSVIER-2377] - Solr-Indexer versucht leeres Dokument zu indexieren, wenn bei der Volltextextraktion bestimmte Sonderzeichen ermittelt werden
* [OPUSVIER-2378] - OAI-Schnittstelle gibt dc:date nicht aus, obwohl Dokument Inhalt für alle Datumsfelder besitzt
* [OPUSVIER-2379] - OAI-Schnittstelle gibt im Feld dc:type falschen Inhalt aus
* [OPUSVIER-2380] - OAI-Schnittstelle gibt Element dc:subject nicht aus
* [OPUSVIER-2387] - Referenz-URL der .tmx-Dateien ist nicht mehr aktuell
* [OPUSVIER-2392] - Migrationsskript bricht nach der ersten Iteration ab, wenn es mit set -e läuft
* [OPUSVIER-2395] - Update-Script muss "workspace/cache/zend_cache*" löschen
* [OPUSVIER-2401] - "Geprüfte" Dokumente sind nicht auffindbar. In der Administration -> Dokumente verwalten fehlt der Punkt "Zeige Dokumente: Geprüft".
* [OPUSVIER-2402] - Element "required-if-fulltext" in Dokumenttyp-Definition bringt abhängig von der Position Fehler
* [OPUSVIER-2403] - Falsche Pfadangabe in der Dokumentation zur XMetaDissPlus.xslt

### Tasks

* [OPUSVIER-1497] - Unklarheit beim Aufruf des Update-Skripts
* [OPUSVIER-2369] - Shell-Skript erstellen, das die IDs der Dokumente ausgibt, die im ServerState published sind, aber nicht im Index enthalten sind
* [OPUSVIER-2405] - XMetaDissPlus auf neue Scheme Location anpassen

### Documentation

* [OPUSVIER-2388] - Hinweis auf Umbenennung "korrupter" Dateinamen in die Dokumentation aufnehmen
* [OPUSVIER-2389] - Hinweis auf manuelles Bearbeiten "korrupter" XML-Dumps in die Dokumentation aufnehmen
* [OPUSVIER-2408] - Abängigkeit von Modul php5-phar auf openSUSE

---

## Release 4.2.0 2012-01-27

### Bugs

* [OPUSVIER-1316] - Collection-Auswahl im Publikationsformular Schritt 2: Nicht ausgewählte Collection kann nicht mehr rückgängig gemacht werden
* [OPUSVIER-1583] - Newlines und Tabulatoren im Dateinamen verursachen Probleme
* [OPUSVIER-1647] - Frontdoor: Dateinamen in Links werden nicht URL-kodiert
* [OPUSVIER-1753] - OAI-Schnittstelle: Unterstützung für weitere OAI-SetSpecs neben doc-type
* [OPUSVIER-1806] - unklare Exception im SendFile-Helper (Reproduzierbarkeit ist ebenfalls bislang unklar)
* [OPUSVIER-1842] - Ausbleibende Validierung bei der Eingabe von Datumswerten
* [OPUSVIER-1845] - DB-Fehler beim Update auf OPUS 4.1.4, sofern in der Datenbank in der Tabelle document_licences schon ein Eintrag mit der ID 8 steht
* [OPUSVIER-1846] - Update-Skript versucht den Solr-Server neuzustarten, obwohl der Benutzer dies verneint hat
* [OPUSVIER-1847] - Update-Skript verwendet beim Anlegen der Datei db/createdb.sh falsche Shell-Kommentarzeichen (; statt #)
* [OPUSVIER-1859] - Übersetzung der Sprache eines Dokuments in der Frontdoor schlägt fehl
* [OPUSVIER-1863] - Beschränkung der Enrichment-Felder auf 255 Zeichen aufheben
* [OPUSVIER-1865] - gesamtes Element <dc:creator> muss für mehrere Autoren wiederholt werden
* [OPUSVIER-1866] - falsche Bezeichnung für Akademischen Titel
* [OPUSVIER-1889] - Abbruch/Abort Link auf der Seite um Sammlungen zuzuweisen gebrochen
* [OPUSVIER-1914] - Funktion hasFiles der Klasse Review_Model_DocumentAdapter gibt immer True zurück
* [OPUSVIER-1916] - Eigenener Schlüssel für die Auswahl des Collectionfeld "Institute"
* [OPUSVIER-1924] - falsche Verlinkung des Felds identifier_url in der Frontdoor
* [OPUSVIER-1928] - Download liefert korruptes PDF aus
* [OPUSVIER-1943] - Falsche Bezeichnung einiger Dokumenttypen in ris.xslt
* [OPUSVIER-1944] - Collection-Auswahl fällt auf alten Wert zurück, wenn man "up"-"down" benutzt
* [OPUSVIER-1956] - Anzeigefehler beim Hinzufügen von weiteren Feldern innerhalb des Publish-Formulars
* [OPUSVIER-2001] - Migrationsskripte laufen im Modus testing (fest codiert)
* [OPUSVIER-2016] - Migrationsskript gibt "kryptische" Fehlermeldung aus, wenn import.ini nicht gefunden bzw. gelesen werden kann
* [OPUSVIER-2053] - Opus_EnrichmentKey::getAll()liefert die Einträge mit Klassenbezeichner aus
* [OPUSVIER-2056] - Export und OAI-Schnittstelle liefern sensible Daten aus
* [OPUSVIER-2057] - Export liefert Informationen zu zugriffsgeschützten Dateien aus
* [OPUSVIER-2058] - Einige Identifier-Felder werden nicht korrekt gespeichert
* [OPUSVIER-2078] - RIS-Export enthält nicht-standardkonformes Feld ID
* [OPUSVIER-2098] - Framework-Tests: Unknown Exception in Zusammenhang mit Opus_Enrichment
* [OPUSVIER-2099] - OAI-Set der Dokumenttypen muss nach DINI "doc-type" heißen
* [OPUSVIER-2100] - JEL-Klassifikation wird nicht korrekt abgespeichert
* [OPUSVIER-2101] - Umbenennung von Document_Sets zu Document_Series in der Datenbank durchziehen
* [OPUSVIER-2105] - Nach dem Anlegen eines EnrichmentKey, der einen Slash im Namen enthält, können die entsprechenden Admin-Actions nicht mehr aufgerufen werden
* [OPUSVIER-2111] - Zu lange Felder werden von mySQL ohne Warnung abgeschnitten.
* [OPUSVIER-2123] - Lowercasing von Wildcard Queries vor dem Abschicken der Anfrage an den Solr-Server durchführen
* [OPUSVIER-2136] - Muss/Sollte der ©-Eintrag im Seitenfooter entfernt werden?
* [OPUSVIER-2144] - Link "Letzte Seite" zeigt nur den letzten Eintrag
* [OPUSVIER-2147] - Anzahl der Dokumente wird beim Dokument-Browsing in der Pagination-Leiste nicht angezeigt
* [OPUSVIER-2151] - Feldnamen erlauben nur bestimmte Zeichen
* [OPUSVIER-2164] - xMetaDissPlus: Auf dem CI-System erzeugt der XSL-Prozessor "dc:person" statt "pc:person"
* [OPUSVIER-2165] - Seitentitel der Frontdoor zeigt nicht den Dokumenttitel in der Dokumentsprache an
* [OPUSVIER-2168] - Sämtliche Dokumenttypdefinitionen verstoßen gegen das XML Schema documenttype.xsd
* [OPUSVIER-2172] - Collection-Edit-Formular zeigt Wert 0 für Attribut oai_subset und number nicht an
* [OPUSVIER-2175] - Unter CentOS liefert die OAI-Schnittstelle per XMetaDissPlus falsche Namespaces aus
* [OPUSVIER-2177] - Dokument-ID bei Fehlerausgabe in der import_error.log  angeben
* [OPUSVIER-2184] - Import-Skript arbeitet im Verzeichnis server/workspace
* [OPUSVIER-2193] - XMetaDissPlus-Ausgabe enthält fest-codiertes Element medium
* [OPUSVIER-2199] - Model setzt beim Anlegen eines Dokuments ServerDatePublished, auch wenn sich das Dokument nicht im ServerState published befindet
* [OPUSVIER-2200] - ServerDatePublished wird beim Überführen eines Dokuments in den ServerState published nicht aktualisiert
* [OPUSVIER-2209] - Null-Pointer-Zugriff im Form-Validator löst Fatal Error aus, wenn für eine CollectionRole name != oai_name bzw. keine CollectionRoles existieren
* [OPUSVIER-2220] - falscher Name für das Ablageverzeichnis der XML-Dokumenttypdefinitionen
* [OPUSVIER-2248] - "URN vorhanden" Validierung greift zu früh beim Veröffentlichen über das Publish-Formular
* [OPUSVIER-2258] - ein Dokument kann in unterschiedlichen Schriftenreihen nicht die gleiche Bandnummer haben
* [OPUSVIER-2261] - Nach dem Speichern von Opus_Document werden greifen weitere Änderungen nicht mehr beim zweiten Speichern
* [OPUSVIER-2265] - Geschütztes Leerzeichen in Facettenanzeige aufnehmen, so dass die Dokumentanzahl nicht allein umgebrochen wird
* [OPUSVIER-2280] - Externe Bemerkungen sind nach der Migration nicht öffensichtlich sichtbar
* [OPUSVIER-2290] - update-db.sh beachtet die Variable DRYRUN nicht und führt Datenbank-Update in jedem Fall durch
* [OPUSVIER-2292] - Document->addIdentifierUrn initialisiert Type-Feld nicht korrekt
* [OPUSVIER-2324] - RIS-Export ignoriert Sichtbarkeit von Note
* [OPUSVIER-2326] - Release Notes im Tarball sind leer
* [OPUSVIER-2329] - Symlink db/schema/opus4current.sql wird beim Update nicht in das Instanzverzeichnis kopiert

### Stories

* [OPUSVIER-979] - Alle Opus_Subject*-Klassen durch ein Opus_Subject ersetzen
* [OPUSVIER-1064] - Fehlende Bandnr. für Schriftenreihen
* [OPUSVIER-2012] - Aktuellstes Datenbank-Schema ab jetzt immer in "opus4current.sql"
* [OPUSVIER-2202] - Änderung Feldtyp  - page_number
* [OPUSVIER-2233] - Anzeige von Legal Notices im zweiten Schritt des Publish-Formulars

### Tasks

* [OPUSVIER-175] - Form for creating and editing languages could use hints
* [OPUSVIER-845] - Einstellung eines Defaultwerts bei Angabe von Seitenzahlen
* [OPUSVIER-849] - Angabe des Gesamtbestandes auf der Startseite
* [OPUSVIER-980] - Alle überflüssigen Subject-Felder aus Opus_Document entfernen
* [OPUSVIER-1339] - Übersetzungen der Feldnamen- und werte in der Übersicht für ein Dokument
* [OPUSVIER-1428] - Fehler beim Eintrag einer MSC-Klasse über das Publish-Formular
* [OPUSVIER-1471] - Update-Script: Neue angelegte Dateien sollen dem OPUS4-User gehören
* [OPUSVIER-1548] - Abfangen, wenn kein Titel in der Dokumentsprache eingegeben wurde
* [OPUSVIER-1603] - View und Edit Code vom DocumentsController entfernen
* [OPUSVIER-1722] - Type Feld für TitleAbstract entfernen
* [OPUSVIER-1757] - Dokument-Metadatenadministration: Anzeige der Funktion "Bearbeiten" bei leeren Feldern unterbinden
* [OPUSVIER-1758] - fehlende Übersetzungen in Metadatenadministration bei Edit-Formularen für unterschiedliche Felder
* [OPUSVIER-1776] - Sprachzuordnung bei SWD-Schlagwortfeldern entfernen
* [OPUSVIER-1788] - Änderungen in der OAI-Schnittstelle übernehmen
* [OPUSVIER-1789] - Änderungen im Publish-Modul übernehmen
* [OPUSVIER-1790] - Änderungen im Admin-Modul übernehmen
* [OPUSVIER-1815] - Migration von SubjectDDC bzw. SubjectMSC in die Collections beim Release-Update
* [OPUSVIER-1832] - Abhilfe gegen die lange Laufzeit der Server-Unit-Tests: ein Test-Target pro Modul einrichten
* [OPUSVIER-1834] - Beschleunigung der Unit Tests des IndexControllers des OAI-Moduls
* [OPUSVIER-1844] - Dokument-ID beim permanenten Löschen eines Dokuments wird nicht validiert
* [OPUSVIER-1851] - Schemaänderung Enrichments: globale Registry für die Enrichments anlegen (Modellierung einer n-zu-m statt einer 1-zu-n Relation zwischen Dokumenten und Enrichments)
* [OPUSVIER-1855] - RSS-Feed für Autorensuche anbieten
* [OPUSVIER-1856] - Import-Schnittstelle für Metadaten-Import (keine Dateien)
* [OPUSVIER-1861] - View Skripte für DocumentController zusammenfassen
* [OPUSVIER-1862] - Ungenutzte View-Skripte für den DocumentController entfernen
* [OPUSVIER-1867] - Konfiguration der Felder für Sektionen im Metadaten Formular in INI auslagern und testen
* [OPUSVIER-1868] - Funktionen zur Formatierung von Feldwerten in View Helper verschieben
* [OPUSVIER-1871] - Referenzen wieder ins Metadaten Formular einbinden
* [OPUSVIER-1872] - Feld Relation in Opus_Reference als Selection mit Defaultwerten definieren
* [OPUSVIER-1873] - Übersetzungen für Sprachen in separate TMX Datei im Default Modul verschieben
* [OPUSVIER-1874] - Änderung der Übersetzungsschlüssel für Sprache prüfen und Konflikte auflösen
* [OPUSVIER-1876] - Default Werte für Feld PublicationState in Opus_Document definieren
* [OPUSVIER-1877] - Übersetzung von Feldwerten für Select Boxen (z.B. ServerState, Type)
* [OPUSVIER-1879] - Übersetzung der Dokumententypen durch Unit Test sicherstellen
* [OPUSVIER-1884] - Ist es möglich Opus_Model_Field so zu erweitern, daß man das Opus_Model abfragen kann?
* [OPUSVIER-1895] - Namen von CollectionRoles bei der Zuweisung von Collections übersetzen
* [OPUSVIER-1896] - Eingabefeld für Titel vergrößern
* [OPUSVIER-1897] - Interne Felder wie ServerDateModified und ServerDatePublished sollen nicht editiert werden
* [OPUSVIER-1898] - Funktion von Personen als erstes Feld anzeigen
* [OPUSVIER-1901] - Anzeige, ob ein Dokument einen Volltext besitzt und ob es zur Bibliographie gehört
* [OPUSVIER-1908] - Mechanismus für die Konfiguration von Model Formularen
* [OPUSVIER-1909] - Konfiguration für Model Klassen in dynamisches Formular integrieren
* [OPUSVIER-1911] - Sortierung der Felder greift nicht bei den Edit-Formularen
* [OPUSVIER-1913] - Functionen für das erzeugen von Form Elementen in separate Klasse verschieben
* [OPUSVIER-1915] - Controller Helper Documents um Funktion zum Prüfen einer Dokumenten ID erweitern
* [OPUSVIER-1925] - Ausgabe von Enrichments in der Frontdoor ermöglichen, die nicht escaped wird
* [OPUSVIER-1929] - PDF-Version der Dokumentation automatisch im Rahmen des Release-Prozesses in den Tarball aufnehmen
* [OPUSVIER-1931] - reine PHP-Dateien dürfen kein schließendes ?> Tag besitzen
* [OPUSVIER-1934] - Funktion zum Prüfen von erlaubten Sektionsnamen im Metadaten Überblick
* [OPUSVIER-1935] - DocumentController aufräumen als Vorbereitung für Einbau von Validierung
* [OPUSVIER-1937] - DocumentController Actions für Änderungen des Dokumentenstatus aufräumen
* [OPUSVIER-1938] - DocumentController Funktionen zum Generieren von Formularen überarbeiten
* [OPUSVIER-1941] - Erlaubte Subject Typen im Framework aktualisieren
* [OPUSVIER-1950] - Zugriff auf 'default' Modul darf Rolle 'guest' nicht entzogen werden
* [OPUSVIER-1953] - Actions für Status-Änderungen an einem Dokument in separaten WorkflowController verschieben
* [OPUSVIER-1955] - RewriteRules auf statische Verzeichnisse unterhalb von public entfernen
* [OPUSVIER-1959] - Umsetzung des grundlegenden Workflow-Modelles
* [OPUSVIER-1964] - Link auf die Schriftenreihe in der Frontdoor
* [OPUSVIER-1965] - Framework-Funktionalität bereitstellen, die es ermöglicht eine Menge von Feldern von einem Opus_Document zu entfernen
* [OPUSVIER-1975] - Anzeige der Dokumente einer Schriftenreihe in der Administration
* [OPUSVIER-1977] - Browsing Änderungen bzgl. Schriftenreihen
* [OPUSVIER-1979] - Tabellenschema für Schriftenreihen erstellen
* [OPUSVIER-1981] - Anzeige von Schriftenreihen in der Administrations-Metadatenübersicht für ein Dokument
* [OPUSVIER-1983] - CI-System (server-Targets) häuft mit jedem Build Temp-Dateien an
* [OPUSVIER-1995] - neuer Datatype "Set" für die Dokumenttypen
* [OPUSVIER-1997] - WorkflowController umbauen, so daß alle Statusänderungen über eine Action "changestate" passieren
* [OPUSVIER-2007] - OPUS3-Testdump in server/tests/migration anlegen, so dass Migrationsskript systematisch getestet werden kann
* [OPUSVIER-2008] - Datenbank-Schema jetzt in "opus4current.sql": Install-/Update-Script anpassen
* [OPUSVIER-2010] - Sprachen in Datei 'languages.tmx' im Default Modul definiert
* [OPUSVIER-2011] - Controller Helper für Workflow Funktionalität implementieren
* [OPUSVIER-2013] - Feld für Bandnummer erzeugen
* [OPUSVIER-2014] - Feld Nummer muss auf Existenz hin validiert werden
* [OPUSVIER-2015] - korrektes Abspeichern des neuen Feldes "DocumentSet"
* [OPUSVIER-2018] - Hinweis auf partielles Löschen des workspace in die Dokumentation aufnehmen
* [OPUSVIER-2020] - Status 'audited' für ServerState von Dokumenten zur Datenbank und zum Framework hinzufügen
* [OPUSVIER-2021] - fehlende identifier types in der Dokumentverwaltung
* [OPUSVIER-2030] - Anpassungen am Datenbank-Design: NOT NULL Constraints entfernen; Anpassung Datentyp des Felds number
* [OPUSVIER-2031] - Umbennungen durchführen: Opus_DocumentSets wird zu Opus_Series
* [OPUSVIER-2033] - Modell muss das Setzen der Nummer aktiv einfordern: Abspeichern ohne Nummer muss zu Exception führen
* [OPUSVIER-2040] - Referenzen in Administration verstecken
* [OPUSVIER-2041] - Effiziente Methode zum Ermitteln der Rollen eines Nutzers implementieren
* [OPUSVIER-2052] - Zuverlässig Validierung von Datumseingaben ermöglichen
* [OPUSVIER-2054] - Verwaltung der Enrichmentkeys in der Administration
* [OPUSVIER-2055] - Anpassungen im Metadatenformular der Dokumente zu DocumentEnrichments
* [OPUSVIER-2059] - Aussagekräftige Fehlermeldung in der Adminstration beim Versuch einen doppelten EnrichmentKey anzulegen
* [OPUSVIER-2062] - Anpassung des Migrationsskripts wegen Schemaänderung der Enrichments
* [OPUSVIER-2063] - Anpassungen im Opus-Update-Skript
* [OPUSVIER-2064] - Feld ThesisYearAccepted in der Datenbank anlegen
* [OPUSVIER-2066] - neues Feld thesisYearAccepted im Adminbereich berücksichtigen
* [OPUSVIER-2067] - neues Feld thesisYearAccepted im Publish berücksichtigen
* [OPUSVIER-2068] - neues Feld thesisYearAccepted in der OAI-Schnittstelle berücksichtigen
* [OPUSVIER-2069] - neues Feld thesisYearAccepted für den Import berücksichtigen
* [OPUSVIER-2070] - ein Testdokument pro Standarddokumenttyp
* [OPUSVIER-2072] - UNIQUE-Constraint für DocumentEnrichment-Tripel (document_id, key_name, value) über das Framework gewährleisten
* [OPUSVIER-2073] - Form_Validate_Date im *Publish* Modul einsetzen
* [OPUSVIER-2074] - Form_Validate_Date im Admin Modul einsetzen
* [OPUSVIER-2081] - Anpassung von RIS- und BibTeX-Export-Templates nach Umbau der Schriftenreihen
* [OPUSVIER-2086] - Anzeige von 'Bibliography' in Dokumentbrowsing von Yes/No zu B/- verändern
* [OPUSVIER-2091] - PublicationState aus der Dokumentenübersicht entfernen
* [OPUSVIER-2096] - Übersetzungen für PublicationState entfernen
* [OPUSVIER-2102] - Unit-Tests für Opus_Series, die CRUD-Completeness sicherstellen
* [OPUSVIER-2107] - example.xslt sollte nur noch rudimentäre Metadaten ausgeben
* [OPUSVIER-2115] - Ausgabe der nicht-leeren Collections als OAI SetSpec per ListSets
* [OPUSVIER-2116] - Update der Collection-Tabelle für Änderungen an den SetSpecs
* [OPUSVIER-2119] - Ausgabe der OAI SetSpecs über Verbs "GetRecord"/"ListRecords"
* [OPUSVIER-2120] - Unterstützung für OAI SetSpecs "bibliography:true" und "bibliography:false"
* [OPUSVIER-2121] - Unterstützung für Parameter "set=" für OAI-Verbs ListRecords/ListIdentifiers
* [OPUSVIER-2126] - Nicht mehr benötigte Datei deposit.phtml entfernen oder Prolog ergänzen
* [OPUSVIER-2129] - Anzeige der zugeordneten Schriftenreihen auf der Frontdoor eines Dokuments
* [OPUSVIER-2130] - Migrationsskript muss für die Schriftenreihen nun Opus_Series verwenden (statt der besonderen Collection Role series)
* [OPUSVIER-2131] - Migration der Collection-basierten Schriftenreihen beim Update von OPUS4-Versionen < 4.2
* [OPUSVIER-2132] - Collection Role series aus der Standardauslieferung entfernen
* [OPUSVIER-2133] - Indexerweiterung im Hinblick auf das Schriftenreihen-Browsing
* [OPUSVIER-2134] - Aufnahme weiterer Metadaten für die Catch-All-Suche
* [OPUSVIER-2137] - Sortierbarkeit der Dokumente einer Schriftenreihe nach Bandnummer
* [OPUSVIER-2138] - Attribute isVisible für Schriftenreihen anbieten
* [OPUSVIER-2142] - Sortierung der Schriftenreihen ermöglichen: Attribute sort_order für Schriftenreihen anbieten
* [OPUSVIER-2149] - Release Notes: Änderung von oai_subset durch das Update-Script
* [OPUSVIER-2153] - Zusätzliches Mappen des Feldes "Externe Bemerkung" auf ein Enrichmentfeld
* [OPUSVIER-2159] - Einbindung der Tests ins CI-System
* [OPUSVIER-2160] - Dokument mit vollständig belegten Metadaten zu den Testdaten hinzufügen
* [OPUSVIER-2169] - Dokumenttypdefinition wird aktuell beim Einlesen nicht validiert
* [OPUSVIER-2179] - Überflüssige Namespace-Deklarationen aus OAI-metaPrefix oai_dc entfernen
* [OPUSVIER-2180] - Erlaubte Zeichen für SetSpecs auf [A-Za-z0-9\-_\.!~\*\'\(\)]+ einschränken
* [OPUSVIER-2189] - beim Update auf Version 4.2.0 sollte das Update-Skript die vorhandenen XML-Dokumenttypdefinitionen auf Schema-Konformität überprüfen
* [OPUSVIER-2191] - Alte Subject*-Felder im Migrationsscript durch Subject[Type='...'] ersetzen
* [OPUSVIER-2192] - Alte Subject*-Felder im Modul citationExport durch Subject[Type='...'] ersetzen
* [OPUSVIER-2205] - alphanumerische Eingaben für pageFirst, pageLast und pageNumber zulassen
* [OPUSVIER-2207] - Invalide Dokumenttypdefinition für Testzwecke aufnehmen, die aber nicht mit ausgeliefert wird
* [OPUSVIER-2208] - Unit-Test schreiben, der die Validierung der Dokumenttypdefinitionen sicherstellt
* [OPUSVIER-2221] - Sichtbarkeitseinstellungen der Schriftenreihen in der Anzeige im Publish-Formular respektieren
* [OPUSVIER-2222] - Sichtbarkeitseinstellungen der Schriftenreihen im Browsing respektieren
* [OPUSVIER-2223] - Sortierung der Schriftenreihen bei der Anzeige im Publish-Formular berücksichtigen
* [OPUSVIER-2224] - Sortierung der Schriftenreihen beim Browsing berücksichtigen
* [OPUSVIER-2225] - Attribut logo für Schriftenreihen entfernen
* [OPUSVIER-2228] - Sortierreihenfolge bei der Anzeige der Dokumente einer Schriftenreihe berücksichtigen
* [OPUSVIER-2229] - Clean-Up-Operation in der Datenbank durchführen nach der Migration der Collection-basierten Schriftenreihen
* [OPUSVIER-2231] - Abhängigkeiten auf subject_msc im Index-Schema entfernen
* [OPUSVIER-2232] - Sichtbarkeitseinstellungen der Schriftenreihen bei der Anzeige auf der Frontdoor respektieren
* [OPUSVIER-2234] - EnrichmentKey LegalNotices aus den Datenbank-Scripten und Testdaten entfernen
* [OPUSVIER-2235] - Enrichments mit dem Schlüssel LegalNotices beim Update von 4.1.4 auf 4.2.0 aus der Datenbank entfernen
* [OPUSVIER-2236] - Anpassung der Übersetzungsressourcen: LegalNotices ist nun kein EnrichmentKey mehr
* [OPUSVIER-2237] - Neuen View-Helper bereitstellen, der die Anzeige der LegalNotices auf der zweiten Formularseite ermöglicht
* [OPUSVIER-2243] - Löschen für spez. EnrichmentKeys unterbinden
* [OPUSVIER-2250] - Funktionalität von 'opus3-migration.sh' in anderen working-directories gewährleisten
* [OPUSVIER-2251] - URN-Bug in Release-Notes aufnehmen
* [OPUSVIER-2252] - URN-Vergabe vorläufig auf Dokumente im Status "published" einschränken
* [OPUSVIER-2253] - Funktionen getDocumentIds und getSortedDocumentIds für Opus_Series hinzufügen (wie bei Collections)
* [OPUSVIER-2256] - Methode zum Abfragen der Existenz einer Bandnummer
* [OPUSVIER-2257] - Update der vorhandenen XML-Dokumenttypdefinitionen
* [OPUSVIER-2263] - import.ini.template / import.ini sollte in migration.ini.template / migration.ini umbenannt werden
* [OPUSVIER-2267] - Schriftenreihen-Logos in die Testdaten aufnehmen
* [OPUSVIER-2269] - Felder publisher und issn aus den Schriftenreihen vorerst entfernen
* [OPUSVIER-2270] - Opus_Series um Methode erweitern, die die Anzahl der Dokumente einer Schriftenreihe zurückgibt
* [OPUSVIER-2271] - Anzahl der einer Schriftenreihe zugeordneten Dokumente in Administration anzeigen und Link ausblenden, wenn keine zugeordneten Dokumente existieren
* [OPUSVIER-2276] - Einblendung eines Warnhinweis vor dem Löschen einer Schriftenreihe
* [OPUSVIER-2277] - Benutzer beim Update über Änderungen im Standardlayout informieren
* [OPUSVIER-2279] - Änderungen in den Konfigurationsschlüsseln und den Logfiles für die Migration übernehmen
* [OPUSVIER-2284] - RSS-Link in Bezug auf die konkrete Suchanfrage in den HTML-Head von Suchergebnisseiten aufnehmen
* [OPUSVIER-2288] - Anlegen der opus4migration-Db auf opus4ci und checkout von cruisecontrol/config.xml
* [OPUSVIER-2289] - URN-Kollision vor einfügen in Datenbank erkennen
* [OPUSVIER-2295] - Anpassungen an update-import.sh beim Update auf OPUS 4.2.0: Verzeichnis server/import nach server/scripts/migration verschoben
* [OPUSVIER-2296] - Anpassungen an update-import.sh beim Update auf OPUS 4.2.0: Behandlung neu hinzugekommener Verzeichnisse
* [OPUSVIER-2302] - Übersetzungs-Schlüssel für Schriftenreihen anlegen (und alte entfernen!)
* [OPUSVIER-2303] - update-config.sh: Anpassungen der Migrationskonfigurationsdateien
* [OPUSVIER-2309] - geeignete Behandlung von Bandnummern-Konflikten bei der Migration der Collection-basierten Schriftenreihen
* [OPUSVIER-2327] - Sichtbarkeitsstatus der einzelnen Schriftenreihen in der Administration farblich kennzeichnen

### Documentation

* [OPUSVIER-1728] - Dokumentation des neuen Parameters form.first.show_rights_checkbox
* [OPUSVIER-1848] - Dokumentation: manuelles Update muss mit sudo ausgeführt werden
* [OPUSVIER-1849] - Konfiguration des FAQ-Menüeintrags, so dass der Link auf eine externe URL verweist
* [OPUSVIER-1926] - Dokumentation der Ausgabe von Enrichments ohne Escaping
* [OPUSVIER-1991] - Übersetzungen für die Formulare zum Erstellen bzw. Editieren von Sprachen haben sich geändert
* [OPUSVIER-1993] - Beim Browsing der Dokumente in der Administration werden Anzahl der Dateien und Bibliographie (Ja/Nein) angezeigt
* [OPUSVIER-1998] - Import-XML und Import-XSD dokumentieren
* [OPUSVIER-2022] - Dokumentstatus ändern in Administration
* [OPUSVIER-2023] - neue Select Felder Aufforderung für Collections
* [OPUSVIER-2024] - Änderungen an den Dokumenttypen
* [OPUSVIER-2026] - Doku: Updatevorgang beim Import von Metadaten
* [OPUSVIER-2071] - Benutzung des Import-Skripts dokumentieren
* [OPUSVIER-2075] - Import-Konfig-Parameter import.debug.logfile und import.error.logfile werden nicht in der Doku beschrieben
* [OPUSVIER-2106] - Kapitel 2.6: Hinweistext zum Fehlen des URL-Parameters stylesheet anpassen
* [OPUSVIER-2108] - Ergänzungen in der Dokumentation zur Migration
* [OPUSVIER-2118] - Sichtbarkeits-Einstellungen für Collections in der OAI-Schnittstelle
* [OPUSVIER-2122] - Aktualisierung des ER-Schemas nach Schemaänderungen für OPUS 4.2.0 und Einstellen ins SVN
* [OPUSVIER-2135] - Anpassung der Dokumentation nach dem Entfernen der Sonder-Collection-Role series
* [OPUSVIER-2154] - "Anzuzeigende Metadaten-Felder im OAI-Set" aus Dokumentation entfernen
* [OPUSVIER-2166] - Anlegen der globalen Enrichment(key)s dokumentieren
* [OPUSVIER-2182] - Mögliche Werte für das Attribut formelement in der Doku ergänzen
* [OPUSVIER-2183] - Liste der möglichen Datatypes in der Datei documenttype.xsd mit der Dokumentation abgeleichen und Beschreibung einfügen
* [OPUSVIER-2215] - Hinweise zum Anlegen neuer Dokumenttyp-Definitionen
* [OPUSVIER-2216] - Element required-if-fulltext in Dokumenttypdefinition wird in Dokumentation nicht beschrieben
* [OPUSVIER-2217] - Wie wird eine Schriftenreihe (Series) in der Dokumenttypdefinition spezifiziert?
* [OPUSVIER-2218] - Hinweis zur Validierung der XML-Dokumenttypdefinitionen in 4.2.0-Releasenotes aufnehmen
* [OPUSVIER-2230] - Hinweis zur Migration der Collection-basierten Schriftenreihen in die Release Notes aufnehmen
* [OPUSVIER-2238] - Dokumentation der Konfiguration der Anzeige von LegalNotices
* [OPUSVIER-2244] - spez. EnrichmentKeys dokumentieren
* [OPUSVIER-2266] - Ablageort der Schriftenreihen-Logos in der Doku beschreiben
* [OPUSVIER-2285] - Beschreibung der Funktion RSS-Feeds auf beliebige Suchanfragen in Doku aufnehmen
* [OPUSVIER-2287] - Neue Datei mit Layout Änderung beim Update dokumentieren
* [OPUSVIER-2316] - fehlender Dokueintrag: Konfigurationsmöglichkeit der Facettenreihenfolge
* [OPUSVIER-2322] - Beschreibung der Inhalte der Datei UPDATE-conflicts.log (früher: conflicts.txt) in Doku nachziehen
* [OPUSVIER-2325] - Eintrag in den Releasenotes, der die Migration der SubjectClassifications (MSC, DDC) beschreibt
* [OPUSVIER-2330] - Grafik Datenmodell aktualisieren

---

## Release 4.1.4 2011-10-18

### Bugs

* [OPUSVIER-1317] - Sichtbarkeitseinstellung einer CollectionRole wird nicht beachtet
* [OPUSVIER-1449] - Unzureichende Validierung von Formularwerten
* [OPUSVIER-1670] - Ausgewählte Option "Belongs to Bibliography" erscheint nicht auf der dritten Formularseite
* [OPUSVIER-1680] - Action publish/deposit/confirm sollte nicht direkt aufrufbar sein
* [OPUSVIER-1695] - Frontdoor: Leere Zeile "Tag" wenn kein subjects vergeben
* [OPUSVIER-1703] - Publish-Formular: Anzeige hochgeladener Dateien, fehlendes Leerzeichen
* [OPUSVIER-1725] - Anzeigefehler in /admin/access/listmodule in Chrome
* [OPUSVIER-1735] - Frontdoor: Feld "PublicationVersion" existiert anscheinend garnicht
* [OPUSVIER-1736] - Frontdoor: Feld-Werte sollen nicht übersetzt werden
* [OPUSVIER-1737] - Sortierreihenfolge der Lizenzen hat keine Wirkung
* [OPUSVIER-1738] - OAI-Schnittstelle missachtet das Datei-Flag VisibleInOai-Flag bei der *zweiten* Datei eines Dokuments
* [OPUSVIER-1744] - Aufruf von admin/document/delete/docId/ mit ungültiger Doc-ID möglich (inkl. XSS)
* [OPUSVIER-1747] - RIS-Export mit falschen Typangaben
* [OPUSVIER-1749] - Migration: Nach dem Import sind in der Tabelle document_title_abstracts einige Einträge doppelt
* [OPUSVIER-1750] - Migration: zip-Datei bei Mehrdateiendokument wird falsch umbenannt
* [OPUSVIER-1755] - opus.css: bei der Anzeige von mehr als zwei Service-Buttons fehlender Abstand zwischen den einzelnen Buttons
* [OPUSVIER-1756] - Frontdoor zeigt nicht alle Metadaten eines Dokuments an
* [OPUSVIER-1771] - Ist-Prüfsummen von hochgeladenen Dateien werden im Filemanager nicht angezeigt
* [OPUSVIER-1779] - Anzeige der Dateinamen in der Frontdoor
* [OPUSVIER-1785] - Anzeige der ausgewählen Collection auf der letzten Seite des Publish-Formulars: alle Ebenen werden angezeigt
* [OPUSVIER-1786] - Abspeicherung der Collections: bei DDC/MSC werden die letzten beiden Ebenen abgespeichert
* [OPUSVIER-1799] - Publish: Anzeige der Sprachen benutzt falsche Ressource
* [OPUSVIER-1819] - Frontdoor hält Templates für nicht (mehr) benutzte Felder vor
* [OPUSVIER-1823] - Navigation der Collections im Adminbereich berücksichtigt nicht die Übersetzungsressourcen
* [OPUSVIER-1826] - leere Lognachricht mit Loglevel error beim Download von Dateien

### Stories

* [OPUSVIER-1807] - DB-Änderungen für die 4.1.4

### Tasks

* [OPUSVIER-715] - Unit-Tests für das PublicationList-Feature erstellen
* [OPUSVIER-835] - Kollektionsauswahl im dritten Formularschritt berücksichtigt nicht die Sichtbarkeitseinstellung von CollectionRole bzw. Collection
* [OPUSVIER-1258] - OAI-Schnittstelle kann keine ZIP-Container für die DNB generieren
* [OPUSVIER-1711] - Beim Browsing der Dokumenttypen alphabetisch nach der Sprache sortieren
* [OPUSVIER-1714] - Lizenz Creative Common updaten
* [OPUSVIER-1740] - Transfer-URL
* [OPUSVIER-1741] - MetadataFormat auch als "xMetaDissPlus" in der Schreibweise mit kleinem "x" unterstützen
* [OPUSVIER-1759] - Den Suchindex um "Weitere  Personen" erweitern
* [OPUSVIER-1780] - Die Sprache "Multilanguage" in die Standardausführung aufnehmen
* [OPUSVIER-1781] - Im Migrationsscript die Sprache "Multilanguage" nach "Multilanguage" mappen.
* [OPUSVIER-1782] - Mapping von Opus-Dokumenttypen auf RIS-Dokumenttypen
* [OPUSVIER-1783] - Integration in das export-Modul
* [OPUSVIER-1792] - view Helper aus dem Publish besser strukturieren
* [OPUSVIER-1803] - Konsistentes Mapping auf Enrichmentfelder
* [OPUSVIER-1808] - Anpassungen an den Enrichment-Feldern in SQL
* [OPUSVIER-1809] - Update-Skript implementieren
* [OPUSVIER-1811] - Unterordner 'html' wird nicht migriert
* [OPUSVIER-1812] - SubjectDDC und SubjectMSC aus der Frontdoor entfernen
* [OPUSVIER-1816] - Errors und Warnings bei der Migration in separates Logfile

### Documentation

* [OPUSVIER-1793] - RIS Mapping bei neu angelegten Dokumenttypen
* [OPUSVIER-1794] - OAI Mapping bei neu angelegten Dokumenttypen
* [OPUSVIER-1836] - Release Notes OPUS 4.1.4
* [OPUSVIER-1838] - Migration: Fehlermeldungen in separatem Logfile
* [OPUSVIER-1839] - Migration: Fehlermeldung bei Dokumenten mit zwei sprachidentischen Titeln oder Abstracts
* [OPUSVIER-1802] - Parameter checksum.maxVerificationSize in die Dokumentation aufnehmen

---

## Release 4.1.3 2011-09-07

### Bugs

* [OPUSVIER-883] - unpassende Notifikation nach dem Ausloggen
* [OPUSVIER-884] - Passwortänderung lässt unzulässige Zeichen zu (keine Validierung)
* [OPUSVIER-1507] - Publish-Formular: Unvollständige Projekte und MSC-Klassifikationen möglich
* [OPUSVIER-1551] - Gutachter wird nicht über oai-xmetadissplus ausgeliefert
* [OPUSVIER-1661] - Publish-Formular: Korrekte Behandlung des Falls, wenn kein Button erkannt wurde
* [OPUSVIER-1669] - Fehlender Übersetzungsschlüssel publish_deposit_confirm
* [OPUSVIER-1689] - Fehlende Übersetzungsschlüssel in Dokument-Administration: Titel und Abstract
* [OPUSVIER-1699] - "wrong_identity_error" direkt nach dem Login
* [OPUSVIER-1700] - Publish-Formular: Required-Collections in "preprintmatheon" werden nicht geprüft
* [OPUSVIER-1704] - SWD-  und freie Schlagwörter fehlerhaft migriert
* [OPUSVIER-1705] - Inhalt des Feldes source_swb aus OPUS3 fehlt
* [OPUSVIER-1707] - Lizenzen werden angezeigt, auch wenn sie den Status inaktiv besitzen
* [OPUSVIER-1710] - Harvesten der Dokumente zur DNB
* [OPUSVIER-1720] - Dokument kann nicht permanent gelöscht werden, wenn in Datenbank noch mindestens eine Datei assoziiert ist, die aber nicht (mehr) im Dateisystem liegt

### Stories

* [OPUSVIER-1702] - Publish-Formular: Session-Variable "depositConfirmDocumentId" verschwunden

### Tasks

* [OPUSVIER-1056] - Workflow für die Migration von URNs
* [OPUSVIER-1204] - Update-Script dokumentieren
* [OPUSVIER-1279] - Rechte-Umbau: Unit-Tests für Frontdoor-Fehlerfälle hinzufügen
* [OPUSVIER-1304] - Unit-Tests für Opus_UserRole erstellen
* [OPUSVIER-1306] - Unit-Test für Rückgabewert von Opus_UserRole
* [OPUSVIER-1554] - Migration von Nicht-Opus4-Klassifikationen
* [OPUSVIER-1635] - Übersetzungsressourcen für die Collection Roles in Modul publish entfernen und auf die Ressourcen im Modul default zugreifen
* [OPUSVIER-1649] - Klassen-Struktur der Formulare verbessern
* [OPUSVIER-1660] - Publish-Formular: Unit-Test für gedrückte Buttons in /publish/form/check/
* [OPUSVIER-1678] - Nach dem Logout nicht auf geschützte Seiten weiterleiten
* [OPUSVIER-1685] - Changelog für die Releases 4.0.0 nachtragen und für 4.0.1 und 4.0.2 erweitern
* [OPUSVIER-1732] - CollectionRole-spezifische Übersetzungsschlüssel für Up-/Down-Button entfernen und durch einen generischen Schlüssel ersetzen

### Documentation

* [OPUSVIER-1633] - Installations-Script: Section mit User-konfigurierbare Variablen erstellt
* [OPUSVIER-1724] - Anlegen einer neuen Collecrtion Role: Welche Änderungen sind bezogen auf das Publish-Formular erforderlich?
* [OPUSVIER-1729] - Config Parameter form-first.numberoffiles wurde entfernt!
* [OPUSVIER-1731] - Dokumentation des neuen Datatypes "CollectionLeaf" für die Dokumenttypen

---

## Release 4.1.2 2011-08-08

### Bugfixes

* [OPUSVIER-232] - Opus_Model_Xml_Version2 crashes
* [OPUSVIER-729] - Frontdoor Unit Tests funktionieren nicht, weil Opus_Statistic_LocalCounter HTTP Header erwartet
* [OPUSVIER-791] - Datei immer noch da nach dem löschen im Dateimanager
* [OPUSVIER-863] - Beim hochladen von Dateien gehen die zugeordneten Collections verloren
* [OPUSVIER-883] - unpassende Notifikation nach dem Ausloggen
* [OPUSVIER-1067] - Keine Rückmeldung beim Hochladen von zu großen Dateien im Filemanager
* [OPUSVIER-1071] - 404-Fehler bei der Auflistung von Volltexten in der Frontdoor für deren MIME-Type kein Icon hinterlegt wurde
* [OPUSVIER-1112] - Übersetzungsschlüssel im Adminmodul korrigieren
* [OPUSVIER-1116] - Fehler im Bereich "Metadaten ändern" im Admin-Modul: Sprache bei Schlagworten
* [OPUSVIER-1139] - Abbruch mit Fehler beim Indexieren
* [OPUSVIER-1141] - Zend_Date vertauscht Monat und Tag wenn Locale nicht gesetzt
* [OPUSVIER-1284] - Werte für die Anzeige von Sammlungen werden nicht korrekt übersetzt
* [OPUSVIER-1320] - Filemanager: Verwirrende Warnungen bei Anzeige von Dateien mit GPG-Signaturen
* [OPUSVIER-1324] - XSS in Frontdoor-Fehlermeldung möglich
* [OPUSVIER-1338] - Abspeichern von unzulässigen Werten für das Feld Sprache
* [OPUSVIER-1390] - Umschalten von public/private beim Bemerkungsfeld fkt. nicht
* [OPUSVIER-1428] - Fehler beim Eintrag einer MSC-Klasse über das Publish-Formular
* [OPUSVIER-1434] - Veröffentlichen eines Dokuments mit 10+ Autoren
* [OPUSVIER-1436] - falscher Objekttyp beim Ändern/Neuanlegen eines Abstracts im Adminformular
* [OPUSVIER-1466] - Sprache für Subject wird nicht korrekt angezeigt
* [OPUSVIER-1467] - Sprachen von Subjects werden nicht in der Frontdoor angezeigt
* [OPUSVIER-1469] - ServerDateUnlocking: Bereits entferntes Feld wird noch im Admin-Bereich benutzt
* [OPUSVIER-1474] - Dateidownload funktioniert nach der Installation über Deb-Package nicht
* [OPUSVIER-1477] - Fehler bei der Übermittlung eines Felder mit 'edit="no"'
* [OPUSVIER-1480] - 302-Redirect auf /, wenn Apache keine Schreibrechte auf opus-console.log besitzt
* [OPUSVIER-1488] - Uninstall-Script: Fehler falls "OPUS4_DB_NAME" leer
* [OPUSVIER-1490] - Sichtbarkeitseinstellung von Collections wird auf Frontdoor nicht beachtet
* [OPUSVIER-1494] - Unschöne Fehlermeldung beim Zugriff auf nicht vorhandene Ressourcen in server/public
* [OPUSVIER-1504] - Breadcrumbs im Adminbereich uneinheitlich
* [OPUSVIER-1505] - "Last Page" in Pagination liefert keine Ergebnisse
* [OPUSVIER-1506] - Publish-Formular: Hart kodierte Bezeichnung "Dokumenttyp" in allen Formularen
* [OPUSVIER-1508] - Publish-Formular: "missing fields", sobald man versucht ein Projekt auszuwählen
* [OPUSVIER-1511] - Keine Eingabe im Datumsfeld für Jahre vor "1900" möglich
* [OPUSVIER-1512] - Erscheinungsjahr wird bei der Migration von OPUS3 nach OPUS4 nicht übernommen
* [OPUSVIER-1514] - Admin-Modul, Dokumente Verwalten: Sortierung nach Author führt zur Ausblendung des Autors
* [OPUSVIER-1516] - Frontdoor-Seite validiert nicht nach XHTML
* [OPUSVIER-1517] - Zeile für Freie und SWD Schlagwörter zu lang
* [OPUSVIER-1518] - Originaldateien sollen nicht im Frontdoor angezeigt werden
* [OPUSVIER-1519] - Lizenzen werden nach der Migration nicht korrekt zugeordnet
* [OPUSVIER-1520] - Die Anzeige bei der Facette Erscheinungsjahr enthält nicht zugeordnete Jahre
* [OPUSVIER-1521] - Doppeltes Escaping im File Manager des Admin Moduls
* [OPUSVIER-1522] - Migrationsskript legt Logdatei opus-console.log mit falschen Permissions an
* [OPUSVIER-1529] - Fehler auf der Frontdoor
* [OPUSVIER-1531] - Falscher Redirect im Adminformular
* [OPUSVIER-1541] - Fehlender Übersetzungsschlüssel in den Admin-Formularen, wenn Fehlernachricht angezeigt wird
* [OPUSVIER-1545] - Unklarer Übersetzungsschlüssel im Publish-Formular
* [OPUSVIER-1546] - Fehler nach dem Abspeichern eines Dokuments mit SWD-Schlagwort "foo"
* [OPUSVIER-1549] - Fehlerhafter Aufruf von "createdb.sh" im Migrationsscript: 'printf %q'
* [OPUSVIER-1553] - Mail-Versand in der Frontdoor prüft Dokument-Berechtigungen nicht
* [OPUSVIER-1557] - OAI-Schnittstelle spuckt den Pfad der Instanz im Dateisystem aus, wenn workspace/tmp/resumption für Apachen nicht schreibbar ist
* [OPUSVIER-1559] - OAI-Schnittstelle: from/until-Parameter werden nicht korrekt ausgewertet
* [OPUSVIER-1560] - OAI-Schnittstelle ignoriert from-Parameter, wenn until-Parameter fehlt
* [OPUSVIER-1565] - leeres Element xmlns in der oai-epicur-Ausgabe
* [OPUSVIER-1568] - Kann bei der Migration die oberste Ebene der Institute wegfallen
* [OPUSVIER-1569] - Verbesserung des Ranking der Ergebnisse bei der Standardsuche
* [OPUSVIER-1573] - führendes Leerzeichen im dc:creator Feld entfernen
* [OPUSVIER-1574] - OAI-Schnittstelle, epicur: Dokumente ohne URN dürfen nicht mit ausgegeben werden
* [OPUSVIER-1577] - MSC-Classification: auf letzter Ebene erscheint Freitextfeld
* [OPUSVIER-1579] - Apache liefert falsche PDFs aus
* [OPUSVIER-1580] - Verschobenes Layout bei Dokumenten ohne Abstract
* [OPUSVIER-1582] - Datei exisitert bereits oder kann nicht gespeichert werden.
* [OPUSVIER-1584] - SolrSearch: All-Documents sortiert *aufsteigend* nach *ID*
* [OPUSVIER-1586] - Security: Download von Dateien möglich , wenn freigegebene IP-Range bekannt
* [OPUSVIER-1590] - Fehlermeldung beim Hochladen nicht zugelassener Dateien als solche kennzeichnen
* [OPUSVIER-1594] - www-data hat keine Schreibrechte für migrierte Dokumente
* [OPUSVIER-1616] - Installations-Script: "chown" schlägt unter Ubuntu fehl, wenn keine gleichnamige Gruppe existiert
* [OPUSVIER-1631] - Fehler beim Zugriff auf Dokument 123 im Adminbereich (/admin/document/index/id/123)
* [OPUSVIER-1640] - Security Realm durch Interface abstrahieren um den Realm für Tests mocken zu können
* [OPUSVIER-1642] - Keine Lizenzen zur Auswahl im Adminbereich
* [OPUSVIER-1643] - Filebrowser im Admin-Bereich überprüft Dateitypen (ps Dateien können nicht importiert werden)
* [OPUSVIER-1646] - Filemanager: Dateinamen werden falsch/nicht escaped
* [OPUSVIER-1651] - Dateinamen vor dem Abspeichern in der Datenbank mit urldecode behandeln
* [OPUSVIER-1656] - direkten Aufruf von /solrsearch/index/results verhindern, z.B. durch Redirect auf /solrsearch/index/index
* [OPUSVIER-1657] - ErrorController wirft Exception wenn der Error Code einer Exception kein HTTP Error Code ist
* [OPUSVIER-1658] - Fehler beim Löschen einer Collection, die mindestens ein Kind besitzt
* [OPUSVIER-1674] - Updateskript muss Zend-Caches (Translation, DB) löschen
* [OPUSVIER-1675] - Dokumentation: Aufnahme eines weiteren Pakets unter Ubuntu 11.04
* [OPUSVIER-1676] - cd-Befehl in Dokumentation Kap. 5.2.6 und Kap. 5.3.6 anpassen

### Stories

* [OPUSVIER-517] - Klärung von bibliothekarischen Fragen
* [OPUSVIER-801] - Änderungen von Dokumenten
* [OPUSVIER-894] - File-Manager: Link auf Volltext fehlt
* [OPUSVIER-978] - Publish-Formular: Mehrfach belegte TMX-Schlüssel
* [OPUSVIER-1213] - Print on Demand (PoD) Funktion
* [OPUSVIER-1373] - Löschen der Dateien im Incoming-Verzeichnis
* [OPUSVIER-1515] - SolrSearch: Nicht-erreichbarer Solr-Server sollte 503 Service Unavailable liefern
* [OPUSVIER-1552] - Performance-Optimierung in der Frontdoor: Opus_Document nur einmal laden
* [OPUSVIER-1564] - Kapitel "Finalizing your OPUS4 installation": PHP-Module "suhosin" und "xdebug"
* [OPUSVIER-1591] - DB-Host- und DB-Port-Settings überarbeiten
* [OPUSVIER-1600] - Rewritemap-Datei-Download durch robusteren Mechanismus ersetzen
* [OPUSVIER-1601] - Frontdoor: Dateien sollen nach Label sortiert angezeigt werden
* [OPUSVIER-1636] - Doku aufnehmen: Anlegen einer neuen Collection Role / Sammlung

### Tasks

* [OPUSVIER-304] - Validierung von Titel und Abstract in Abhängigkeit von der Dokumentsprache
* [OPUSVIER-324] - Security: Hochgeladene PHP-Dateien werden ausgeführt
* [OPUSVIER-873] - Mehrfachbelegt Felder in einer Tabellenzeile zusammenfassen
* [OPUSVIER-874] - Personennamen: Vorname Nachname (ohne Komma)
* [OPUSVIER-973] - Behandlung von Volltexten, die einer Zugriffsbeschränkung unterliegen
* [OPUSVIER-1104] - Migrationsscript: Behandlung geänderter Download-Pfade
* [OPUSVIER-1267] - Google-Scholar in module frontdoor aktivieren
* [OPUSVIER-1307] - Unit-Tests für Opus_File_Plugin_GuestAccess erstellen
* [OPUSVIER-1354] - Feld *CompletedYear* kann nicht auf NULL gesetzt werden
* [OPUSVIER-1411] - Datumsformat ISO_8601 in Opus_Date erzwingen
* [OPUSVIER-1423] - Alte OPUS3-Migrations-Dokumentation durchsehen und ggf. löschen
* [OPUSVIER-1454] - Übersetzungen für die CollectionRoles in der Collection-Administration anzeigen
* [OPUSVIER-1502] - Dokument-Metadaten bearbeiten: Aufspalten von "edit field" und "add field" im Dokument-Formular
* [OPUSVIER-1509] - Code-Duplication und Division-By-Zero im Solrsearch_IndexController beseitigen
* [OPUSVIER-1523] - Admin-Navigation: Benutzerverwaltung
* [OPUSVIER-1526] - Testdokument anlegen für "MailToAuthor" Funktion
* [OPUSVIER-1527] - DNB-Institution in den Testdaten aufnehmen
* [OPUSVIER-1532] - Update-Skript muss die Verschiebung der Rewrite-Rule aus der Apache-Config nach .htaccess beachten und Apache neustarten
* [OPUSVIER-1534] - Multiplicity für Felder ThesisPublisher und ThesisGrantor anpassen
* [OPUSVIER-1535] - Schlüssel für die Group-Felder bei ThesisPublisher und ThesisGrantor
* [OPUSVIER-1547] - nicht mehr verwendete Codeteile in Zusammenhang mit der Abschaffung der Sammlungsauswahl im dritten Formularschritt aus Codebase entfernen
* [OPUSVIER-1548] - Abfangen, wenn kein Titel in der Dokumentsprache eingegeben wurde
* [OPUSVIER-1570] - Index-Schema-Änderung in OPUS 4.1.2 propagieren und Indexneubau anstoßen
* [OPUSVIER-1593] - Admin darf beliebige Dateitypen hochladen
* [OPUSVIER-1596] - Install-Skript muss Kommentarzeichen in config.ini und createdb.sh beim Setzen von DB-Host und -Port entfernen
* [OPUSVIER-1598] - Defaultwerte für DB-Host und -Port im Installskript anpassen
* [OPUSVIER-1602] - Aufräumen von DocumentsController und DocumentController
* [OPUSVIER-1605] - Für Document URLs den neuen View Helper anstatt des Review DocumentAdapter verwenden
* [OPUSVIER-1606] - Lange Schlagwortketten in der Frontddoor brechen nicht um
* [OPUSVIER-1609] - Neuer Datei-Download: Fehlerbehalung/Fehler-Seiten
* [OPUSVIER-1610] - Verschobenes Layout bei Dokumenten ohne Volltexte
* [OPUSVIER-1619] - Verschobenes Layout bei Dokumenten ohne Titel
* [OPUSVIER-1620] - Unit Test für neuen Delivery Mechanismus entwickeln
* [OPUSVIER-1621] - Auftrennung der freien Schlagwörter nach Sprache
* [OPUSVIER-1622] - erster Schritt des Refactoring
* [OPUSVIER-1623] - OAI-Schnittstelle: Zurückgegebener Zeitstempel ungleich dem per from-until abgefragtem Zeitstempel
* [OPUSVIER-1625] - Neuer Datei-Download: Klasse RewriteMap/Apache, etc. entfernen
* [OPUSVIER-1626] - Neuer Datei-Download: Im Installer nachziehen
* [OPUSVIER-1627] - Neuer Datei-Download: Im Update-Script nachziehen
* [OPUSVIER-1628] - Neuer Datei-Download: Weiterer Fehlerfall Datei existiert in DB, aber nicht im Dateisystem
* [OPUSVIER-1629] - Neuer Datei-Download: gelöschte Abhängigkeit zu mod_proxy aus Deb-Package entfernen
* [OPUSVIER-1632] - Übersetzungsschlüssel für die Frontdoor beim Anlegen neuer Sammlungen soll auch aus dem default-Modul kommen
* [OPUSVIER-1638] - Schitt 2: Session weiter entlasten
* [OPUSVIER-1652] - Verbessertes Logging/Debugging bei der Migration
* [OPUSVIER-1653] - Verbesserung: benutze statt temporärer Dateien besser inline replacing beim Aufruf von sed
* [OPUSVIER-1654] - Anpassung des Deinstall-Skripts nach Überarbeitung von DB Host und Port
* [OPUSVIER-1655] - Fehlerbehandlung im Deinstallationsskript einfügen, wenn Konfigurationsdateien nicht existieren

### Documentation

* [OPUSVIER-1020] - Anzeige der hochgeladenen Dateien
* [OPUSVIER-1330] - Kapitel 8.3 Dokumenttypen überarbeiten
* [OPUSVIER-1383] - Hinweis in Doku aufnehmen wie man Anzeige der Revision-Nummer auf den Webseiten verhindern kann
* [OPUSVIER-1407] - Dokumentation erlaubter Zeichen
* [OPUSVIER-1410] - Config-Parameter "startmodule" aus der Doku entfernen
* [OPUSVIER-1424] - Hinweis in Dokumentation, dass ein Mailserver benötigt wird
* [OPUSVIER-1432] - Dokumentation update-public
* [OPUSVIER-1478] - geänderte Download-Pfade
* [OPUSVIER-1483] - Konfigurationsvariable mail.opus.address muss gesetzt sein, damit OAI-Schnittstelle valides Ergebnis liefert
* [OPUSVIER-1487] - Dokumentation: Rewrite-Rule für Datei-Download in .htaccess statt apache-config
* [OPUSVIER-1491] - Hinweis zu den Feldern LinkLogo und LinkLicence aufnehmen
* [OPUSVIER-1495] - Formatierungsanpassungen in der Doku
* [OPUSVIER-1537] - Übersetzungsschlüssel für CollectionRoles-Bezeichnungen nach Module default verschoben
* [OPUSVIER-1539] - Ablageort der Übersetzungsdatei für CollectionRoles wurde geändert
* [OPUSVIER-1595] - Anpassung der Dokumentation nach Änderungen in config.ini.template
* [OPUSVIER-1597] - Anpassung der Ausgaben des Installskriptes zu DB-Host und -Port
* [OPUSVIER-1599] - Anpassung der Beschreibung zur manuellen Installation (im Zusammenhang mit DB-Host und DB-Port)
* [OPUSVIER-1612] - Mapping Felder pro Dokumenttyp im Anhang der Doku ergänzen
* [OPUSVIER-1624] - Neuer Datei-Download: Dokumentations-Änderungen
* [OPUSVIER-1634] - Änderung des Translation Keys für die Übersetzung der Collection Roles in Module admin, frontdoor und browsing
* [OPUSVIER-1644] - Hinweis zum Löschen der Dateien im Verzeichnis workspace/incoming aufnehmen
* [OPUSVIER-1645] - Neue Sammlungen als Browsing-Felder im Publikationsformular
* [OPUSVIER-1664] - Dokumentation Migration Zugriffsbeschränkung
* [OPUSVIER-1665] - Dokumentation Migration Lizenzen

---

## Release 4.1.1 2011-06-03

### Bugfixes

* [OPUSVIER-752] - Wiederherstellen (Publishing) eines gelöschten Dokumentes von der Metadata Seite erzeugt SOLR Exception
* [OPUSVIER-810] - Alternatives Startmodul wird nicht verlinkt
* [OPUSVIER-852] - Einbringen einer neuen Datei im Dateimanager trotz Fehlermeldung
* [OPUSVIER-864] - OutOfMemory-Fehler beim Zuordnen der Collection "bk" zu einem Dokument
* [OPUSVIER-885] - Einige Opus Modelle produzieren Fehler wenn man *__toString* für neue noch "leere" Instanzen aufruft
* [OPUSVIER-926] - OAI-Schnittstelle wirft Exception, wenn ungültige Strings in server_date_published stehen
* [OPUSVIER-998] - Datei-Download: Zugriff nicht nach Benutzername steuerbar
* [OPUSVIER-1006] - Form_Builder überschreibt NULL-Werte wenn leere Eingabefelder übermittelt werden
* [OPUSVIER-1048] - Exception in Collection-Auswahl beim Reload von publish/collection/sub
* [OPUSVIER-1155] - Cache-Dateien liegen in "/tmp"
* [OPUSVIER-1311] - User-Feedback: "php-mbstring" standardmäßig nicht installiert unter SuSE
* [OPUSVIER-1316] - Collection-Auswahl im Publikationsformular Schritt 2: Nicht ausgewählte Collection kann nicht mehr rückgängig gemacht werden
* [OPUSVIER-1325] - XSS in Collectionauswahl möglich
* [OPUSVIER-1332] - Titel fehlt, wenn ein Dokument von restricted zu published gesetzt wird
* [OPUSVIER-1337] - Dokumentsprache wird in section/general nicht übernommen
* [OPUSVIER-1345] - Tarball Versionsnummer maskieren
* [OPUSVIER-1346] - Publish-Formular: HTML-Sonderzeichen werden falsch abgespeichert.
* [OPUSVIER-1348] - falsche Verlinkung zum alten Admin-Formular
* [OPUSVIER-1351] - Script opus-dump-document-xml.php benutzt XML-Cache nicht
* [OPUSVIER-1363] - Reihenfolge der Autoren eines Dokuments nicht definiert
* [OPUSVIER-1365] - Problem mit server_date_created
* [OPUSVIER-1369] - Syntaxfehler im Datenbank-Updateskript
* [OPUSVIER-1372] - nichtexistierende Grafik zu Lizenzen auf der Frontdoor
* [OPUSVIER-1385] - Fehler beim Anlegen eines eigenen Layouts
* [OPUSVIER-1388] - postinst Skript im Deb-Package enthält Windows-Steuerzeichen 0D0A
* [OPUSVIER-1399] - kein Frontdoor-Zugriff für guest nach Update von 4.0.2 auf 4.1.0
* [OPUSVIER-1405] - neue Sprachen erfordern Anpassungen in den fieldnames.tmx
* [OPUSVIER-1429] - Deb-Package: install_ubuntu.sh: line 19: ./install.sh: No such file or directory
* [OPUSVIER-1433] - 403-fehlermeldung: "server" statt "sever"
* [OPUSVIER-1435] - E-Mailversand an Autoren funktioniert nicht
* [OPUSVIER-1438] - Dateidownload funktioniert nicht, wenn im Dateinamen zwei oder mehr zusammenhängende Leerzeichen enthalten sind
* [OPUSVIER-1439] - Deb-Package: Unnötige Dependencies "subversion", "curl", "php-crypt-gpg", "xdebug"
* [OPUSVIER-1444] - Werte im Publish-Formular werden doppelt-escaped in Datenbank gespeichert
* [OPUSVIER-1446] - Schlagwortsprache wird nicht im Publish-Formular abgefragt
* [OPUSVIER-1452] - \$Id\$-Keyword im OPUS-Prolog wird bei einigen Dateien aufgrund nicht gesetzter svn:keywords Eigenschaft nicht gesetzt
* [OPUSVIER-1458] - Abbrechen-Aktion im zweiten Formularschritt führt kein Cleanup durch
* [OPUSVIER-1462] - createdb.sh.template stirbt, wenn Passwort Leerzeichen enthält
* [OPUSVIER-1468] - Publish-Formulare: Feld "ServerDateUnlocking" muss entfernt werden
* [OPUSVIER-1470] - Installation des Deb-Package stürzt unter Ubuntu 11.04 ab
* [OPUSVIER-1472] - Probleme mit der Frontdoor bzw. Statistik (weiße Seite)
* [OPUSVIER-1475] - deletePermanent() sollte Ordner des Dokuments löschen
* [OPUSVIER-1484] - beim Aufruf der Applikation über index.php zerbricht das Layout und index.php verbleibt in URL
* [OPUSVIER-1489] - create-tarball-script: Verzeichnis "workspace/files/error" existiert nicht
* [OPUSVIER-1493] - keine Auslieferung und Ausführung von common.phtml in /public erlauben

### Stories

* [OPUSVIER-995] - FileManager: Upload-Size und erlaubte Dokumenttypen fest vorgegeben
* [OPUSVIER-1094] - Datenbank-Änderungen für die 4.1
* [OPUSVIER-1102] - Anpassungen für geänderten Datei-Download-Pfad

### Tasks

* [OPUSVIER-974] - Zugriffsbeschränkung sollte auf Frontdoor angezeigt werden
* [OPUSVIER-987] - Subject-Unterfeld "Type" wird mit "Document Type" beschriftet
* [OPUSVIER-1151] - Titleimport bei Dissertationen
* [OPUSVIER-1158] - Gesammelte Datenbank-Änderungen für die 4.1
* [OPUSVIER-1236] - Model Opus_File: EmbargoDate
* [OPUSVIER-1243] - Umbau Rechte: Modul "review" fixen und Unit-tests wieder aktivieren
* [OPUSVIER-1247] - Rechte-Umbau, Modul Account: Check der Config-Parameter 'account.editOwnAccount' in Controller verlagern
* [OPUSVIER-1260] - Durch Datenbank-Umbau überflüssige Übersetzunggschlüssel entfernen
* [OPUSVIER-1313] - OAI-Schnittstelle: MetadataPrefix "oai_pp" / Proprint Format
* [OPUSVIER-1350] - Fix für ungültige XML-Zeichen in Opus-Modellen: Wodurch soll ersetzt werden?
* [OPUSVIER-1352] - Unit-Tests: Behandlung von ungültigen Zeichen in der Datenbank
* [OPUSVIER-1356] - Update-Skript: SolrIndexBuilder.php erst nach dem Aktualisieren der Datenbank aufrufen
* [OPUSVIER-1374] - Update-Skript darf keine impliziten Annahmen über Installationsverzeichnis machen
* [OPUSVIER-1375] - eingegebene OPUS-Versionsnummer wird nicht validiert
* [OPUSVIER-1377] - Solr-Server muss nach Schemaupdate neu gestartet werden, damit Änderung übernommen wird
* [OPUSVIER-1378] - Update-Skript sollte die durchgeführten Änderungen in Logdatei protokollieren
* [OPUSVIER-1379] - Update-Skript darf nicht MySQL root-Credentials abfragen
* [OPUSVIER-1380] - Auslesen von Werten aus Konfigdateien nicht mit fest-codierter Positionsangabe
* [OPUSVIER-1382] - Update-Skript aktualisiert nicht die Apache-Konfiguration
* [OPUSVIER-1389] - Update-Skript momentan auf Zielversion 4.1.0 zugeschnitten
* [OPUSVIER-1392] - Updateskript stirbt beim Update von OPUS 4.0.x auf OPUS 4.1.0 bei neu hinzugefügten Verzeichnissen
* [OPUSVIER-1393] - Updateskript nicht resistent gegen Fehleingaben
* [OPUSVIER-1396] - Update-Skript kopiert keine neu hinzugefügten Controller
* [OPUSVIER-1397] - Update-Skript fügt nur neu hinzukommende Dateien hinzu, löscht aber keine Dateien, die nicht mehr ausgeliefert werden
* [OPUSVIER-1401] - Update-Skript berücksichtigt nicht alle Modul-Verzeichnisse
* [OPUSVIER-1403] - Neustart des Apachen am Ende des Install-Skripts aufnehmen
* [OPUSVIER-1404] - Update-Skript sollte Datenbank-Verbindungsdaten aus db/createdb.sh
* [OPUSVIER-1412] - Unit-Test: Vertauschen von Tag/Monat in Opus_Date reproduzieren
* [OPUSVIER-1421] - Update Skript sollte in mehrere Skripte zerlegt werden, die Teilaspekte implementieren
* [OPUSVIER-1425] - Update des OPUS Layouts überdenken
* [OPUSVIER-1440] - Rechte-Umbau: Methode "requirePrivilege" aus matheon-Modul entfernen
* [OPUSVIER-1441] - Rechte-Umbau: Methode "requirePrivilege" aus remotecontrol-Modul entfernen
* [OPUSVIER-1442] - Rechte-Umbau: Methode "requirePrivilege" aus Klasse "Controller_Action" entfernen
* [OPUSVIER-1460] - Create backup script for database
* [OPUSVIER-1464] - Opus_Person: Automatisches Setzen von SortOrder beim Hinzufügen neuer Autoren
* [OPUSVIER-1465] - Opus_Person: SortOrder korrekt initialisieren bei "->setPersonAuthor($authors)"

### Documentation

* [OPUSVIER-1327] - Fehler in Kapitel 8.3.1 XML-Dokumenttypdefinitionen
* [OPUSVIER-1328] - Browsing-Felder dokumentieren
* [OPUSVIER-1329] - Fehlender Zeilenumbruch in Kapitel 8.4.3 Anzeige in der Frontdoor und in Kapitel 11.3 Weitere Interaktion
* [OPUSVIER-1331] - Fehler in Kapitel 8.4.1
* [OPUSVIER-1344] - ServerName in Apache-Config eintragen: Kap. 5.2.4
* [OPUSVIER-1366] - Unklarheit in der Beschreibung des manuellen Updates (Kap. 11.2)
* [OPUSVIER-1367] - Fehler in Kap. 2.6 (Export)
* [OPUSVIER-1381] - Fehler in Kap. 5.2.4 (Installation unter Ubuntu ohne Paketverwaltung)
* [OPUSVIER-1384] - Fehler im Kapitel 8.3.2 Templates
* [OPUSVIER-1387] - Fehler in Kap. 8.6 (benutzerspezifische Layouts)
* [OPUSVIER-1402] - Fehler in Doku in Kap. 5.2.1 und 5.3.1 (Installation von php5 unter ubuntu bzw. opensuse)

---

## Release 4.1.0 2011-04-27

### Stories

* OPUSVIER-1233 -   nachträgliches Hinzufügen von Dateien zu Dokumenten
* OPUSVIER-1209 -   Umbau OPUS-Rechtemanagement
* OPUSVIER-1157 -   OAI-Schnittstelle unterstützt Feature "deletedRecord" nicht
* OPUSVIER-1110 -   OpenSearch Support
* OPUSVIER-1096 -   Module "crawlers/sitelinks" soll Dokumente jahresweise anzeigen
* OPUSVIER-1094 -   Datenbank-Änderungen für die 4.1
* OPUSVIER-1092 -   Collections-Methode mit Rückgabe der Parent-Id
* OPUSVIER-1091 -   Frontdoor-Titel: Anzeigen des Dokument-Titels
* OPUSVIER-1089 -   Referee field in frontdoor is not clickable
* OPUSVIER-1088 -   Link-Attribute nofollow,noindex für Such-Crawler
* OPUSVIER-1080 -   Zugriffsrechte
* OPUSVIER-1059 -   Editieren des Bemerkungsfeldes zu einer Datei im Nachhinein nicht möglich
* OPUSVIER-1040 -   Veröffentlichungsformular - Auswahl der DDC-Sachgruppen als Pull-Down-Liste
* OPUSVIER-1039 -   Veröffentlichungsformular - Link zur SWD Schlagwortdatei
* OPUSVIER-1038 -   Erstellen von Schriftenreihen
* OPUSVIER-919  -   Vorlagen für Impressum und Deposit-License erstellen
* OPUSVIER-909  -   Release-Updateskript erstellen
* OPUSVIER-797  -   Kein HTML bei Kontaktinformationen
* OPUSVIER-613  -   OAI-Schnittstelle
* OPUSVIER-184  -   Prüfen und ergänzen der Metadatenformate, die die OAI-Schnitstelle unterstützt

### Specification

* OPUSVIER-895  -   Collections für die Standardauslieferung
* OPUSVIER-438  -   Umgang mit den Zend_Lucene-Relikten

### Documentation

* OPUSVIER-1303 -   Anlegen der Datei opus-apache-rewritemap-caller.sh aus der Doku entfernen
* OPUSVIER-1289 -   Featurebeschreibung für nachträgliche Hinzufügen von Dateien zu Dokumenten
* OPUSVIER-1288 -   neues Verzeichnis workspace/incoming in Dokumentation aufnehmen
* OPUSVIER-1286 -   Komplkationen mit php5-librdf und OAI sowie BibTeX
* OPUSVIER-1234 -   Nutzer-Doku: Individuelle Templates
* OPUSVIER-1221 -   Neues Rechte-System dokumentieren
* OPUSVIER-1218 -   Beschreibung des Moduls Export
* OPUSVIER-1212	-   fehlende OAI-Konfigparameter (aktuell in application.ini)
* OPUSVIER-1196 -   Vorgehen zum Anlegen eines eigenen Layouts
* OPUSVIER-1192 -   Unklarheiten in der Konfiguration des FAQ-Mechanismus
* OPUSVIER-1170 -   Hinweis in Doku, dass einem Dokument maximal eine Bandnummer zugewiesen werden kann
* OPUSVIER-1167 -   Übersetzungsschlüssel für Werte der Bibliographie- und Volltext-Vorhanden-Facette
* OPUSVIER-896  -   Script "change-password.php" in Dokumentation aufnehmen

### Bug Fixes

* OPUSVIER-1314 -   Edit-Links in Dokumentverwaltung funktionieren nicht, wenn Dokument direkt per ID aufgerufen wird
* OPUSVIER-1312 -   MailToAuthor: unerlaubter Mailversand an Autoren möglich durch clientsetige Manipulation des Formulars
* OPUSVIER-1305 -   neu hochgeladene Dateien können zunächst von niemandem gesehen werden (außer Admin)
* OPUSVIER-1296 -   Funktion "mail to Author" funktioniert nicht
* OPUSVIER-1282 -   Beim Speichern einer Sammlung werden alle Zwischenschritte als einzelne Sammlungszuweisungen mitgespeichert
* OPUSVIER-1281 -   Fehler beim Abspeichern einer Sammlung
* OPUSVIER-1278 -   XSS-Vulnerability im CitationExport (sowohl BibTeX als auch RIS)
* OPUSVIER-1277 -   BibTeX-Export: Escaping von Non-ASCII-Zeichen (außer äÄöÖüÜ)
* OPUSVIER-1274 -   CitationExport liefert auch Dokumente aus, die nicht im server_state published sind
* OPUSVIER-1261 -   Suche nach ID nicht bei Sammlungen verwalten anzeigen
* OPUSVIER-1255 -   Frontdoor: Statt Dateiname wird Label-Feld angezeigt
* OPUSVIER-1254 -   Filemanager: Label-Feld kann nicht bearbeitet werden
* OPUSVIER-1252 -   Collectionzuordnung für Dokumente zeigt nicht alle vorhandenen CollectionRoles an
* OPUSVIER-1251 -   fehlende Übersetzungsschlüssel für Seitentitel in Collection/CollectionRole-Administration
* OPUSVIER-1246 -   DNB-Institute werden nicht richtig im Publish-Formular angezeigt
* OPUSVIER-1244 -   Unterschiedlicher Abstand in Footer-Links
* OPUSVIER-1227 -   Script "rebuilding_database.sh" entfernt SVN-Verzeichnisse und bricht "opus4server" auf dem CI-System
* OPUSVIER-1215 -   Export liefert zu wenige Dokumente
* OPUSVIER-1208 -   Application_Exception in /solrsearch/index/search (was caught in Matheon instance)
* OPUSVIER-1205 -   Parameter form.first.requireupload
* OPUSVIER-1188 -   obsolete Schlüssel in home.tmx entfernen
* OPUSVIER-1186 -   rebuilding_database.sh ist abhängig vom momentanen arbeitsverzeichnis
* OPUSVIER-1177 -   Fehler in Bestätigungsnachricht nach Entfernen der Collectionzuordnung zu einem Dokument
* OPUSVIER-1176 -   Nicht angezeigte Collection_Role kann durch Raten der RootCollection-ID dennoch angezeigt werden
* OPUSVIER-1175 -   leere CollectionRole mit zugeordneten published Dokumenten erscheint nicht im Browsing
* OPUSVIER-1174 -   CollectionRole anklickbar, obwohl alle Untercollections auf der ersten Ebene unsichtbar sind und der CollectionRole selbst keine published Dokumente zugeordnet sind
* OPUSVIER-1173 -   Unsichtbarkeit von Collections wird nicht auf die Knoten im Unterbaum propagiert
* OPUSVIER-1168 -   rows-Parameter wird nicht validiert
* OPUSVIER-1165 -   Fehler in Doku 8.5 und 8.6: Kontaktseite bzw. Impressumseite
* OPUSVIER-1159 -   Facettenschlüssel verbleiben nach Entfernen einer ausgewählten Facette in der URL
* OPUSVIER-1155 -   Cache-Dateien liegen in "/tmp"
* OPUSVIER-1146 -   automatische URN Vergabe funktioniert nicht
* OPUSVIER-1143 -   Fehlende Übersetzungsschlüssel
* OPUSVIER-1142 -   Filemanager: Fehler beim Erzeugen der Md5, SHA512 Prüfsummen
* OPUSVIER-1137 -   Installation unter OpenSuse: Anpassung der Variable USER in opus-apache-rewritemap-caller-secure.sh
* OPUSVIER-1127 -   Fehler in RewriteRule in Kapitel 10.4
* OPUSVIER-1115 -   falsches Geburtsdatum bei Personen
* OPUSVIER-1100 -   solrsearch/views/scripts/index/results.phtml(82): Invalid argument supplied for foreach()
* OPUSVIER-1093 -   Model löscht Collections, wenn man die Zuordnung zu einem Dokument entfernt
* OPUSVIER-1087 -   Fehlerhafter Leitlinien-Link in Upload-Formular
* OPUSVIER-1062 -   erweitertes <epicur>-Wurzelelement
* OPUSVIER-1061 -   OAI - Es werden keine URLs bei der Abfrage (epicur) ausgegeben
* OPUSVIER-982  -   Eingeschraenkte Zugriffsberechtigung über IP-Bereiche
* OPUSVIER-971 	-   BibTeX-Export liefert 20-Byte-Datei aus
* OPUSVIER-970 	-   Frontdoor: Volltext-Dateien mit zu langen Dateinamen machen das Layout kaputt
* OPUSVIER-948 	-   Keine Datei-Downloads für unpublished-Dokument möglich (Cookie-Problem!)
* OPUSVIER-935 	-   Auth-Controller verliert Parameter bei Weiterleitung
* OPUSVIER-930 	-   Parameter requireupload funktioniert nicht
* OPUSVIER-928 	-   Seitentitel "OPUS 4 | default_auth_login" bei fehlgeschlagenem Login
* OPUSVIER-903 	-   Zu DDC zugeordnetes Dokument wird nicht im Browsing angezeigt
* OPUSVIER-900 	-   Leitlinien-Link im Formular führt auf Fehlerseite nachdem Formular mit Eingabefehlern zurückgegeben wurde
* OPUSVIER-865 	-   angezeigtes Datum soll CompletedDate sein
* OPUSVIER-712 	-   OAI-Schnittstelle missachtet Flag "VisibleInOai"
* OPUSVIER-652 	-   Uneinheitliche Darstellung bei Freie Schlagworte/Tags
* OPUSVIER-482 	-   Matheon-Instanz: BibTeX nicht okay
* OPUSVIER-354 	-   Aufräumen der Reste von abgebrochenen Dokumentuploads
* OPUSVIER-139 	-   OPUS_Security blockt IPv6

### Tasks

* OPUSVIER-1319 -   Rechte-Umbau: Anzeige von Edit-Button auf Frontdoor kaputt für User != Admin
* OPUSVIER-1318 -   Rechte-Umbau: Anzeige von Admin-Button kaputt für User != Admin
* OPUSVIER-1315 -   Link zur Autorkontaktierung nur anzeigen, wenn mindestens ein Autor existiert, der kontaktierbar ist
* OPUSVIER-1300 -   Änderung von Kommentar, Sprache und Label von Dateien im Dateimanager
* OPUSVIER-1299 -   Link auf Filebrowser in Administration einfügen
* OPUSVIER-1298 -   Rechte-Umbau: Hinzufügen von Dokument-Rechten über die Administration
* OPUSVIER-1297 -   Rechte-Umbau: Hinzufügen von Datei-Rechten über die Administration
* OPUSVIER-1294 -   Änderungen der Rewrite-Regeln in der Dokumentation erwähnen
* OPUSVIER-1293 -   Controller und View für das nachträgliche Hinzufügen von Dateien zu Dokumenten im Admin-Bereich
* OPUSVIER-1291 -   Leitlinien Link
* OPUSVIER-1290 -   Änderung des Verzeichnis des Migrationsskripts
* OPUSVIER-1287 -   incoming Verzeichnis in Tarball aufnehmen
* OPUSVIER-1283 -   Migrations-Scripte in server/scripts in eigenes Verzeichnis migration auslagern
* OPUSVIER-1280 -   Code Duplication in BibTeX-Export entfernen
* OPUSVIER-1272 -   Zuweisung einer Bandnummer im Publish-Formular
* OPUSVIER-1259 -   Rechte-Umbau: Admin->FileHelper enthält noch alten Privilege-Code
* OPUSVIER-1257 -   OAI-Parameter oai.ddb.contactid aus der config.ini-template entfernen
* OPUSVIER-1256 -   Rechte-Umbau: Nicht-benötigte Klasse "Controller_Plugin_ModuleInit" entfernen
* OPUSVIER-1250 -   Rechte-Umbau, Modul Publish: "Opus_Security_Realm::getInstance()->check('publish')" in Controller
* OPUSVIER-1249 -   Rechte-Umbau, Modul Frontdoor: "check('readMetadata', $doc->getServerState()))" in Controller verlagern.
* OPUSVIER-1248 -   Rechte-Umbau, Modul CitationExport: "check('readMetadata', $doc->getServerState()))" in Controller verlagern.
* OPUSVIER-1245 -   Link nur anzeigen, wenn die Collection verknüpfte Dokumente enthält
* OPUSVIER-1239 -   URN-Parameter "urn.autoCreate = true" entfernen
* OPUSVIER-1238 -   Model Opus_Dnbinstitute: neues Feld 'IsPublisher' und neue Methode 'getPublishers'
* OPUSVIER-1237 -   Model Opus_Reference: Relation
* OPUSVIER-1235 -   Model Opus_Document: IdentifierArxiv, IdentifierPubmed, ReferenceOpus4
* OPUSVIER-1228 -   Umbau Rechte: "init.php" aus allen Server-Modulen entfernen
* OPUSVIER-1226 -   LatestDocuments-XHTML mit link-Element auf RSS-Feed anreichern
* OPUSVIER-1225 -   Einbindung RSS-Icon in Latest Documents Browsing
* OPUSVIER-1224 -   Umbenennung der Schlüssel für die Startseite
* OPUSVIER-1223 -   Rechte-Umbau: Model-Klasse für Tabelle "access_modules" erstellen
* OPUSVIER-1222 -   neues Feld server_date_created in Tabelle documents
* OPUSVIER-1220 -   Rechte-Umbau: Check der Datei-Rechte implementieren
* OPUSVIER-1219 -   Rechte-Umbau: Check der Dokument-Rechte implementieren
* OPUSVIER-1217 -   Rechte-Umbau: Check der Modul-Rechte implementieren
* OPUSVIER-1216 -   URL des offiziellen OPUS-Subversion veröffentlichen
* OPUSVIER-1211 -   Dokumentexport durch Anhängen von Parametern in den solrsearch/index/search URLs
* OPUSVIER-1210 -   Zugriff auf Modul export einschränken
* OPUSVIER-1206 -   Update-Skript in Deb-Package einbauen
* OPUSVIER-1204 -   Update-Script dokumentieren
* OPUSVIER-1203 -   Löschen der Übersetzungsschlüssel der gelöschten FAQ-Texte
* OPUSVIER-1202 -   Festlegen der Standard-FAQ-Texte
* OPUSVIER-1201 -   Übersicht, welche Felder in den Templates als group bzw. element ausgewiesen werden müssen
* OPUSVIER-1200 -   MD5SUMS Datei im Wurzelverzeichnis des Tarballs aufnehmen
* OPUSVIER-1199 -   Verzeichnis releases im Wurzelverzeichnis des Tarballs erstellen
* OPUSVIER-1198 -   Datei VERSION.txt im Wurzelverzeichnis des Tarballs erstellen
* OPUSVIER-1197 -   Release-Tagging für apacheconf, solrconfig und install
* OPUSVIER-1195 -   Datei md5sums in Deb-Package aufnehmen
* OPUSVIER-1194 -   Anlegen eines alter-db-skript
* OPUSVIER-1189 -   Schemaänderung propagieren
* OPUSVIER-1187 -   Checkliste vor Liveschaltung einer OPUS 4-Instanz
* OPUSVIER-1182 -   Kapitelüberschriften den Kapiteldtitelblättern hinzufügen
* OPUSVIER-1180 -   Erweiterung der Anzeige in der Dokumentverwaltung
* OPUSVIER-1179 -   Link in der Sammlungsadministration, der alle zugeordneten Dokumente anzeigt
* OPUSVIER-1172 -   Menüpunkte im Breadcrumb abkürzen
* OPUSVIER-1169 -   Anzeige von Bandnummer und zugehöriger Schriftenreihe in Frontdoor
* OPUSVIER-1161 -   Implizite Abhängigkeit zwischen Apache-Config und config.ini
* OPUSVIER-1154 -   Migration der Bandnummern für Schriftenreihen
* OPUSVIER-1150 -   Migration der BKL
* OPUSVIER-1147 -   Erweiterung der Felder für DNB-Institut
* OPUSVIER-1147 -   Erweiterung der Felder für DNB-Institut
* OPUSVIER-1126 -   Anpassung in den RewriteRules für das Umschreiben der alten OPUS 3.x Volltext-URLs (S. 63 ff.)
* OPUSVIER-1125 -   Anpassung Apache Config in der Dokumentation (Seite 32)
* OPUSVIER-1124 -   Anzeige von Bandnummer und zugehöriger Schriftenreihe im Browsing
* OPUSVIER-1122 -   Ausgabe von Suchtreffern als RSS-Feed
* OPUSVIER-1121 -   Anpassung der XML-Ausgabe per XSL-Stylesheets
* OPUSVIER-1120 -   Ausgabe von Suchtreffern im Format OPUS-XML
* OPUSVIER-1119 -   Facettierte Suche innerhalb des Browsings
* OPUSVIER-1117 -   GPL Text zur Distribution hinzufügen
* OPUSVIER-1113 -   Fehler in Anzeige der Suchergebnisseite beim Verkleinern des Browserfensters
* OPUSVIER-1111 -   Bereitstellung eines OpenSearch Deskriptors
* OPUSVIER-1105 -   Installations-Script: Download-Pfad jetzt als Unterverzeichnis "/opus4/files/"
* OPUSVIER-1103 -   Dokumentation anpassen: Config-Schlüssel "deliver.url.prefix" entfernt
* OPUSVIER-1099 -   Neue Schema-Datei anlegen!
* OPUSVIER-1098 -   Model Opus_File: Neues Feld "comment" in Taballe "documents_files"
* OPUSVIER-1097 -   Update-Script: Neues Feld "comment" in Tabelle "documents_files"
* OPUSVIER-1095 -   Update-Script: Neue Datenbank-Constraints für die collections-Tabelle
* OPUSVIER-1065 -   Kommentare auf Dateiebene
* OPUSVIER-1042 -   Änderung der Reihenfolge von Collections auf einer Ebene
* OPUSVIER-1041 -   Veröffentlichungsformular - Auswahl der Schriftenreihen
* OPUSVIER-1037 -   Veröffentlichungsformular - Auswahl der Institute
* OPUSVIER-1018 -   Zuordnung von Collections im zweiten Schritt als Browsing innerhalb des Formulars
* OPUSVIER-988 	-   Anzeigefehler im Opera: blauer Balken im Seitenfooter
* OPUSVIER-976 	-   Layout: message-Element in opus.css ist 1000px breit
* OPUSVIER-975 	-   Menüleiste verschwindet bei Auflösung von 800x600 und kleiner
* OPUSVIER-957 	-   Bugfix in schema.xml erfordert Neuaufbau des Index
* OPUSVIER-947 	-   Anzeigefehler in Chrom(e|ium)
* OPUSVIER-934 	-   Anzeige der Bibliographie- und Volltext-Facette
* OPUSVIER-933 	-   Credits
* OPUSVIER-904 	-   Fehler im Design bei den Breadcrumbs im Browsing
* OPUSVIER-890 	-   Dok.typen für XMetaDiss Auslieferung umbenennen
* OPUSVIER-830 	-   leere Collections nicht anklickbar machen
* OPUSVIER-829 	-   Übersetzungsschlüssel von Collections
* OPUSVIER-821 	-   Suchergebnis "halten"
* OPUSVIER-769 	-   Funktion zum Verändern des Sichtbarkeitsstatus eines Unterbaums
* OPUSVIER-767 	-   Download aller Dokumente mit Flag 'Bibliographie' als Liste
* OPUSVIER-640 	-   Suche Zurücksetzen Button löscht Suchergebnisse
* OPUSVIER-553 	-   Hinzufügen von Dependent-Models für Document<->Collection, CollectionRole<->Collection
* OPUSVIER-423 	-   Export von Suchergebnissen / Publikationslisten
* OPUSVIER-238 	-   Behandlung von Fehlern in Templates
* OPUSVIER-217 	-   Konsolidierung der unterschiedlichen Ablageorte von Klassen mit Solr-Bezug
* OPUSVIER-185 	-   Prüfen der Metadatenformate, die die OAI-Schnittstelle derzeit Unterstützt
* OPUSVIER-153 	-   Asynchrone Indexaktualisierung mittels Cron
* OPUSVIER-113 	-   Bearbeitung der OAI-Resumption-Token in extra Klasse auslagern

---

## Release 4.0.3 2011-02-21

### Bug Fixes

* [OPUSVIER-598] - Ausgewählter Reiter wird nicht immer korrekt angezeigt
* [OPUSVIER-663] - RSS-Export der neuesten Dokumente liefert HTML-Seite aus
* [OPUSVIER-724] - Validierung "required-if-fulltext" funktioniert nicht
* [OPUSVIER-847] - leere Klammern in der Ergebnisanzeige
* [OPUSVIER-906] - Probleme beim Upload weiterer Datei
* [OPUSVIER-920] - Übersetzungsschlüssel anpassen
* [OPUSVIER-929] - Multiplicity bei Instituten läßt sich nicht ändern
* [OPUSVIER-936] - Installations-Skript funktioniert nicht mit openSUSE 11.3
* [OPUSVIER-937] - Installationsprobleme im Zusammenhang mit namebased vhost
* [OPUSVIER-938] - Import
* [OPUSVIER-939] - Migration: Problem mit Sonderzeichen und Klassifikationen
* [OPUSVIER-940] - Problem mit der OPUS-ID
* [OPUSVIER-941] - Problem beim Import von DDC-Sachgruppen
* [OPUSVIER-942] - Import nicht publizierter Dokumente
* [OPUSVIER-944] - XSS im Publish-Formular
* [OPUSVIER-945] - XSS-Vulnerability in Dokumentverwaltung
* [OPUSVIER-946] - Dokumentverwaltung zeigt mehrere Submitter, obwohl nur einer eingetragen wurde
* [OPUSVIER-951] - Login-Mechanismus funktioniert nicht, wenn mehrere Parameter übergeben werden
* [OPUSVIER-952] - Dokumente werden nicht angenommen, wenn rechtliche Hinweise nicht ausgewählt
* [OPUSVIER-953] - Publish-Formular: Allow-Email-Contact per Default immer an
* [OPUSVIER-954] - Publish-Formular: Formular springt bei Änderungen wieder nach oben
* [OPUSVIER-955] - SolrIndexBuilder wirft Zend_Db_Statement_Exception, wenn startID (und endID) angegeben werden
* [OPUSVIER-959] - Formatierungsfehler in Dokumentation: zu lange Zeilen
* [OPUSVIER-960] - Klarstellung zur Migration: Direktupdate von OPUS 3.0 möglich
* [OPUSVIER-963] - "Invalid credentials" - "No token was provided to match against"
* [OPUSVIER-966] - XSS ersten Schritt des Publish-Formulars (Dateiname)
* [OPUSVIER-967] - XSS im Filemanager
* [OPUSVIER-969] - Publish-Formular: Manchmal werden Dateien doppelt hochgeladen
* [OPUSVIER-983] - Fehler bei Migration von OPUS 3.x-Instanzen ohne Collections
* [OPUSVIER-985] - Opus_File: Datei mit selbem Namen überschreibt alte!
* [OPUSVIER-986] - Dateimanager-Crash bei nicht-existierenden Dateien
* [OPUSVIER-989] - Manage documents: Too few columns for the primary key
* [OPUSVIER-990] - Abstract muss required sein wenn Datei hochgeladen wird
* [OPUSVIER-991] - FileManager: "File too large" falls Null-Byte-Datei
* [OPUSVIER-994] - FileManager: Kein Fehler, falls Datei-Ordner nicht schreibbar
* [OPUSVIER-1001] - Opus_Model: ParentId wird nicht korrekt gesetzt
* [OPUSVIER-1002] - Fehlender Übersetzungsschlüssel "admin_filemanager_file_does_not_exist" im Admin-Bereich
* [OPUSVIER-1003] - Apt-Get befehle korrekt einrücken
* [OPUSVIER-1004] - Deb-Package installiert openjdk, obwohl sun-jdk installiert ist
* [OPUSVIER-1005] - hochgeladene Datei wird zweimal abgespeichert
* [OPUSVIER-1008] - Sprungmechanismus im zweiten Formularschritt beim Hinzufügen von neuen Fehlern funktioniert nicht nach dem Zurückkehren aus dem dritten Schritt
* [OPUSVIER-1009] - aussagekräftigere Meldung beim nicht-wiederherstellbaren Löschen eines Dokuments
* [OPUSVIER-1010] - Nicht benutzte Dokument-Feld publication_state in Metadatenverwaltung ausblenden
* [OPUSVIER-1012] - Exception nach Aufruf des Login-Formulars ohne r*-URL-Parameter
* [OPUSVIER-1013] - Aussagekräftige Fehlermeldung im Produktivsystem abstellen
* [OPUSVIER-1014] - OPUS-Installation benötigt Subversion-Client
* [OPUSVIER-1016] - Given resumption path /workspace/tmp/resumption is not a directory.
* [OPUSVIER-1028] - XSS im Publish-Formular (Collections-Auswahl)
* [OPUSVIER-1029] - Fehlende Übersetzungen in Collection-Auswahl
* [OPUSVIER-1030] - XSS im Publish-Formular (Fehlermeldungen)
* [OPUSVIER-1031] - Kein gültiges Datumsformat: "YYYY/MM/YY"
* [OPUSVIER-1032] - Benutzer kann erfolgreich seinen Loginnamen überschreiben
* [OPUSVIER-1033] - Keine Informationen über nicht-existierende Accounts preisgeben
* [OPUSVIER-1034] - Logout-Link unter auth/index funktioniert nicht
* [OPUSVIER-1036] - Publish-Formular nicht resistent gegen abgelaufene / ungültige User-Session
* [OPUSVIER-1051] - Fehler im Bootstrap (Applikation startet nicht) , wenn opus.log für Webserver-Prozess nicht schreibbar
* [OPUSVIER-1054] - Apache Installation/Konfiguration unter openSuSE
* [OPUSVIER-1057] - Error beim Publish von grossen Dokumenten ausserhalb der zulaessigen Dateigroesse
* [OPUSVIER-1058] - Dateiupload von Dateien, die größer 20 MB sind, aktuell nicht möglich
* [OPUSVIER-1069] - Verbesserte Fehlermeldung beim Versuch eine bereits hochgeladene Datei erneut hochzuladen
* [OPUSVIER-1070] - Falsches Sprungziel nach abspeichern einer Collection
* [OPUSVIER-1079] - XSS-Vulnerability in Browsing nach Dokumenttypen

### Improvements

* [OPUSVIER-912] - Feld "Allow Email Contact?" wird immer wieder aktiviert
* [OPUSVIER-968] - Dateien auf Check-Seite des Publish-Formulars werden nicht angezeigt
* [OPUSVIER-1046] - Sichtbarkeit in Google-Scholar
* [OPUSVIER-2] - Instanzspezifische Übersetzungsressourcen
* [OPUSVIER-202] - IgnoreMultiplicity-Hack von aus Opus_Model_Field entfernen
* [OPUSVIER-660] - Eingabemöglichkeit für DDC
* [OPUSVIER-835] - Kollektionsauswahl im dritten Formularschritt berücksichtigt nicht die Sichtbarkeitseinstellung von CollectionRole bzw. Collection
* [OPUSVIER-892] - Innere Knoten von Collections sollen auch zugeordnet werden können
* [OPUSVIER-931] - Abbrechen-Button bei der Sammlungs-Auswahl
* [OPUSVIER-932] - nach erfolgreichem publish-Vorgang kein redirect auf Schritt 1
* [OPUSVIER-943] - Aufnahme der Lizenzen in die Standardauslieferung
* [OPUSVIER-958] - Fehler beim Auswählen einer neu angelegten Collection beim Publish
* [OPUSVIER-961] - Heraustrennung der distributionsspezifischen Teile aus Install-/Uninstall-Skript
* [OPUSVIER-962] - Anpassung in .htaccess durch Install-Skript unter Debian/Ubuntu
* [OPUSVIER-965] - RewriteRule für die Weiterleitung von alten auf neue OPUS-IDs
* [OPUSVIER-981] - Referenzierung der Subjects in solr.xslt ändern
* [OPUSVIER-1019] - Opus_Document aus Session entfernen?
* [OPUSVIER-1023] - Default-Wert für "mehrwertige" Felder
* [OPUSVIER-1044] - Dokumentation des value-Parameters
* [OPUSVIER-1045] - Defaultwerte für "implizite" Unterfelder
* [OPUSVIER-1047] - Anzeige der zuvor ausgewählten Collection im 3. Formularschritt
* [OPUSVIER-1055] - Migration von noch nicht veröffentlichten Dokumenten
* [OPUSVIER-1063] - OAI-Parameter ergänzen
* [OPUSVIER-1068] - Filemanager dokumentieren
* [OPUSVIER-1075] - Definition von eigenen Select-Feldern
* [OPUSVIER-1077] - Hinweis zur Apache-Konfiguration
* [OPUSVIER-1078] - Weiterer Hinweis zur PHP-Konfiguration in php.ini

---

## Release 4.0.2 2010-12-22

### Änderungen

* Erfassung von Änderungen in CHANGES.txt in Tarball

### Behobene Fehler

* Fehler behoben, der dafür sorgte, daß beim Freischalten bzw. Ablehnen
eines Dokumentes die Zuordnung dieses Dokumentes zu Collections verloren ging.
Issue #OPUSVIER-863 - Beim hochladen von Dateien gehen die zugeordneten
Collections verloren

* Für 'PageFirst', 'PageLast' und 'PageNumber' eine spezielle Behandlung in den
FormBuilder eingebaut, die bei leeren Eingabefeldern, diese Werte auf 'null'
setzt, um zu verhindern, das einmal gesetzte Felder auch nach dem entfernen der
Werte in der Frontdoor mit dem Wert '0' auftauchen.
Issue #OPUSVIER-924 - Nach dem Löschen von Seiten Einträgen im
Metadaten-Formular erscheint '0' als Wert

### Bugfixes

* [OPUSVIER-858] - Keine Field Hints bei hinzugefügten Feldern
* [OPUSVIER-1011] - r*-Mechanismus in Sprachumschaltung bzw. Login führt zu Exception
* [OPUSVIER-1015] - Subversion Package-Dependency in deb-Package aufnehmen

---

## Release 4.0.1 2010-11-26

* Layout/CSS Änderungen

* Opus3-Import

* Migration OPUS 3.x

* Fehlende Übersetzungseinträge wurden hinzugefügt

* Korrekte Validierung von Passwörtern in Eingabeformularen im Account
und im Admin Modul

* FormBuilder geändert um bei uninitialisierten Modelinstancen
Exceptions zu verhindern

### Bugfixes

* [OPUSVIER-1011] - r*-Mechanismus in Sprachumschaltung bzw. Login führt zu Exception
* [OPUSVIER-1015] - Subversion Package-Dependency in deb-Package aufnehmen

### Tasks

* [OPUSVIER-804] - Collections aus voherigem Schritt ausblenden

---

## Release 4.0.0 2010-11-15

### Bugfixes

* [OPUSVIER-799] - Klick auf OAI-Links verursacht Error
* [OPUSVIER-818] - Mail an Autor
* [OPUSVIER-822] - Validierung Feld "Herausgeber"
* [OPUSVIER-823] - Übersetzungschlüssel 'solrsearch_index_search' fehlt
* [OPUSVIER-826] - Fehler bei Titel ohne Sprache
* [OPUSVIER-831] - Validierung der Felder
* [OPUSVIER-832] - Validierung der Datumsfelder
* [OPUSVIER-833] - Zuordnung mehrerer Collections bislang nicht möglich
* [OPUSVIER-836] - Einheitlichkeit der Bezeichnung von CollectionRole bzw. Collection sicherstellen
* [OPUSVIER-838] - fehlende Übersetzungsressource bei Kollektionauswahl
* [OPUSVIER-841] - Übersetzungsschlüssel bei fehlgeschlagenem Zugriff auf die Frontdoor fehlen
* [OPUSVIER-842] - Adminmodul: Fehler beim Hinzufügen / Löschen von Feldern
* [OPUSVIER-846] - Ergebnisanzeige: Dokumente ohne Titel
* [OPUSVIER-854] - Fehler beim Erstellen eines Sammlungseintrags
* [OPUSVIER-855] - Jahr erscheint nicht in der Trefferanzeige
* [OPUSVIER-860] - Verschieben von Collections
* [OPUSVIER-861] - Fehler beim Abspeichern eines Dokuments
* [OPUSVIER-935] - Auth-Controller verliert Parameter bei Weiterleitung
* [OPUSVIER-1011] - r*-Mechanismus in Sprachumschaltung bzw. Login führt zu Exception
* [OPUSVIER-1015] - Subversion Package-Dependency in deb-Package aufnehmen

### Stories

* [OPUSVIER-770] - Rewrite-Mechanismus zum Download von Dateien überprüfen
* [OPUSVIER-853] - Matheon-Anfragen: Remotecontrol, Rechte

### Tasks

* [OPUSVIER-68] - Modellierung einer fortgeschrittenen Dokument-Validierung
* [OPUSVIER-73] - Dokumente in Collections zaehlen
* [OPUSVIER-85] - XSS Pen-Tests
* [OPUSVIER-96] - Vereinfachungen von Opus_Model_Field, _Filter
* [OPUSVIER-165] - Opus_Model_Abstract: getLogger hinzufügen
* [OPUSVIER-202] - IgnoreMultiplicity-Hack von aus Opus_Model_Field entfernen
* [OPUSVIER-430] - Tool Tips für die publishing Seite vervollständigen / korrigieren
* [OPUSVIER-501] - Sprache ist notwendig für jeden eingegebenen Titel und jeden Abstract
* [OPUSVIER-769] - Funktion zum Verändern des Sichtbarkeitsstatus eines Unterbaums
* [OPUSVIER-794] - ShowModel View Helper überarbeiten
* [OPUSVIER-795] - Das Freigabe Modul (Review) benötigt eine Möglichkeit Dokumente abzulehnen
* [OPUSVIER-819] - keine leeren Felder anzeigen
* [OPUSVIER-829] - Übersetzungsschlüssel von Collections
* [OPUSVIER-840] - Emailadresse bei Kontaktdaten des Einstellers
* [OPUSVIER-843] - Anzeige der Dateigröße im Dateimanager
* [OPUSVIER-857] - Bestätigungsfeld für Übertragung der Rechte

---

## Release 4.0.0 Release Candidate 2010-10-08

### Bugfixes

* [OPUSVIER-142] - Fehlerbehandlung bei ungültigen Parameterwerten in URL
* [OPUSVIER-144] - Statstik-Modul funktioniert nicht mit PHP 5.3
* [OPUSVIER-166] - In frontdoor the links to files are broken
* [OPUSVIER-178] - SQL in StatisticController in admin module out of date
* [OPUSVIER-242] - Keine Fehlermeldung, wenn Datenbank nicht erreichbar
* [OPUSVIER-334] - Dateiname Review_Model_DocumentAdapter.php in der application\module\review folgt nicht den Zend-Konventionen für Dateinamen.
* [OPUSVIER-335] - Cookie-Path wird nicht korrekt gesetzt
* [OPUSVIER-345] - Exception in der Suche nach Klick auf Autoren-Namen
* [OPUSVIER-350] - Opus-Matheon: Icons auf der Frontdoor funktionieren nicht
* [OPUSVIER-352] - fehlende "required" Dokumenttyp-Felder im Template produzieren keinen sichtbaren Fehler
* [OPUSVIER-365] - Attribut CompletedYear im Element Opus_Model_Filter in der OPUS-Xml-Dokumentrepräsentation entfernen
* [OPUSVIER-371] - Authentifikation funktioniert nicht
* [OPUSVIER-377] - Konzeptionelle Überarbeitung der Behandlung von Default-Werten bei XML-Erzeugung
* [OPUSVIER-384] - Reihenfolge der Feld-Initialisierung beeinflusst Inhalt von _fetch*-Methoden
* [OPUSVIER-386] - Get rid of *Invalid controller specified (layouts)* exception
* [OPUSVIER-387] - 1800 Datenbankabfragen für 1 Publish-Formular
* [OPUSVIER-402] - Exception not found in the publish module (buggy error handling)
* [OPUSVIER-408] - Slash in suchanfragen führt zu http 404
* [OPUSVIER-412] - Browsing zeigt Dokumente mit ServerState unpublished an
* [OPUSVIER-425] - Frontdoor-Layout wechselt
* [OPUSVIER-431] - frontdoor.css kollidiert mit Matheon-Layout
* [OPUSVIER-441] - webapi-Parameter in config.ini
* [OPUSVIER-472] - ServerDatePublished wird nicht gesetzt
* [OPUSVIER-473] - Instanz opus4-devel: Link auf Datei (PDF) funktioniert nicht
* [OPUSVIER-474] - Instanz opus4-devel: Link auf Lizenz funktioniert nicht
* [OPUSVIER-481] - Matheon-Daten: Detailanzeige von Dokumenten "komisch"
* [OPUSVIER-483] - Matheon-Daten: Unvollständige Daten
* [OPUSVIER-484] - Opus_Security blockt Zugriff auf localhost
* [OPUSVIER-506] - Warning im zweiten Schritt des Publikationsformulars
* [OPUSVIER-507] - Default Submit-Button falsch gesetzt
* [OPUSVIER-513] - Anwendungsfehler in Matheon-Instanz
* [OPUSVIER-516] - Fehler in Matheon-Instanz: Invalid controller specified (foo)
* [OPUSVIER-521] - Relevanzsortierung bei Autorensuche entfernen
* [OPUSVIER-541] - Authorensuche ausgehend vom browsing nach neuesten dokumenten funktioniert nicht
* [OPUSVIER-542] - Call to a member function translate() on a non-object
* [OPUSVIER-543] - Speicherung der IP-Adressen ist mangelhaft
* [OPUSVIER-552] - Publish_DepositControllerTest::testDepositActionWithValidPost schlägt fehl
* [OPUSVIER-554] - Anwendungsfehler im Publish-Formular für Dokumenttyp all
* [OPUSVIER-558] - Matheon-Instanz: Klick des Send-Buttons im zweiten Formularschritt führt zu Publish_Model_OpusServerException
* [OPUSVIER-559] - Matheon: Fehler bei der Migration der Projektinformationen
* [OPUSVIER-566] - Publish-Formular: Exception "No pressed button found! Possibly ..."
* [OPUSVIER-589] - Lizenz Link auf Front Door funktioniert nicht
* [OPUSVIER-596] - Neue Suche aus Trefferliste heraus nicht möglich
* [OPUSVIER-598] - Ausgewählter Reiter wird nicht immer korrekt angezeigt
* [OPUSVIER-604] - Automatisch erstellter Bug aus einem kleinen Perl-Script
* [OPUSVIER-605] - Solr-Exceptions im Matheon-Logfile
* [OPUSVIER-610] - Bilder können vom front door xslt nicht aufgelöst werden
* [OPUSVIER-612] - Fehler in Erweiterter Suche: name + fulltext
* [OPUSVIER-619] - Vulnerability: Metadaten eines vorhandenen Dokuments über publish-Formular veränderbar
* [OPUSVIER-620] - OPUS_File misses OaiExportFlag
* [OPUSVIER-621] - Fehler beim Absenden eines ungültigen Dokumenttyps
* [OPUSVIER-622] - Fehlende Validierung im dritten Formularschritt
* [OPUSVIER-623] - Nach dem erfolgreichen Abspeichern eines Dokuments Redirect durchführen
* [OPUSVIER-624] - Vulnerability: Formular kann mit zusätzlichen Feldern angereichert werden
* [OPUSVIER-625] - Anpassung der Fehlermeldung beim Upload einer zu großen Datei
* [OPUSVIER-626] - Maximale Upload-Filesize kann im Client geändert werden
* [OPUSVIER-627] - Vulnerability: Dokument kann mit beliebigem Dokumenttyp in der Datenbank gespeichert und dadurch die Validierung umgangen werden
* [OPUSVIER-628] - Nach fehlerhafter Eingabe im dritten Formularschritt wird Fehlermeldung und gleichzeitig "Das Dokument wurde erfolgreich gespeichert." angezeigt
* [OPUSVIER-629] - Beim Auftreten eines Application Errors im dritten Formularschritt wird dennoch ein Dokument in der DB im Zustand unpublished gespeichert
* [OPUSVIER-632] - fehlender translation key collection_role_frontdoor_ institutes
* [OPUSVIER-634] - Problem mit Lizenzen im Publish Formular
* [OPUSVIER-638] - Ausgewählter Dokumententyp wird bei wiederholtem Einstellen von Dokumenten nicht mehr berücksichtigt
* [OPUSVIER-639] - Dokument-Admin: Leere Document-ID wirft keine Fehlermeldung
* [OPUSVIER-646] - edit this document link broken
* [OPUSVIER-647] - Störendes Springen von Anzeige bei Anfordern von neuen Eingabefeldern
* [OPUSVIER-650] - Die Sprachumschaltung (Deutsch - Englisch) funktioniert nicht ("Seite nicht gefunden")
* [OPUSVIER-651] - Sprache in der Front Door uneinheitlich
* [OPUSVIER-663] - RSS-Export der neuesten Dokumente liefert HTML-Seite aus
* [OPUSVIER-669] - specialtitle für results view nicht als url parameter übergeben
* [OPUSVIER-672] - Dokument lässt sich nicht abspeichern
* [OPUSVIER-673] - Felder TitleParent, TitleSub und TitleAdditional nur noch als Group?
* [OPUSVIER-674] - fehlerhafte Einträge in Tabelle collection_nodes für die Collection institutes (role = 1)
* [OPUSVIER-687] - Cross-Site-Scripting ganz einfach über home/index/help
* [OPUSVIER-692] - CollectionRole ohne zugeordnete Collections wird nicht korrekt behandelt
* [OPUSVIER-697] - DnbInstitutes sollen über das Adminmodul verwaltet werden
* [OPUSVIER-698] - ThesisGrantor, ThesisPublisher im publishing-Modul
* [OPUSVIER-699] - Fehlende Publish-Übersetzungressourcen in Matheon-Instanz
* [OPUSVIER-700] - Helper aus Publish-Modul liegen in globalem Helper-Verzeichnis
* [OPUSVIER-702] - Fehlermeldungen in der aktuell eingestellten Sprache anzeigen
* [OPUSVIER-704] - add-fields dauerhaft innerhalb der Session?
* [OPUSVIER-705] - Matheon-Instanz: Variable "configPath" nicht gesetzt
* [OPUSVIER-706] - Validierung des Datumsfeld nicht vollständig
* [OPUSVIER-707] - Datumsfeldvalidierung berücksichtigt Accept-Language HTTP Header
* [OPUSVIER-709] - Erzeugung der Publikationslisten skaliert nicht
* [OPUSVIER-717] - Opus_Role guest und administrator sollte man nicht über den Adminbereich löschen können
* [OPUSVIER-721] - Warning im Publikationsformular, Schritt 2
* [OPUSVIER-726] - socialBookmarking: User-Passwort laufen über unsere Server
* [OPUSVIER-728] - Noch schneller.
* [OPUSVIER-730] - Nicht definierte Variable im Publish-Formular (2. und 3. Schritt)
* [OPUSVIER-731] - Sprachumschaltung funktioniert nicht (Einfache Suche und Browsing)
* [OPUSVIER-732] - Opus_Validate_Date: Zugriff auf Session-Daten nicht möglich
* [OPUSVIER-762] - OPUS4 lässt nicht alle URN-Namespaces zu
* [OPUSVIER-776] - Matheon-Layout: FlashMessenger schreibt "Y" in den Kopf der Seite
* [OPUSVIER-779] - Exception 'Solr search server is out of service.'
* [OPUSVIER-780] - Exception beim Abspeichern einer Collection mit Default-Theme
* [OPUSVIER-782] - Fehlermeldungen im Publikationsformular von Matheon
* [OPUSVIER-783] - addNextSibling bzw. addPrevSibling fügt Collection immer am rechten Rand ein
* [OPUSVIER-790] - Alte Dokumenttypen aus dem Config-Verzeichnsi entfernen
* [OPUSVIER-813] - XML-Cache nicht konsistent zur DB
* [OPUSVIER-814] - Zugriff auf Volltext-Dokument verweigert
* [OPUSVIER-859] - Fehler im Browsing/Collections
* [OPUSVIER-1011] - r*-Mechanismus in Sprachumschaltung bzw. Login führt zu Exception
* [OPUSVIER-1015] - Subversion Package-Dependency in deb-Package aufnehmen

### Stories

* [OPUSVIER-11] - T8: Modul zum Freischalten von Dokumenten
* [OPUSVIER-14] - Freigabedatum für Dokumente nutzen/abfragen
* [OPUSVIER-39] - T1: Performance verbessern
* [OPUSVIER-47] - XSS verhindern
* [OPUSVIER-80] - Prio 5: Dokument-Updates und Volltexte nachtragen
* [OPUSVIER-86] - T6: Datenbank-Modell und ORM vereinheitlichen/vereinfachen
* [OPUSVIER-101] - T9: Entwicklungssystem aufsetzen
* [OPUSVIER-229] - Zugriff auf Dokumente über sprechende Namen
* [OPUSVIER-434] - Behandlung von Matheon-spezifischen Änderungen in der Codebasis
* [OPUSVIER-567] - Änderungen am Matheon Layout
* [OPUSVIER-594] - Produktivsystem aufsetzen
* [OPUSVIER-606] - Migration der Opus-ZIB-Instanz
* [OPUSVIER-664] - Internationalisierung
* [OPUSVIER-682] - Collections und CollectionNodes zusammenfassen
* [OPUSVIER-811] - Einräumung von Verwertungsrechten

### Tasks

* [OPUSVIER-8] - Anwenderdokumentation
* [OPUSVIER-15] - Schlüsselnamen der Felder in den Übersetzungsressourcen ändern
* [OPUSVIER-16] - Überprüfen der Mailfunktionen
* [OPUSVIER-40] - Abstraktion vereinfachen
* [OPUSVIER-41] - Caching-Layer in XML-Output einfuegen
* [OPUSVIER-48] - Evaluieren, ob Denial-of-Service moeglich
* [OPUSVIER-53] - Installations-Dokumentation
* [OPUSVIER-58] - Neue Unit-Tests fuer ungetesteten Code schreiben
* [OPUSVIER-63] - Standard-Template fuer Publikation durch Autoren erstellen
* [OPUSVIER-64] - Validierung der Dokument-Metadaten bei der Eingabe
* [OPUSVIER-67] - Validierung aus Opus_Document entfernen
* [OPUSVIER-71] - Auswahl-Mechanismus fuer Collections in Publikation
* [OPUSVIER-73] - Dokumente in Collections zaehlen
* [OPUSVIER-78] - Filtern von Dokumenten nach Volltext ja/nein
* [OPUSVIER-79] - Bibliographie in Dokumenttypen nachbilden
* [OPUSVIER-83] - Ueberpruefung der Eingaben aus dem Admin-Bereich
* [OPUSVIER-84] - Browsing und Suche auf XSS ueberpruefen
* [OPUSVIER-85] - XSS Pen-Tests
* [OPUSVIER-87] - OAI-Schnittstelle viel zu langsam
* [OPUSVIER-88] - Personeneindeutigkeit, PND
* [OPUSVIER-94] - Vereinfachungen wie getIdentifier* -> getIdentifier evaluieren
* [OPUSVIER-95] - Einfachen LRU-Cache in Model-Klassen evaluieren
* [OPUSVIER-96] - Vereinfachungen von Opus_Model_Field, _Filter
* [OPUSVIER-97] - DocumentBuilder entfernen
* [OPUSVIER-98] - Haeufig ueberschriebene Methoden in Opus_Model refactoren
* [OPUSVIER-99] - Coding-Styles von Codesniffer mit IDE abgleichen
* [OPUSVIER-100] - Alte Coding-Styles von Opus4 in Codesniffer einstellen
* [OPUSVIER-107] - Server besorgen und einrichten
* [OPUSVIER-112] - Exception-/Fehlerbehandlung in OAI-Controller fixen
* [OPUSVIER-127] - Auflistung aller verwendeten 3rd party libraries im Wiki
* [OPUSVIER-129] - Erstellung eines Indexschemas
* [OPUSVIER-130] - Indexierung implementieren
* [OPUSVIER-131] - Suchfunktionalität im Framework implementieren
* [OPUSVIER-134] - Sprachdateien von global in die Module
* [OPUSVIER-141] - Funktion zum initialen Erstellen eines Index
* [OPUSVIER-146] - Exception-/Fehler-Behandlung im Frontdoor-Modul
* [OPUSVIER-147] - Exception-/Fehler-Behandlung im Bootstrap-Prozess
* [OPUSVIER-149] - Solr Proof-of-Concept erstellen
* [OPUSVIER-162] - Opus_Model_Abstract: _internalFields streichen
* [OPUSVIER-163] - Opus_Model_AbstractDb: Unit test für ParentId handling
* [OPUSVIER-165] - Opus_Model_Abstract: getLogger hinzufügen
* [OPUSVIER-169] - Update translation resources
* [OPUSVIER-170] - Cleanup pages (phtml files) for admin module
* [OPUSVIER-181] - Controller im Adminmodul zur Verwaltung von Roles und Privileges
* [OPUSVIER-182] - Controller im Adminmodul zur Verwaltung von Accounts
* [OPUSVIER-183] - Controller im Adminmodul zur Verwaltung von IP-Adressen
* [OPUSVIER-187] - Spezifikation der Dokumenttypen
* [OPUSVIER-188] - Webserver neu mit aktuellem OpenSuSE aufsetzen
* [OPUSVIER-201] - Dokumentation für config.ini erstellen
* [OPUSVIER-202] - IgnoreMultiplicity-Hack von aus Opus_Model_Field entfernen
* [OPUSVIER-203] - "PublisherUniversity" ersetzen durch "Publisher"
* [OPUSVIER-204] - Caching-Layer in OAI-Schnittstelle einfuegen
* [OPUSVIER-205] - Validierung der Suchformulareingaben und URL-Parameter
* [OPUSVIER-207] - Restful URLs erzeugen
* [OPUSVIER-208] - SolrserachController und zugehörige Views umsetzen
* [OPUSVIER-217] - Konsolidierung der unterschiedlichen Ablageorte von Klassen mit Solr-Bezug
* [OPUSVIER-234] - Korrektes Suchformular auf der Startseite anbieten
* [OPUSVIER-235] - Nach absetzen einer neuen Suche eventuelle Filter Queries aus der URL löschen
* [OPUSVIER-236] - Split configuration file into config.ini and application.ini
* [OPUSVIER-237] - Zugreifen auf nicht vorhandene array keys im solr search controller erzeugt fehler im apache log
* [OPUSVIER-238] - Behandlung von Fehlern in Templates
* [OPUSVIER-243] - ErrorHandler should show appropriate information to the user
* [OPUSVIER-248] - XML-Strategie Version1 und Version2 enthalten teure Rekursionen
* [OPUSVIER-250] - Browsing Modul aus dem alten Search Modul heraustrennen
* [OPUSVIER-253] - Erweiterte Suche implementieren
* [OPUSVIER-256] - Erweiterte Suche: setter für default operator in klasse Query
* [OPUSVIER-257] - Erweiterte Suche: beim bauen der query alle suchfelder überprüfen
* [OPUSVIER-260] - Suchfeld modifikatoren in die query klasse aufnehmen
* [OPUSVIER-261] - Paginierung für erweiterte Suche implementieren
* [OPUSVIER-265] - Alte Unit Tests für Application reparieren
* [OPUSVIER-266] - In der erweiterten Suche den Query Parameter, falls vorhanden, aus der URL entfernen
* [OPUSVIER-290] - Löschen der DNB-Institusfelder aus den Collections
* [OPUSVIER-291] - Clean up controllers in admin module
* [OPUSVIER-295] - Verknüpfung der Institutionen mit der Personen-Tabelle
* [OPUSVIER-307] - Dokumenttyp Preprint
* [OPUSVIER-311] - In der Ansicht der Suchergebnisse die Angabe über die Dauer der Abfrage entfernen
* [OPUSVIER-314] - Externe Opus-Tabellen vereinfachen, überflüssige Felder löschen
* [OPUSVIER-315] - Collection-Attributes und Vereinfachung des Theme-Handlings
* [OPUSVIER-326] - tool tips für die publish formulare
* [OPUSVIER-329] - Opus_File enthält Feld VisibleInFrontdoor, das noch nicht beachtet wird
* [OPUSVIER-330] - Darf die Frontdoor zu Dokumenten mit Status != published angezeigt werden?
* [OPUSVIER-332] - Zend_Loader_PluginLoader_Exception im View abfangen
* [OPUSVIER-339] - Start/Stop der Solr-Server in init-Skripte einfügen
* [OPUSVIER-347] - Polish review module code
* [OPUSVIER-353] - Fix metadata edit form im admin module
* [OPUSVIER-359] - Solr-extrahierte Volltexte cachen
* [OPUSVIER-374] - Verwendung des Wertes von theme aus der Config in allen Pfadangaben auf img bzw. css
* [OPUSVIER-376] - CSS für die Error Seite
* [OPUSVIER-397] - Remove methods from Browsing/Opus_Document obsoleted by Solr
* [OPUSVIER-413] - javascript injection im Browsing modul
* [OPUSVIER-416] - javascript injection im clearance modul
* [OPUSVIER-424] - Matheon-Preprint-Listen projektweise exportieren
* [OPUSVIER-426] - Liste der benötigten 3rd party libs in Doku aufnehmen
* [OPUSVIER-429] - Unit Tests für Applikation lauffähig machen
* [OPUSVIER-432] - Update-Mechanismus für die Collections
* [OPUSVIER-437] - Instanzneutrale Codebasis bei Auslieferung sicherstellen
* [OPUSVIER-442] - Solr-Indexfelder mit stored=true bzw. Facettenfelder auf Escaping testen
* [OPUSVIER-444] - Es wird eine Solr Query für die neuesten x dokumente benötigt
* [OPUSVIER-447] - durch browsing refactoring obsoleten code entfernen
* [OPUSVIER-448] - Aufsplitten der Installationsanleitung im Wiki
* [OPUSVIER-451] - Unit test: Primary Keys in Datenbank vs. Table-Gateway?
* [OPUSVIER-452] - Unit test: Test AbstractDb->__construct für Multi-Primary-Keys
* [OPUSVIER-453] - Entfernen der Methode setConstructionAttributesMap
* [OPUSVIER-454] - Unit test: Datei-Operationen in Opus_File können fehlschlagen
* [OPUSVIER-455] - Unit test: Opus_File->getDocumentId()
* [OPUSVIER-456] - Feld VisibleOnFrontdoor in Opus_File wird in Frontdoor nicht beachtet.
* [OPUSVIER-457] - Unit test: Falsche Permissions in Opus_File
* [OPUSVIER-458] - Prüfen, ob alte DB-Tabellen/Felder referenziert werden
* [OPUSVIER-460] - Unit test: Korrekte Reihenfolge von Collection-Feldern
* [OPUSVIER-461] - Unit test: Korrekte Parent-Id direkt nach dem Speichern (ehemaliger Model-Bug)
* [OPUSVIER-462] - Unit test: Bug beim Speichern von CollectionNodes mit ungespeichertem Parent-Model
* [OPUSVIER-463] - Unit test: Theme-Handling in Collection
* [OPUSVIER-464] - Unit test: Enrichment-Handling in Collection
* [OPUSVIER-465] - Code für Publish-Modul refactoren
* [OPUSVIER-466] - Variable Anzahl von neuesten Dokumenten
* [OPUSVIER-467] - Überflüssige Publish-Klassen entfernen
* [OPUSVIER-468] - Unit test: Speichern und Laden von Collections über die Dokument-Klasse
* [OPUSVIER-471] - Checkout-Script zum Erstellen neuer Instanzen
* [OPUSVIER-475] - Neue Facette "Bibliographie"
* [OPUSVIER-477] - Migrations-Intelligenz
* [OPUSVIER-480] - Temp- und Ziel-Verzeichnis in {{Opus_File}} aus Config laden
* [OPUSVIER-486] - nur "befüllte" Collections im Browsing anzeigen
* [OPUSVIER-487] - Add 'clearance' privilege for Clearance (Review) module to framework and database
* [OPUSVIER-488] - Unbenutztes Feld "publication_state" in der Datenbank/Opus_Document?
* [OPUSVIER-492] - Check clearance privilege before allowing access to clearance module
* [OPUSVIER-493] - Referees should only receive notifications for specific project/collection
* [OPUSVIER-500] - Übersetzungen der Fehlermeldungen im Publish-Formular
* [OPUSVIER-503] - Benutzer dürfen Unterfelder in Dokumenttypen definieren
* [OPUSVIER-504] - Collection Informationen aus XML in den Solr Index mit aufnehmen
* [OPUSVIER-505] - Änderungen am neuen Browsing Modul mit Solr
* [OPUSVIER-509] - Entfernen der Social Bookmarks
* [OPUSVIER-510] - frontdoor.css überarbeiten
* [OPUSVIER-511] - front door ui überarbeiten
* [OPUSVIER-512] - Browsing-Funktion "list all documents" restlos entfernen
* [OPUSVIER-515] - Code-Duplication beim BibTex-Export
* [OPUSVIER-520] - Hervorhebung der ausgewählten Sortierreihenfolge
* [OPUSVIER-522] - Add tests.ini that contains common testing configuration that normally is not edited locally
* [OPUSVIER-523] - Add unit tests for administration controllers
* [OPUSVIER-525] - Selenium RC-Server aufsetzen
* [OPUSVIER-526] - Selenium-Tests auf CI-System einrichten
* [OPUSVIER-527] - Bootstrapping von Server-Tests initialisiert nicht den Zend-Translator
* [OPUSVIER-528] - Zugriffsschutz für WebAPI-Modul
* [OPUSVIER-529] - Einbindung in CI-System
* [OPUSVIER-532] - Neue Instanz opus4-selenium auf opus4web anlegen
* [OPUSVIER-534] - Einheitliche Benennung des Freischalt-Moduls: Review oder Clearance
* [OPUSVIER-535] - Im Logfile gefunden: Invalid controller specified (search)
* [OPUSVIER-546] - Installation einer neuen  Opus4-Instanz auf dem Webserver
* [OPUSVIER-547] - Feld "PublicationState" existiert in Datenbank, aber nicht in Opus_Document
* [OPUSVIER-548] - Die Migration der Metadaten
* [OPUSVIER-550] - BibTex_Icon freischalten
* [OPUSVIER-551] - Freischalten RIS-Icon
* [OPUSVIER-555] - Umstrukturierung des Imports
* [OPUSVIER-556] - Redundantes Feld "document_server_state" in Tabelle "privileges"
* [OPUSVIER-557] - Klasse "Opus_Security_Realm" ist viel zu lang und voll von Code-Duplication
* [OPUSVIER-560] - Einige Dateien im Browsing Modul haben keinen Prolog
* [OPUSVIER-561] - Wenn Jahr der Fertigstellung 0000, dann Jahr der freischaltung verwenden
* [OPUSVIER-562] - Autorenanzeige in der Front Door überarbeiten
* [OPUSVIER-563] - Abstand zwischen den Dateinamen ist zu klein
* [OPUSVIER-564] - Die Migration der Collections
* [OPUSVIER-565] - Refactoring Solrsearch Controller
* [OPUSVIER-569] - Facetten absetzen
* [OPUSVIER-573] - Verbleibende Browsing-Funktionen als Suchen umsetzen
* [OPUSVIER-578] - Dokumenttypen article, bachelor_thesis, book, book_part, conference_object, contribution_to_periodical, course_material
* [OPUSVIER-579] - Änderungen an der Navigation
* [OPUSVIER-580] - Hinzugekommene Matheon-Änderungen vor Release überprüfen
* [OPUSVIER-583] - Impressum Link im Footer des Master Layouts implementieren
* [OPUSVIER-584] - frontdoor.css nicht im phtml anhängen
* [OPUSVIER-587] - Collections für die Institute
* [OPUSVIER-588] - Dokumenttypen doctoralthesis, festschrift, habilitation, image, lecture, masterthesis, misc
* [OPUSVIER-607] - Problem der Zeichencodierung
* [OPUSVIER-608] - Neustrukturierung der Collections
* [OPUSVIER-609] - Zuordnung von Dokumenten zu Collections
* [OPUSVIER-615] - Admin sollte sich nicht selbst löschen können
* [OPUSVIER-616] - Dokumenttypen movingimage, preprint, report, review, sound, studythesis, workingpaper
* [OPUSVIER-617] - Navigation an Rechte des eingeloggten Benutzers anpassen
* [OPUSVIER-630] - Die Licences aus Opus3.2 müssen migriert werden
* [OPUSVIER-631] - Language Selector anpassen
* [OPUSVIER-635] - BibTex-Import
* [OPUSVIER-636] - Analyse der BibTex-Daten
* [OPUSVIER-637] - Migration des "alten" BibTex-Imports
* [OPUSVIER-642] - Abstract-Hide JavaScript: Schleife entfernen und durch einen Aufruf ersetzen
* [OPUSVIER-643] - JavaScript für Suchformular löschen Button onclick
* [OPUSVIER-648] - Bennenung der Download Links auf der Front Door
* [OPUSVIER-653] - Stresstest mit 10.000 Dokumenten durchführen
* [OPUSVIER-656] - Anzahl aller "published" Dokumente im Unterbaum bestimmen
* [OPUSVIER-657] - Attribut link_docs_path_to_root aus Tabelle collections_roles entfernen
* [OPUSVIER-658] - Abbrechen-Button im 2. Formularschritt
* [OPUSVIER-659] - Propagierung der Sichtbarkeit von CollectionRole auf zugehörige Collections
* [OPUSVIER-665] - Übersetzen der Dokumenttypen
* [OPUSVIER-667] - Documenttype-Browsing: Anzeige des ausgewählten Dokumenttyps in der Dokumentauflistung
* [OPUSVIER-668] - Umsetzung einer Breadcrumb-Navigation beim Collection-Browsing
* [OPUSVIER-671] - JQuery JavaScript-Datei aus der Codebasis entfernen
* [OPUSVIER-678] - Deduplication beim BibTeX- Import
* [OPUSVIER-679] - BibTeX-Einträge in Collections einfügen
* [OPUSVIER-680] - Import der Metadaten aus den BibTex-Files
* [OPUSVIER-681] - Preprocesing der BibTex-Daten
* [OPUSVIER-683] - Workflow zur Einbindung von JQuery und Javascript dokumentieren
* [OPUSVIER-684] - Sprache der Veröffentlichung auf erster Seite aussuchen
* [OPUSVIER-685] - Dokumenttyp soll in Dokumentanzeige im Review-Modul erscheinen
* [OPUSVIER-688] - bessere/eindeutige Begriffe für Browsing, Collections, CollectionRoles, Veröffentlichen
* [OPUSVIER-689] - Einheitlichkeit von Begriffen im Web-Interface und im Admin-Interface
* [OPUSVIER-690] - Style-Elemente aus den Templates für die Dok.typen ins CSS auslagern
* [OPUSVIER-691] - getChildren() ersetzt getSubCollections()
* [OPUSVIER-695] - Show document ID in review module
* [OPUSVIER-701] - Add navigation to administration module
* [OPUSVIER-710] - Code Duplication zwischen results.phtml und plainresults.phtml auflösen
* [OPUSVIER-714] - Testdaten erweitern, so dass PublicationList-Module getestet werden kann
* [OPUSVIER-716] - Integration des PublicationList- in das Solrsearch-Modul
* [OPUSVIER-719] - Testdokumente mit den Feldern ThesisGrantor und Thesis Publisher
* [OPUSVIER-727] - Verwaltung der Collections im Adminmodul funktioniert nicht
* [OPUSVIER-733] - Opus4-Theme für Startseite anpassen
* [OPUSVIER-735] - Opus4-Theme für die Erweiterte Suche einpflegen
* [OPUSVIER-736] - Opus4-Theme für das Browsing anpassen
* [OPUSVIER-737] - Opus4-Theme für Veröffentlichen einpflegen
* [OPUSVIER-738] - Opus4-Theme für das Hilfemenü
* [OPUSVIER-739] - Opus4-Logo
* [OPUSVIER-740] - Auslieferung der Themes
* [OPUSVIER-741] - Opus4-Theme für die Ergebnisse anpassen
* [OPUSVIER-742] - CSS in Opus4-Layout in der korrekten Reihenfolge einbinden
* [OPUSVIER-743] - Body-Tags brauchen eindeutige id
* [OPUSVIER-744] - Anwenderdokumentation - Konfiguration
* [OPUSVIER-745] - Default Wert für CompletedDate: auf aktuelles Datum setzen
* [OPUSVIER-746] - IE6-Css in common.phtml einfügen
* [OPUSVIER-747] - opus.css: Kein Separator, wenn nur eine Sprache zur Auswahl
* [OPUSVIER-750] - Fix author search links on document metadata editing result page
* [OPUSVIER-751] - Fix or remove "Dokument einer organisatorischen Einheit zuweisen" functionality on metadata edit page
* [OPUSVIER-753] - Überprüfung der Hilfetexte und Übersetzungen
* [OPUSVIER-759] - Abhängigkeiten zu Opus_Solrsearch_* im IndexController des PublicationList-Moduls entfernen
* [OPUSVIER-761] - Messages in die common.phtml
* [OPUSVIER-763] - Haken für Emailbenachrichtigung
* [OPUSVIER-764] - Migartionstest mit mehreren Opus3-Instanzen
* [OPUSVIER-769] - Funktion zum Verändern des Sichtbarkeitsstatus eines Unterbaums
* [OPUSVIER-771] - Übersetzungsressourcen für die Dokumentverwaltung
* [OPUSVIER-773] - Einige Model Felder sind falsch konfiguriert und erscheinen mit dem falschen Formulareelement
* [OPUSVIER-774] - Eingabe von Datumsangaben vereinfachen
* [OPUSVIER-777] - TitleParent sollte durchsuchbar sein
* [OPUSVIER-787] - "Erfolgsmeldung" nach dem Zurückziehen von Dokumenten
* [OPUSVIER-803] - Statt DB-Kürzel richtige Namen anzeigen
* [OPUSVIER-805] - Language-Keys nicht vorhanden
* [OPUSVIER-806] - Collection-Hierarchie auch nach "oben durchlaufen"
* [OPUSVIER-807] - Seite zum Überprüfen der Eingaben besser gestalten
* [OPUSVIER-809] - alphabetische Sortierungen für Selects
* [OPUSVIER-834] - Ausgabe der Dokument-ID nach dem erfolgreichen Speichern eines Dokuments
* [OPUSVIER-893] - ZIB-Instanz: Anzeige der hinzugefügten Collections
* [OPUSVIER-902] - Einbindung des OPUS4-Favicons
* [OPUSVIER-914] - Anzeige von Dateikommentaren
* [OPUSVIER-915] - Anzeige von Dateigröße in KB
* [OPUSVIER-1149] - Migration der SWD-Schlagworte
* [OPUSVIER-1191] - Änderungen der Dokumenttypen in OAI-Schnittstelle prüfen
* [OPUSVIER-1336] - 'Cancel' Button in den Edit Formularen für Metadaten sollte 'Zurück' heißen

### Documentation

* [OPUSVIER-148] - Dokument-Config dokumentieren
* [OPUSVIER-435] - Matheon-spezifischen Änderungen im Wiki dokumentieren
* [OPUSVIER-443] - Schlüssel ohne Default-Werte in den Admin-Scripte/Config-Dateien
* [OPUSVIER-531] - Installationsanleitung schreiben
* [OPUSVIER-661] - Hinweis zur Sichtbarkeit von Collections im Browsing
* [OPUSVIER-675] - Hinweis in Doku zum Collection-Browsing bzw. beim CSV-Export aufnehmen
* [OPUSVIER-754] - Dokumentation der Konfiguration der Startseite
* [OPUSVIER-755] - Dokumentation der Konfiguration der Hilfeseite
* [OPUSVIER-756] - Dokumentation der Konfiguration der Impressumseite
* [OPUSVIER-757] - Dokumentation der Konfiguration der Kontaktseite

### Specification

* [OPUSVIER-60] - Anforderungen an Formular-Generierung
* [OPUSVIER-66] - Technische Umsetzung von Validierung
* [OPUSVIER-82] - Spezifikation der Escaping-Strategie
* [OPUSVIER-105] - Dokumente nachbearbeiten/Volltexte nachtragen widerspricht DINI2010
* [OPUSVIER-132] - Requirements für die Suche festlegen
* [OPUSVIER-180] - Spezifikation von OPUS_Securitiy aufschreiben/dokumentieren
* [OPUSVIER-190] - Dokumenttypen mit DINI-Vokabular abgleichen
* [OPUSVIER-191] - Bedeutungen von Dokumenttypen und Feldnamen festlegen
* [OPUSVIER-438] - Umgang mit den Zend_Lucene-Relikten
* [OPUSVIER-514] - Mapping der Opus-Dokumenttypen auf die BibTex- und RIS-Dokumenttypen
* [OPUSVIER-708] - Sortierung für Dokumente mit mehreren Titel nicht eindeutig

