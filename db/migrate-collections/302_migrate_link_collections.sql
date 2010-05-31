INSERT INTO link_documents_collections(document_id, collection_id, role_id)
SELECT l.documents_id, c.id, c.role_id FROM collections AS c, link_documents_collections_1 AS l WHERE c.role_id = 1 AND c.oldid = l.collections_id;

INSERT INTO link_documents_collections(document_id, collection_id, role_id)
SELECT l.documents_id, c.id, c.role_id FROM collections AS c, link_documents_collections_2 AS l WHERE c.role_id = 2 AND c.oldid = l.collections_id;

INSERT INTO link_documents_collections(document_id, collection_id, role_id)
SELECT l.documents_id, c.id, c.role_id FROM collections AS c, link_documents_collections_3 AS l WHERE c.role_id = 3 AND c.oldid = l.collections_id;

INSERT INTO link_documents_collections(document_id, collection_id, role_id)
SELECT l.documents_id, c.id, c.role_id FROM collections AS c, link_documents_collections_4 AS l WHERE c.role_id = 4 AND c.oldid = l.collections_id;

INSERT INTO link_documents_collections(document_id, collection_id, role_id)
SELECT l.documents_id, c.id, c.role_id FROM collections AS c, link_documents_collections_5 AS l WHERE c.role_id = 5 AND c.oldid = l.collections_id;

INSERT INTO link_documents_collections(document_id, collection_id, role_id)
SELECT l.documents_id, c.id, c.role_id FROM collections AS c, link_documents_collections_6 AS l WHERE c.role_id = 6 AND c.oldid = l.collections_id;

INSERT INTO link_documents_collections(document_id, collection_id, role_id)
SELECT l.documents_id, c.id, c.role_id FROM collections AS c, link_documents_collections_7 AS l WHERE c.role_id = 7 AND c.oldid = l.collections_id;

INSERT INTO link_documents_collections(document_id, collection_id, role_id)
SELECT l.documents_id, c.id, c.role_id FROM collections AS c, link_documents_collections_9 AS l WHERE c.role_id = 9 AND c.oldid = l.collections_id;

INSERT INTO link_documents_collections(document_id, collection_id, role_id)
SELECT l.documents_id, c.id, c.role_id FROM collections AS c, link_documents_collections_10 AS l WHERE c.role_id = 10 AND c.oldid = l.collections_id;

