# OPUS 4 Release Notes

## Release 4.7.0.5 2020-08-17

Dieser Patch Release behebt zwei kleinere Bugs. Das Editieren der Inhalte der 
Impressum und Kontakt-Seite ist nun auch von der FAQ-Seite aus ohne Probleme beim 
Speichern möglich.
Von den Suchlinks für Autoren in der Anzeige von Suchergebnissen wurden 
Anführungszeichen entfernt. Damit funktioniert die Autorensuche nun zuverlässiger,
insbesondere auch mit Namen, die  Bindestriche enthalten.

Die Deklaration des Namespaces "xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" 
erfolgt nun beim OAI-Export in jedem Metadata Wurzel Element (GH-412). 

## Release 4.7.0.4 2020-12-02

Diese Version behebt einen Bug im Framework bei der Abfrage, wenn in einem Dokument
mehrere Identifier vom gleichen Typ vorhanden sind. Dieses Problem hat in einem Fall
die Anzeige des DOI-Reports in der Administration verhindert. Weitere Auswirkungen 
wurden nicht entdeckt.

Ein Update der Source-Dateien mit `git pull` und die Installation des aktualisierten
Frameworks mit `composer update` sollten für das Update auf diese Version ausreichen.

Die Versionen 4.7.0.1-4.7.0.3 wurden als kleine Patch-Releases veröffentlicht, ohne
die Versionsnummer von OPUS 4 zu verändern. In Zukunft werden wir auch für diese 
Patch-Releases die Versionsnummer aktualisieren.

## Release 4.7 2020-07-31

Die Änderungen in OPUS __4.7__, die hier aufgeführt sind, ergänzen was schon für 
OPUS __4.7-RC__ weiter unten beschrieben wurde. Für die vollständigen Informationen
zur neuen Version bitte die Notizen beider Releases lesen.

Seit dem Release Candidate wurden noch kleinere Probleme behoben und weitere 
Funktionen hinzugefügt. Bei Schwierigkeiten, melden Sie sich am besten über die 
Mailing-Liste oder legen Sie ein Issue auf GitHub an.

<https://www.kobv.de/entwicklung/software/opus-4>
<https://github.com/OPUS4/application/issues>  
 
OPUS 4.7 befindet sich auf dem MASTER Branch auf GitHub.

<https://github.com/OPUS4/application>

Das OPUS 4 Handbuch wurde für diese Version an vielen Stellen aktualisiert und neu
strukturiert. Insbesondere bei der Anpassung von Übersetzungen, der Konfiguration
der Suchfacetten und den Enrichments hat sich einiges getan.

<http://www.opus-repository.org/userdoc>

### Update

Es wurde noch Probleme beim Import von komplexeren FAQ-Anpassungen in die Datenbank
behoben. Nach dem Update sollte die FAQ-Inhalte so angezeigt werden wie vorher.

Hinweise zum Update finden sich auch in der OPUS 4 Dokumentation.

<http://www.opus-repository.org/userdoc/update/update47.html>

#### Ungültige Namen von CollectionRoles

Die Übersetzungen von CollectionRoles können jetzt direkt im Edit-Formular in der 
"Sammlungsverwaltung" editiert werden. Die Übersetzung von Collections (Sammlungen)
ist komplizierter und ist für später geplant. 

Der Name einer CollectionRole wird als Teil des Übersetzungsschlüssels verwendet. 
In manchen Instanzen wurden Namen mit Sonderzeichen verwendet, was zu technischen 
Schwierigkeiten bei der Verwendung als Schlüssel führt. Daher werden beim Update 
alle Namen validiert. 

Sollte ein Name nicht gültig sein, weil Sonderzeichen oder Leerzeichen verwendet 
wurden, wird versucht den Namen durch den OAI-Namen zu ersetzen. Falls dieser auch 
nicht gültig ist, wird ein Name aus der ID der CollectionRole generiert. Der 
ursprüngliche Name wird als Übersetzung für sämtliche Sprachen gespeichert, damit 
nach dem Update die Anzeige so aussieht wie vorher. 

Die Schritte werden im Update-Log dokumentiert. Die generierten Namen können nach 
dem Update durch einen Administrator angepasst werden. Wird der Name einer 
CollectionRole (Sammlung) verändert, werden die Namen der Schlüssel für die 
Übersetzung automatisch angepasst.

### Übersetzungsverwaltung

Die Übersetzungsverwaltung findet sich in der Administration unter 
"Oberflächenanpassungen > Übersetzungen". Hier wurden noch einige Bug beseitigt.
Die Reihenfolge der angezeigten Sprachen richtet sich nun nach dem Parameter
__supportedLanguages__ in der Konfiguration (`config.ini`). Es ist möglich eine 
neue Sprache hinzuzufügen, z.B. `de,en,fr`. In den Edit-Formularen für Übersetzungen 
taucht dann Französisch als dritte Sprache auf. Sobald ein einziger Eintrag für die 
neue Sprache existiert kann sie in den Einstellungen für die Nutzeroberfläche 
aktiviert werden.

Die Spracheinstellungen für Sprachen wurden vom "Einstellungen"-Bereich der 
Administration zur Übersetzungsverwaltung verschoben.

#### Veränderte Übersetzungsschlüssel

Bei den folgenden drei Schlüsseln wurde die Bindestriche durch Unterstriche ersetzt.

    fulltext-icon-tooltip        -> fulltext_icon_tooltip
    fulltext-icon-oa-tooltip     -> fulltext_icon_oa_tooltip
    admin-actionbox-goto-section -> admin_actionbox_goto_section

Falls diese Schlüssel lokal angepasst wurden, wird die Umbenennung des angepassten 
Schlüssels beim Update auf 4.7 nicht automatisch vorgenommen. Der alte Schlüssel
existiert nach dem Update in der Datenbank. Bei der Anzeige wird allerdings wieder 
der Standardtext aus den TMX-Dateien verwendet. Um das zu korrigieren, müssen in der
Übersetzungsverwaltung die neuen Schlüssel editiert und die alten gelöscht werden.

Hinweis: Es wird für die weitere Entwicklung erforderlich sein größere Mengen an 
Übersetzungsschlüssel umzubenennen. Dafür wird es in Zukunft automatische Update-
Funktionen geben, damit keine manuelle Nacharbeiten notwendig sind.  

### FAQ-Seite editieren

Auf der FAQ Seite tauchen nun Editier-Icons auf, wenn der Nutzer Zugriff auf das 
Setup-Modul hat. Diese Links erlauben das Editieren der Sektionsüberschriften und
der FAQ Einträge. Neue Sektionen und Einträge können auf der "FAQ-Seite" im Setup 
hinzugefügt werden. Die entsprechenden Übersetzungsschlüssel tauchen dann auf der
FAQ-Seite auf und können von dort aus editiert werden.

Damit die Einträge der FAQ-Seite editiert werden können müssen das __Home__ und das 
__Help__ Modul für die Bearbeitung in der Übersetzungsverwaltung freigeschaltet sein. 

