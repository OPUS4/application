--
-- Daten für Tabelle `enrichments`
--

INSERT INTO `enrichmentkeys` (`name`) VALUES
('submitter.user_id'), 
('reviewer.user_id'),
('review.rejected_by'),
('review.accepted_by'),
('BibtexRecord'),
('Relation'),
('Audience'),
('Coverage'),
('opus_doi_json'),
('opus_crossrefDocumentType'),
('opus_crossrefLicence'),
('opus_doiImportPopulated'),
('opus_import_origin'),
('opus_doi_flag');


-- 'opus_doi_json': Enthält nach dem DOI-Import die kompletten Metadaten des Dokuments von Crossref. Wird gebraucht, wenn die Page im Browser neu geladen wird (nicht user-relevant).

-- 'opus_crossrefDocumentType': Enthält nach dem Import den Dokumenttyp, der in Crossref angegeben ist (kann user-relevant sein, zwecks Nachvollziehbarkeit des Mappings).

-- 'opus_crossrefLicence': Enthält die Lizenz, die in Crossref angegeben ist (user-relevant, da die OPUS-Lizenz u.U. danach einzutagen ist).

-- 'opus_doiImportPopulated': Enthält eine Liste der Felder, die mittels DOI-Import befüllt wurden (kommasepariert). Wird benötigt, um bei einer Leerung des Formulars die Werte zurückzusetzen (nicht user-relevant).

-- 'opus_import_origin': Enthält die Quelle des DOI-Imports, z.Zt. immer "crossref" (evtl. user-relevant, z.B. als Facette)

-- 'opus_doi_flag': Flag wird 'true', wenn im aktuellen Formular ein DOI-Import durchgeführt wurde und alle verfügbaren Werte (insbesondere mehrfach belgbare Felder) ins Formular eingetragen wurden, so dass danach kein Reload mehr kommen kann (nicht user-relevant).
