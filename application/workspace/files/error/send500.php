<?php
header($_SERVER['SERVER_PROTOCOL'] . " 500 Internal Server Error");

echo('
<!DOCTYPE HTML PUBLIC "-//IETF//DTD HTML 2.0//EN">
<html><head>
<title>500 Internal Server Error</title>
</head><body>
<h1>Internal Server Error</h1>
<p>The server encountered an internal error and was unable to complete your request. Either the server is overloaded or there was an error in a CGI script.</p>
<hr>
</body></html>
');
