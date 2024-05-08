<?php
$includesArray = array(
	'title' => 'FH-Complete',
	'customCSSs' => [
		'public/css/Cis4/Legacy.css'
	]
);

$this->load->view('templates/FHC-Header', $includesArray);
?>

<script type="text/javascript">
	function resizeIframe(obj) {
		// NOTE(chris): this only works on sites on the same domain which is always the case in this template
		obj.style.height = (obj.contentWindow.document.scrollingElement.scrollHeight) + 'px';
		// TODO(chris): add trigger on window.resize
	}
</script>

<iframe src="<?= base_url($url); ?>" onload="resizeIframe(this)"></iframe>

<?php $this->load->view('templates/FHC-Footer', $includesArray); ?>

