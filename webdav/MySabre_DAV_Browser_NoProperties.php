<?php

/**
 * Browser plugin variant that keeps SabreDAV's HTML overview but hides the
 * verbose WebDAV properties table.
 */
class MySabre_DAV_Browser_NoProperties extends \Sabre\DAV\Browser\Plugin
{
	public function generateDirectoryIndex($path)
	{
		$html = parent::generateDirectoryIndex($path);

		$cleanHtml = preg_replace(
			'#<section><h1>Properties</h1>.*?</section>#s',
			'',
			$html,
			1
		);

		$cleanHtml = preg_replace(
			'#<section><h1>Actions</h1>.*?</section>#s',
			'',
			$cleanHtml,
			1
		);

		return $cleanHtml === null ? $html : $cleanHtml;
	}
}
