"use strict";
var exports = new Object;
Object.defineProperty(exports, "__esModule", { value: true });
exports.getEdition = exports.getArticleNumber = exports.getLicence = exports.getSubject = exports.getThesisAccepted = exports.getPublisherPlace = exports.getTranslator = exports.getConferencePlace = exports.getConferenceTitle = exports.getUrl = exports.getIssn = exports.getEditor = exports.getIsbn = exports.getCompletedDate = exports.getVolume = exports.getIssue = exports.getPages = exports.getAuthor = exports.getPersonAuthorAcademicTitle = exports.getPersonAuthorIdentifierOrcid = exports.getType = exports.getTitleParent = exports.getAbstract = exports.getPersonAuthorLastName = exports.getPersonAuthorFirstName = exports.getOtherperson = exports.getTranslator = exports.getEditor = exports.getContributor = exports.getIdentifierIsbn = exports.getLanguage = exports.getIdentifierUrl = exports.getContributingCorporation = exports.getNote = exports.getPageCount = exports.getEdition = exports.getTitleSub = exports.getCreatingCorporation = exports.getId = exports.getCompletedYear = exports.getPublisherName = exports.getTitleMain = exports.parseDoi = void 0;


function finalize(field)
{
  // Grüne Farbe und Feldname wird in populatedFields geschrieben, um die Feldfarben nach einem Reload neu aufzubauen
    colorGreen(field);
    populatedFields.push(field);
}

function colorGreen(field)
{
    document.getElementById(field).style.backgroundColor = "#BFFFCF";
}
function colorPink(field)
{
    document.getElementById(field).style.backgroundColor = "#FFC0CB";
}


///////////////////////////////////////////////////////////////////////////
// Ab hier weden die einzelnen Felder analysiert.


function getLicence(json)
{
    if (json['message']['license'] != undefined) {
        var result = json['message']['license'][0]['URL'];
        if (result != undefined) {
            finalize("Enrichmentlocal_crossrefLicence")
        }
    }
    return result ? result : ''
}
exports.getLicence = getLicence;


function getSubject(json)
{
    if (json.message.subject != undefined) {
        var _z, subject;
        var subjects = [];
        var _laenge  = json.message.subject.length;
        if (_laenge > 0) {
            for (_z = 0; _z < _laenge; _z++) {
                subject = json.message.subject[_z];
                subjects.push(subject);
            }
        }
        return subjects
    } else {
        return ''
    }
}
exports.getSubject = getSubject;


function getConferenceTitle(json)
{
    if (json['message']['event'] != undefined) {
        var result = json['message']['event']['name'];
        if (result != undefined) {
            finalize("Enrichmentconference_title")
        }
    }
    return result ? result : ''
}
exports.getConferenceTitle = getConferenceTitle;

function getConferencePlace(json)
{
    if (json['message']['event'] != undefined) {
        var result = json['message']['event']['location'];
        if (result != undefined) {
            finalize("Enrichmentconference_place")
        }
    }
    return result ? result : ''
}
exports.getConferencePlace = getConferencePlace;

function getUrl(json)
{
    if (json['message']['link'] != undefined && json['message']['link'][0]['URL'] != undefined) {
        var result = json['message']['link'][0]['URL'];
        if (result != undefined) {
            finalize("IdentifierUrl")
        }
    }
    return result ? result : ''
}
exports.getUrl = getUrl;

function getIssn(json)
{
    if (json['message']['issn-type'] != undefined) {
        var result = json['message']['issn-type'][0]['value'];
        if (result != undefined) {
            finalize("IdentifierIssn")
        }
    }
    return result ? result : ''
}
exports.getIssn = getIssn;

function getIsbn(json)
{
    if (json['message']['isbn-type'] != undefined) {
        var result = json['message']['isbn-type'][0]['value'];
        if (result != undefined) {
            finalize("IdentifierIsbn")
        }
    }
    return result ? result : ''
}
exports.getIsbn = getIsbn;

function getCompletedDate(json)
{
    if (json['message']['issued']['date-parts'][0] != undefined && json['message']['issued']['date-parts'][0][0] != null) {
        var dates = [];

        if (json['message']['issued']['date-parts'][0][0]) {
            var resultYear = json['message']['issued']['date-parts'][0][0];
            dates.push(resultYear);
        } else {
            dates.push('-')
        }

        if (json['message']['issued']['date-parts'][0][1]) {
            var resultMonth = json['message']['issued']['date-parts'][0][1];
            dates.push(resultMonth);
        } else {
            dates.push('-')
        }

        if (json['message']['issued']['date-parts'][0][2]) {
            var resultDay = json['message']['issued']['date-parts'][0][2];
            dates.push(resultDay);
        } else {
            dates.push('-')
        }

        return dates;
    } else {
        return '';
    }
}
exports.getCompletedDate = getCompletedDate;

