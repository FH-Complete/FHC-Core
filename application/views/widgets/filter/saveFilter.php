<script language="Javascript" type="text/javascript">

	$(document).ready(function() {

		$("#saveCustomFilterButton").click(function() {
			if ($("#customFilterDescription").val() != '')
			{
				$.ajax({
					url: "<?php echo site_url('system/Filters/saveFilter'); ?>",
					method: "POST",
					data: {
						customFilterDescription: $("#customFilterDescription").val(),
						fhc_controller_id: getUrlParameter("fhc_controller_id")
					}
				})
				.done(function(data, textStatus, jqXHR) {

					refreshSideMenu()

				}).fail(function(jqXHR, textStatus, errorThrown) {
					alert(textStatus);
				});
			}
			else
			{
				alert("Please fill te description of this filter");
			}
		});

	});

</script>

<br>

<div>
	<span class="filter-options-span">
		Filter description:
	</span>
	<span>
		<input type="text" id="customFilterDescription" value="">
	</span>

	<span>
		<input type="button" id="saveCustomFilterButton" value="Save filter">
	</span>
</div>

<br>
