<?php
	$this->load->view('templates/FHC-Header', array('title' => 'Info Center', 'jquery3' => true, 'tablesorter' => true));
?>

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
			<?php
				$this->load->view('system/infocenter/infocenterData.php');
			?>
		</span>

	</body>

<?php $this->load->view('templates/FHC-Footer'); ?>
