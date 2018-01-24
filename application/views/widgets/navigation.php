	<nav class="navbar navbar-default navbar-static-top" role="navigation" style="margin-bottom: 0">
		<?php
			// Header
			echo $this->widgetlib->widget('NavigationHeaderWidget', $widgetData[NavigationWidget::NAVIGATION_HEADER]);

			// Left menu
			echo $this->widgetlib->widget('NavigationMenuWidget', $widgetData[NavigationWidget::NAVIGATION_MENU]);
		?>
	</nav>
