
let populatedFields = [];

// TODO Englische Funktionsnamen fuer Einheitlichkeit im Code bitte

function startCheck()
{
    var doi = document.getElementById("IdentifierDoi").value.trim();
    if (doi.trim() === '') {
        // Feld "IdentifierDoi" ist leer
        alert("Bitte zuerst eine DOI eingeben...");
    } else if (doi.trim() !== '' && document.getElementById("Enrichmentopus_doi_flag").value !== "true") {
        // OK, starte Import
        leseDoi(doi);
    } else if (doi.trim() !== '' && document.getElementById("Enrichmentopus_doi_flag").value === "true") {
        // Import wurde bereits durchgeführt -> Bestätigung
        if (confirm("Achtung, alle Felder des Formulars werden gelöscht und ein neuer Import gestartet! Fortfahren?")) {
            cleanup();
            document.getElementById("IdentifierDoi").value = doi;
            leseDoi(doi);
        } else {
            return;
        }
    }
}

function cleanup()
{
    let fields = document.getElementById("Enrichmentlocal_doiImportPopulated").value;
    document.getElementById("Alles").reset(); // Alle Felder leeren
    document.getElementById("PersonAuthorLastName_1").value        = ""; // Explizites reset(), weil die Felder sonst stehen bleiben
    document.getElementById("PersonAuthorFirstName_1").value       = "";
    document.getElementById("PersonAuthorIdentifierOrcid_1").value = "";

    const usedFields = fields.split(',');
    for (const element of usedFields) { // Hier wird der grüne Hintergrund entfernt
        if (document.getElementById(element)) {
            document.getElementById(element).style.backgroundColor = null;
        }
    }
}

function leseDoi(doi)
{
 // Diese Funktion wird beim Klick auf den Button "DOI-Daten übernehmen" aufgerufen und steuert alles Weitere

    if (doi.trim() !== '') {

        var getUrl        = window.location;
            const baseUrl = getUrl.protocol + "//" + getUrl.host + "/" + getUrl.pathname.split('/')[1];
            //alert("URL: "+getUrl.pathname);
            const finalUrl = baseUrl + '/api/crossref?doi=' + doi;
        get(
            finalUrl,
            function () {
                var jsonraw = this.responseText;
                if (jsonraw === "Resource not found.") {
                    alert("DOI wurde nicht in Crossref gefunden.");
                    colorPink("IdentifierDoi");
                } else {
                    // document.getElementById("Enrichmentlocal_doiJson").value = jsonraw;
                    document.getElementById("Enrichmentopus_import_data").value = jsonraw;
                    document.getElementById("Enrichmentopus_doi_flag").value    = "false";
                    parseJson(jsonraw);
                }
            }
        );
    }
}

