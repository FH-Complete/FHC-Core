<?php
	function _printLists($listFilters)
	{
		foreach ($listFilters as $filterId => $description)
		{
			$toPrint = '<div><a href="%s=%s">%s</a></div>';

			echo sprintf($toPrint, base_url('index.ci.php/system/infocenter/InfoCenter?filterId'), $filterId, $description).PHP_EOL;
		}
	}

// HTML
	// body
		// span
?>
			<div>

				<div>
					Abgeschickt:
				</div>

				<?php _printLists($listFiltersSent); ?>

				<div>
					Nicht abgeschickt:
				</div>

				<?php _printLists($listFiltersNotSent); ?>

			</div>
