<?php
/* Copyright (C) 2006 Technikum-Wien
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
 *          Rudolf Hangl 		< rudolf.hangl@technikum-wien.at >
 *          Gerald Simane-Sequens 	< gerald.simane-sequens@technikum-wien.at >
 *
 */

// Oberflaeche zum Upload von Bildern

//session_cache_limiter('none'); //muss gesetzt werden damit der upload in chrome und das automatische updaten des profilbildes funktioniert
require_once('../../config/cis.config.inc.php');
require_once('../../include/functions.inc.php');
require_once('../../include/person.class.php');
require_once('../../include/benutzer.class.php');
require_once('../../include/akte.class.php');
require_once('../../include/phrasen.class.php');
require_once('../../include/fotostatus.class.php');

$user = get_uid();
$sprache = getSprache();
$p = new phrasen($sprache);

echo '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
	<link href="../../skin/style.css.php" rel="stylesheet" type="text/css">
	<link rel="stylesheet" type="text/css" href="../../skin/jquery-ui.css">
	<link rel="stylesheet" type="text/css" href="../../skin/simplecropper.css">'.
	cropCss().'
	<script type="text/javascript" src="../../include/js/jquery.js"></script>
	<script type="text/javascript" src="../../include/js/jquery.min.1.11.1.js"></script>
	<script type="text/javascript" src="../../include/js/jquery.Jcrop.js"></script>
	<script type="text/javascript" src="../../include/js/jquery.SimpleCropper.js"></script>
	<script type="text/javascript" src="../../include/js/cropper.js"></script>
	<title>'.$p->t('profil/Bildupload').'</title>
</head>
<body>
<h1>'.$p->t('profil/Bildupload').'</h1>';

function resize($filename, $width, $height)
{
		$ext = explode('.',$_FILES['bild']['name']);
		$ext = strtolower($ext[count($ext)-1]);
	
		// Hoehe und Breite neu berechnen
		list($width_orig, $height_orig) = getimagesize($filename);
	
		if ($width && ($width_orig < $height_orig)) 
		{
		   $width = ($height / $height_orig) * $width_orig;
		}
		else
		{
		   $height = ($width / $width_orig) * $height_orig;
		}
		
		$image_p = imagecreatetruecolor($width, $height);                       
				
		$image = imagecreatefromjpeg($filename);
		
		//Bild nur verkleinern aber nicht vergroessern
		if($width_orig>$width || $height_orig>$height)
			imagecopyresampled($image_p, $image, 0, 0, 0, 0, $width, $height, $width_orig, $height_orig);
		else 
			$image_p = $image;
			
		imagejpeg($image_p, $filename, 80);
			
		@imagedestroy($image_p);
		@imagedestroy($image);
}


if(isset($_GET['person_id']))
{
	$benutzer = new benutzer();
	$benutzer->load($user);
		
	if($benutzer->person_id!=$_GET['person_id'])
		die($p->t('global/keineBerechtigungFuerDieseSeite'));
		
	$fs = new fotostatus();
	if($fs->akzeptiert($benutzer->person_id))
		die($p->t('profil/profilfotoUploadGesperrt'));
}
else 
	die($p->t('global/fehlerBeiDerParameteruebergabe'));

echo '<br>';
echo $p->t('profil/BilduploadInfotext',array($p->t('dms_link/bildRichtlinien'))).'<br><br>';
echo '<div class="simple-cropper-images">
	 '.$p->t('profil/fotoAuswählen').'
      <div class="cropme" id="croppingdiv" style="width: 300px; height: 400px; background-image:url(../../skin/images/photoupload.png); margin:10px; cursor:pointer;"></div>
	  <script>
		// Init Simple Cropper
		$(".cropme").simpleCropper();
	  </script>
      </div>
	  <input type="button" name="submitbild" id="saveimgbutton" value="'.$p->t('profil/bildSpeichern').'" style="margin-left:90px;"/>
 	  <input type="hidden" id="person_id" value="'.$_GET['person_id'].'" />';

if (isset($_POST['src'])) {
	$src = $_POST['src'];
	echo $src;
}