function parseJson(jsonraw)
{
    var data = JSON.parse(jsonraw);
    colorGreen("IdentifierDoi");

// Mehrfach belegbare Felder:
    var editor = getEditor(data); expandEditor(editor);
    var translator = getTranslator(data); expandTranslator(translator);
    var subject = getSubject(data); expandSubject(subject);
    var author = getAuthor(data); expandAuthor(author); // Muss an letzter Stelle der Mehrfach-Felder stehen bleiben!
// Ende mehrfach belegbare Felder

    getDoctypes(data);
    document.getElementById("Enrichmentconference_title").value = getConferenceTitle(data);
    document.getElementById("Enrichmentconference_place").value = getConferencePlace(data);
    document.getElementById("ContributingCorporation").value    = getContributingCorporation(data);    //json.author.name;
    document.getElementById("PublisherName").value              = getPublisherName(data);    //json.message.publisher;
    document.getElementById("PublisherPlace").value             = getPublisherPlace(data);  //json.message.publisher-location;
    document.getElementById("TitleMain_1").value                = getTitleMain(data);//json.message.title[0];
    document.getElementById("TitleSub_1").value                 = getTitleSub(data);    //json.message.title[1];

    var language = getLanguage(data);
    expandLanguage(language);

    document.getElementById("TitleAbstract_1").value = getAbstract(data);
    document.getElementById("TitleParent_1").value   = getTitleParent(data);  //json.message.container-title[0];

    var pages = getPages(data);
    expandPages(pages); //json.message.page;

    document.getElementById("ArticleNumber").value = getArticleNumber(data);

    var thesisAccepted = getThesisAccepted(data);
    expandThesisAccepted(thesisAccepted); //json.message.approved.date-parts[0][0] (bzw. ...[0][1] und [0][2] für Tag/Monat)

    document.getElementById("Issue").value   = getIssue(data);    //json.message.issue or json.message.journal-issue.issue;
    document.getElementById("Volume").value  = getVolume(data);  //json.message.volume;
    document.getElementById("Edition").value = getEdition(data);    //json.message.edition-number;

    var dates = getCompletedDate(data);
    expandCompletedDate(dates);

    document.getElementById("IdentifierIsbn").value = getIsbn(data);    //json.message.isbn-type[0].value;
    document.getElementById("IdentifierIssn").value = getIssn(data);    //json.message.issn-type[0].value;
    //document.getElementById("IdentifierUrl").value = getUrl(data);    //json.message.link[0].url -> Soll laut aw raus
    document.getElementById("Enrichmentlocal_crossrefLicence").value    = getLicence(data);
    document.getElementById("Enrichmentlocal_import_origin").value      = "crossref";
    document.getElementById("Enrichmentlocal_doiImportPopulated").value = populatedFields;
}

function expandLanguage(language)
{
    switch (language) {   // Mapping der Sprachen, bei denen die Kürzel in Crossref von denen in Opus abweichen
        case "ger": language = "deu"; break;
        case "en": language = "eng"; break;
        case "chi": language = "zho"; break;
    }

    let sprachen = [];
    var len      = document.getElementById("Language").length - 1;  // Anzahl der auswählbaren Sprachen
    for (let i = 1; i <= len; i++) {
         sprachen[i] = document.getElementById("Language").options[i].value; // Array der auswählbaren Sprachen (Index 1 bis x)
    }
    if (sprachen.indexOf(language) >= 0) {
        document.getElementById("Language").selectedIndex = sprachen.indexOf(language);
    } else {
        document.getElementById("Language").selectedIndex = 0;  // Falls die gelieferte Sprache nicht in Opus angelegt ist
        colorPink("Language");
    }
}

function expandCompletedDate(dates)
{
 // Für CompletedYear und CompletedDate

    if (dates !== '' && dates.length > 2) {  // = Wenn überhaupt ein Jahr enthalten ist

        date = dates.join();

        //if (date.includes('-')){
        if ((date.split(',')[0].length) = 4) {
            document.getElementById("CompletedYear").value = date.split(',')[0];
            finalize("CompletedYear");
        }
         // Das else wird nur gebraucht, wenn CompletedDate befüllt werden soll.
         /* else {
             document.getElementById("CompletedYear").value = date.split(',')[0];
             colorGreen("CompletedYear");
             var month = date.split(',')[1];
             if (month.length == 1){month = '0'+month;}
             var day = date.split(',')[2];
             if (day.length == 1){day = '0'+day;}
             document.getElementById("CompletedDate").value = day+'.'+month+'.'+date.split(',')[0];
             colorGreen("CompletedDate");
         } */
    }
}


function expandSubject(subject)
{
    if (subject[0] !== undefined) {
        var _laenge                                            = subject.length;
        var schlagwort                                         = subject[0] + '';
        document.getElementById("SubjectUncontrolled_1").value = schlagwort;
        finalize("SubjectUncontrolled_1");

        if (document.getElementById('SubjectUncontrolled_' + _laenge) === null) {
            var button = document.getElementById("addMoreSubjectUncontrolled");
            button.click();
        } else {
            var _z;
            for (_z = 1; _z < _laenge; _z++) {
                var feld       = _z + 1;
                var schlagwort = subject[_z] + '';
                document.getElementById("SubjectUncontrolled_" + feld).value = schlagwort;
                finalize("SubjectUncontrolled_" + feld);
            }
        }
    }
}



