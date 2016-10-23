# OPUS 4 Release Notes

---

## Release 4.5-RC1 2016-04-25

Dies ist der erste Release Candidate für OPUS 4.5. Er dient dazu den Release
Prozess zu proben und die aktuelle Version für allgemeine Tests freizugeben.
Es sollte keine akuten kritischen Probleme mehr geben.

Sie können die Entwicklung unterstützen indem Sie diese Version testen und uns
Rückmeldungen geben, z.B. über die OPUS 4 Tester Mailing-Liste oder auch als
Issue Ticket auf GitHub.

* [Mailing-Liste](http://listserv.zib.de/mailman/listinfo/kobv-opus-tester)
* [Issues auf GitHub](https://github.com/OPUS4/application/issues)

Für die eigentliche Verwaltung von Issues wird weiterhin intern JIRA verwendet.
Entwicklungspartner können Zugriff auf JIRA bekommen. Die Issues auf GitHub
sollen vorallem dazu dienen wichtige Entwicklungsthemen zu kommunizieren und zu
diskutieren. Wir stecken da aber noch in den Anfängen.

### Umstieg auf Git

Die Entwicklung ist zu GitHub.com umgezogen. Der OPUS 4 Source Code befindet
sich nun unter:

https://github.com/OPUS4

Die Dokumentation ist jetzt online. Das OPUS 4 Handbuch findet sich unter:

https://opus4.github.io/userdoc

Für Entwickler gibt es weitere Informationen hier:

https://opus4.github.io

Mit dem Umstieg auf Git als Entwicklungswerkzeug ändert sich die Installation
von OPUS 4 und die Durchführung von Updates wesentlich. Außerdem wird Composer
eingesetzt um Abhängigkeiten, wie z.B. das Zend Framework zu installieren und
zu aktualisieren.

### Installation

Es gibt keinen Tarball mehr. Die Installation wird mit Git und mit Hilfe
eines Installationsskriptes durchgeführt. Mehr Informationen dazu finden
sich in der Online Dokumentation.

### Update

Es gibt kein Updateskript auf diese neue Git-basierte Version von OPUS 4. Es
wird empfohlen mit Hilfe der Online Dokumentation eine neue Instanz mit Git
aufzusetzen und die Anpassungen der alten Instanz mit geeigneten Werkzeugen
zu übertragen. Mehr Informationen dazu finden sich in der Online Dokumentation.

OPUS 4.5-RC1 verwendet das gleiche Datenbankschema wie 4.4.5. Eine neue Instanz
kann also mit der alten Datenbank betrieben werden.

### Weitere Änderungen

Es wurde auf die Solarium Library für die Solr-Anbindung gewechselt. Bei der
Installation wird jetzt Apache Solr 5.3.1 mit installiert (optional).

Es gibt este Anfänge einer Online-Konfiguration für OPUS in der Administration
unter Oberflächenanpassungen->Optionen.

Die URLs für creativecommons.org in den Lizenzen wurden auf HTTPS umgestellt,
um bei Instanzen mit HTTPS "Mixed Content"-Warnungen zu vermeiden.

Die erlaubten Zeichen für EnrichmentKeys wurde eingeschränkt. EnrichmentKeys
müssen mit einem Buchstaben beginnen und dürfen Buchstaben, Zahlen, '.' und
'_' verwenden. Existierende EnrichmentKeys können weiterhin verwendet werden.

Es wurden viele kleine Tickets und Bugs behoben.

Mehr Informationen zu den Änderungen in diesem Release finden sich in der
Datei [CHANGES.md](CHANGES.md)

---

## Release 4.4.5 2014-10-30

Es wurde ein Bug gefixt, der beim Update von Instanzen der Version 4.4.2 oder
früher mit umfangreichen, sortierten Sammlungseinträgen zu Problemen führen
konnte.
Das Update sollte jetzt durchlaufen und die Sortierung der Sammlungseinträge
erhalten bleiben.

Die für den Fix implementierten Sortierfunktionen wurden in die Administration
integriert, so daß Sammlungseinträge einer Ebene jetzt durch einen Klick
automatisch nach Namen oder Nummern, aufwärts oder abwärts sortiert werden
können. Das Menü für die neuen Sortierfunktionen erscheint oberhalb der
Tabellen, die die Einträge einer Sammlung anzeigen.

Die neuen Funktionen stehen nicht für Sammlungen (oberste Ebene) zur
Verfügung. Die Sortierung umfangreicher Sammlungen kann einen Moment dauern,
da die notwendigen Datenbankoperationen aufwendig sind.

Weitere Informationen zu Änderungen finden sich in der Datei CHANGES.txt.

---

## Release 4.4.4 2014-10-13

### Allgemein

* Dokument-Embargo möglich - bis zu diesem Zeitpunkt wird der Volltext des
  Dokuments unter Verschluss gehalten. Metadaten können eingesehen werden.
* MathJax Support kann aktiviert werden um Formeln in Zusammenfassungen oder
  Titeln anzuzeigen.
* Zend Framework wird beim Update auf Version 1.12.9 aktualisiert.
* Komplette Sammlungsnamen werden jetzt in der Frontdoor angezeigt und sind
  mit den Sammlungen verlinkt.
* Javascript Validierung des Dateityps im Publish-Modul vor dem Hochladen.
* Personen-ID Felder ins Publish-Modul integriert. (Die Felder sind noch nicht
  in die Suche integriert, können jetzt aber bereits erfasst werden.)
* Link für XML Export von Dokumenten und Suchergebnissen kann mit einem
  Defaultstylesheet aktiviert werden.

### Konfiguration

* Die Defaultrolle für neue Dateien kann in der config.ini festgelegt werden.
* Die Sprachauswahl verschwindet automatisch, wenn nur eine Sprache als
  "supported" in der config.ini festgelegt wird.
* In der Frontdoor kann eine lokale XSLT Datei verwendet werden, so daß die
  ausgelieferte Datei nicht mehr verändert werden muss.

#### Apache 2.4 Support

OPUS 4 wird in diesem Release noch mit einer Konfiguration für Apache 2.2
ausgeliefert. Um Apache 2.4 zu verwenden müssen kleine Änderungen manuell
an der opus4 Apache-Konfiguration vorgenommen werden. Das ist im Handbuch
näher erläutert.

### OpenAIRE

Erweiterung des OpenAIRE Supports. Die Validierung gegen Version 3 von
OpenAIRE war erfolgreich, wobei bisher nicht die optionalen Anforderungen
umgesetzt wurden.

### XMetaDissPlus

* Erweiterung von isPartOf um Band oder Heftnummer für Zeitschriften
* Unterscheidung des Akademischen Grades für Abschlussarbeiten

### Datenmodell

* Datum für das Hinzufügen einer Datei wird erfasst.
* Embargo Datum für Dokumente kann festgelegt werden.

### Bug Fixes

* Codebereinigung um Probleme mit aktuellen PHP Versionen (z.B. 5.5.9) zu
  beseitigen
* Korrektur der Statistikseite in der Administration
* Reduzierung der "Unable to translate" Nachrichten im Log für Debugging

### Sonstiges

* Modernisierung der Entwicklungsinfrastruktur, einschließlich Upgrade zu
  PHPUnit 4.2.5

---

## Release 4.4.3 2014-06-04

### Allgmein

* Zahlreiche Bug-Fixes
* Tarball wird jetzt in Unterverzeichnis opus-x.x.x (opus-4.4.3) entpackt und
  nicht mehr einfach in das aktuelle Verzeichnis

### Administration

* Auflistung und Validierung der verfügbaren Dokumententypen unter
  "Systeminformationen -> Dokumenttypen"

### OpenAIRE (experimentell)

Es gibt eine erste Umsetzung des OpenAIRE Supports. Dafür wurde eine Variante
der "oai_dc.xslt" Datei mit dem Namen "oai_dc.xslt.openaire" angelegt. Durch
das Umbenennen der OpenAIRE Datei in "oai_dc.xslt" lässt sich der OpenAIRE
konforme OAI Export aktivieren.

### XMetaDiss Plus Änderungen

Es wurden einige Erweiterungen für XMetaDiss Plus vorgenommen, insbesondere
für Zeitschriftenlieferungen.

* Neue Attribute "ZSTitelID" (Opus ID der Schriftenreihe) und
  "ZS-Ausgabe" (Bandnummer) für "isPartOf" Element
* Neuer Dokumententyp "PeriodicalPart" für Zeitschriftenbände
* Dokumententypen wurden um ThesisPublisher erweitert (muss für angepasste
  Typen manuell nachgezogen werden)

### ServerDatePublished

Ändert sich der ServerState eines Dokuments von 'published' zu einem anderen
Zustand wird das Feld ServerDatePublished gelöscht, so daß dieses Dokument
auch nicht mehr in der Veröffentlichungsstatistik auftaucht. Wird das Dokument
anschließend wieder veröffentlich, wird ServerDatePublished auf das neue Datum
gesetzt.

---

## Release 4.4.2 2013-11-22

### XMetaDiss Plus Änderungen

Es gab einige Anpassungen, um den Standard besser zu unterstützen. Dazu wird
unter anderem bei Sprachen jetzt der Wert von Feld "Part2B" verwendet, so
dass zum Beispiel 'ger' anstelle von bisher 'deu' verwendet wird. Weitere
Hinweise dazu gibt es in Kapitel 9.6 der Dokumentation.

### Datenmodel Änderungen

Die maximale Länge der Felder "edition", "issue", und "volume" von Dokumenten
wurde auf 255 Zeichen erhöht.

### Frontdoor Layout & CSS

Die Zusammenfassungen werden jetzt als HTML Liste (UL) ausgegeben, um valides
XHTML zu generieren. Dafür wurde auch das CSS in opus.css angepasst. Unter
Umständen müssen daher Anpassungen am CSS für Ihre OPUS Instanzen vorgenommen
werden. Die Probleme mit dem Einklappen von Zusammenfassungen sollten durch
diese Änderungen behoben sein.

### Update Skript

Das Update Skript funktioniert nur mit BASH Versionen ab 4.0. Die Version wird
jetzt am Anfang des Update-Skript geprüft.

---

## Release 4.4.1 2013-10-17

### Neue Konfigurationsoption für Dateien in Publikationslisten

Im neu in 4.4.0 eingeführten Feature der Publikationslisten war im
ausgelieferten Stylesheet default.xslt nur die Anzeige von PDF-Dateien
vorgesehen. Nun ist es möglich, über entsprechende Konfigurationsoptionen
die Anzeige verschiedener Dateitypen zuzulassen. Wenn hier kein Wert für die
Anzeige gesetzt ist, werden keine Dateien in der Liste ausgegeben.

### Umbau Administration

Der Umbau der Administration wurde weiter fortgesetzt, insbesondere wurde der
Dateimanager überarbeitet.

### Bekannte Probleme

#### Editieren von Collections eines Dokuments

Das Editieren, als ersetzen einer Collection hat es nicht mehr in diesen
Release geschafft. Um eine Collection zu ersetzen muss weiterhin die alte
entfernt und eine neue zugewiesen werden.

#### Positionierung des Metadaten-Formulars

Nach dem Hinzufügen oder Entfernen eines Objekts im Metadaten-Formulars sollte
der Browser wieder in die Nähe der usprünglichen Position springen. Das ist
leider noch nicht konsequent für das gesamte Formular umgesetzt, insbesondere
die Collections.

---

## Release 4.4.0 2013-07-22

### Zugriff auf neue Funktionen innerhalb der Administration

OPUS 4.4 bietet innerhalb der Administration folgende zusätzliche Funktionen an

  unter der Rubrik "Systeminformationen"

  * Verwaltung des Solr-Suchindex
  * Verwaltung der Job-Ausführung (sofern in der Konfiguration aktiviert)

  unter der Rubrik "Oberflächenanpassungen" (aktuell Beta)

  * Verwaltung der Einträge in der FAQ-Seite
  * Verwaltung von statischen Seiten (Haupt-, Kontakt-, Impressumsseite)
  * Verwaltung von Übersetzungsressourcen

Für den Zugriff auf die Funktionen sind entsprechende Rechte erforderlich. Für
den Zugriff auf die ersten beiden Funktionen muss der Rolle das Recht
"Suchindex verwalten" bzw. "Job-Verarbeitung verwalten" zugeordnet werden.

Für den Zugriff auf die drei letzten Funktionen ist entweder das Recht für das
Modul "setup" zu gewähren oder einzeln das Recht "FAQ-Seiten verwalten",
"Statischen Seiten verwalten" bzw. "Übersetzungsressourcen verwalten"
zuzuweisen. Bitte beachten Sie, dass Benutzer, die der Rolle administrator
zugeordnet sind, bereits automatisch über diese neuen Zugriffsrechte verfügen.

Bitte beachten Sie:

Soll der Benutzer neben dem Zugriff auf einzelne im Modul "setup" enthaltene
Funktionen auch Zugriff auf das Modul "admin" erhalten, so müssen dem Benutzer
dafür zwei getrennte Rollen zugewiesen werden. Es ist aktuell nicht möglich nur
eine Rolle zu definieren, die gleichzeitig den Zugriff auf das Modul "admin" und
auf eine oder mehrere der Funktionen "FAQ-Seiten verwalten", "Statischen Seiten
verwalten" bzw. "Übersetzungsressourcen verwalten" erlaubt.


### Anpassung der benötigten Version des Zend Frameworks

Mit OPUS 4.4 ändert sich die Versionsabhängigkeit auf das Zend Framework von
1.10.6 auf 1.12.3. Sofern Sie die Instanz mit dem Update-Skript aktualisieren,
wird das Zend Framework automatisch auf die neue Version aktualisiert.
Andernfalls konsultieren Sie bitte die Dokumentation (Abschnitt 6.3.1) und führen
die Änderungen manuell durch.


### Anpassungen am Solr-Indexschema

Mit OPUS 4.4 werden vier neue Indexfelder eingeführt:

  * year_inverted
  * server_date_modified
  * fulltext_id_success
  * fulltext_id_failure

Damit diese Änderung im Rahmen des Updates der Instanz wirksam wird, muss die
Datei schema.xml (diese befindet sich im Verzeichnis solrconfig des Release-
Tarballs) in das Solr-Konfigurationsverzeichnis conf übernommen werden. Wurde
Solr über das Installskript installiert, so findet die Aktualisierung der Datei
automatisch im Rahmen der Ausführung des Update-Skript statt.

Nach der Aktualisierung der Solr-Schemadatei muss der Solr-Server neu gestartet
werden.

In jedem Fall ist nach dem Update der Solr-Schemadatei und der Instanz auf die
Version 4.4 eine Reindexierung aller Dokumente erforderlich, damit die neu
eingeführten Indexfelder für alle Indexdokumente angelegt werden.


### Verwendung des Felds 'IdentifierUrn' im Publikationsformular

Mit der Version 4.4 wurde das optionale Feld IdentifierUrn in den ausgelieferten
Dokumenttypen (sowohl in der XML-Typdefinition als auch den PHTML-Templates)
vollständig entfernt. Hintergrund ist, dass wir bezogen auf URNs keine globalen
Aussagen bezüglich der Kollisionsfreiheit machen können. Daher sollten URNs
nach Möglichkeit nicht durch den allgemeinen Benutzer angegeben werden. Außerdem
wird in OPUS standardmäßig eine URN erzeugt, sobald ein Dokument freigeschaltet
wird (und es noch keine URN besitzt). Wir empfehlen daher, dass Feld im
Publikationsformular nicht mehr zu verwenden und stattdessen den automatischen
Vergabemechanismus in OPUS 4 zu benutzen.

Um die Abwärtskompatibilität sicherzustellen, wird das Feld aber weiterhin
unterstützt. Der Benutzer kann in diesem Fall weiterhin eine URN im
Publikationsformular eintragen. Für diese wird nun auch geprüft, ob sie lokal
(d.h. bezüglich der in der OPUS-Instanz gespeicherten URNs) kollisionsfrei ist.
Beim Freischalten des Dokuments ist durch den Administrator aber darauf zu
achten, dass die URN aus dem eigenen Namensraum kommt (ansonsten kann keine
Kollisionsfreiheit sichergestellt werden).


### Konfiguration der bearbeitbaren Übersetzungsressourcen

Für die Bearbeitung von Übersetzungsressourcen über die Administration
muss in der config.ini festgelegt werden, welche Module bearbeitbar sein sollen.
Das geschieht über den Parameter setup.translation.modules.allowed, der eine
durch Kommata getrennte Liste aller zur Bearbeitung freigegebenen Module enthält.

Wird dieser Schlüssel nicht angegeben, dann ist die Bearbeitung der
Übersetzungsressourcen über die Administrationsoberfläche nicht möglich.

Als Vorgabe werden in der Datei config.ini.template die Module "default" und
"publish" freigegeben.


### Verschiebung der PHTML-Templates für die Dokumenttypen

Die PHTML-Templates wurden verschoben von modules/publish/views/scripts/form
nach application/configs/doctypes_templates. Wird das Update-Skript zur
Aktualisierung der Instanz verwendet, so werden die bestehenden PHTML-Templates
automatisch in das neue Verzeichnis übernommen (ggf. mit Rückfrage, wenn für
einen Dokumenttyp eine "Differenz" zum Standard-PHTML-Template besteht).

---

## Release 4.3.1 2013-02-21

### Bugfixes

In diesem Release wurden einige Fehler behoben, genaueres findet sich in der
Datei CHANGES.txt

---

## Release 4.3.0 2012-12-20

### Anpassung an den PHTML-Dokumenttyp-Templates

Sämtliche Dokumenttyp-Templates im Verzeichnis modules/publish/views/scripts/form
wurden angepasst. Zum einen wurde ein auskommentierter Codeblock direkt unter
dem OPUS4-Prolog entfernt. Außerdem wurde zur Vermeidung doppelter Übersetzungen
die Zeile

    <h2><?= $this->title ?></h2>

ersetzt durch

    <h2><?= $this->translate($this->title) ?></h2>

Die alten Dokumenttyp-Templates funktionieren weiterhin. Wird die Änderung
bezüglich der Übersetzung nicht nachgezogen, so erscheint auf der zweiten
Seite des Publikationsformulars ein nicht übersetzter Titel. Die Funktionsweise
der Formulare wird dadurch nicht berührt.

Die Änderung wird automatisch beim Update übernommen, sofern die Dokumenttyp-
Templates nicht vom Benutzer verändert wurden. Geänderte oder neu angelegte
Dokumenttyp-Templates müssen manuell aktualisiert werden. Das Update-Skript
wird bei geänderten Templates einen Hinweis ausgeben.


### Änderung an der Frontdoor - index.xslt

Die Datei modules/frontdoor/view/scripts/index.xslt wurde aufgeteilt,
um die Übersichtlichkeit und Wartbarkeit zu verbessern.
Die bisher in dieser Datei definierten XSLT-Templates wurden in mehrere Dateien
verschoben, die nun im Unterverzeichnis "templates" liegen.

Sofern sie eine eigene, angepasste index.xslt verwenden, können sie diese ohne
Änderungen weiter verwenden. Die ausgelagerten Templates werden in diesem Fall
nicht verwendet.
Stellen Sie dabei sicher, dass ihre eigene Version der Datei bei einem Update
nicht überschrieben wird, indem sie vor dem Update eine Sicherungskopie
erstellen und diese danach wieder herstellen. Eventuelle Änderungen, die sich
durch ein Update ergeben, müssen in diesem Fall von Hand eingearbeitet werden.

---

## Release 4.2.2 2012-07-04

### URNs werden nur noch für Dokumente mit sichtbarem Volltext vergeben

Die DNB lässt nur URNs für Dokumente mit Volltexten zu. Daher wurde die
Vergabe von URNs auf Dokumente im Status "published" beschränkt, die per OAI
sichtbare Dateien besitzen. Sichtbar per OAI sind alle Dateien, bei denen
das Attribut "VisibleInOai" auf 1 gesetzt ist.

Bitte stellen Sie für bereits existierende Dokumente sicher, dass Sie keine
Dokumente ohne sichtbare Dateien an die DNB melden. Zu diesem Zweck haben
wir ein kleines Script für Sie vorbereitet:

    $ opus4/scripts/
    $ php opus-console.php snippets/find_urns_for_docs_without_visible_files.php


### Paginierung in der Exportausgabe

Mit dieser Version ist die Paginierung innerhalb des Exports möglich (unter
Verwendung der Parameter *start* und *rows*). Bislang wurden die beiden
Paginierungsparameter ignoriert (im Export waren immer alle Suchtreffer
enthalten). Wird der Export durch Anhängen von '/export/xml' aus einer
Suchergebnisseite angefordert, so sind nun nur noch die dort angezeigten Treffer
im Exportergebnis enthalten. Will man dagegen alle Ergebnisse der Suche
exportieren, so müssen die beiden Paginierungsparameter aus der URL entfernt
werden.


### Hilfedateien (FAQ-, Kontakt- und Impressumsseite)

Die Hilfedateien liegen jetzt unter $BASEDIR/opus4/application/configs/help".
(Siehe auch Dokumentation Kapitel 8.10 ff. und 11.4) Beim Update von existierenden
Instanzen werden die Dateien automatisch verschoben. Danach wird für jede Datei
nachgefragt, ob sie ersetzt werden soll.

Die Konfiguration der Hilfeseite, die bisher in der Datei
"$BASEDIR/opus4/modules/home/views/scripts/index/help.phtml" erfolgte, befindet
sich jetzt in der Datei "$BASEDIR/opus4/application/configs/help/help.ini". Die
alte Konfiguration muß manuell übertragen werden.

### Umbenennung von benutzerspezifischen Enrichment-Feldern

Mit OPUS 4.2.2 wird in der Standardauslieferung ein neues Enrichment-Feld
eingeführt, das für die Migration von Dokumenten aus OPUS3 relevant sind:

    InvalidVerification

Damit im Rahmen eines OPUS4-Updates keine Konflikte mit identischen, vom
Benutzer angelegten, Enrichment-Feldern auftreten, wird ein gleichnamiges
benutzerdefiniertes Enrichment-Feld vor dem Update auf OPUS 4.2.2 umbenannt in:

    TempInvalidVerification

Im Falle einer Umbenennung muss die Übersetzungsressource des alten Feldes
angepasst werden.

Zum Beispiel muss im Falle der Umbennung von SourceTitle in TempSourceTitle in
der Datei $BASEDIR/opus4/modules/default/language_custom/custom.tmx folgende
Änderung vollzogen werden:

ersetze

    <tu tuid="InvalidVerification">
    ...
    </tu>

durch

    <tu tuid="TempInvalidVerification">
    ...
    </tu>


### Änderungen an Solr-Indexschema

Wird der Solr-Server nicht im Rahmen des Updateskripts aktualisiert (weil er
z.B. auf einem anderen Server betrieben wird), dann muss das Indexschema
aktualisiert werden. Dazu muss die Datei

    solrconfig/schema.xml

in das Konfigurationsverzeichnis der Solr-Server kopiert werden und anschließend
der Index neu erstellt werden mittels:

    cd $BASEDIR/opus4/scripts
    ./SolrIndexBuilder.php

---

## Release 4.2.1 2012-03-01

### Validierung des XML-Dumps für die Migration aus OPUS3

Ab dieser Version wird der Opus3-XML-Dump vor Beginn der Migration validiert.
Treten dabei Fehler auf, bricht die Migration mit Hinweis auf Fehlerstelle und
Fehlerursache ab.
Wir empfehlen in diesem Fall, den Opus3-XML-Dump von Hand zu korrigieren und die
Migration anschließend erneut durchzuführen.


### Migration von Volltextdateien aus OPUS3 mit fehlerhaften Sonderzeichen

Enthält der Dateiname eines Dokuments fehlerhafte Sonderzeichen, wird diese
Datei mit modifizierten Dateinamen migriert: die fehlerhaften Zeichen werden aus
dem Dateinamen herausgeschnitten.
Wir empfehlen, die Originaldatei nach Abschluss der Migration von Hand
umzubenennen und über die Administration einzufügen. Die durch die Migration
umbenannte Datei kann anschließend gelöscht werden.

---

## Release 4.2.0 2012-01-27

### Dokumentation im Tarball aufgenommen

Ab dieser Version ist die aktuelle Dokumentation in deutscher Sprache im
Tarball enthalten (opus_dokumentation_de.pdf).


### Änderungen an Solr-Indexschema

Wird der Solr-Server nicht im Rahmen des Updateskripts aktualisiert (weil er
z.B. auf einem anderen Server betrieben wird), dann muss das Indexschema
aktualisiert werden. Dazu muss die Datei

    solrconfig/schema.xml

in das Konfigurationsverzeichnis der Solr-Server kopiert werden und anschließend
der Index neu erstellt werden mittels:

    cd $BASEDIR/opus4/scripts
    ./SolrIndexBuilder.php


### Änderungen an der Datenbank

Das Update-Script befüllt für alle Collections automatisch das Feld "oai_subset"
mit dem Inhalt des Feldes "number", sofern "number" gesetzt und das Feld
"oai_subset" nicht leer ist.  Eventuelle manuelle Änderungen des Feldes
"oai_subset" überschreibt das Update nicht.  Trotzdem empfehlen wir ein Backup
Ihrer OPUS4-Instanz und -Datenbank vor dem Update.

Kapitel 9.7 "Sammlungen verwalten" beschreibt, welche OAI-Einstellungen die
Collections besitzen und wie diese manuell geändert werden können.


### Migration der sammlungsbasierten Schriftenreihen

Die Schriftenreihen werden in OPUS 4.2.0 neu modelliert. Es gibt nun ein
eigenes Modell für die Abbildung von Schriftenreihen. Der Umweg über die
Sammlung mit dem Namen _series_ und das Speichern der (global gültigen)
Bandnummer eines Dokuments im Feld IdentifierSerial entfällt damit ab sofort.
Damit kann ein Dokument fortan mehreren Schriftenreihen zugewiesen werden. Pro
Schriftenreihe muss eine Bandnummer für das Dokument für das Dokument vergeben
werden.

Im Rahmen des Updates werden -- nach Bestätigung durch den Benutzer -- die
Einträge in der Sammlung series auf das neue Schriftenreihen-Modell migriert.
Für jeden Sammlungseintrag wird eine neue Schriftenreihe angelegt. Dabei werden
Name, Sichtbarkeitsstatus und Sortierung übernommen. Die einzelnen Aktionen
bei der Durchführung der Migration werden im Logfile $BASEDIR/UPDATE-series.log
protokolliert.

Bei der Übernahme von Dokumenten, die den Sammlungseinträgen der Sammlung series
zugeordneten sind, werden im Rahmen der Migration mehrere Fälle unterschieden.

Fall 1: Dokument hat keinen IdentifierSerial (Bandnummer)

* das Dokument wird nicht migriert -- es verbleibt am Sammlungseintrag / an
  den Sammlungseinträgen in der Sammlung series
* es wird eine Warnung im Logfile ausgegeben (diese enthält die ID des
  Dokuments)
* im Zuge der Nacharbeit kann das Dokument manuell in die neuen Schriftenreihen
  migriert werden -- dabei muss eine Bandnummer festgelegt werden

Fall 2: Dokument hat mehrere IdentifierSerial

* das Dokument wird nicht migriert -- es verbleibt am Sammlungseintrag / an
  den Sammlungseinträgen in der Sammlung series
* es wird eine Warnung im Logfile ausgegeben (diese enthält die ID des
  Dokuments)
* im Zuge der Nacharbeit kann das Dokument manuell in die neuen Schriftenreihen
  migriert werden -- dabei muss eine Bandnummer festgelegt werden
* ggf. können nach der Übernahme die nicht mehr benötigten IdentifierSerials
  manuell vom Dokument entfernt werden

Fall 3: Dokument hat genau einen IdentifierSerial

* grundsätzlich: das Dokument wird nur dann in die neuen Schriftenreihen
  migriert, wenn es zu keinem Bandnummernkonflikt (d.h. bei der Zuweisung zu
  einer Schriftenreihe wird eine Bandnummer verwendet, die für ein bereits
  zugewiesenes Dokument in der Schriftenreihe genutzt wurde) kommt
* für jeden Sammlungseintrag aus der Sammlung series, für den eine Verknüpfung
  mit dem Dokument besteht, wird die Migration in die neuen Schriftenreihen
  durchgeführt
* tritt dabei kein Konflikt auf, so wird der IdentifierSerial als Bandnummer
  gesetzt und vom Dokument entfernt; ferner wird die Verknüpfung zum
  Sammlungseintrag nach der Übernahme in die Schriftenreihe entfernt
* tritt ein Konflikt auf, so wird eine Warnung ins Logfile geschrieben (enthält
  ID des Dokument und ID des betroffenen Sammlungseintrags)

Am Ende der Migration wird die Sammlung series auf unsichtbar gesetzt, so dass
Sie für den Benutzer fortan nur noch in der Administration angezeigt wird.

Das Updateskript gibt nach Beendigung eine Statistik aus. Aus dieser können

* die Anzahl der aufgetretenen Konflikte
* die Anzahl der migrierten Sammlungseinträge
* die Anzahl der erfolgreich migrierten Dokumente

entnommen werden.

Sind die erforderlich Nacharbeiten nach Durchsicht der Logdatei abgeschlossen,
kann die Sammlung series entfernt werden.


### Migration von SubjectMsc bzw. SubjectDdc in die Sammlungen msc bzw. ddc

In früheren Versionen konnten MSC- und DDC-Klassifikationen eines Dokuments
sowohl in den Sammlungen (msc bzw. ddc) als auch in den Subject-Feldern
SubjectMsc bzw. SubjectDdc gespeichert werden. Mit OPUS 4.2.0 entfällt die
letztgenannte Möglichkeit. Daher müssen die Subject-Felder beim
Update in die Sammlungen migriert und anschließend gelöscht werden.

Die während der Migration der Subject-Felder durchgeführten Änderungen werden in
der Logdatei $BASEDIR/UPDATE-subjects.log protokolliert. Folgende Schritte
werden beim Update für jedes Dokument durchgeführt:

* es werden alle Werte in den Feldern SubjectMsc bzw. SubjectDdc betrachtet
* wenn das Dokument bereits einem Sammlungseintrag zugeordnet ist, dessen
  Nummer dem Wert des Subject-Feldes entspricht, dann wird das Subject-Feld
  entfernt
* andernfalls wird das Dokument dem entsprechenden Sammlungseintrag zugewiesen
  und anschließend das Subject-Feld entfernt
* im Rahmen des Versuchs der Zuweisung kann folgender Fehlerfall auftreten: es
  gibt keinen oder mehrere Sammlungseinträge mit der gegeben Nummer. In diesem
  Fall wird der Wert des Subject-Feldes in einem Enrichment 'MigrateSubjectDDC'
  bzw. 'MigrateSubjectMSC' abgelegt. Das Subject-Feld wird anschließend
  entfernt.

Werden während des Prozesses Werte aus den Subject-Feldern in Enrichments
gespeichert, so ist im Anschluss an die Ausführung des Updates eine manuelle
Nacharbeit erforderlich.


### Anpassungen im den XML-Dokumenttypdefinitionen zugrunde liegenden XML Schema

Mit dieser Version hat sich die Spezifikation von Feldern, die auf den internen
Feldern Identifier*, Subject* und Note basieren, geändert. Außerdem muss durch
die Einführung eines neuen Schriftenreihen-Modells die Spezifikation für Felder,
die auf der Collection Role Series basieren, verändert werden.

Die Änderungen umfassen (jeweils bezogen auf die Elemente field):

* enthält das Attribut name den Wert IdentifierOld, IdentifierSerial,
  IdentifierUuid, IdentifierDoi, IdentifierHandle, IdentifierOpus3 ,
  IdentifierIsbn, IdentifierIssn, IdentifierUrn, IdentifierOpac, IdentifierUrl,
  IdentifierStdDoi, IdentifierCrisLink, IdentifierSplashUrl, IdentifierPubmed,
  oder IdentifierArxiv, so muss der Wert des Attributs datatype in Identifier
  geändert werden

* enthält das Attribut name den Wert SubjectSwd oder SubjectUncontrolled, so
  muss der Wert des Attributs datatype in Subject geändert werden

* enthält das Attribut name den Wert Note, so muss der Wert des Attributs
  datatype in Note geändert werden

* enthält das Attribut name den Wert Series und hat das Attribut datatype den
  Wert Collection sowie das Attribut root den Wert series, so muss im Attrubut
  datatype der Wert Series eingetragen werden. Das Attribut root muss entfernt
  werden.

Im Tarball findet sich unter install/update-documenttypes.php ein PHP-Skript,
das die Umschreibung durchführt. Es erwartet als Eingabe die umzuschreibende
Datei und optional die XML-Schemadefinition, gegen die das Resultat validiert
werden soll. Wenn Sie die Instanz mit dem Update-Skript aktualisieren, müssen
sie das Skript *nicht* manuell aufrufen (s.u.).

In sämtlichen XML-Dokumenttypdefinitionen, die standardmäßig ausgeliefert
werden, sind die Änderungen schon berücksichtigt. Sofern Sie beim Update die
Änderungen übernehmen, sind keine weiteren Eingriffe erforderlich. Für
benutzerdefinierte XML-Dokumenttypen führt das Update-Skript, sofern
erforderlich, eine automatische Umschreibung durch. In diesem Fall wird eine
Backup-Datei im Verzeichnis $BASEDIR/opus4/applications/config/doctypes
angelegt, die z.B. für die Datei foo.xml mit foo.xml.update-backup.<TIMESTAMP>
bezeichnet wird. Außerdem werden die benutzerdefinierten
Dokumenttypdefinitionen gegen das XML-Schema (siehe nächster Punkt) validiert.
Wird ein Schemaverstoß festgestellt, so wird der Dokumenttyp invalidiert,
indem z.B. die Datei foo.xml in foo.xml.invalid.<TIMESTAMP> umbenannt wird.
Alle Manipulationen werden im Logfile $BASEDIR/UPDATE-documenttypes.log
protokolliert.


### Änderung in der Verarbeitung der XML-basierten Dokumenttypdefinitionen

Mit OPUS 4.2.0 werden die XML-basierten Dokumenttypdefinitionen (im Verzeichnis
$BASEDIR/opus4/applications/config/doctypes) gegen das XML-Schema validiert,
das im Verzeichnis $BASEDIR/opus4/library/Opus/Document/documenttype.xsd
abgelegt ist. Wir empfehlen im Rahmen des Updates auf OPUS 4.2.0 sämtliche
Dokumenttypdefinitionen auf Schema-Konformität zu testen, um so sicherzustellen,
dass die Dokumenttypen weiterhin im Publikationsformular benutzt werden können.
Einzelheiten dazu sind in Kapitel 8.4.6 der OPUS4-Dokumentation aufgeführt.

Wenn Sie die Instanz über das Update-Skript aktualisieren, wird im Anschluss
an die Aktualisierung der XML-Dokumenttypdefinitionen eine Validierung
der unter $BASEDIR/opus4/applications/config/doctypes abgelegten XML-Dateien
durchgeführt. Wird für eine Datei foo.xml ein Schemaverstoß festgestellt, so
wird die Datei durch Umbenennung in foo.xml.invalid.<TIMESTAMP> invalidiert.
Der Dokumenttyp kann dann nicht mehr im Publikationsformular ausgewählt
werden. Die festgestellten Validierungsfehler werden in der Logdatei unter
$BASEDIR/UPDATE-documenttypes.log protokolliert. Nach dem Beheben der
Fehler können Sie den Dokumenttyp durch das Entfernen des Suffix
.invalid.<TIMESTAMP> im Dateinamen wieder für die Auswahl im Publikations-
formular freigeben.


### Hinweis zu URNs von migrierten Dokumenten

Wir haben einen Hinweis in die Dokumentation aufgenommen, die alle Instanzen
betrifft, die bereits vor OPUS4 für die URN-Vergabe registriert waren.  Bei
der Migration müssen Sie in der config.ini den Präfix des NISS anpassen, um
Kollisionen mit bereits vergebenen URNs zu vermeiden.  Die Informationen
finden Sie in Kapitel 7.6 "URN SETTINGS".

Der entsprechende Schlüssel in der Config lautet "urn.nss".  Hatten ihre URNs
vor der Migration z.B. die Form "urn:nbn:de:kobv:123-opus-123x" für ein
Dokument mit der ID 123, so genügt es den Schlüssel "urn.nss" auf den Wert
"de:kobv:123-opus4" zu setzen.  Die URN eines neuen Dokuments 123 lautet dann
einfach "urn:nbn:de:kobv:123-opus4-123y" und kollidiert nicht mehr mit der
ID 123 aus dem alten System.

Weitere Details erhalten Sie in Kapitel 7.6 unserer Dokumentation, bei der
DNB oder im Nestor-Handbuch:

[http://nbn-resolving.de/urn/resolver.pl?urn=urn:nbn:de:0008-20100305176]


### Neue Konfigurationsdatei für die Migration aus OPUS3

Mit OPUS 4.2.0 ändert sich die Konfiguration der Migration aus OPUS3. Für
instanzspezifische Anpassungen steht im Verzeichnis
$BASEDIR/opus4/applications/config/ das Template migration_config.ini.template
zur Verfügung. Dieses muss vor Ablauf der Migration in migration_config.ini
umbenannt werden.

Kapitel 10.1 "Vorbereitung" beschreibt, welche Einstellungen manuell geändert
werden können.

Die alten Anpassungen aus $BASEDIR/opus4/applications/config/import.ini müssen
vor der Migration nach migration_config.ini unter Berücksichtigung der neuen
Konfigurationsschlüssel übetragen werden.


### Validierung in Metadaten-Formularen (Administration)

Es wurde angefangen die Validierung der Formulare umzusetzen. Es werden aber
immer noch nicht sämtliche Fehleingaben abgefangen und es kann zu Exceptions
kommen. In diesem Fall bitte den 'Back' Knopf im Browser verwenden und die
Eingaben korrigieren. Es sollte bei einer Fehleingabe nicht zu korrumpierten
Daten in der Datenbank kommen.

---

## Release 4.1.4 2011-10-18

### Änderungen an Dateien im benutzerdefinierten Layout-Verzeichnis

Wird ein Custom Layout verwendet, so müssen zwei Änderungen an CSS und
JavaScript manuell nachgezogen werden, da das Update-Skript keine Veränderung an
benutzerdefinierten Layouts vollzieht. Betroffen sind die Dateien

    public/layouts/opus4/css/opus.css
    public/layouts/opus4/js/searchutil.js

Diese müssen in das eigene Layout durch Kopieren der Dateien übernommen werden.


### Änderungen an Solr-Indexschema

Wird der Solr-Server nicht im Rahmen des Updateskripts aktualisiert (weil er
z.B. auf einem anderen Server betrieben wird), dann muss das Indexschema
aktualisiert werden. Dazu muss die Datei

    solrconfig/schema.xml

in das Konfigurationsverzeichnis der Solr-Server kopiert werden und anschließend
der Index neu erstellt werden mittels:

    cd $BASEDIR/opus4/scripts
    php SolrIndexBuilder.php


### Umbenennung von benutzerspezifischen Enrichment-Feldern

Mit OPUS 4.1.4 werden in der Standardauslieferung neue Enrichment-Felder
eingeführt, die für die Migration von Dokumenten aus OPUS3 relevant sind:

    * ClassRvk
    * SourceTitle
    * SourceSwd
    * SourceSwb
    * ContributorsName
    * SubjectUncontrolledGerman
    * SubjectUncontrolledEnglish

Damit im Rahmen eines OPUS4-Updates keine Konflikte mit identischen, vom
Benutzer angelegten, Enrichment-Feldern auftreten, werden die benutzerdefinier-
ten Enrichment-Felder vor dem Update auf OPUS 4.1.4 umbenannt in:

    * TempClassRvk
    * TempSourceTitle
    * TempSourceSwd
    * TempSourceSwb
    * TempContributorsName
    * TempSubjectUncontrolledGerman
    * TempSubjectUncontrolledEnglish

Im Falle einer Umbenennung muss die Übersetzungsressource des alten Feldes
angepasst werden.

Zum Beispiel muss im Falle der Umbennung von SourceTitle in TempSourceTitle in
der Datei $BASEDIR/opus4/modules/default/language_custom/custom.tmx folgende
Änderung vollzogen werden:

ersetze

    <tu tuid="EnrichmentSourceTitle">
    ...
    </tu>

durch

    <tu tuid="EnrichmentTempSourceTitle">
    ...
    </tu>