<http://www.opus-repository.org/userdoc/translation>

### Logging

Bei Fehlern wird jetzt die Request-URI mit ins Log geschrieben, um sehen zu können
welcher Aufruf das Problem ausgelöst hat.

Die Fehlermeldungen für Übersetzungsschlüssel, die nicht übersetzt werden konnten, 
werden jetzt in eine separate Datei geschrieben. Die Anzahl dieser Meldungen wurde 
außerdem deutlich verringert. Trotzdem gibt es immer noch Stellen an denen unter 
Umständen versucht wird Werte von Feldern zu übersetzen, die nicht übersetzt werden
können. 

Meldungen im Zusammenhang mit den Übersetzungen, wie fehlende Schlüssel, werden in 
die Datei `translation.log` geschrieben. 

### Datenmodel

Das Sortierfeld für mit Dokumenten verknüpfte Personen, z.B. Autoren, wurde 
vergrößert, um mit mehr als 255 Autoren klarzukommen.

### Suche 

Die Konfiguration von Facetten wurde erweitert. Es können nur zusätzliche Optionen
für einzelne Facetten definiert werden. 

Es können nun auch Enrichments als Facetten eingesetzt werden. Dabei kann bestimmt 
werden, ob eine Facette für alle Nutzer sichtbar ist oder nur für Administratoren.

<http://www.opus-repository.org/userdoc/search/facets.html>

Für die Jahr-Facette gibt es nun mehrere Konfigurationsmöglichkeiten. Es können
verschiedene Index-Felder für die Anzeige ausgewählt werden bzw. die Indizierung
so konfiguriert werden, dass nur die gewünschten Date/Year-Felder der Dokumente 
berücksichtigt werden. Mehr dazu in der Dokumentation.

<http://www.opus-repository.org/userdoc/search/yearfacet.html>

### Enrichments

Die Übersetzungen von Enrichments können nun direkt im Edit-Formular für ein
Enrichment editiert werden. Die notwendigen Schlüssel für die Anpassung des 
Publish-Modules für ein Enrichment werden automatisch angelegt und können in 
der Übersetzungsverwaltung editiert werden. Es gibt in der Enrichmentverwaltung
Links zu den Übersetzungen. Unter Umständen werden dabei zusätzliche Schlüssel
angezeigt, die den Namen des Enrichments enthalten, aber eigentlich nichts damit
zu tun haben. 

---

## Release Candidate 4.7-RC 2020-04-07

Dieser Release Candidate sollte nicht für produktive Instanzen verwendet werden. Er
dient in erster Linie dem Testen des Updates und der neuen Funktionen von OPUS 4.7,
bevor die endgültige Version veröffentlicht wird. Wir hoffen auf Ihr Feedback.

Die Version 4.7-RC ist auf dem gleichnamigen Branch zu finden.

<https://github.com/OPUS4/application/tree/4.7-RC>

Die neue Version von OPUS 4 enthält eine Vielzahl von Veränderungen. Es wurde fast jede 
Datei angefasst. Für Instanzen, die Anpassungen an OPUS 4 Dateien, insbesondere mit 
den Endungen `.php` und `.xslt`, vorgenommen haben, kann dieses Update aufwendiger sein.
Wenn es Probleme gibt, wenden Sie sich bitte an die Mailing-Liste 'kobv-opus-tester' 
bzw. legen Sie einen neuen "Issue" auf GitHub an.

* <http://listserv.zib.de/mailman/listinfo/kobv-opus-tester>
* <https://github.com/OPUS4/application/issues>

Für Instanzen im Hosting des KOBV und BSZ wurde ein Katalog der Anpassungen an OPUS 4 
Dateien erstellt, um im Rahmen der weiteren Entwicklung mehr und mehr dieser Anpassungen, 
insbesondere beim Export, bei der Suche und in der Anzeige unnötig zu machen und die 
Konfigurationsoptionen zu ersetzen. Das sollte künftige Updates vereinfachen. 

### System Anforderungen

OPUS 4 sollte auf einer Vielzahl von Systemen lauffähig sein. Es wird aber mit Linux
unter Ubuntu 16 entwickelt. Es verwendet momentan noch Zend Framework 1 und ist damit
leider nicht kompatibel zu PHP 7.2 und neuer. Ubuntu 16 kommt mit PHP 7.0.  

Weitere allgemeine Informationen zu den Anforderungen finden sich hier. 

<http://www.opus-repository.org/userdoc/installation/requirements.html>

Der Umstieg auf die aktuelle Version des Zend Frameworks, der mit umfangreichen Änderungen
verbunden ist, wird für OPUS 4.8 angestrebt.  

### Installation 

Das Installationsskript von OPUS 4 ist auf Ubuntu 16 zu geschnitten. Es sollte dort 
funktionieren. Während der Installation gibt es die Möglichkeit Solr 7.7.2 zu 
installieren. Diese Installation ist zu Testzwecken ausreichend. Für produktive 
Instanzen wird empfohlen, Solr manuell und entsprechend den Empfehlungen der Solr 
Dokumentaton zu installieren. Im Installationsskript werden dann nur die Daten für
die Verbindung zu Solr angegeben.   

* <http://www.opus-repository.org/userdoc/installation/>
* <https://lucene.apache.org/solr/guide/7_7/>

### Update

Die Entwicklungsarbeiten haben viele Dateien berührt. Darüber hinaus wurde in fast 
allen Dateien die Formatierung vereinheitlicht, um eine automatische Prüfung des 
Coding Style zu ermöglichen, was die Entwicklung und die Zusammenarbeit mit externen 
Entwicklern vereinfacht. Beim Update mit `git` kann es also zu Konflikten kommen, 
wenn Dateien lokal angepasst wurden.  

Das Update funktioniert prinzipiell immer noch wie bei OPUS 4.6. Zuerst müssen die 
Dateien mit Git aktualisiert werden. Dann muss ein Update der Composer Pakete 
durchgeführt werden. Am Ende führt das Update-Skript die notwendigen Schritte aus,
um die Datenbank zu aktualisieren und andere Anpassungen vorzunehmen. 

<http://www.opus-repository.org/userdoc/update/update46.html>

Bei diesem Update werden insbesondere folgende Schritte ausgeführt. 

* Änderung des Datenbankzeichensatzes zu `utf8mb4`
* Migration aller TMX-Dateien in `language_custom` Verzeichnissen in die Datenbank
* Migration der Hilfe-Dateien in die Datenbank
* Solr muss manuell auf Solr 7.7.2 aktualisiert werden

### Übersetzungen

Die Übersetzung von Sprachen, z.B. 'deu' => 'German', erfolgt nun mit Hilfe von PHP 
Funktionen. Die Datei 'modules/default/language/languages.tmx' wurde gelöscht.

Die Anpassungen an den Übersetzungen, die bisher in 'language_custom' Verzeichnissen
gespeichert wurden, werden mit dem Update auf diese Version in die Datenbank verschoben. 
Die normalen TMX-Dateien enthalten weiterhin die Standardübersetzungen, während die 
lokalen Anpassungen aus der Datenbank gelesen werden.