function expandThesisAccepted(dates)
{
    if (dates !== '' && dates.length > 2) {  // = Wenn überhaupt ein Jahr enthalten ist

        date = dates.join();
        if (date.includes('-')) {
            document.getElementById("ThesisYearAccepted").value = date.split(',')[0];
            finalize("ThesisYearAccepted");
        } else {
            var month = date.split(',')[1];
            if (month.length === 1) {
                month = '0' + month;}
            var day = date.split(',')[2];
            if (day.length === 1) {
                day = '0' + day;}
            document.getElementById("ThesisDateAccepted").value = day + '.' + month + '.' + date.split(',')[0];
            finalize("ThesisDateAccepted");
        }
    }
}

function expandPages(page)
{
    if (page.includes('-')) {
        const pages    = page.split("-");
        var pageFirst  = pages[0];
        var pageLast   = pages[1];
        var pageNumber = pageLast - pageFirst + 1;
        if (pageNumber !== undefined && /^\d+$/.test(pageNumber)) {
            document.getElementById("PageNumber").value = pageNumber;
            finalize("PageNumber");
        }
        if (pageFirst !== undefined && /^\d+$/.test(pageFirst)) {
            document.getElementById("PageFirst").value = pageFirst;
            finalize("PageFirst");
        }
        if (pageLast !== undefined && /^\d+$/.test(pageLast)) {
            document.getElementById("PageLast").value = pageLast;
            finalize("PageLast");
        }
    }
}


function expandAuthor(author)
{
    // Abbruch, wenn kein Autor vorhanden
    if (! author[0]) {
        document.getElementById("Enrichmentopus_doi_flag").value = "true";
        return;
    }

    const maxAuthors   = 50;
    const authorLength = author.length;

    // Setzen von Autor-Informationen
    const setAuthorInfo                  = (index, feld) => {
        const completeName               = author[index] + '';
        const [nachname, vorname, orcid] = completeName.split(',').map(s => s.trim());

        document.getElementById(`PersonAuthorFirstName_${feld}`).value = vorname;
        finalize(`PersonAuthorFirstName_${feld}`);

        document.getElementById(`PersonAuthorLastName_${feld}`).value = nachname;
        finalize(`PersonAuthorLastName_${feld}`);

        if (orcid) {
            document.getElementById(`PersonAuthorIdentifierOrcid_${feld}`).value = orcid;
            finalize(`PersonAuthorIdentifierOrcid_${feld}`);
        }
    };

    // Überprüfen und ggf. weitere Autor-Felder hinzufügen
    let maxFields            = Math.min(maxAuthors, authorLength);
    const ensureAuthorFields = () => {
        if (document.getElementById(`PersonAuthorLastName_${maxFields}`) === null) {
            document.getElementById("addMorePersonAuthor").click();
            return false;
        }
        return true;
    };

    // Überprüfen und Hinweis für zu viele Autoren
    if (document.getElementById('PersonAuthorLastName_' + maxAuthors) !== null ) {
        alert("Der DOI-Datensatz hat zu viele AutorInnen (" + authorLength + "). Importiert werden die ersten 49 AutorInnen sowie der/die letzte AutorIn.");
    }

    // Ersten Autor setzen
    setAuthorInfo(0, 1);

    // Weitere Autoren setzen
    if (ensureAuthorFields()) {
        const limitedLength = Math.min(authorLength, maxAuthors);

        for (let i = 1; i < limitedLength; i++) {
            setAuthorInfo(i, i + 1);
        }

        // Flag setzen, dass alle Felder vorhanden sind
        document.getElementById("Enrichmentopus_doi_flag").value = "true";
    }
}

