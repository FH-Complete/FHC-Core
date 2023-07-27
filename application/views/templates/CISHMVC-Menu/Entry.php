<?php
if (is_array($entry) && isset($entry['content_id']))
	$entry = (object)$entry;

if (!isset($content_id))
	$content_id = $entry->content_id;

if (!isset($path))
	$path = '';

$menu_id .= '-' . $content_id;

// TODO(chris): remove! DEBUG
#$entry->menu_open = false;

if (property_exists($entry, 'path')) {
	$lang = getUserLanguage();
	$link = $path . '/' . $entry->path[$lang];
	$active = in_array($content_id, $this->router->breadcrumb);
	$menu_open = $active ? true : (property_exists($entry, 'menu_open') ? $entry->menu_open : false);
	$target = '';
	$title = htmlspecialchars($entry->sprache[$lang]->titel);
} else {
	$link = $entry->url;
	$active = false;
	$menu_open = $entry->menu_open;
	$target = $entry->target;
	$title = htmlspecialchars($entry->titel);
	$entry->children = $entry->childs;
}

?>
<?php if ($entry->children) { ?>
	<?php if (substr($link, 0, 1) == '#') { ?>
		<a href="#<?= $menu_id; ?>" data-bs-toggle="collapse" aria-expanded="<?= $menu_open ? 'true' : 'false'; ?>" class="btn btn-default rounded-0 w-100 text-start dropdown-toggle btn-level-<?= substr_count($menu_id, '-'); ?><?= $menu_open ? '' : ' collapsed'; ?><?= $active ? ' active' : ''; ?>">
			<span><?= $title; ?></span>
		</a>
	<?php } else { ?>
		<div class="btn-group w-100">
			<a<?= $link ? ' href="' . $link . '"' : ''; ?><?= $target ? ' target="' . $target . '"' : ''; ?> class="btn btn-default rounded-0 text-start btn-level-<?= substr_count($menu_id, '-'); ?><?= $active ? ' active' : ''; ?>">
				<?= $title; ?>
			</a>
			<a href="#<?= $menu_id; ?>" data-bs-toggle="collapse" aria-expanded="<?= $menu_open ? 'true' : 'false'; ?>"class="btn btn-default rounded-0 dropdown-toggle dropdown-toggle-split flex-grow-0<?= $menu_open ? '' : ' collapsed'; ?><?= $active ? ' active' : ''; ?>" >
				<span class="visually-hidden">Toggle Dropdown</span>
			</a>
		</div>
	<?php } ?>
	<ul id="<?= $menu_id; ?>" class="nav w-100 collapse<?= $menu_open ? ' show' : ''; ?>">
		<?php foreach ($entry->children as $id => $child)
			$this->load->view('templates/CISHMVC-Menu/Entry', ['content_id' => $id, 'entry' => $child, 'menu_id' => $menu_id, 'path' => $path]);
		?>
	</ul>
<?php } else { ?>
	<a<?= $link ? ' href="' . $link . '"' : ''; ?><?= $target ? ' target="' . $target . '"' : ''; ?> class="btn btn-default rounded-0 w-100 text-start btn-level-<?= substr_count($menu_id, '-'); ?><?= $active ? ' active' : ''; ?>">
		<?= $title; ?>
	</a>
<?php } ?>
