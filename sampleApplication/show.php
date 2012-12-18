<?php

if ($_GET['file'] != 'glue.php' && $_GET['file'] != 'sampleApplication.php' &&
        $_GET['file'] != 'sampleTemplate.html' && $_GET['file'] != 'sampleTemplate.html.compiledTemplate')
{
    header("HTTP/1.0 404 Not Found");
    die();
}

if (!file_exists($_GET['file']))
{
    die("File not found.");
}

echo "<html>\n";
echo "<head>\n";
echo "<title>Showing " . $_GET['file'] . "</title>\n";
echo "</head>\n";
echo "<body>\n";

echo "<pre>";
echo htmlentities(file_get_contents($_GET['file']));

if ($_GET['file'] == 'sampleTemplate.html' || $_GET['file'] == 'sampleApplication.html.compiledTemplate')
{
    echo "&lt;/body&gt;\n&lt;/html&gt;";
}

echo "</pre>\n";

echo "<script type='text/javascript'>\n";
echo "  parent.document.getElementById('menuframe').contentDocument.getElementById('stats').innerHTML = '';\n";
echo "</script>\n";

echo "</body>\n";
echo "</html>\n";

?>