Im Setup-Bereich der Administration lassen sich beliebige Übersetzungsschlüssel 
editieren bzw. neu anlegen. Darüber hinaus wurde das Editieren von Übersetzungen in 
verschiedenste Formulare integriert, so dass z.B. die Übersetzungen für ein Enrichment
direkt in den Enrichment-Verwaltung editiert werden können.

Mit dem Konfigurationsparameter `setup.translation.modules.allowed` kann weiterhin 
bestimmt werden für welche Module die Anpassung der Übersetzungen erlaubt ist. 

Es ist möglich die Anpassungen als TMX-Datei zu exportieren bzw. zu importieren, um
Anpassungen von einer Instanz auf eine andere zu übertragen. Es ist auch möglich mit 
externen Tools an den Texten zu arbeiten und sie dann zu importieren. 

Die Integration der Übersetzungsmöglichkeiten in die Formulare der Administration
wird noch weiter fortgesetzt und in kommenden Releases ausgebaut. 

Beim Editieren von Übersetzungen kann das Modul nicht verändert werden. Es wird durch
das Modul der TMX-Datei mit der Standardübersetzung bestimmt. Für neue Schlüssel kann 
ein Modul ausgewählt werden. Das ist in erster Linie für die Entwicklung wichtig. Hier
wird es bestimmt noch weitere Veränderungen geben. Im Zweifelsfall ist es in Ordnung
einfach `default` ausgewählt zu lassen. Wir werden vermutlich später Namespaces für
Übersetzungen von Sammlungen, Enrichments, Feldern, etc. einführen. 

Jeder Schlüssel darf nur einmal innerhalb der Applikation existieren. Der gleiche 
Schlüssel darf also auch nicht in unterschiedlichen Modulen auftauchen.

### Erweiterung der Suche

Mit diesem Release wurden einige wichtige Verbesserungen der Suche in OPUS 4 
umgesetzt. Die Entwicklung der Suche ist damit aber noch nicht abgeschlossen und 
es wird mehr Erweiterungen und Änderungen in kommenden Versionen geben.

Der Code für die Suchanbindung ist vom 'framework'-Repository auf GitHub in das
'search'-Repository verschoben worden.

* <https://github.com/OPUS4/framework>
* <https://github.com/OPUS4/opus4-search>

Die Datei `solr.xslt` existiert nicht länger im Konfigurationsverzeichnis 
`application/configs/solr`. Die Defaultdatei ist Teil des `opus4-search`-Paketes.
Eine eigene Datei kann aber weiterhin in der `config.ini`-Datei spezifiziert 
werden. Wird eine lokale Datei verwendet, muss nach einem Update selbstständig 
sichergestellt werden, dass Änderungen in der Standarddatei in die lokale, 
angepasste Datei übernommen werden.

Die Suche mit diakritischen Zeichen funktioniert jetzt.     

#### Apache-Solr Update

Mit diesem Release wechselt OPUS 4 zu Apache Solr 7.7.2. Der Umstieg muss manuell
durchgeführt werden. Apache Solr ist gut dokumentiert und die Installationsskripte 
funktionieren nach unserer Erfahrung zuverlässig. 

<http://lucene.apache.org/solr/>

Wir empfehlen, Solr als Service auf dem OPUS 4-Server zu installieren. Dazu kann 
man nach dem Download und Auspacken von Solr folgendes Skript verwenden.

    solr-7.7.2/bin/install_solr_service.sh PATH_TO_DOWNLOADED_SOLR_TAR 
    
Genauere Informationen finden sich in der Solr-Dokumentation. 

<http://lucene.apache.org/solr/guide/7_7/taking-solr-to-production.html>

Anschließend müssen gegebenenfalls in der Konfigurationsdatei `config.ini` die 
Solr-Parameter, z.B. für einen neuen Port, aktualisiert werden.

Für die richtige Funktion der Suche muss Solr mit OPUS 4-Konfigurationsdateien
betrieben werden. Auf der folgenden Seite findet sich eine einfache Anleitung,
wie man diese in Solr einbinden kann.

<http://www.opus-repository.org/devdoc/installation/solrsetupmanuell.html> 

Zum Abschluss muss mit dem SolrIndexBuilder-Skript der Index neu aufgebaut werden.

    $ php scripts/SolrIndexBuilder.php

#### Suche für Administratoren

Es werden nun alle Dokumente indiziert. Für normale Nutzer werden weiterhin nur 
publizierte Dokumente gefunden und angezeigt. Administratoren können nun die normale 
Suche verwenden, um nach allen Dokumenten zu suchen und mit Facetten zu filtern.

Dadurch ist es nun für Administratoren möglich, z.B. nach noch nicht freigeschalteten 
Dokumenten einer Autorin oder eines Autoren, sowie nach Dokumenten mit oder ohne 
Volltext zu filtern. Es gibt Facetten wie z.B. den Status von Dokumenten, die nur 
für Administratoren angezeigt werden. 

Die Verwaltung der Dokumente in der Administration funktioniert weiter wie bisher. Je 
nach Bedarf sollte man die Suche oder Dokumentenverwaltung verwenden. Die Verwaltung
setzt direkt auf der Datenbank auf. Aufgrund der steigenden Anforderungen wird die 
Dokumentenverwaltung für Administratoren nach und nach mit der Suche zusammengeführt,
so dass dann nur noch mit einem User Interface gearbeitet werden muss.    

#### Suche nach Enrichments

Es werden jetzt alle Enrichments indiziert. Es kann konfiguriert werden, welche 
Enrichments in der Suche als Facetten auftauchen sollen und ob diese Facetten für 
alle Nutzer oder nur Administratoren sichtbar sein sollen.

Die Quelle von Dokumenten, also Publish-Modul oder SWORD, wird als neues Enrichment 
gespeichert. Damit ist die Unterscheidung zwischen lokal eingestellten Dokumenten 
und Dokumenten, die z.B. von DeepGreen geliefert wurden, einfach möglich.

Die Konfiguration erfolgt momentan über die Datei `config.ini`. Wir arbeiten noch
an der Konfiguration im Rahmen der Enrichmentverwaltung in der Administration.
    
### OAI/Export

Das OAI Module und der Export unterstützen nun MARC21-XML.

Der DCMI-Type von Dokumenttypen wird nicht mehr im XSLT für die OAI-Schnittstelle
definiert. Der Typ kann jetzt in den Konfigurationsdateien definiert werden. Der
DC-Type kann ebenfalls festgelegt werden. 

```
documentType.default.dcmiType = 'Text'
docuemntType.default.dcType = 'Other'
documentType.diplthesis.dcType = 'masterThesis'
documentType.image.dcmiType = 'Image'
```

Die Defaultkonfiguration befindet sich in der Datei `application.ini` und kann in
der Datei `config.ini` ergänzt bzw. überschrieben werden.