function getThesisAccepted(json)
{
    if (json['message']['approved'] != undefined && json['message']['approved']['date-parts'][0] != undefined) {
        var dates = [];

        if (json['message']['approved']['date-parts'][0][0]) {
            var resultYear = json['message']['approved']['date-parts'][0][0];
            dates.push(resultYear);
        } else {
            dates.push('-')
        }

        if (json['message']['approved']['date-parts'][0][1]) {
            var resultMonth = json['message']['approved']['date-parts'][0][1];
            dates.push(resultMonth);
        } else {
            dates.push('-')
        }

        if (json['message']['approved']['date-parts'][0][2]) {
            var resultDay = json['message']['approved']['date-parts'][0][2];
            dates.push(resultDay);
        } else {
            dates.push('-')
        }

        return dates;
    } else {
        return '';
    }
}
exports.getThesisAccepted = getThesisAccepted;


function getVolume(json)
{
    var result = json.message.volume;
    if (result != undefined) {
        finalize("Volume")
    }
    return result ? result : ''
}
exports.getVolume = getVolume;


function getIssue(json)
{
    if (json['message']['issue'] != undefined) {
        var result = json['message']['issue'];
        finalize("Issue");
        return result
    } else {
        return ''
    }
}
exports.getIssue = getIssue;

function getEdition(json)
{
    if (json['message']['edition-number'] != undefined && json['message']['edition-number'] != '0') {
        var result = json['message']['edition-number'];
        finalize("Edition");
        return result
    } else {
        return ''
    }
}
exports.getEdition = getEdition;


function getPages(json)
{
    if (json['message']['page'] != undefined) {
        var result = json.message.page;
        return result
    } else {
        return ''
    }
}
exports.getPages = getPages;

function getArticleNumber(json)
{
    if (json['message']['article-number'] != undefined) {
        const articlenumber = json['message']['article-number'];
        finalize("ArticleNumber");
        return articlenumber;
    } else if (json['message']['page'] != undefined && ! json['message']['page'].includes('-') && /^\d+$/.test(json['message']['page'])) {
        const articlenumber = json['message']['page'];
        finalize("ArticleNumber");
        return articlenumber;
    } else {
        return ''
    }
}
exports.getArticleNumber = getArticleNumber;


function getType(json)
{
    var result = json.message.type;
    if (result != null) {
        if (result == 'posted-content' && json.message.subtype != null) {
            result = result + '/' + json.message.subtype;
        }
        if (result == 'dissertation' && json.message.degree != undefined) {
            result = result + '/' + json.message.degree[0].toLowerCase();
        }

        return result;
    }
}
exports.getType = getType;


function getEditor(json)
{
    if (json.message.editor) {
        var vorname, nachname, _z, orcid, complete_name;
        var editors = [];
        var _laenge = json.message.editor.length;
        if (_laenge > 0) {
            for (_z = 0; _z < _laenge; _z++) {
                vorname  = json.message.editor[_z].given;
                nachname = json.message.editor[_z].family;
                if (json.message.editor[_z].ORCID != null) {
                    if (json.message.editor[_z].ORCID.includes("/")) {
                        var orcid_raw = json.message.editor[_z].ORCID;
                        let re        = orcid_raw.match(/([\d\-X]+)/g);
                        if (re != null) {
                            orcid = re[0];} else {
                            orcid = ''}
                    } else {
                        orcid = json.message.editor[_z].ORCID;}
                } else {
                    orcid = ''
                }
                complete_name = nachname + ',' + vorname + ',' + orcid;
                editors.push(complete_name);
            }
        }
        return editors
    } else {
        return ''
    }
}
exports.getEditor = getEditor;


function getTranslator(json)
{
    if (json.message.translator) {
        var vorname, nachname, _z, complete_name;
        var translators = [];
        var _laenge     = json.message.translator.length;
        if (_laenge > 0) {
            for (_z = 0; _z < _laenge; _z++) {
                vorname       = json.message.translator[_z].given;
                nachname      = json.message.translator[_z].family;
                complete_name = nachname + ',' + vorname;
                translators.push(complete_name);
            }
        }
        return translators
    } else {
        return ''
    }
}
exports.getTranslator = getTranslator;


