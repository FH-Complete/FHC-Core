	<div class="navbar-header">
		<button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
			<span class="sr-only">Men&uuml; umschalten </span>
			<span class="icon-bar"></span>
			<span class="icon-bar"></span>
			<span class="icon-bar"></span>
		</button>
		<a class="navbar-brand" href="<?php echo $headertextlink ?>"><?php echo $headertext ?></a>
	</div>


	<ul class="nav navbar-top-links navbar-right">
		<?php foreach($items as $name => $item): ?>
		<li class="dropdown">
			<a class="dropdown-toggle" data-toggle="dropdown" href="#">
				<i class="fa fa-<?php echo $item['icon'] ?> fa-fw"></i> <i class="fa fa-caret-down"></i>
			</a>
			<ul class="dropdown-menu dropdown-<?php echo $name ?>">
				<?php foreach($item['children'] as $child): ?>
					<li>
						<a href="<?php echo $child['link'] ?>">
							<?php echo $child['html'] ?>
						</a>
					</li>
					<li class="divider"></li>
				<?php endforeach;
					if(isset($item['showall'])):
				?>
				<li>
					<a class="text-center" href="<?php echo $item['showall']['showalllink'] ?>">
						<strong><?php echo $item['showall']['showalltext'] ?></strong>
						<i class="fa fa-angle-right"></i>
					</a>
				</li>
				<?php endif; ?>
			</ul>
		</li>
		<?php endforeach; ?>
	</ul>