Administratoren können jetzt in der Frontdoor das DataCite-XML exportieren, um 
das XML bei Problemenn prüfen zu können und eine manuelle Registrierung durchzuführen. 

### Import (SWORD)

Beim Import von Dokumenten wird jetzt das Enrichment `opus.source` gesetzt, um ein
Dokument als importiert zu markieren. Zusammen mit den Erweiterungen der Suche
kann dieses Enrichment genutzt werden, um zwischen lokal eingestellten und z.B. von
DeepGreen hochgeladenen Dokumenten zu unterscheiden.

Die Dokumentation der SWORD Schnittstelle wurde ausgebaut und ist hier zu finden.

<http://www.opus-repository.org/userdoc/import/sword.html>   

### Erweiterte Enrichments

Die Verwaltung der Enrichments wurde erweitert. Es können jetzt Typen für 
Enrichments festgelegt und konfiguriert werden. Zu den Standardtypen gehört z.B. 
eine Liste (Select), in der erlaubte Werte für das Enrichment festgelegt werden
können. 

Die Enrichments werden im Metadatenformular entsprechend ihrem Typ angezeigt, so
dass man z.B. für ein Boolean-Enrichment eine Checkbox sieht. Enrichments, für die
kein Typ festgelegt wurde, werden weiterhin als Textfeld angezeigt.   

Die Handhabung von Enrichments in den Veröffentlichungsformularen ist noch nicht 
mit der neuen Konfiguration verknüpft. Dort müssen Enrichments momentan immer 
noch zusätzlich konfiguriert werden. Mit der geplanten Überarbeitung des Publish
Modules wird die Konfiguration zusammengeführt werden.

Die Validierung von Enrichments kann flexibel eingestellt werden, um mit dem Fall 
umzugehen, dass Werte, die in der Vergangenheit gültig waren, durch eine Änderung
in der Konfiguration nicht länger erlaubt sind. Das könnte der Fall sein, wenn die
Liste der erlaubten Werte eines Select-Enrichments später geändert wird. Es gibt 
in diesem Fall die Möglichkeit abweichende Werte, die bereits in der Datenbank 
gespeichert sind, beim Editieren zu tolerieren. Neu ausgewählte Werte müssen aber 
der aktuellen Konfiguration entsprechen.

Wir hoffen auf Ihr Feedback für die weitere Entwicklung der Enrichmentfunktionen.  

### Frontdoor

Die META-Tags, die in der Frontdoor für ein Dokument ausgegeben werden, wurden 
erweitert um die für einen Dokumenttyp angemessenen Informationen auszugeben und 
dadurch z.B. auch Google Scholar besser zu unterstützen.

Das Mapping der Dokumenttypen zu Dokumentkategorien, um die Erzeugung der Tags
zu steuern, ist in der Dokumentation beschrieben.

<http://www.opus-repository.org/userdoc/reference/metatags.html>

### Browsing

Leere Sammlungen können nun optional ausgeblendet werden. Dafür gibt es jetzt
eine Option in den Einstellungen von CollectionRoles in der Sammlungsverwaltung.    
    
### Administration    
    
Dateinamen werden beim Upload in der Administration nun wie im Publish-Modul auf
Länge und Zeichensatz geprüft. 

Das Löschen von benutzten Sprachen, Lizenzen, DNB-Instituten und Sammlungen wird 
nun verhindert.

### MySQL Zeichensatz aktualisiert
    
Um sämtliche Zeichen speichern zu können, verwendet die Datenbank jetzt den 
Zeichensatz `utf8mb4` und die Collation `utf8mb4_unicode_ci`. Das Update-Skript 
führt automatisch die Konvertierung durch. Wie immer ist dringend geraten vorher
ein Backup der Datenbank anzulegen. Neue Instanzen verwenden automatisch den 
neuen Zeichensatz. Nach der Konvertierung der Datenbank sollten *Repair* und
*Optimize* für die Datenbank durchgeführt werden, zum Beispiel wie folgt:

    $ mysqlcheck -u root -p --auto-repair --optimize opusdb
    
### Datenmodell

Die Metadaten für Dokumente wurden um eine Aufsatznummer erweitert. Sie kann in
der Administration zusammen mit den anderen bibliographischen Informationen 
editiert werden.

Die Namen von CollectionRoles werden jetzt validiert. In manchen Instanzen wurden
für die Namen Strings mit Sonderzeichen verwendet. Das hat zu Problemen geführt,
da diese Namen als Identifier, z.B. im HTML-Code, verwendet werden. Für die 
angezeigten Namen von CollectionRoles sollte der Übersetzungsmechanismus verwendet 
werden. Die Übersetzungen von CollectionRoles können jetzt direkt in den Formularen 
der Sammlungsverwaltung editiert werden. Es gibt noch keine Möglichkeit die 
untergeordneten Sammlungen zu übersetzen. 

Die Wiederholung einer Bandangabe bei Dokumenten einer Schriftenreihe ist jetzt
erlaubt. 

### Datenschutz

Externe Ressourcen, wie z.B. Fonts und Icon-Sammlungen, wurden in die Dateien von 
OPUS 4 übernommen, um das Laden von externen Servern und die damit unter 
Umständen verbundenen Cookies zu vermeiden. Momentan wird für OPUS 4 nur noch das
PHP Session Cookie benötigt, z.B. wenn man sich einloggt oder mit mehrseitigen 
Formularen arbeitet.  
    
### Dokumentation

Für die Entwicklerdokumentation und das OPUS 4 Handbuch wurde DuckDuckGo als Suche
integriert. Es gab viele Aktualisierungen im Handbuch.   

<https://www.opus-repository.org> 

Beiträge zur Dokumentation sind ein guter Weg die Entwicklung von OPUS 4 zu 
unterstützen. Die Inhalte werden wie der Source-Code auf GitHub gehostet.  

<https://github.com/OPUS4/userdoc>
   
### Bugs        

Es wurde eine Vielzahl von großen und kleinen Problemen behoben. Die genaue Liste
befindet sich in [`CHANGES.md`](CHANGES.md).

### Entwicklung

Die Git-Repositorien wurden um Konfigurationsdateien für Travis-ci.org und GitHub
Actions ergänzt. 

<https://travis-ci.org/github/OPUS4>

Damit können auch für einen Fork der OPUS 4 Repositorien sehr leicht die Unit 
Tests ausgeführt werden. Diese erleichtern die Entwicklung und können 
genutzt werden, wenn externe Entwickler Beiträge zu OPUS 4 leisten wollen.

Die Dateien von OPUS 4 folgen jetzt einem einheitlichen Coding Style. Der Style
kann mit `composer cs-check` geprüft und in vielen Fällen mit `composer cs-fix`
korrigiert werden. Das ist wichtig, wenn Sie Erweiterungen bzw. Vorschläge für 
Änderungen mit einem Pull Request an die OPUS 4 Entwicklung weitergeben wollen.

---

## Release 4.6.3 2018-11-05

Mit diesem Release wurden eine Reihe von Fehlern behoben und kleinere Verbesserungen
eingebaut.

