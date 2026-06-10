<?php

if (! defined('BASEPATH')) exit('No direct script access allowed');

class Components extends FHC_Controller
{
	/**
	 * @return void
	 */
	public function primevue(...$args)
	{
		$path = implode('/', $args);
		$target = FHCPATH . 'vendor/npm-asset/primevue/' . $path;
		if (!file_exists($target))
			return show_404();
		
		$newUrl = str_repeat('../', count($args));

		$ext = substr($path, -7) == '.min.js' ? '.esm.min.js' : '.esm.js';

		$contents = file_get_contents($target);
		
		$contents = preg_replace_callback('/import([^;]*)from\s*[\'"]vue[\'"];/i', function ($matches) {
			return 'let ' . str_replace(' as ', ': ', $matches[1]) . ' = Vue;';
		}, $contents);
		
		$contents = preg_replace_callback('/(import[^;]*[\'"])(primevue[^\'"]+)([\'"])/i', function ($matches) use ($ext, $newUrl) {
			if (is_file(FHCPATH . 'vendor/npm-asset/' . $matches[2])) {
				$newUrl .= $matches[2];
			} else {
				$testfile = $matches[2] . '/index' . $ext;
				if (file_exists(FHCPATH . 'vendor/npm-asset/' . $testfile))
					$newUrl .= $testfile;
				else
					$newUrl .= $matches[2] . strrchr($matches[2], '/') . $ext;
			}
			return $matches[1] . $newUrl . $matches[3];
		}, $contents);
		
		$this->output->set_content_type('text/javascript');
		$this->output->set_output($contents);
	}
}
