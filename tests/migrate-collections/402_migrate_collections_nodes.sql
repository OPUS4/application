INSERT INTO collections_nodes(role_id, collection_id, left_id, right_id, visible)
SELECT c.role_id, c.id, s.left, s.right, s.visible FROM collections AS c, collections_structure_1 AS s WHERE c.role_id = 1 AND c.oldid = s.collections_id;

INSERT INTO collections_nodes(role_id, collection_id, left_id, right_id, visible)
SELECT c.role_id, c.id, s.left, s.right, s.visible FROM collections AS c, collections_structure_2 AS s WHERE c.role_id = 2 AND c.oldid = s.collections_id;

INSERT INTO collections_nodes(role_id, collection_id, left_id, right_id, visible)
SELECT c.role_id, c.id, s.left, s.right, s.visible FROM collections AS c, collections_structure_3 AS s WHERE c.role_id = 3 AND c.oldid = s.collections_id;

INSERT INTO collections_nodes(role_id, collection_id, left_id, right_id, visible)
SELECT c.role_id, c.id, s.left, s.right, s.visible FROM collections AS c, collections_structure_4 AS s WHERE c.role_id = 4 AND c.oldid = s.collections_id;

INSERT INTO collections_nodes(role_id, collection_id, left_id, right_id, visible)
SELECT c.role_id, c.id, s.left, s.right, s.visible FROM collections AS c, collections_structure_5 AS s WHERE c.role_id = 5 AND c.oldid = s.collections_id;

INSERT INTO collections_nodes(role_id, collection_id, left_id, right_id, visible)
SELECT c.role_id, c.id, s.left, s.right, s.visible FROM collections AS c, collections_structure_6 AS s WHERE c.role_id = 6 AND c.oldid = s.collections_id;

INSERT INTO collections_nodes(role_id, collection_id, left_id, right_id, visible)
SELECT c.role_id, c.id, s.left, s.right, s.visible FROM collections AS c, collections_structure_7 AS s WHERE c.role_id = 7 AND c.oldid = s.collections_id;

INSERT INTO collections_nodes(role_id, collection_id, left_id, right_id, visible)
SELECT c.role_id, c.id, s.left, s.right, s.visible FROM collections AS c, collections_structure_9 AS s WHERE c.role_id = 9 AND c.oldid = s.collections_id;

INSERT INTO collections_nodes(role_id, collection_id, left_id, right_id, visible)
SELECT c.role_id, c.id, s.left, s.right, s.visible FROM collections AS c, collections_structure_10 AS s WHERE c.role_id = 10 AND c.oldid = s.collections_id;
