<?php
if (is_array($entry) && isset($entry['content_id']))
	$entry = (object)$entry;
$menu_id .= '-' . $entry->content_id;

switch ($entry->template_kurzbz) {
	case 'redirect':
		$url = '';
		$target = '';
		$xml = new DOMDocument();
		if($entry->content != '')
		{
			$xml->loadXML($entry->content);
			if ($xml->getElementsByTagName('url')->item(0))
				$url = $xml->getElementsByTagName('url')->item(0)->nodeValue;
			// TODO(chris): get params
			if (isset($params) && is_array($params))
				foreach ($params as $key => $value)
					$url = str_replace('$' . $key, addslashes($value), $url);
			if ($xml->getElementsByTagName('target')->item(0))
				$target = $xml->getElementsByTagName('target')->item(0)->nodeValue;

			if (strpos($url, '../cms/news.php') === 0)
				$url = site_url('/CisHtml/Cms/news' . substr($url, 15));
			if (strpos($url, '../') === 0)
				$url = APP_ROOT . substr($url, 3);
		}
		if ($target == 'content')
			$target = '';

		$this->load->view('templates/CISHTML-Menu/EntryBase', ['entry' => $entry, 'menu_id' => $menu_id, 'link' => $url, 'target' => $target]);
		break;
	case 'include':
		$this->load->view('templates/CISHTML-Menu/EntryInclude', ['entry' => $entry, 'menu_id' => $menu_id]);
		break;
	default:
		$this->load->view('templates/CISHTML-Menu/EntryBase', [
			'entry' => $entry,
			'menu_id' => $menu_id,
			'link' => site_url('/CisHtml/Cms/content/' . $entry->content_id)
		]);
		break;
}
?>