function getAuthor(json)
{
    if (json.message.author) {
        var vorname, nachname, orcid, _z, complete_name;

        var authors = [];
        var _laenge = json.message.author.length;

        if (_laenge > 0) {
            // Zuerst alle Autoren in das 'authors'-Array einfügen
            for (_z = 0; _z < _laenge; _z++) {
                if (json.message.author[_z].given != null || json.message.author[_z].family != null) {
                    vorname  = json.message.author[_z].given;
                    nachname = json.message.author[_z].family;
                    if (json.message.author[_z].ORCID != null) {
                        if (json.message.author[_z].ORCID.includes("/")) {
                            var orcid_raw = json.message.author[_z].ORCID;
                            let re        = orcid_raw.match(/([\d\-X]+)/g);
                            if (re != null) {
                                orcid = re[0];
                            } else {
                                orcid = '';
                            }
                        } else {
                            orcid = json.message.author[_z].ORCID;
                        }
                    } else {
                        orcid = '';
                    }
                    complete_name = nachname + ',' + vorname + ',' + orcid;
                    authors.push(complete_name);
                }
            }

            // Prüfen, ob es mehr als 50 Autoren gibt
            if (_laenge > 50) {
                // Den letzten Autor extrahieren
                let lastAuthor;
                //lastAuthor = authors[_laenge - 1];
                if (authors[_laenge - 1]) {
                    lastAuthor = authors[_laenge - 1];
                } else {
                    lastAuthor = authors[_laenge - 2];
                }
                // Entferne den letzten Autor vom Array (da wir ihn an Position 50 wieder einfügen)
                authors.pop();
                // Füge den letzten Autor an der 50. Position ein
                authors.splice(49, 0, lastAuthor);
                //alert("lastAuthor: "+lastAuthor);
            }
        }
        return authors; // Gibt das Autoren-Array direkt zurück
    } else {
        return '';
    }
}
exports.getAuthor = getAuthor;


function getTitleMain(json)
{
    var result = json.message.title[0];
    if (result != undefined) {
        finalize("TitleMain_1")
    }
    return result ? result : ''
}
exports.getTitleMain = getTitleMain;

function getContributingCorporation(json)
{
    if (json.message.author) {
        var name, _z;
        var _laenge = json.message.author.length;
        if (_laenge > 0) {
            for (_z = 0; _z < _laenge; _z++) {
                if (json.message.author[_z].name != null) {
                    name = json.message.author[_z].name;
                    finalize("ContributingCorporation")
                    return name;
                } else {
                    return ''
                }
            }
        }
    } else {
        return ''
    }
}
exports.ContributingCorporation = getContributingCorporation;


function getTitleSub(json)
{
    var result = json.message.subtitle;
    if (result != undefined && result != '') {
        colorGreen("TitleSub_1")}
    return result ? result : ''}
exports.getTitleSub = getTitleSub;

function getTitleParent(json)
{
    if (json['message']['container-title']['0']) {
        var result = json['message']['container-title']['0'];
        if (result.includes("&amp;")) {
            result = result.replace("&amp;", "&")
        }
        if (result != undefined) {
            finalize("TitleParent_1")
        }
    }
    return result ? result : ''
}
exports.getTitleParent = getTitleParent;


function getLanguage(json)
{
    var result = json.message.language;
    if (result != undefined) {
        finalize("Language")
    } else {
        colorPink("Language")
    }
    if (result != null) {
        return result;
    }
}
exports.getLanguage = getLanguage;


function getAbstract(json)
{
    var raw = json.message.abstract;
    if (raw != undefined) {
        finalize("TitleAbstract_1");
        var str     = raw.toString();
        var result0 = str.replace(/[\t ]+/g, " ");
        var result  = result0.replace(/<[^>]*(>|$)|jats:sec|jats:title|jats:p|&laquo;|&gt;/g, '');
    }
    return result ? result : ''
}
exports.getAbstract = getAbstract;


function getPublisherName(json)
{
    var result = json.message.publisher;
    if (result.includes('&amp;')) {
        result = result.replace("&amp;", "&")}
    if (result != undefined) {
        finalize("PublisherName")
    }
    return result ? result : '';
}
exports.getPublisherName = getPublisherName;

function getPublisherPlace(json)
{
    var result = json['message']['publisher-location'];
    if (result != undefined) {
        finalize("PublisherPlace")
    }
    return result ? result : '';
}
exports.getPublisherPlace = getPublisherPlace;
