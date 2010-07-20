<?php
header($_SERVER['SERVER_PROTOCOL'] . " 401 Unauthorized");

echo('
<!DOCTYPE HTML PUBLIC "-//IETF//DTD HTML 2.0//EN">
<html><head>
<title>401 Unauthorized</title>
</head><body>
<h1>Unauthorized</h1>
<p>You were not authorized to request this URL.</p>
<hr>
</body></html>
');

