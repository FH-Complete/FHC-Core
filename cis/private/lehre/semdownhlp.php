<?php
    $filename = "muster_semesterplan_index.html";
    $fp = fopen($filename, "rb");
    if ($fp)
    {
    	header("Content-Type: application/html");
    	header("Content-Disposition: attachment; filename=\"Semesterplan.html\"");
        $buffer = fread ($fp, filesize ($filename));
        echo $buffer;
        fclose($fp);
    }
    else 
    	echo 'Datei wurde nicht gefunden';
    exit();
?> 