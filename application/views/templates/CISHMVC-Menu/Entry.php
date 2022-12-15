<?php
if (is_array($entry) && isset($entry['content_id']))
	$entry = (object)$entry;
$menu_id .= '-' . $entry->content_id;

// TODO(chris): remove! DEBUG
#$entry->menu_open = false;

$link = $entry->url;
$target = $entry->target;

?>
<?php if ($entry->childs) { ?>
	<?php if (substr($link, 0, 1) == '#') { ?>
		<a href="#<?= $menu_id; ?>" data-bs-toggle="collapse" aria-expanded="<?= $entry->menu_open ? 'true' : 'false'; ?>" class="btn btn-default rounded-0 w-100 text-start dropdown-toggle btn-level-<?= substr_count($menu_id, '-'); ?><?= $entry->menu_open ? '' : ' collapsed'; ?>">
			<span><?= htmlspecialchars($entry->titel); ?></span>
		</a>
	<?php } else { ?>
		<div class="btn-group w-100">
			<a<?= $link ? ' href="' . $link . '"' : ''; ?><?= $target ? ' target="' . $target . '"' : ''; ?> class="btn btn-default rounded-0 text-start btn-level-<?= substr_count($menu_id, '-'); ?>">
				<?= htmlspecialchars($entry->titel); ?>
			</a>
			<a href="#<?= $menu_id; ?>" data-bs-toggle="collapse" aria-expanded="<?= $entry->menu_open ? 'true' : 'false'; ?>"class="btn btn-default rounded-0 dropdown-toggle dropdown-toggle-split flex-grow-0<?= $entry->menu_open ? '' : ' collapsed'; ?>" >
				<span class="visually-hidden">Toggle Dropdown</span>
			</a>
		</div>
	<?php } ?>
	<ul id="<?= $menu_id; ?>" class="nav w-100 collapse<?= $entry->menu_open ? ' show' : ''; ?>">
		<?php foreach ($entry->childs as $child) 
			$this->load->view('templates/CISHMVC-Menu/Entry', ['entry' => $child, 'menu_id' => $menu_id]);
		?>
	</ul>
<?php } else { ?>
	<a<?= $link ? ' href="' . $link . '"' : ''; ?><?= $target ? ' target="' . $target . '"' : ''; ?> class="btn btn-default rounded-0 w-100 text-start btn-level-<?= substr_count($menu_id, '-'); ?>">
		<?= htmlspecialchars($entry->titel); ?>
	</a>
<?php } ?>
