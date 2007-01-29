<HTML>
<HEAD>
<TITLE>Copy mLists</TITLE>
<META http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<LINK rel="stylesheet" href="../../../include/styles.css" type="text/css">
</HEAD>

<BODY class="background_main">
<H3>Copy mLists</H3>
<?php
	function mysystem($command) 
	{
    	if (!($p=popen("($command)2>&1","r"))) 
		{ 
        	return 126;
        }

        while (!feof($p)) 
		{
        	$line=fgets($p,1000);
            $out .= $line;
        }
        pclose($p);
        return $out; 
    }
	$var="../../../../mlists/copymlists.sh";
    echo mysystem($var);
?>
Verarbeitung erledigt!
</BODY>
</HTML>