function expandEditor(editor)
{
    if (editor[0] !== undefined) {
        var _laenge                                              = editor.length;
        var completeName                                         = editor[0] + '';
        var vorname                                              = completeName.split(',')[1].trim();  // [1] = Vorname
        document.getElementById("PersonEditorFirstName_1").value = vorname;
        finalize("PersonEditorFirstName_1");
        var nachname                                            = completeName.split(',')[0].trim();  // [0] = Nachname
        document.getElementById("PersonEditorLastName_1").value = nachname;
        finalize("PersonEditorLastName_1");
        if (completeName.split(',')[2].trim() !== '') {
            var orcid = completeName.split(',')[2].trim();  // [2] = ORCID
            document.getElementById("PersonEditorIdentifierOrcid_1").value = orcid;
            finalize("PersonEditorIdentifierOrcid_1");
        }

        if (document.getElementById('PersonEditorLastName_' + _laenge) === null) {
            var button = document.getElementById("addMorePersonEditor");
            button.click();
        } else {
            var _z;
            for (_z = 1; _z < _laenge; _z++) {
                var feld         = _z + 1;
                var completeName = editor[_z] + '';
                var vorname      = completeName.split(',')[1].trim();  // [1] = Vorname
                document.getElementById("PersonEditorFirstName_" + feld).value = vorname;
                finalize("PersonEditorFirstName_" + feld);
                var nachname                                                  = completeName.split(',')[0].trim();  // [0] = Nachname
                document.getElementById("PersonEditorLastName_" + feld).value = nachname;
                finalize("PersonEditorLastName_" + feld);
                if (completeName.split(',')[2].trim() !== '') {
                    var orcid = completeName.split(',')[2].trim();  // [2] = ORCID
                    document.getElementById("PersonEditorIdentifierOrcid_" + feld).value = orcid;
                    finalize("PersonEditorIdentifierOrcid_" + feld);
                }
            }
        }
    }
}

function expandTranslator(translator)
{
    if (translator[0] !== undefined) {
        var _laenge      = translator.length;
        var completeName = translator[0] + '';
        var vorname      = completeName.split(',')[1].trim();  // [1] = Vorname

        document.getElementById("PersonTranslatorFirstName_1").value = vorname;
        finalize("PersonTranslatorFirstName_1");
        var nachname = completeName.split(',')[0].trim();  // [0] = Nachname

        document.getElementById("PersonTranslatorLastName_1").value = nachname;
        finalize("PersonTranslatorLastName_1");

        if (document.getElementById('PersonTranslatorLastName_' + _laenge) === null) {
            var button = document.getElementById("addMorePersonTranslator");
            button.click();
        } else {
            var _z;
            for (_z = 1; _z < _laenge; _z++) {
                var feld         = _z + 1;
                var completeName = translator[_z] + '';
                var vorname      = completeName.split(',')[1].trim();  // [1] = Vorname
                document.getElementById("PersonTranslatorFirstName_" + feld).value = vorname;
                finalize("PersonTranslatorFirstName_" + feld);
                var nachname = completeName.split(',')[0].trim();  // [0] = Nachname
                document.getElementById("PersonTranslatorLastName_" + feld).value = nachname;
                finalize("PersonTranslatorLastName_" + feld);
            }
        }
    }
}



function get(url, callback)
{
    var xhr = new XMLHttpRequest();
    xhr.open("GET", url, true);
    xhr.onreadystatechange = function () {
        if (xhr.readyState === 4) {
            // defensive check
            if (typeof callback === "function") {
                callback.apply(xhr);
            }
        }
    };
    xhr.send();
}