Für ISBN- und ISSN-Identifier werden jetzt im Browser mit Javascript Nachrichten 
ausgegeben, wenn die Eingaben ungültig sind. Das Speichern von ungültigen Werten
wird nicht verhindert.  

Die GND-Schlagwörter werden nicht länger automatisch alphabetisch sortiert. Mit dem 
Eintrag `frontdoor.subjects.alphabeticalSorting = 1` in der Datei `config.ini` kann 
die Sortierung wieder aktiviert werden.

Das DataCite-XML für die DOI-Registrierung wird nun unterhalb des "log"-Verzeichnisses 
gespeichert, um Probleme besser diagnostizieren zu können. 
Die Empfänger von DOI-Benachrichtungen können nun über die Rechteverwaltung in der 
Administration festgelegt werden. 

Dateinamen und ihre Länge werden im Publish-Formular nun bereits im Browser geprüft, 
bevor die Dateien zum Server hochgeladen werden. Die Regeln für Dateinamen können in 
der Konfiguration festgelegt werden.

Neue, leere Dokumente können nun direkt in der Administration angelegt werden. Das 
Duplizieren von Dokumenten in der Administration ist in Arbeit. 

Nach der Aktualisierung der Dateien (`git pull`) sollten die Composer-Abhängigkeiten 
aktualisiert werden (`composer update` bzw. `php composer.phar update`). Das Schema
der Datenbank hat sich für diesen Release nicht verändert. Das Update-Skript sollte 
ausgeführt werden (`bin/update.sh`), um notwendige Enrichments für den DOI-Support
anzulegen.

Fragen können Sie gerne über die User-Mailing-Liste an die OPUS 4 Entwicklung richten: 
<http://listserv.zib.de/mailman/listinfo/kobv-opus-tester>

---

## Release 4.6.2 2018-06-11

Mit diesem Release wird der neue DOI-Support veröffentlicht. 

Die Dokumentation für den DOI-Support findet sich hier:
<http://www.opus-repository.org/userdoc/configext/doi.html>

Nach der Aktualisierung der Dateien (`git pull`) sollten die Composer-Abhängigkeiten 
aktualisiert werden (`composer update` bzw. `php composer.phar update`). Anschließend 
muss das Update-Skript ausgeführt werden (`bin/update.sh`), um die Datenbank für den 
erweiterten DOI-Support anzupassen.

---

## Release 4.6.1 2018-02-26

In diesem Release wurden Fehler behoben und einige kleinere Verbesserungen 
umgesetzt. Die genaue Liste der Tickets befindet sich in der Datei `changes.md`.
Fragen zu einzelnen Tickets können gerne über die OPUS 4 Tester Mailingliste
gestellt werden (kobv-opus-tester@zib.de).

<https://listserv.zib.de/mailman/listinfo/kobv-opus-tester>

### Sortierung von Schriftenreihen

Mit der neuen Konfigurationsoption "series.sortByTitle = 1" kann dafür gesorgt
werden, dass Schriftenreihen unabhängig vom `SortOrder` Feld immer alphabetisch
sortiert angezeigt werden. Eine alphabetische Sortierung mit manuellen Ausnahmen
ist nicht möglich. 

### Validierung ISBN und ISSN

ISBN und ISSN werden in der Administration nun validiert. Ist momentan ein 
ungültiger in einem Dokument gespeichert, kann es beim nächsten Editieren dazu 
kommen, dass die ungültige ISBN oder ISSN erst korrigiert werden muss bevor
das Dokument gespeichert werden kann.

---

## Release 4.6 2017-08-14

Im folgenden werden die wichtigsten Änderungen und Neuheiten für OPUS 4.6
beschrieben.

Es wurden eine Menge kleiner und großer Bugs gefixt. Eine Auflistung der 
Tickets findet sich in der Datei `CHANGES.md`.

### Imports mit SWORD

