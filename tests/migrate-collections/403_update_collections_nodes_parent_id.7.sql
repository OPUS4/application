DROP TABLE IF EXISTS temp_1;

CREATE TABLE temp_1
SELECT l.role_id AS role_id, l.id AS node_id, l.left_id, MAX(p.left_id) AS parent_left_id FROM collections_nodes AS l, collections_nodes AS p
 WHERE p.role_id = l.role_id AND l.role_id = 7
   AND l.left_id > p.left_id AND l.left_id < p.right_id
 GROUP BY node_id;
    
DROP TABLE IF EXISTS temp_2;

CREATE TABLE temp_2
SELECT t.node_id AS nid, p.id AS pid FROM temp_1 AS t, collections_nodes AS p
WHERE t.role_id = p.role_id AND t.parent_left_id = p.left_id;
    
UPDATE collections_nodes, temp_2 SET collections_nodes.parent_id = temp_2.pid WHERE temp_2.nid = collections_nodes.id AND collections_nodes.role_id = 7;

DROP TABLE IF EXISTS temp_1;
DROP TABLE IF EXISTS temp_2;                                                          
