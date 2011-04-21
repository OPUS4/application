--
-- Insert Default Roles
--

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";

--
-- System permissions.
--

INSERT INTO access_modules (role_id, module_name) VALUES
(1, 'admin'),
(2, 'home'),
(2, 'frontdoor'),
(2, 'default'),
(2, 'solrsearch'),
(2, 'publish'),
(2, 'rewrite'),
(2, 'rss'),
(2, 'citationExport'),
(4, 'review');
