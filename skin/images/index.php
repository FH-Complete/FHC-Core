<!DOCTYPE html>
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<link rel="stylesheet" href="../fhcomplete.css" />
	<link rel="stylesheet" href="../vilesci.css" />
</head>
<body>
<h1>Bilder</h1>
<table>
<?php
$dir = dirname(__FILE__).'/';

if (is_dir($dir))
{
    if ($dh = opendir($dir)) 
	{
        while (($file = readdir($dh)) !== false) 
		{
			if(filetype($dir . $file)=='file')
			echo '
			<tr>
				<td>
					<img src="'.$file.'" style="max-height:30px; max-width:50px" />
				</td>
				<td><a href="'.$file.'">'.$file.'</a></td>
			</tr>';
            //echo "filename: $file : filetype: " .  . "\n";
        }
        closedir($dh);
    }
}
?>
</table>
</body>
