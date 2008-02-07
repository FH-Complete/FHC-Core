<?php
// Dieses Script verhindert, dass das Dokument inline geoeffnet wird.
// Es erschein automatisch das Download/Speichern Fenster
	if (isset($_GET["format"]) && $_GET["format"] == "doc"){
		$filename= "../../cisdocs/muster_semesterplan.doc";
		$format = "doc";
	}
	else{
    	$filename = "../../cisdocs/muster_semesterplan_index.html";
		$format = "html";    
    }
    $fp = fopen($filename, "rb");
    if ($fp)
    {
    	header("Content-Type: application/html");
		    	
    	header("Content-Disposition: attachment; filename=\"Semesterplan.".$format."\"");
        $buffer = fread ($fp, filesize ($filename));
        echo $buffer;
        fclose($fp);
    }
    else 
    	echo 'Datei wurde nicht gefunden';
    exit();
?> 