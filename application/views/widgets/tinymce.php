<script type="text/javascript">
	tinymce.init({
		menubar: <?php echo $menubar; ?>,
		selector: "<?php echo $selector; ?>",
		plugins: [<?php echo $plugins; ?>],
		toolbar: "<?php echo $toolbar; ?>"
	});
</script>
<<?=$selector?> name="<?=$name?>" style="<?=$style?>"><?=$text?></<?=$selector?>>

