<script language="Javascript" type="text/javascript">

	$(document).ready(function() {

		$("#saveCustomFilterButton").click(function() {
			if ($("#customFilterDescription").val() != '')
			{
				$.ajax({
					url: "<?php echo base_url('index.ci.php/system/Filters/saveFilter'); ?>",
					method: "POST",
					data: {
						customFilterDescription: $("#customFilterDescription").val()
					}
				})
				.done(function(data, textStatus, jqXHR) {
					alert("Filter successfully saved");
				}).fail(function(jqXHR, textStatus, errorThrown) {
					alert(textStatus);
				});
			}
			else
			{
				alert("You forgot something!");
			}
		});

	});

</script>

<div>
	<span>
		Filter description: <input type="text" id="customFilterDescription" value="">
	</span>

	<span>
		<input type="button" id="saveCustomFilterButton" value="Save filter">
	</span>
</div>
