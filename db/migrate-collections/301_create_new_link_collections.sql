--
-- Verbinde Dokumente und Collections miteinander...
--

CREATE TABLE link_documents_collections (
   document_id     INT UNSIGNED NOT NULL,
   collection_id   INT UNSIGNED NOT NULL,
   role_id         INT UNSIGNED NOT NULL,

   PRIMARY KEY(collection_id, document_id),
   FOREIGN KEY(document_id)            REFERENCES documents(id),
   FOREIGN KEY(collection_id)          REFERENCES collections(id),
   FOREIGN KEY(role_id)                REFERENCES collections_roles(id),
   FOREIGN KEY(role_id, collection_id) REFERENCES collections(role_id, id),
   INDEX(document_id, collection_id),
   INDEX(document_id, role_id)
) ENGINE = InnoDB
CHARACTER SET = 'utf8'
COLLATE = 'utf8_general_ci';
