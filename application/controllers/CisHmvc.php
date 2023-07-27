<?php

if (! defined('BASEPATH')) exit('No direct script access allowed');

use \DOMDocument as DOMDocument;

/**
 *
 */
class CisHmvc extends FHC_Controller
{
	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->load->library('AuthLib');
		$this->load->library('PermissionLib');

		$this->load->model('content/Content_model', 'ContentModel');
	}

	// -----------------------------------------------------------------------------------------------------------------
	// Public methods

	/**
	 * @param string $method
	 *
	 * @return void
	 */
	public function _remap($method)
	{
		$this->index();
	}

	/**
	 * @return void
	 */
	public function index()
	{
		// TODO(chris): CI can't handle empty ("//") parameters
		$path = explode('/', uri_string());
		array_shift($path); // NOTE(chris): remove cis4/
		
		$menu = $this->ContentModel->getMenu(6739, get_uid());
		if (isError($menu)) {
			// TODO(chris): Error Handling
			return $this->load->view('CisHmvc/Error', ['error' => getError($menu)]);
		}
		$menu = getData($menu) ?? (object)['childs' => []];

		$menu = $this->convertMenu($menu->childs, $path, APP_ROOT . 'index.ci.php/CisHmvc');
		$current = ['childs' => $menu];
		$params = $path;
		
		foreach ($path as $key) {
			if (!isset($current['childs'][$key])) {
				if ($current['childs'] == $menu)
					$current = null;
				break;
			}
			$current = $current['childs'][$key];
			array_shift($params);
		}
		if (!$current) {
			return $this->notfound();
		}
		if (!$path)
		{
			$controller = 'CisHmvc/Dashboard';
			$action = 'index';
		}
		else
		{
			switch ($current['orig']->template_kurzbz) {
				case 'redirect':
					list ($url, $target) = $this->getRedirectUrlAndTarget($current['orig']->content);
					if (substr($url, 0, 1) == '#')
					{
						$controller = 'CisHmvc';
						$action = 'notfound';
						break;
					}
					if ($target)
					{
						$controller = 'CisHmvc';
						$action = 'redirect';
						array_unshift($params, $url);
						break;
					}
					if (preg_match('/^(\.\.\/|\.\/)*index\.ci\.php\/(.*)$/', $url, $matches))
					{
						list ($controller, $action, $p) = $this->getControllerMethodAndParamsFromUrl($matches[2]);
						if (!$controller)
						{
							$controller = 'CisHmvc';
							$action = 'notfound';
						}
						else
						{
							while (count($p))
								array_unshift($params, array_pop($p));
						}
						break;
					}
					if (preg_match('/^(\.\.\/|\.\/)*cms\/news\.php(.*)$/', $url, $matches))
					{
						$controller = 'CisHmvc/Cms';
						$action = 'news';
						$p = parse_url($url . '?infoscreen=0', PHP_URL_QUERY);
						if ($p)
						{
							parse_str($p, $p);
							$params += array_values(array_merge([
								'infoscreen' => false,
								'studiengang_kz' => null,
								'semester' => null,
								'mischen' => true,
								'titel' => '',
								'edit' => false,
								'sichtbar' => true
							], $p));
						}
						break;
					}
					if (preg_match('/^(\.\.\/|\.\/)*(addons|cms|cis)\/(.*)$/', $url, $matches))
					{
						$controller = 'CisHmvc/Cms';
						$action = 'legacy';
						array_unshift($params, $matches[2] . "/" . $matches[3]);
						break;
					}
					
					$controller = 'CisHmvc/cms';
					$action = 'debug';
					array_unshift($params, $current['orig']);
					break;
				case 'contentohnetitel':
				case 'contentmittitel':
					$controller = 'CisHmvc/Cms';
					$action = 'content';
					array_unshift($params, $current['orig']->content_id);
					break;
				default:
					$controller = 'CisHmvc/Cms';
					$action = 'debug';
					array_unshift($params, $current['orig']);
					break;
			}
		}

		$className = ucfirst(basename($controller));
		$path = APPPATH . 'controllers/' . dirname($controller) . '/' . $className . '.php';

		#var_dump(is_loaded());
		require_once $path;
		foreach (is_loaded() as $k => $v)
			if (!in_array($k, ['benchmark','hooks','config','log','utf8','uri','router','output','security','input','lang','loader']))
				unset(is_loaded()[$k]);
		
		$this->router->directory = dirname($controller) . '/';
		$this->router->class = $className;
		$this->router->method = $action;

		#var_dump($this->router->method);
		$controller = new $className();
		// NOTE(chris): this is needed because we loaded it in this controller and it can't be loaded twice
		$controller->ContentModel = $this->ContentModel;

		$this->load->library('CisHmvc/Loader', [$menu, $this->load], 'Cis4Loader');
		$controller->load = $controller->Cis4Loader;
		call_user_func_array(array(&$controller, $action), $params);
	}

	/**
	 * @return void
	 */
	public function notfound()
	{
		set_status_header(404);
		$this->load->view('CisHmvc/Error', ['error' => '404: Site Not Found']);
	}

	/**
	 * @param uri_string	$url
	 *
	 * @return void
	 */
	public function redirect($url)
	{
		redirect($url);
	}


	// -----------------------------------------------------------------------------------------------------------------
	// Protected methods (move to lib?)

	protected function getControllerMethodAndParamsFromUrl($url)
	{
		$segments = explode('/', $url);
		$path = '';
		while ($possibleController = array_shift($segments)) {
			if (file_exists(APPPATH . 'controllers/' . $path . ucfirst($possibleController) . '.php'))
				return [$path . $possibleController, array_shift($segments) ?: 'index', $segments];
			$path .= $possibleController . '/';
		}
		return [null, null, null];
	}

	protected function getRedirectUrlAndTarget($content)
	{
		if (!$content)
			return ['#', ''];

		$url = '';
		$target = '';

		$xml = new DOMDocument();
		$xml->loadXML($content);
		if ($xml->getElementsByTagName('url')->item(0))
			$url = $xml->getElementsByTagName('url')->item(0)->nodeValue;
		// TODO(chris): get params
		/*if (strpos($url, '$') !== FALSE)
			var_dump($url);*/
		if (isset($params) && is_array($params))
			foreach ($params as $key => $value)
				$url = str_replace('$' . $key, addslashes($value), $url);
		if ($xml->getElementsByTagName('target')->item(0))
			$target = $xml->getElementsByTagName('target')->item(0)->nodeValue;

		if (!$url)
			$url = '#';

		if (substr($url, 0, 1) == '#')
			$target = '';
		
		if ($target == 'content' || $target == '_self')
			$target = '';

		return [$url, $target];
	}

	protected function convertMenu($items, $path, $path_prefix)
	{
		$menu = [];
		$current_path = array_shift($path);
		foreach ($items as $item)
		{
			$entry = [];
			$entry['template_kurzbz'] = 'cis';
			$entry['content_id'] = $item->content_id;
			$entry['titel'] = $item->titel;

			$slug = $this->createSlug($item->titel);

			$entry['active'] = ($slug == $current_path);
			$entry['menu_open'] = $entry['active'];
			$entry['url'] = $path_prefix . '/' . $slug;
			$entry['target'] = '';

			$entry['childs'] = $this->convertMenu($item->childs, $path, $entry['url']);
			
			if ($entry['active'])
				$entry['orig'] = $item;

			// TODO(chris): rewrite external and hash urls
			if ($item->template_kurzbz == 'redirect')
			{
				list ($url, $target) = $this->getRedirectUrlAndTarget($item->content);
				
				if (substr($url, 0, 1) == '#')
				{
					$entry['url'] = $url;
				}
				elseif ($target)
				{
					$entry['url'] = $url;
					$entry['target'] = $target;
				}
				/*elseif (substr($url, 0, 7) != '../cis/' && substr($url, 0, 7) != '../cms/' && substr($url, 0, 10) != '../addons/' && substr($url, 0, 16) != '../index.ci.php/' && substr($url, 0, 1) != '?')
				{
					var_dump($entry['url']);
				}*/
			}

			$menu[$slug] = $entry;
		}
		return $menu;
	}

	/**
	 * Function used to create a slug associated to an "ugly" string.
	 *
	 * @param string $text the string to transform.
	 *
	 * @return string the resulting slug.
	 */
	protected function createSlug($text)
	{
		$table = [
			'Š'=>'S', 'š'=>'s', 'Đ'=>'Dj', 'đ'=>'dj', 'Ž'=>'Z', 'ž'=>'z', 'Č'=>'C', 'č'=>'c', 'Ć'=>'C', 'ć'=>'c',
			'À'=>'A', 'Á'=>'A', 'Â'=>'A', 'Ã'=>'A', 'Ä'=>'Ae', 'Å'=>'A', 'Æ'=>'A', 'Ç'=>'C', 'È'=>'E', 'É'=>'E',
			'Ê'=>'E', 'Ë'=>'E', 'Ì'=>'I', 'Í'=>'I', 'Î'=>'I', 'Ï'=>'I', 'Ñ'=>'N', 'Ò'=>'O', 'Ó'=>'O', 'Ô'=>'O',
			'Õ'=>'O', 'Ö'=>'Oe', 'Ø'=>'O', 'Ù'=>'U', 'Ú'=>'U', 'Û'=>'U', 'Ü'=>'Ue', 'Ý'=>'Y', 'Þ'=>'B', 'ß'=>'Ss',
			'à'=>'a', 'á'=>'a', 'â'=>'a', 'ã'=>'a', 'ä'=>'ae', 'å'=>'a', 'æ'=>'a', 'ç'=>'c', 'è'=>'e', 'é'=>'e',
			'ê'=>'e', 'ë'=>'e', 'ì'=>'i', 'í'=>'i', 'î'=>'i', 'ï'=>'i', 'ð'=>'o', 'ñ'=>'n', 'ò'=>'o', 'ó'=>'o',
			'ô'=>'o', 'õ'=>'o', 'ö'=>'oe', 'ø'=>'o', 'ù'=>'u', 'ú'=>'u', 'û'=>'u', 'ü'=>'ue', /*'ý'=>'y', duplicate? => see quality check*/'ý'=>'y', 'þ'=>'b',
			'ÿ'=>'y', 'Ŕ'=>'R', 'ŕ'=>'r', '/' => '-', ' ' => '-'
		];
		$text = preg_replace(['/\s{2,}/', '/[\t\n]/'], ' ', $text);
		$text = preg_replace('~[^\pL\d]+~u', '-', $text);
		$text = strtr($text, $table);
		$text = preg_replace('~^[^a-z]~i', '', $text);
		return strtolower($text);
	}
}
