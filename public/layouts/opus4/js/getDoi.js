
let populatedFields = [];

function startePruefung()
{
    var doi = document.getElementById("IdentifierDoi").value.trim();
    if (doi.trim() == '') {
        // Feld "IdentifierDoi" ist leer
        alert("Bitte zuerst eine DOI eingeben...")
    } else if (doi.trim() != '' && document.getElementById("Enrichmentopus_doi_flag").value != "true") {
        // OK, starte Import
        leseDoi(doi)
    } else if (doi.trim() != '' && document.getElementById("Enrichmentopus_doi_flag").value == "true") {
        // Import wurde bereits durchgeführt -> Bestätigung
        if (confirm("Achtung, alle Felder des Formulars werden gelöscht und ein neuer Import gestartet! Fortfahren?")) {
            aufraeumen(doi)
        } else {
            return
        }
    }
}

function aufraeumen(doi)
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

    document.getElementById("IdentifierDoi").value = doi;
    leseDoi(doi)
}

function leseDoi(doi)
{
 // Diese Funktion wird beim Klick auf den Button "DOI-Daten übernehmen" aufgerufen und steuert alles Weitere
    if (doi.trim() != '') {
        var finalUrl = "https://api.crossref.org/v1/works/" + doi + "?mailto=repositorien%40bsz-bw.de";
        get(
            finalUrl,
            function () {
                var jsonraw = this.responseText;
                if (jsonraw == "Resource not found.") {
                    alert("DOI wurde nicht in Crossref gefunden.");
                    colorOrange("IdentifierDoi")} else {
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
    var language                                                = getLanguage(data); expandLanguage(language);
    document.getElementById("TitleAbstract_1").value = getAbstract(data);
    document.getElementById("TitleParent_1").value   = getTitleParent(data);  //json.message.container-title[0];
    var pages                                        = getPages(data); expandPages(pages); //json.message.page;
    document.getElementById("ArticleNumber").value = getArticleNumber(data);
    var thesisAccepted                             = getThesisAccepted(data); expandThesisAccepted(thesisAccepted); //json.message.approved.date-parts[0][0] (bzw. ...[0][1] und [0][2] für Tag/Monat)
    document.getElementById("Issue").value   = getIssue(data);    //json.message.issue or json.message.journal-issue.issue;
    document.getElementById("Volume").value  = getVolume(data);  //json.message.volume;
    document.getElementById("Edition").value = getEdition(data);    //json.message.edition-number;
    var dates                                = getCompletedDate(data); expandCompletedDate(dates);
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

    if (dates != '' && dates.length > 2) {  // = Wenn überhaupt ein Jahr enthalten ist

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
    if (subject[0] != undefined) {
        var _laenge                                            = subject.length;
        var schlagwort                                         = subject[0] + '';
        document.getElementById("SubjectUncontrolled_1").value = schlagwort;
        finalize("SubjectUncontrolled_1");

        if (document.getElementById('SubjectUncontrolled_' + _laenge) == null) {
                var button = document.getElementById("addMoreSubjectUncontrolled");
                button.click();} else {
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
    if (dates != '' && dates.length > 2) {  // = Wenn überhaupt ein Jahr enthalten ist

        date = dates.join();
        if (date.includes('-')) {
            document.getElementById("ThesisYearAccepted").value = date.split(',')[0];
            finalize("ThesisYearAccepted");
        } else {
            var month = date.split(',')[1];
            if (month.length == 1) {
                month = '0' + month;}
            var day = date.split(',')[2];
            if (day.length == 1) {
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
        if (pageNumber != undefined && /^\d+$/.test(pageNumber)) {
            document.getElementById("PageNumber").value = pageNumber;
            finalize("PageNumber");
        }
        if (pageFirst != undefined && /^\d+$/.test(pageFirst)) {
            document.getElementById("PageFirst").value = pageFirst;
            finalize("PageFirst");
        }
        if (pageLast != undefined && /^\d+$/.test(pageLast)) {
            document.getElementById("PageLast").value = pageLast;
            finalize("PageLast");
        }
    }
}


function expandAuthor(author)
{
    if (author[0] != undefined) {
        var _laenge      = author.length;
        var completeName = author[0] + '';

        var vorname                                              = completeName.split(',')[1].trim();  // [1] = Vorname
        document.getElementById("PersonAuthorFirstName_1").value = vorname;
        finalize("PersonAuthorFirstName_1");

        var nachname                                            = completeName.split(',')[0].trim();  // [0] = Nachname
        document.getElementById("PersonAuthorLastName_1").value = nachname;
        finalize("PersonAuthorLastName_1");

        if (completeName.split(',')[2].trim() != '') {
            var orcid = completeName.split(',')[2].trim();  // [2] = ORCID
            document.getElementById("PersonAuthorIdentifierOrcid_1").value = orcid;
            finalize("PersonAuthorIdentifierOrcid_1");
        }

        if (document.getElementById('PersonAuthorLastName_' + _laenge) == null) {
                var button = document.getElementById("addMorePersonAuthor");
                button.click();} else {
                var _z;
            for (_z = 1; _z < _laenge; _z++) {
                var feld         = _z + 1;
                var completeName = author[_z] + '';
                var vorname      = completeName.split(',')[1].trim();  // [1] = Vorname
                document.getElementById("PersonAuthorFirstName_" + feld).value = vorname;
                finalize("PersonAuthorFirstName_" + feld);
                var nachname                                                  = completeName.split(',')[0].trim();  // [0] = Nachname
                document.getElementById("PersonAuthorLastName_" + feld).value = nachname;
                finalize("PersonAuthorLastName_" + feld);
                if (completeName.split(',')[2].trim() != '') {
                    var orcid = completeName.split(',')[2].trim();  // [2] = ORCID
                    document.getElementById("PersonAuthorIdentifierOrcid_" + feld).value = orcid;
                    finalize("PersonAuthorIdentifierOrcid_" + feld);
                }
            }
                document.getElementById("Enrichmentopus_doi_flag").value = "true";  // Hier wird das Ende der Reloads erreicht! (alle Felder sind vorhanden)
                }
    } else {
        colorPink("PersonAuthorLastName_1");}
}

function expandEditor(editor)
{
    if (editor[0] != undefined) {
        var _laenge                                              = editor.length;
        var completeName                                         = editor[0] + '';
        var vorname                                              = completeName.split(',')[1].trim();  // [1] = Vorname
        document.getElementById("PersonEditorFirstName_1").value = vorname;
        finalize("PersonEditorFirstName_1");
        var nachname                                            = completeName.split(',')[0].trim();  // [0] = Nachname
        document.getElementById("PersonEditorLastName_1").value = nachname;
        finalize("PersonEditorLastName_1");
        if (completeName.split(',')[2].trim() != '') {
            var orcid = completeName.split(',')[2].trim();  // [2] = ORCID
            document.getElementById("PersonEditorIdentifierOrcid_1").value = orcid;
            finalize("PersonEditorIdentifierOrcid_1");
        }

        if (document.getElementById('PersonEditorLastName_' + _laenge) == null) {
                var button = document.getElementById("addMorePersonEditor");
                button.click();} else {
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
                if (completeName.split(',')[2].trim() != '') {
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
    if (translator[0] != undefined) {
        var _laenge                                                  = translator.length;
        var completeName                                             = translator[0] + '';
        var vorname                                                  = completeName.split(',')[1].trim();  // [1] = Vorname
        document.getElementById("PersonTranslatorFirstName_1").value = vorname;
        finalize("PersonTranslatorFirstName_1");
        var nachname                                                = completeName.split(',')[0].trim();  // [0] = Nachname
        document.getElementById("PersonTranslatorLastName_1").value = nachname;
        finalize("PersonTranslatorLastName_1");

        if (document.getElementById('PersonTranslatorLastName_' + _laenge) == null) {
                var button = document.getElementById("addMorePersonTranslator");
                button.click();} else {
                var _z;
            for (_z = 1; _z < _laenge; _z++) {
                var feld         = _z + 1;
                var completeName = author[_z] + '';
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
        if (xhr.readyState == 4) {
            // defensive check
            if (typeof callback === "function") {
                callback.apply(xhr);
            }
        }
    };
            xhr.send();
}


        async function getDoctypes(data)
        {
            const response                                        = await fetch('../../getDoctypes.php');
            const text                                            = await response.text();
            const split                                           = text.split(",");
            const existingDoctypes                                = split.map(element => { return element.trim(); });
            document.getElementById("CrossrefDocumentType").value = getType(data);
            var crossrefType                                      = document.getElementById("CrossrefDocumentType").value;
            document.getElementById("Enrichmentlocal_crossrefDocumentType").value = crossrefType; // Zuweisung des originalen Crossref-DokTyps zum Enrichment "local_crossrefDocumentType"
    if (crossrefType == 'journal-article') {
        if (existingDoctypes.includes("article")) {
            document.getElementById("DocumentType").value = 'article'; } else {
            document.getElementById("DocumentType").value = 'other'; }
    }
    if (crossrefType == 'book') {
        if (existingDoctypes.includes("book")) {
            document.getElementById("DocumentType").value = 'book'; } else {
            document.getElementById("DocumentType").value = 'other'; }
    }
    if (crossrefType == 'book-set') {
        if (existingDoctypes.includes("book")) {
            document.getElementById("DocumentType").value = 'book'; } else {
            document.getElementById("DocumentType").value = 'other'; }
    }
    if (crossrefType == 'edited-book') {
        if (existingDoctypes.includes("book")) {
            document.getElementById("DocumentType").value = 'book'; } else {
            document.getElementById("DocumentType").value = 'other'; }
    }
    if (crossrefType == 'reference-book') {
        if (existingDoctypes.includes("book")) {
            document.getElementById("DocumentType").value = 'book'; } else {
            document.getElementById("DocumentType").value = 'other'; }
    }
    if (crossrefType == 'monograph') {
        if (existingDoctypes.includes("book")) {
            document.getElementById("DocumentType").value = 'book'; } else {
            document.getElementById("DocumentType").value = 'other'; }
    }
    if (crossrefType == 'book-chapter') {
        if (existingDoctypes.includes("bookpart")) {
            document.getElementById("DocumentType").value = 'bookpart'; } else {
            document.getElementById("DocumentType").value = 'other'; }
    }
    if (crossrefType == 'book-section') {
        if (existingDoctypes.includes("bookpart")) {
            document.getElementById("DocumentType").value = 'bookpart'; } else {
            document.getElementById("DocumentType").value = 'other'; }
    }
    if (crossrefType == 'book-part') {
        if (existingDoctypes.includes("bookpart")) {
            document.getElementById("DocumentType").value = 'bookpart'; } else {
            document.getElementById("DocumentType").value = 'other'; }
    }
    if (crossrefType == 'proceedings') {
        if (existingDoctypes.includes("conferenceobject")) {
            document.getElementById("DocumentType").value = 'conferenceobject'; } else {
            document.getElementById("DocumentType").value = 'other'; }
    }
    if (crossrefType == 'proceedings-article') {
        if (existingDoctypes.includes("conferenceobject")) {
            document.getElementById("DocumentType").value = 'conferenceobject'; } else {
            document.getElementById("DocumentType").value = 'other'; }
    }
    if (crossrefType == 'proceedings-series') {
        if (existingDoctypes.includes("conferenceobject")) {
            document.getElementById("DocumentType").value = 'conferenceobject'; } else {
            document.getElementById("DocumentType").value = 'other'; }
    }
    if (crossrefType == 'journal') {
        if (existingDoctypes.includes("periodical")) {
            document.getElementById("DocumentType").value = 'periodical'; } else {
            document.getElementById("DocumentType").value = 'other'; }
    }
    if (crossrefType == 'journal-volume') {
        if (existingDoctypes.includes("periodicalpart")) {
            document.getElementById("DocumentType").value = 'periodicalpart'; } else {
            document.getElementById("DocumentType").value = 'other'; }
    }
    if (crossrefType == 'journal-issue') {
        if (existingDoctypes.includes("periodicalpart")) {
            document.getElementById("DocumentType").value = 'periodicalpart'; } else {
            document.getElementById("DocumentType").value = 'other'; }
    }
    if (crossrefType == 'posted-content/preprint') {
        if (existingDoctypes.includes("preprint")) {
            document.getElementById("DocumentType").value = 'preprint'; } else {
            document.getElementById("DocumentType").value = 'other'; }
    }
    if (crossrefType == 'report') {
        if (existingDoctypes.includes("report")) {
            document.getElementById("DocumentType").value = 'report'; } else {
            document.getElementById("DocumentType").value = 'other'; }
    }
    if (crossrefType == 'report-series') {
        if (existingDoctypes.includes("report")) {
            document.getElementById("DocumentType").value = 'report'; } else {
            document.getElementById("DocumentType").value = 'other'; }
    }
    if (crossrefType == 'posted-content/report') {
        if (existingDoctypes.includes("report")) {
            document.getElementById("DocumentType").value = 'report'; } else {
            document.getElementById("DocumentType").value = 'other'; }
    }
    if (crossrefType == 'peer-review') {
        if (existingDoctypes.includes("review")) {
            document.getElementById("DocumentType").value = 'review'; } else {
            document.getElementById("DocumentType").value = 'other'; }
    }
    if (crossrefType == 'book-track') {
        if (existingDoctypes.includes("sound")) {
            document.getElementById("DocumentType").value = 'sound'; } else {
            document.getElementById("DocumentType").value = 'other'; }
    }
    if (crossrefType == 'posted-content/working_paper') {
        if (existingDoctypes.includes("workingpaper")) {
            document.getElementById("DocumentType").value = 'workingpaper'; } else {
            document.getElementById("DocumentType").value = 'other'; }
    }
            //Wenn crossrefType nur "dissertation", ohne Slash mit Degree
    if (crossrefType == 'dissertation') {
        if (existingDoctypes.includes("doctoralthesis")) {
            document.getElementById("DocumentType").value = 'doctoralthesis'; } else {
            document.getElementById("DocumentType").value = 'other'; }
    }

    if (crossrefType.includes("dissertation/")) { // Wenn crossrefType "dissertation" mit Slash: mit Degree
        const degree            = crossrefType.split('/')[1];
        const keys_master       = ["master", "mestrado", "m.phil.", "m.a.", "m.sc.", "ll. m.", "m. ed.", "m. eng.", "m. f. a.", "m. mus.", "ll.m.", "m.ed.", "m.eng.", "m.f.a.", "m.mus.", "m.s."];
        const keys_bachelor     = ["bachelor", "bacharel", "b.a.", "b.sc.", "ll. b.", "b. ed.", "b. eng.", "b. f. a.", "b. mus.", "b. m. a", "ll.b.", "b.ed.", "b.eng.", "b.f.a.", "b.mus.", "b.m.a"];
        const keys_habilitation = ["habil"];
        if (keys_master.some(el => degree.includes(el))) {
            if (existingDoctypes.includes("masterthesis")) {
                document.getElementById("DocumentType").value = 'masterthesis'; } else {
                document.getElementById("DocumentType").value = 'doctoralthesis'; }
        } else if (keys_bachelor.some(el_1 => degree.includes(el_1)) || degree === "ba") {
            if (existingDoctypes.includes("bachelorthesis")) {
                document.getElementById("DocumentType").value = 'bachelorthesis'; } else {
                document.getElementById("DocumentType").value = 'doctoralthesis'; }
        } else if (keys_habilitation.some(el_2 => degree.includes(el_2))) {
            if (existingDoctypes.includes("habilitation")) {
                document.getElementById("DocumentType").value = 'habilitation'; } else {
                document.getElementById("DocumentType").value = 'doctoralthesis'; }
        } else {
            document.getElementById("DocumentType").value = 'doctoralthesis'; }
    }
            document.getElementById("OpusDocumentType").value = document.getElementById("DocumentType").value;
            return text;
        }





