<?php
$this->load->view(
	'templates/FHC-Header',
	array(
		'title' => 'MessageReply',
		'jquery' => true,
		'bootstrap' => true,
		'fontawesome' => true,
		'tinymce' => true,
		'sbadmintemplate' => true,
		'customCSSs' => array('public/css/sbadmin2/admintemplate_contentonly.css', 'public/css/messageWrite.css'),
		'customJSs' => array('public/js/bootstrapper.js')
	)
);
?>
<body>
<?php
$href = site_url().'/ViewMessage/sendReply';
?>
<div id="wrapper">
	<div id="page-wrapper">
		<div class="container-fluid">
			<div class="row">
				<div class="col-lg-12">
					<h3 class="page-header">Send Message</h3>
				</div>
			</div>
			<form id="sendForm" method="post" action="<?php echo $href; ?>">
				<?php $this->load->view('system/messageForm.php'); ?>
				<?php
				for ($i = 0; $i < count($receivers); $i++)
				{
					$receiver = $receivers[$i];
					$receiverid = $receiver->person_id;
					$fieldname = 'persons[]';

					echo '<input type="hidden" name="'.$fieldname.'" value="'.$receiverid.'">'."\n";
				}
				?>

				<?php
				if (isset($message))
				{
					?>
					<input type="hidden" name="relationmessage_id" value="<?php echo $message->message_id; ?>">
					<?php
				}
				if (isset($token))
				{
					?>
					<input type="hidden" name="token" value="<?php echo $token; ?>">
					<?php
				}
				?>

			</form>
		</div>
	</div>
</div>
<script>
	tinymce.init({
		selector: "#bodyTextArea",
		plugins: "autoresize",
		autoresize_min_height: 150,
		autoresize_max_height: 600,
		autoresize_bottom_margin: 10,
		auto_focus: "bodyTextArea"
	});

	$(document).ready(function ()
	{
		if ($("#sendButton") && $("#sendForm"))
		{
			$("#sendButton").click(function ()
			{
				if ($("#subject") && $("#subject").val() != '' && tinyMCE.get("bodyTextArea").getContent() != '')
				{
					$("#sendForm").submit();
				}
				else
				{
					alert("Subject and text are required fields!");
				}
			});
		}
	});
</script>

</body>

<?php $this->load->view("templates/FHC-Footer"); ?>
