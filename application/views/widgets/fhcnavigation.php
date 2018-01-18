<div class="navbar-default sidebar" role="navigation">
	<div class="sidebar-nav navbar-collapse">
		<ul class="nav" id="side-menu">
<!--			<li class="sidebar-search">
				<div class="input-group custom-search-form">
					<input type="text" class="form-control" placeholder="Search...">
					<span class="input-group-btn">
                                    <button class="btn btn-default" type="button">
                                        <i class="fa fa-search"></i>
                                    </button>
                                </span>
				</div> -->
				<!-- /input-group -->
			<!-- </li>-->
			<?php
			foreach ($items as $item)
				printNavItem($item); ?>
		</ul>
	</div>
	<!-- /.sidebar-collapse -->
</div>
<!-- /.navbar-static-side -->
<?php
function printNavItem($item, $depth = 1)
{
	$expanded = isset($item['expand']) && $item['expand'] === true ? ' active' : '';
	echo '<li class="'.$expanded.'">
				<a href="'.$item['link'].'"'.$expanded.'>'.(isset($item['icon']) ? '<i class="fa fa-'.$item['icon'].' fa-fw"></i> ' : '').$item['description'].(!empty($item['children']) ? '<span class="fa arrow"></span>':'').'</a>';
	if (!empty($item['children']))
	{
		$level = '';
		if ($depth === 1)
			$level = 'second';
		elseif ($depth > 1)
			$level = 'third';

		echo '<ul class="nav nav-'.$level.'-level" '.$expanded.'>';
		foreach ($item['children'] as $child)
			printNavItem($child, ++$depth);
		echo '</ul>';
	}
	echo '</li>';
}
?>