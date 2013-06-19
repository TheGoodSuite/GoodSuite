<?php

if ($_GET['file'] != 'glue.php' && $_GET['file'] != 'sampleApplication.php' &&
        $_GET['file'] != 'sampleTemplate.html' && $_GET['file'] != 'sampleTemplate.html.compiledTemplate')
{
    header("HTTP/1.0 404 Not Found");
    exit;
}

if (!file_exists($_GET['file']))
{
    exit("File not found.");
}

echo "<html>\n";
echo "<head>\n";
echo "<title>Showing " . $_GET['file'] . "</title>\n";
echo "</head>\n";
echo "<body>\n";

echo "<pre>";
echo htmlentities(file_get_contents($_GET['file']));
echo "</pre>\n";

echo "</body>";
echo "</html>";

?>