function cropCss() {
	return '
	<style type="text/css">
		/* jquery.Jcrop.css v0.9.12 - MIT License */
		/*
		  The outer-most container in a typical Jcrop instance
		  If you are having difficulty with formatting related to styles
		  on a parent element, place any fixes here or in a like selector
		
		  You can also style this element if you want to add a border, etc
		  A better method for styling can be seen below with .jcrop-light
		  (Add a class to the holder and style elements for that extended class)
		*/
		.jcrop-holder {
		  direction: ltr;
		  text-align: left;
		}
		/* Selection Border */
		.jcrop-vline,
		.jcrop-hline {
		  background: #ffffff url("../images/Jcrop.gif");
		  font-size: 0;
		  position: absolute;
		}
		.jcrop-vline {
		  height: 100%;
		  width: 1px !important;
		}
		.jcrop-vline.right {
		  right: 0;
		}
		.jcrop-hline {
		  height: 1px !important;
		  width: 100%;
		}
		.jcrop-hline.bottom {
		  bottom: 0;
		}
		/* Invisible click targets */
		.jcrop-tracker {
		  height: 100%;
		  width: 100%;
		  /* "turn off" link highlight */
		  -webkit-tap-highlight-color: transparent;
		  /* disable callout, image save panel */
		  -webkit-touch-callout: none;
		  /* disable cut copy paste */
		  -webkit-user-select: none;
		}
		/* Selection Handles */
		.jcrop-handle {
		  background-color: #333333;
		  border: 1px #eeeeee solid;
		  width: 14px;
		  height: 14px;
		  font-size: 1px;
		}
		.jcrop-handle.ord-n {
		  left: 50%;
		  margin-left: -4px;
		  margin-top: -4px;
		  top: 0;
		}
		.jcrop-handle.ord-s {
		  bottom: 0;
		  left: 50%;
		  margin-bottom: -4px;
		  margin-left: -4px;
		}
		.jcrop-handle.ord-e {
		  margin-right: -4px;
		  margin-top: -4px;
		  right: 0;
		  top: 50%;
		}
		.jcrop-handle.ord-w {
		  left: 0;
		  margin-left: -4px;
		  margin-top: -4px;
		  top: 50%;
		}
		.jcrop-handle.ord-nw {
		  left: 0;
		  margin-left: -4px;
		  margin-top: -4px;
		  top: 0;
		}
		.jcrop-handle.ord-ne {
		  margin-right: -4px;
		  margin-top: -4px;
		  right: 0;
		  top: 0;
		}
		.jcrop-handle.ord-se {
		  bottom: 0;
		  margin-bottom: -4px;
		  margin-right: -4px;
		  right: 0;
		}
		.jcrop-handle.ord-sw {
		  bottom: 0;
		  left: 0;
		  margin-bottom: -4px;
		  margin-left: -4px;
		}
		/* Dragbars */
		.jcrop-dragbar.ord-n,
		.jcrop-dragbar.ord-s {
		  height: 7px;
		  width: 100%;
		}
		.jcrop-dragbar.ord-e,
		.jcrop-dragbar.ord-w {
		  height: 100%;
		  width: 7px;
		}
		.jcrop-dragbar.ord-n {
		  margin-top: -4px;
		}
		.jcrop-dragbar.ord-s {
		  bottom: 0;
		  margin-bottom: -4px;
		}
		.jcrop-dragbar.ord-e {
		  margin-right: -4px;
		  right: 0;
		}
		.jcrop-dragbar.ord-w {
		  margin-left: -4px;
		}
		/* The "jcrop-light" class/extension */
		.jcrop-light .jcrop-vline,
		.jcrop-light .jcrop-hline {
		  background: #ffffff;
		  filter: alpha(opacity=70) !important;
		  opacity: .70!important;
		}
		.jcrop-light .jcrop-handle {
		  -moz-border-radius: 3px;
		  -webkit-border-radius: 3px;
		  background-color: #000000;
		  border-color: #ffffff;
		  border-radius: 3px;
		}
		/* The "jcrop-dark" class/extension */
		.jcrop-dark .jcrop-vline,
		.jcrop-dark .jcrop-hline {
		  background: #000000;
		  filter: alpha(opacity=70) !important;
		  opacity: 0.7 !important;
		}
		.jcrop-dark .jcrop-handle {
		  -moz-border-radius: 3px;
		  -webkit-border-radius: 3px;
		  background-color: #ffffff;
		  border-color: #000000;
		  border-radius: 3px;
		}
		/* Simple macro to turn off the antlines */
		.solid-line .jcrop-vline,
		.solid-line .jcrop-hline {
		  background: #ffffff;
		}
		/* Fix for twitter bootstrap et al. */
		.jcrop-holder img,
		img.jcrop-preview {
		  max-width: none;
		}
		.clear{
		  font-size: 0px;
		  line-height: 0px;
		  overflow: hidden;
		  width: 0px;
		  height: 0px;
		  clear: both;
		}
		.simple-cropper-images{
		  width: 820px;
		  margin: 0 auto 20px;
		  
		}
		
		.cropme{
		  background-image: url(../../skin/images/photoupload.png);
		}
		
		.cropme:hover{
			
		}
		
		.text{
		  font-family: arial;
		  font-size: 14px;
		  color: #4e4e4e;
		  margin-bottom: 20px;
		}
		
		.code{
		  font-family: arial;
		  font-size: 14px;
		  color: #4e4e4e;
		  margin-bottom: 20px;
		  background-color: #f1f1f1;
		  padding: 10px;
		}
		#fileInput{
		  width:0;
		  height:0;
		  overflow:hidden;
		}
		
		#modal{
		  z-index: 10;
		  position: fixed;
		  top: 0px;
		  left: 0px;
		  width: 100%;
		  height: 100%;
		  background-color: #5F5F5F;
		  opacity: 0.95;
		  display: none;
		}
		
		#preview{
		  z-index: 11;
		  position: fixed;
		  top: 0px;
		  left: 0px;
		  display: none;
		  border: 4px solid #A5A2A2;
		  border-radius: 4px;
		  float: left;
		  font-size: 0px;
		  line-height: 0px;
		}
		
		#preview .buttons{
		  width: 36px;
		  position: absolute;
		  bottom:0px;
		  right: -44px;
		}
	</style>';
}
?>
</body>
</html>
