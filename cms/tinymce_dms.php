<?php
/* Copyright (C) 2011 FH Technikum Wien
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307, USA.
 *
 * Authors: Christian Paminger <christian.paminger@technikum-wien.at>,
 *          Andreas Oesterreicher <andreas.oesterreicher@technikum-wien.at> and
 *          Karl Burkhart <karl.burkhart@technikum-wien.at>.
 */
require_once('../config/cis.config.inc.php');
require_once('../include/dms.class.php');
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<html>
<head>
	<title>FHComplete Document Management System</title>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
	<link rel="stylesheet" href="../skin/style.css.php" type="text/css">
	<script type="text/javascript" src="../include/tiny_mce/tiny_mce_popup.js"></script>
	<script type="text/javascript">
	var FileBrowserDialog={
			init: function(){
			},
			mySubmit : function (id) {
				  var URL = "dms.php?id="+id;
			        var win = tinyMCEPopup.getWindowArg("window");

			        // insert information now
			        win.document.getElementById(tinyMCEPopup.getWindowArg("input")).value = URL;

			        // are we an image browser
			        if (typeof(win.ImageDialog) != "undefined") {
			            // we are, so update image dimensions...
			            if (win.ImageDialog.getImageData)
			                win.ImageDialog.getImageData();

			            // ... and preview if necessary
			            if (win.ImageDialog.showPreviewImage)
			                win.ImageDialog.showPreviewImage(URL);
			        }

			        // close popup window
			        tinyMCEPopup.close();
			}
	};
	tinyMCEPopup.onInit.add(FileBrowserDialog.init, FileBrowserDialog);
	</script>
</head>
<body>

<a href="#" onclick="FileBrowserDialog.mySubmit(1)">File 1</a>
<a href="#" onclick="FileBrowserDialog.mySubmit(2)">File 2</a>
<a href="#" onclick="FileBrowserDialog.mySubmit(3)">File 3</a>
<a href="#" onclick="FileBrowserDialog.mySubmit(4)">File 4</a>

</body>
</html>