// TODO get mapping from server REST API
var crossrefTypeMapping = {
    "journal-article": "article",
    "book": "book",
    "book-set": "book",
    "edited-book": "book",
    "reference-book": "book",
    "monograph": "book",
    "book-chapter": "bookpart",
    "book-section": "bookpart",
    "book-part": "bookpart",
    "proceedings": "conferenceobject",
    "proceedings-article": "conferenceobject",
    "proceedings-series": "conferenceobject",
    "journal": "periodical",
    "journal-volume": "periodicalpart",
    "journal-issue": "periodicalpart",
    "posted-content/preprint": "preprint",
    "report": "report",
    "report-series": "report",
    "posted-content/report": "report",
    "peer-review": "review",
    "book-track": "sound",
    "posted-content/working_paper": "workingpaper",
    "dissertation": "doctoralthesis"
};

/**
 *
 * @param data
 * @returns {Promise<string>}
 *
 * TODO aehnlich wie frueher in den XSTL-Dateien sind hier die OPUS 4 Dokumenttypen fest verdrahtet. Das Javascript
 *      muesste also unter Umständen lokal angepasst werden. Das sollte gefixt werden.
 */
async function getDoctypes(data)
{
    var getUrl    = window.location;
    const baseUrl = getUrl.protocol + "//" + getUrl.host + "/" + getUrl.pathname.split('/')[1];
    //alert("URL: "+getUrl.pathname);
    const finalUrl = baseUrl + '/api/doctypes';
    get(
        finalUrl,
        function () {
            var existingDoctypes = this.responseText;
            //alert("Doctypes: " + existingDoctypes);

            document.getElementById("CrossrefDocumentType").value = getType(data);
            var crossrefType                                      = document.getElementById("CrossrefDocumentType").value;
            document.getElementById("Enrichmentlocal_crossrefDocumentType").value = crossrefType; // Zuweisung des originalen Crossref-DokTyps zum Enrichment "local_crossrefDocumentType"

            // Map Crossref document type to OPUS type
            var opusType = crossrefTypeMapping[crossrefType];

            if (! existingDoctypes.includes(opusType)) {
                opusType = 'other';
            }



            if (crossrefType.includes("dissertation/")) {
                // Wenn crossrefType "dissertation" mit Slash: mit Degree
                const degree            = crossrefType.split('/')[1];
                const keys_master       = ["master", "mestrado", "m.phil.", "m.a.", "m.sc.", "ll. m.", "m. ed.", "m. eng.", "m. f. a.", "m. mus.", "ll.m.", "m.ed.", "m.eng.", "m.f.a.", "m.mus.", "m.s."];
                const keys_bachelor     = ["bachelor", "bacharel", "b.a.", "b.sc.", "ll. b.", "b. ed.", "b. eng.", "b. f. a.", "b. mus.", "b. m. a", "ll.b.", "b.ed.", "b.eng.", "b.f.a.", "b.mus.", "b.m.a"];
                const keys_habilitation = ["habil"];
                if (keys_master.some(el => degree.includes(el))) {
                    if (existingDoctypes.includes("masterthesis")) {
                        opusType = 'masterthesis';
                    } else {
                        opusType = 'doctoralthesis';
                    }
                } else if (keys_bachelor.some(el_1 => degree.includes(el_1)) || degree === "ba") {
                    if (existingDoctypes.includes("bachelorthesis")) {
                        opusType = 'bachelorthesis';
                    } else {
                        opusType = 'doctoralthesis';
                    }
                } else if (keys_habilitation.some(el_2 => degree.includes(el_2))) {
                    if (existingDoctypes.includes("habilitation")) {
                        opusType = 'habilitation';
                    } else {
                        opusType = 'doctoralthesis';
                    }
                } else {
                    opusType = 'doctoralthesis';
                }
            }

            document.getElementById('DocumentType').value = opusType;

            //alert("OpusDoctype: " + document.getElementById('DocumentType').value);
            document.getElementById("OpusDocumentType").value = document.getElementById("DocumentType").value;
            return;
        }
    );
}