Es wurde eine SWORD v1.3 Schnittstelle implementiert. Mit dieser Schnittstelle
können Metadaten und Volltexte automatisch importiert werden. Die Schnittstelle
ist für andere Systeme wie DeepGreen (<https://deepgreen.kobv.de>) gedacht, die 
Dokumente in ein OPUS 4 Repositorium hochladen möchten. Das Hochladen ist mit 
Hilfe von einfachen Skripten möglich.

Mit der SWORD-Schnittstelle von OPUS können Pakete (ZIB/TAR) mit den Metadaten 
und Dateien von einem Dokumenten gemäß der SWORD-Spezifikation importiert werden.
Die Schnittstelle ist aber auch in der Lage Pakete mit mehreren Dokumenten in 
einem Request zu verarbeiten.

Die Dokumentation zur SWORD-Schnittstelle ist noch nicht fertig und wird unter
<http://www.opus-repository.org> zu finden sein.

Es ist in dieser Version noch nicht ohne weiteres möglich sämtliche Metadaten,
einschließlich von Verknüpfungen zu Sammlungen, Lizenzen usw. zu importieren. Zur 
Zeit ist dafür die Kenntnis der internen Datenbank-IDs notwendig. In der weiteren
Entwicklung wird der Metadaten-Import weiter ausgebaut und vereinfacht werden. 

### Updates

Für Updates müssen im Allgemeinen folgende Schritte ausgeführt werden. Das 
gilt für die Versionen 4.5-RC1 und neuer.

1. Source Code aktualisieren (git pull, gegebenenfalls Konflikte auflösen)
2. Composer Pakete aktualisieren 

    ```
    $ php composer.phar update --no-dev --optimize-autoloader
    ```
3. Updateskriptausführen

    ```
    $ bin/update.sh
    ```

Das Updateskript aktualisiert zuerst die Datenbank und führt dann die weiteren
notwendigen Schritte aus. 

In der Datenbank werden die Schema-Version und eine OPUS Version gespeichert.
Anhand dieser Versionen wird bestimmt welche Updateschritt auszuführen sind.
Nach einem Update werden daher bei einem erneuten Aufruf von `bin/update.sh`,
die bereits ausgeführten Update-Schritte übersprungen.

Für den Umstieg von älteren OPUS-Versionen, wie 4.4.5, auf die aktuelle Version
mit Git, muss der Dokumentation im OPUS 4 Handbuch gefolgt werden.

<http://www.opus-repository.org/userdoc/update/from445.html>

Beim Update kann wahlweise jeder Updateschritt vom Nutzer vor der Ausführung 
bestätigt werden.

    $ bin/update.sh --confirm-steps
    
Mit dieser Option wird vor jedem Schritt gefragt, ob er ausgeführt werden soll. 
Damit lassen sich problematische Skript überspringen. Im Normalfall sollte das 
nicht notwendig sein.

#### Update auf 4.6

Beim Update werden führende Nullen von GND-Nummern für Autoren entfernt, um eine
korrekte Verlinkung in der Frontdoor zu ermöglichen. Führende Nullen werden bei 
der Eingabe in der Administration nicht mehr akzeptiert.

Im Verzeichnis `db` werden der Link zum Schema-Verzeichnis des OPUS 4 Frameworks
und die Datei `createdb.sh` entfernt, da sie nicht länger benötigt werden. Die
Schema-Dateien werden nur noch indirekt über die Klassen des Frameworks 
verwendet.

### Export

Suchergebnisse können in verschiedenen Formaten exportiert werden.
Unangemeldete Nutzer könnten maximal 100 Dokument auf einmal exportieren. Für
angemeldete Nutzer liegt die Grenze bei 500 und Administratoren haben keine 
Beschränkung. Die Grenzen können konfiguriert werden. 

Der Export aller Dokumente erfolgt auch für Administratoren nicht automatisch.
Das Limit lässt sich aber über URL Parameter steuern, in dem z.B.  `/rows/all`
angegeben wird.
         
Die unterstützten Formate sind BibTeX, RIS, CSV, und XML. Für den XML-Export 
muss wie bisher ein XSLT-Stylesheet konfiguriert werden. Nur Administratoren
haben Zugriff auf das interne OPUS-XML-Format.

Die Export-Links werden nur angezeigt, wenn der Nutzer zugriff auf das Export-
Modul hat. 

### DINI

Die MetaTags für die Frontdoor wurden ergänzt, um die Zugriffsrechte der 
Dokumente maschinenlesbar zu machen.

### Suche

Es gibt nun die Möglichkeit Suchergebnisse in der gewählten Sprache der
Nutzeroberfläche anzeigen zu lassen. Wird OPUS 4 also auf Englisch 
verwendet, werden dann bevorzugt die englischen Titel angezeigt. Ist dieses
Verhalten gewünscht, muss es in der Konfiguration aktiviert werden.

    search.result.display.preferUserInterfaceLanguage = 1

Wird diese Funktion genutzt und sollen die Suchergebnisse nach dem 
Titel sortiert werden, erfolgt die Sortierung weiterhin anhand des 
Titels in der Sprache des Dokuments (Haupttitel), also nicht unbedingt 
anhand des Titels in der Sprache der Oberfläche, der dann angezeigt 
wird. Das kann zu einer unerwarteten Reihenfolge führen. Es ist geplant
dieses Problem in einem kommenden Release durch Änderungen am Index zu beheben.

Das Symbol für Zusammenfassungen an den Suchergebnissen wurde durch ein 
Symbol für Volltexte ersetzt. Die Zusammenfassungen können nun durch eine 
graue Leiste unterhalb der Anzeige eines Suchergebnisses auf- und zugeklappt 
werden.

Für Open-Access-Volltexte wird das Volltextsymbol mit einem Open-Access-
Zeichen angezeigt. Ob ein Dokument Open-Access ist, wird weiterhin über die 
Sammlung "open_access" bestimmt.

Die Links für die Navigation zwischen mehreren Seiten mit Dokumenten
wurden durch Icons ersetzt. Die Anzahl der angezeigten Dokumente kann
über ein DropDown-Menü ausgewählt werden.

### Browsing

Es gibt jetzt ein Browsing nach dem Jahr der Veröffentlichung. Die Anzeige
richtet sich nach der Suchfacette "year".
  
### Frontdoor

Die PHP Funktionen die im XSLT verwendet wurden, sind jetzt als Zend
View Helper implementiert und haben teilweise neue Namen. Das muss bei
Repositorien berücksichtigt werden, die ihre Frontdoor angepasst haben.
Die Änderung erlaubt einfachere Tests und Erweiterbarkeit.

Anstelle von `Frontdoor_IndexController` wird jetzt `Application_Xslt`
verwendet. Die folgenden Funktionen haben außerdem neue Namen bekommen.

    checkIfFileEmbargoHasPassed -> embargoHasPassed
    useCustomSortOrder          -> customFileSortingEnabled
    checkIfUserHasFileAccess    -> fileAccessAllowed
    checkLanguageFile           -> languageImageExists
    getStylesheet               -> frontdoorStylesheet
    
Zusammenfassungen werden nicht mehr mitten im Wort abgeschnitten, wenn 
sie gekürzt angezeigt werden.

Die Frontdoor für ein Dokument kann jetzt mit einer kurzen URL aufgerufen
werden, indem die ID des Dokuments direkt nach der URL für die Instanz
angegeben wird.

<https://opu4web.zib.de/opus4-demo/92>

WARNUNG: Diese URLs sind nicht zum Zitieren gedacht. Dafür sollten 
permanente Idenfier wie URNs eingesetzt werden.

Wenn ein Dokument keine Sprache hat wird der erste Titel als Haupttitel 
angezeigt. 

Ist eine ORCID- oder eine GND Nummer vorhanden, wird sie für Autoren 
verlinkt angezeigt. 
    
#### Download von Dateien
    
Dateien werden normalerweise mit dem HTTP-Header 
    
    Content-Disposition: attachment 

ausgeliefert, so dass sie vom Browser als Dateien gespeichert und nicht 
im Browser angezeigt werden. Das lässt sich nun konfigurieren und ist
für PDF-Dateien im Standard abgeschaltet, so dass PDF-Dateien direkt im
Browser angezeigt werden.

WARNUNG: Es wird nicht empfohlen XHTML, Javascript und ähnliche Inhalte
direkt im Browser anzeigen zu lassen, da damit beträchtliche Sicherheits-
risiken entstehen, wenn z.B. ein Administrator eine solche Datei aufrufen 
sollte.
    
### Lizenzen
    
Beim Update können die Creative Commons 4.0 Lizenzen hinzugefügt werden.
Lizenzen besitzen jetzt das Feld **name** in dem Kurzbezeichnungen wie 
"CC BY 4.0" gespeichert sind. Beim Update wird auch versucht, die 
Kurzbezeichnungen für die alten "CC 3.0" Lizenzen hinzuzufügen. Wenn die
Lizenzen lokal editiert wurden kann es dabei zu falschen Zuordnungen 
kommen. Deshalb sollten die Lizenzen anschließend in der Administration
überprüft werden.

Das Feld **name** wird in der Übersicht aller Lizenzen in der Spalte 
**Label** angezeigt. Ansonsten wird es momentan noch nicht verwendet. In 
Zukunft soll es unter anderem für das Matching von Lizenzen beim Import 
zum Einsatz kommen.

### Anpassungen

Die Datei `custom.css` wird nun immer benutzt, ohne Anpassungen an 
`common.phtml` vornehmen zu müssen. Dort kann mit Hilfe von CSS das 
Aussehen von OPUS 4 verändert werden.

Das Logo für das Repositorium und die Bilder für viele Icons können
nun über CSS ausgewählt werden, so dass Änderungen in `custom.css` 
vorgenommen werden können.

Der Link für das Logo kann in der Konfiguration durch den Parameter 
`logoLink` bestimmt werden. Der Übersetzungschlüssel `logo_title` 
bestimmt den Inhalt des **title**-Attributs.

Es geht darum, Änderungen an `common.phtml` in den meisten Fällen
unnötig zu machen, um Updates noch weiter zu vereinfachen. Die Datei
wird sich in kommenden Releases weiter verändern.

### Datenbank

Es wurden einige Änderungen am Datenbank-Schema vorgenommen, unter anderem,
um Kompatibilität mit strikteren MySQL Konfigurationen herzustellen, wie sie
in aktuellen Linux-Distributionen, z.B. Ubuntu 16, üblich sind.

Beim Speichern von Werten werden jetzt führende und nachfolgende Leerzeichen
immer entfernt. Eine automatische Bereinigung der gesamten Datenbank beim 
Update findet für OPUS 4.6 nicht statt.

### Solr

Der Volltextcache wird nun wieder genutzt. Einmal extrahierte Volltexte 
werden im Verzeichnis `workspace/cache` gespeichert, um eine erneute
Extraktion bei der nächsten Indizierung zu vermeiden.

OPUS 4.6 sollte mit den Solr-Schema von OPUS 4.5 weiterhin funktionieren.
Für OPUS 4.7 sind umfangreiche Änderungen am Schema geplant.

### Administration

In der Administration wird für die Anzeige jetzt die gesamte Breite des
Browserfensters genutzt. Es sind die neuen Bereiche "Einstellungen" und 
"Personen" hinzugekommen.

#### Einstellungen

Unter "Einstellungen" werden in den kommenden Releases immer mehr Seiten
zusammengefasst werden, die Veränderungen an der Konfiguration von OPUS 4 
ermöglichen. 

Es gibt hier auch die neue Seite "Module". Dort werden die vorhandenen 
Module mit dem Zugriffstatus für "guest"-Nutzer aufgelistet. Die Seite 
soll in kommenden Releases weiter ausgebaut werden, um die Konfiguration 
von Modulen zu ermöglichen.

#### Personen

Im Bereich "Personen" gibt es erste einfache Möglichkeiten die Personen
in einem Repositorium zu verwalten. Es ist zum Beispiel möglich sich 
die Dokumente eines Autoren anzeigen zu lassen und Personen können über 
mehrere Dokumente hinweg editiert werden. Das kann z.B. genutzt werden, 
um die Metadaten einer Person um eine ORCID zu ergänzen.

Eine "Person" wird in OPUS bisher durch viele Personen-Objekte verknüpft
mit einzelnen Dokumenten repräsentiert. Um eine Person zu identifizieren 
werden folgende Attribute verwendet.

* Vorname (FirstName)
* Nachname (LastName)
* ORCID (IdentifierOrcid)
* GND-Nummer (IdentifierGnd)
* IdentifierMisc (Interne ID)

Personen-Objekte in der Datenbank, bei denen diese fünf Felder identisch 
sind, werden in der Personenverwaltung zusammengefasst angezeigt. Es wird
angezeigt wieviele Dokumente mit der "Person" verknüpft sind und diese können
aufgelistet werden. Es werden auch die Rollen in denen eine Person auftritt
angezeigt. 

Die Liste der Personen wird auf mehrere Seiten verteilt, mit 50 Personen pro
Seite. Die Liste kann anhand eines Strings oder einer Rolle gefiltert werden,
so dass z.B. nur alle "Betreuer" mit dem Namen "John" angezeigt werden.

Jede Person kann editiert werden. Wenn eine Person im Kontext eines Dokuments 
editiert wird, werden nur die Metadaten für dieses Dokument verändert. In dem 
neuen Formular werden die passenden Personen-Objekte (der "Person") über 
mehrere bzw. alle Dokumente hinweg verändert.

Im Edit-Formular für Personen müssen die Felder, die geändert werden sollen,
explizit ausgewählt werden. Gibt es bei Feldern, wie z.B. der E-Mail Adresse
unterschiedliche Werte in den Personen-Objekten für die Person, werden diese 
aufgelistet und können ausgewählt werden, um die Daten zu vereinheitlichen. 

Auf einer Bestätigungsseite werden die Änderungen und die betroffenen Dokumente
angezeigt bevor sie in die Datenbank geschrieben werden. Durch die Änderungen
wird auch das Datum der letzten Änderung (ServerDateModified) für die Dokumente 
aktualisiert. Im Bestätigungsformular werden leere Werte als "NULL" oder "LEER"
angezeigt. Das bedeutet, dass ein Teil der Personen-Objekte keinen Wert für 
dieses Feld haben. Wenn keines der Personen-Objekte für ein Feld einen Wert hat,
wird das nicht weiter hervorgehoben.

Es handelt sich bei diesen Funktionen noch nicht, um eine richtige Verwaltung
von Personen. Es gibt kein "Master"-Objekt, das eine Person darstellt und als
Referenz bei der Eingabe verwendet werden kann. Mit diesen ersten Funktionen 
können Administratoren aber jetzt schon erste Erfahrungen sammeln und evlt. 
Feedback für die weitere Entwicklung geben. Die abschließende Umsetzung der 
Personenverwaltung ist für OPUS 4.8 geplant, mit OPUS 4.7 für weitere wichtige 
Entwicklungsschritte auf dem Weg dorthin.  

User-Mailingliste @opus-tester
http://listserv.zib.de/mailman/listinfo/kobv-opus-tester

oder auch direkt an 

opus4[at]kobv.de

#### Dokumente

Das Layout der Dokumentverwaltung wurde verändert als Vorbereitung für eine
Umstellung auf eine Suche mit Facetten. Die Änderungen werden in den nächsten 
Version noch weiter fortgesetzt werden.  

In einer zusätzlichen Spalte wird jetzt für jedes Dokument der Zeitpunkt 
der letzten Änderung angezeigt.

Es ist jetzt möglich sich alle Dokumente anzeigen zu lassen und nicht nur
immer die Dokumente mit einem bestimmten Status. Der Status wird jetzt in der
Tabelle mit angezeigt und farblich hervorgehoben.

Für das ID-Eingabefeld wurde jetzt "i" als Access-Key definiert, um mit
einem Knopfdruck dorthin springen zu können, z.B. mit "ALT + i". Welche 
Tasten für Access-Keys verwendet werden ist abhängig vom Browser und 
Betriebssystem. 

https://en.wikipedia.org/wiki/Access_key

Wird die Dokumentverwaltung von der Personenverwaltung aus aufgerufen, werden
unter Filter die Identifikationsparameter für die Person angezeigt. Die Liste
der Dokumente kann dann nach der Rolle der Person gefiltert werden.

#### Sammlungen

Beim Hinzufügen einer neuen Sammlung wird nicht mehr automatisch in die
Sammlung gewechselt, sondern die Anzeige bleibt auf der selben Ebene. So
lassen sich mehrere Sammlungen auf der selben Ebene schneller hinzufügen.

Beim Zuweisen von Sammlungen zu einem Dokument, werden bereits zugewiesene 
Sammlungen hervorgehoben und die Buttons versteckt. 

Mit zwei neuen Konfigurationsoptionen für Sammlungen können Einschränkungen 
beim Zuweisen von Sammlungen wie sie im Publish-Formular möglich sind, nun 
auch in der Administration berücksichtigt werden. Langfristig werden diese 
Parameter auch im Publish-Modul berücksichtigt werden, so dass keine explizite 
Konfiguration im Dokumenttyp mehr notwendig ist.

Abhängig von der Einstellung "Sammlung kann Dokumenten der obersten Ebene
zugewiesen werden" für die CollectionRole können Sammlungen auf 
der obersten Ebene (Root-Collection) nicht mehr zugewiesen werden. 

Die Einstellung "Dokumente können nur der untersten Ebene zugewiesen werden" 
beschränkt Zuweisungen auf die unterste Ebene, die Leaf-Nodes der Sammlung.

### Konfiguration
 
Zusätzlich zu den Dateien `application.ini` und `config.ini` gibt es jetzt auch 
noch die Konfigurationsdatei `console.ini`. Letztere wird nur verwendet, wenn 
OPUS-Skripte direkt auf dem Server-System ausgeführt werden.

In der Datei `console.ini` werden Optionen gespeichert, die für den normalen 
Betrieb nicht notwendig sind, sondern z.B. nur für die Ausführung von Updates. 

Die Resolving-URL wurde zu "<https://doi.org>" geändert und wird jetzt in der 
Konfiguration festgelegt (`doi.resolverUrl`). Der Defaultwert steht in der Datei 
`application.ini`. Die URN-Resolver-URL wird ebenfalls in der Konfiguration 
definiert (`urn.resolverUrl`). 

### Weiteres

Wenn eine URL geschützt ist, z.B. in der Administration wird der Nutzer
zur Login-Seite umgeleitet. Nach dem erfolgreichen Login wird der Nutzer
nun wieder zur ursprünglichen URL umgeleitet. 

### OAI 

Das Format **xMetaDiss** wurde von der OAI-Schnittstelle entfernt.

Für OAI-DC wurde die Ausgabe der Zugriffrechte überarbeitet, um in den 
Metadaten deutlich zu machen, ob es sich um eine Dokument mit folgendem
Status handelt.

* openAccess - uneingeschränkter Zugriff
* closedAccess - keine (sichtbaren) Dateien
* embargoedAccess - zeitlich begrenzte Einschränkung des Zugriffs 
* restrictedAccess - Zugriffsbeschränkungen 

Für Lizenzen wird nun der Link anstelle des langen Namens ausgegeben, um
eine Erkennung in Systemen wie Base zu vereinfachen.

Unbekannte 'identifier' für 'ListMetadataFormats' und doppelte Parameter 
verursachen nun entsprechende Fehlermeldungen.

#### OpenAire

Im Enrichment 'relation' müssen Projekt-Identifier vollständig angegeben
werden, also z.B. mit dem Prefix "info:eu-repo/grantAgreement/EC/FP7/". 
Dieses Feld wird als `<dc:relation>` ausgegeben und kann auch für andere 
Werte, die keine Projekte identifizieren verwendet werden. 

### Crawlers - Google Scholar

Der Aufruf der Sitelinks für Crawlers wie Google, wurde vereinfacht, so
das jetzt ein Aufruf mit dem Pfad `../crawlers` genügt.

http://www.opus-repository.org/userdoc/configext/crawler.html

### Notifikationen

Für Benachrichtungsemails können nun auch die folgenden drei Parameter
konfiguriert werden.

* mail.opus.replyTo
* mail.opus.replyToName
* mail.opus.returnPath

### Dokumenttypen

Die Dokumententypen wurden so angepasst, dass die DDC-Klassifikation nur 
noch auf der untersten Ebene, also mit 3-stelligem Code zugewiesen kann.

Die Art und Weise der Zuordnung von Klassifikationen im Publish-Formular
wird sich mit den nächsten Releases weiter vereinfachen.

---

## Release 4.5 2016-12-06

Dieser Release enhält viele Fehlerkorrekturen und grundlegenden Arbeiten
um eine stabile und effektive Weiterentwicklung von OPUS 4 sicherzustellen.
Eine Auflistung von Tickets findet sich in der Datei [CHANGES.md].
 
Die aktuelle Release-Version von OPUS 4 befindet sich auf dem **master**
Branch von https://github.com/opus4/application. Die Entwicklung wird
auf dem Branch **4.6** weitergehen. Patches für kritische Bugs werden
auch zwischen Releases zum **master** Branch hinzugefügt, um sie zügig
den Repositorien zur Verfügung stellen zu können.

Für diesen Release hat sich das Datenbankschema verändert. Nach der 
Aktualisierung der Sourcen mit Git muss das Updateskript `bin/update.sh`
ausgeführt werden, um das Datenbankschema auf den aktuellen Stand zu 
bringen. Die Datenbank muss vor der Aktualisierung auf dem Stand von 
OPUS 4.4.5, dem letzten Tarball-Release, sein.

Bei den Abhängigkeiten (`composer.json`) ist jQuery-UI hinzugekommen.
Beide Javascript-Bibliotheken, jQuery und jQuery-UI, werden nun von 
Composer standardmässig installiert. Javascript wird vermehrt für neue 
Funktionen im User Interface verwendet. Für eine Aktualisierung der 
Abhängigkeiten das Skript `bin/install-composer.sh` ausführen, nach dem 
die Sourcedateien mit Git auf den neuesten Stand gebracht wurden.

Es gibt eine neue Konfigurationsdatei `console.ini`. In ihr stehen 
Parameter, die nur für die lokal ausgeführten Skripte verwendet werden.

Der Schlüssel 'name' in der `config.ini` kann verwendet werden, um den Namen 
der Instanz zu definieren, z.B. 'OPUS 4 Demo'. Dieser Name wird z.B. im Titel
von RSS Feeds eingesetzt, anstelle des bisherigen 'OPUS RSS Feed'. Dadurch
lassen sich Feeds von mehreren Instanzen leichter unterscheiden.

Die Anzeige und Sortierung von Autoren wurde umgedreht und findet jetzt 
zuerst nach dem Nachnahmen und dann dem Vornamen statt. Dafür muss eine
Neuindizierung aller Dokumente durchgeführt werden. Das Indexschema hat 
sich mit diesem Release nicht verändert.

Bei der Navigation von Suchergebnissen in der Frontdoor können jetzt auch
die Cursor-Tasten, rechts bzw. links, für den Wechsel zum nächsten oder
vorherigen Dokument verwendet werden.

In der Administration werden bei der Eingabe von GND-Schlagwörtern jetzt 
Vorschläge angeboten. Diese Vorschläge kommen von den bereits im Repository
vorhandenen Schlagwörtern und nicht aus den GND Daten. Es handelt sich
um ein User Interface Experiment. Hilfen bei der Eingabe sollen weiter 
ausgebaut werden. 

Für weitere Informationen schauen Sie auf der OPUS 4 Homepage vorbei.

http://www.opus-repository.org

bzw.

https://opus4.github.io

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
