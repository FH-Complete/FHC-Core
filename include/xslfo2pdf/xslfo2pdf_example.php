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
if ($_GET["file"]) {
  require_once("xslfo2pdf.php");
  $buffer = file_get_contents($_GET["file"]);
  $fo2pdf = new XslFo2Pdf(); 
  if (!$fo2pdf->generatePdf($buffer, $_GET["file"], "D")) {
    echo "Failed parsing file:".$_GET["file"]."<br>";
  }
 }
 else {
   $files = addDir("examples");
   foreach ($files as $file) {
     echo('<a href="xslfo2pdf_example.php?file='.$file.'">'.$file.'</a><br>');
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
	   $files[] = $dirname . "/" . $file;	
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
