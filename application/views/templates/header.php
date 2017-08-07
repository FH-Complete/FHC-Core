<?php
if (! defined('BASEPATH')) exit('No direct script access allowed');

isset($title) ? $title = 'VileSci - '.$title : $title = 'VileSci';
!isset($jquery) ? $jquery = false : $jquery = $jquery;
!isset($jqueryComposer) ? $jqueryComposer = false : $jqueryComposer = $jqueryComposer;
!isset($jqueryui) ? $jqueryui = false : $jqueryui = $jqueryui;
!isset($jquery_checkboxes) ? $jquery_checkboxes = false : $jquery_checkboxes = $jquery_checkboxes;
!isset($jquery_custom) ? $jquery_custom = false : $jquery_custom = $jquery_custom;
!isset($tablesort) ? $tablesort = false : $tablesort = $tablesort;
!isset($sortList) ? $sortList = '0,0' : $sortList = $sortList;
!isset($widgets) ? $widgets = 'zebra' : $widgets = $widgets;
!isset($headers) ? $headers = '' : $headers = $headers;
!isset($tinymce) ? $tinymce = false : $tinymce = $tinymce;
!isset($jsoneditor) ? $jsoneditor = false : $jsoneditor = $jsoneditor;
!isset($jsonforms) ? $jsonforms = false : $jsonforms = $jsonforms;
!isset($textile) ? $textile = false : $textile = $textile;
!isset($widgetsCSS) ? $widgetsCSS = false : $widgetsCSS = $widgetsCSS;
!isset($datepicker) ? $datepicker = false : $datepicker = $datepicker;

if ($tablesort || $jquery_checkboxes || $jquery_custom)
	$jquery = true;

if($datepicker)
	$jqueryui = true;

if($jqueryui)
	$jqueryComposer = true;

if($jquery && $jqueryComposer)
	show_error("Two JQuery versions used: composer and includefolderversion");

?>
<!DOCTYPE HTML>
<html>
<head>
	<title><?php echo $title; ?></title>
	<meta charset="UTF-8">
	<link rel="shortcut icon" type="image/x-icon" href="<?php echo base_url('skin/images/Vilesci.ico'); ?>" />
	<link rel="stylesheet"    type="text/css"     href="<?php echo base_url('skin/vilesci.css'); ?>" />
<?php if($tablesort) : ?>
	<link rel="stylesheet"    type="text/css"     href="<?php echo base_url('skin/tablesort.css'); ?>" />
<?php endif ?>

<?php if($jquery) : ?>
	<script type="text/javascript" src="<?php echo base_url('include/js/jquery1.9.min.js'); ?>"></script>
<?php endif ?>

<?php if($jqueryComposer) : ?>
	<script type="text/javascript" src="<?php echo base_url('vendor/components/jquery/jquery.min.js'); ?>"></script>
<?php endif ?>

<?php if($jqueryui) : ?>
	<script type="text/javascript" src="<?php echo base_url('vendor/components/jqueryui/jquery-ui.min.js'); ?>"></script>
	<link rel="stylesheet"    type="text/css"     href="<?php echo base_url('vendor/components/jqueryui/themes/base/jquery-ui.min.css'); ?>" />
<?php endif ?>

<?php if($jquery_checkboxes) : ?>
	<script type="text/javascript" src="<?php echo base_url('include/js/jquery.checkboxes-1.0.7.min.js'); ?>"></script>
<?php endif ?>

<?php if($jquery_custom) : ?>
	<link rel="stylesheet"    type="text/css"     href="<?php echo base_url('skin/jquery-ui-1.9.2.custom.min.css'); ?>" />
<?php endif ?>

<?php if($tablesort && !empty($tableid)) : ?>
	<script language="Javascript" type="text/javascript">
		$(document).ready(function()
		{
			$("#<?php echo $tableid; ?>").tablesorter(
			{
				sortList: [[<?php echo $sortList; ?>]],
				widgets: ["<?php echo $widgets; ?>"],
				headers: {<?php echo $headers; ?>}
			});
		});
	</script>
<?php endif ?>

<?php if($datepicker && !empty($datepickerclass)) : ?>
	<script language="Javascript" type="text/javascript">
		$(document).ready(function()
		{
			$(".<?php echo $datepickerclass; ?>").datepicker(
				{
					dateFormat:"dd.mm.yy"
				});
		});
	</script>
<?php endif ?>

<?php if($tinymce) : ?>
	<script type="text/javascript" src="<?php echo base_url('vendor/tinymce/tinymce/tinymce.min.js');?>"></script>
<?php endif ?>
<?php if($textile) : ?>
	<script type="text/javascript" src="<?php echo base_url('include/js/textile.min.js');?>"></script>
<?php endif ?>
<?php if($jsoneditor) : ?>
	<link rel="stylesheet" type="text/css" href="<?php echo base_url('vendor/jsoneditor/dist/jsoneditor.css');?>" />
	<script type="text/javascript" src="<?php echo base_url('vendor/jsoneditor/dist/jsoneditor.js');?>"></script>
<?php endif ?>
<?php if($jsonforms) : ?>
	<link rel="stylesheet" type="text/css" href="<?php echo base_url('vendor/json-forms/dist/css/brutusin-json-forms.min.css'); ?>" />
	<script type="text/javascript" src="<?php echo base_url('vendor/json-forms/dist/js/brutusin-json-forms.min.js'); ?>"></script>
<?php endif ?>
<?php if($widgetsCSS) : ?>
	<link rel="stylesheet" type="text/css" href="<?php echo base_url('skin/widgets.css'); ?>" />
<?php endif ?>
</head>