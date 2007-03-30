<?PHP /*
xslfo2pdf
Copyright (C) 2005       Tegonal GmbH

This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.

Contact:
mike.toggweiler@tegonal.com
http://xslf2pdf.tegonal.com
*/ ?>
<?PHP
if ($_GET["file"] ) {
  require_once("xslfo2pdf.php");
  $buffer = file_get_contents($_GET["file"]);
  $fo2pdf = new XslFo2Pdf(); 
  if (!$fo2pdf->generatePdf($buffer, $_GET["file"], "D")) {
    echo "Failed parsing file:".$_GET["file"]."<br>";
  }
 }
 else if ($_FILES["file"]) {
  $buffer = file_get_contents($_FILES['file']['tmp_name']);
  require_once("xslfo2pdf.php");
  $fo2pdf = new XslFo2Pdf(); 
  if (!$fo2pdf->generatePdf($buffer, $_FILES['file']['name'], "D")) {
    echo "Failed parsing file:".$_FILES['file']['name']."<br>";
	//store file in specific directory that failed document may be checked by the developer team
	file_put_contents("failed/".basename($_FILES['file']['name']));
	echo "Stored file for inspection";
  }
 }
 else {
	 ?>
<div >
<a target="_new" href="http://www.tegonal.com/en"><img src="http://www.tegonal.com/images/tegonal_logo.png" border="0"></img></a>
<p>
Browse a local file to process:
<form name="extfile" action="index.php" method="post" enctype="multipart/form-data">
	<input type="hidden" name="MAX_FILE_SIZE" value="30000" />
	<input name="file" type="file" size="50" maxlength="100000" accept="text/*.xml">
	<input type="submit" value="Process" />
</form>
</p>
<p>
Or choose one of the predefined examples<br />
<?PHP

   $files = addDir("examples");
   foreach ($files as $file) {
     echo('<a href="index.php?file='.$file.'">'.$file.'</a>(<a href="'.$file.'">.xml</a>)<br>');
   }
 }

function addDir($dirname) {
  $dir = dir($dirname);
   $files = array();
   while ($file = $dir->read()) {
     if($file != "." && $file != "..") {
       if (!is_dir($dirname."/".$file)) {
	 $path_parts = pathinfo($file);
	 
	 if ($path_parts['extension'] == "xml" || 
	     $path_parts['extension'] == "fo") {
	   $files[$file] = $dirname . "/" . $file;	
	 }
       }
       else {
	 $files += addDir($dirname."/".$file);	 
       }
     }
   }	    	
   $dir->close();
   asort($files);
   return $files;
}
?>
</p>
<p>XPMT is kindly hosted on <br/><a class="normallink" href="http://sourceforge.net" target="_new">
  <img src="http://sourceforge.net/sflogo.php?group_id=132608&amp;type=1" width="88" height="31" border="0" alt="SourceForge.net Logo" /></a>
</p>
</div>
</div>
