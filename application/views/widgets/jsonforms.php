<div id="<?=$id?>" style="<?=$style?>"></div>
<script language="Javascript" type="text/javascript">
	var container = document.getElementById('<?=$id?>');
	var schema = <?=$schema?>;
	var BrutusinForms = brutusin["json-forms"];
	var <?=$objectname?> = BrutusinForms.create(schema);
	<?=$objectname?>.render(container);
</script>


