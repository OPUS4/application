-- Create enrichment keys necessary for import metadata

INSERT INTO `enrichmentkeys` (`name`) VALUES
('opus.import.checksum'),
('opus.import.date'),
('opus.import.file'),
('opus.import.user');