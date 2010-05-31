ALTER TABLE `collections` ADD `oldid` INT UNSIGNED NOT NULL AFTER `id`, ADD INDEX ( `oldid` );

INSERT INTO collections (oldid, role_id, name, address, city, phone, dnb_contact_id, is_grantor)
SELECT id, 1 AS role_id, name, address, city, phone, dnb_contact_id, is_grantor FROM collections_contents_1;

INSERT INTO collections (oldid, role_id, name, number)
SELECT id, 2 AS role_id, name, number FROM collections_contents_2;

INSERT INTO collections (oldid, role_id, name, number)                                                
SELECT id, 3 AS role_id, name, number FROM collections_contents_3;

INSERT INTO collections (oldid, role_id, name, number)                                                
SELECT id, 4 AS role_id, name, number FROM collections_contents_4;                                          

INSERT INTO collections (oldid, role_id, name, number)                                                
SELECT id, 5 AS role_id, name, number FROM collections_contents_5;                                          

INSERT INTO collections (oldid, role_id, name, number)                                                
SELECT id, 6 AS role_id, name, number FROM collections_contents_6;                                          

INSERT INTO collections (oldid, role_id, name, number)                                                
SELECT id, 7 AS role_id, name, number FROM collections_contents_7;                                          

INSERT INTO collections (oldid, role_id, name)                                                
SELECT id, 9 AS role_id, name FROM collections_contents_9;                                          

INSERT INTO collections (oldid, role_id, name)                                                
SELECT id, 10 AS role_id, name FROM collections_contents_10;                                          
