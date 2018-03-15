<script language="Javascript" type="text/javascript">

	$(document).ready(function() {

		$.ajax({
			url: "<?php echo base_url('index.ci.php/system/Navigation/header'); ?>",
			method: "GET",
			data: {
				navigation_widget_called: "<?php echo $this->router->directory.$this->router->class.'/'.$this->router->method; ?>"
			}
		})
		.done(function(data, textStatus, jqXHR) {

			if (data != null)
			{
				jQuery.each(data, function(i, e) {
					$(".menu-header-items").append('<a class="navbar-brand" href="' + e + '">' + i + '</a>');
				});
			}

		}).fail(function(jqXHR, textStatus, errorThrown) {
			alert(textStatus);
		});

	});

</script>

	<div class="navbar-header">
		<span>
			<button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
				<span class="sr-only">Men&uuml; umschalten </span>
				<span class="icon-bar"></span>
				<span class="icon-bar"></span>
				<span class="icon-bar"></span>
			</button>
		</span>
		<span class="menu-header-items"></span>
	</div>
