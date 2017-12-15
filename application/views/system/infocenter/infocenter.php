<?php $this->load->view('templates/FHC-Header', array('title' => 'Info Center', 'jquery3' => true)); ?>

<?php $iFrameSrc = base_url('index.ci.php/system/infocenter/InfoCenter/filter'); ?>

<script language="Javascript" type="text/javascript">
	$(document).ready(function() {

		// $("#iFrameFilter").attr('width', $(window).width());

	});
</script>

	<body>

		<span>
			<?php
				$this->load->view(
					'system/infocenter/infocenterFilters.php',
					array(
						'listFiltersSent' => $listFiltersSent,
						'listFiltersNotSent' => $listFiltersNotSent
					)
				);
			?>
		</span>

		<span>
			<iframe id="iFrameFilter" name="iFrameFilter" src="<?php echo $iFrameSrc; ?>" width="800" height="700" frameborder="0">
				Your browser does not support iframes, please update it
			</iframe>
		</span>

	</body>

<?php $this->load->view('templates/FHC-Footer'); ?>
