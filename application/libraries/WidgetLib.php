<?php
/**
 * @name        CodeIgniter Template Library
 * @author      Jens Segers
 * @link        http://www.jenssegers.be
 * @license     MIT License Copyright (c) 2012 Jens Segers
 * 
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 * 
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 * 
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */

if (!defined("BASEPATH")) exit("No direct script access allowed");

class WidgetLib
{
    /* default values */
    private $_template = 'template';
    private $_parser = FALSE;
    private $_cache_ttl = 0;
    private $_widget_path = '';
    
    private $_ci;
    private $_partials = array();
    
    /**
     * Construct with configuration array. Codeigniter will use the config file otherwise
     * @param array $config
     */
    public function __construct($config = array())
	{
        $this->_ci = & get_instance();
        
        // set the default widget path with APPPATH
        $this->_widget_path = APPPATH . 'widgets/';
        
        if (!empty($config))
            $this->initialize($config);
        
        log_message('debug', 'Template library initialized');
    }
    
    /**
     * Initialize with configuration array
     * @param array $config
     * @return Template
     */
    public function initialize($config = array())
	{
        foreach ($config as $key => $val)
            $this->{'_' . $key} = $val;
        
        if ($this->_widget_path == '')
            $this->_widget_path = APPPATH . 'widgets/';
        
        if ($this->_parser && !class_exists('CI_Parser'))
            $this->_ci->load->library('parser');
    }
    
    /**
     * Set a partial's content. This will create a new partial when not existing
     * @param string $index
     * @param mixed $value
     */
    public function __set($name, $value)
	{
        $this->partial($name)->set($value);
    }
    
    /**
     * Access to partials for method chaining
     * @param string $name
     * @return mixed
     */
    public function __get($name)
	{
        return $this->partial($name);
    }
    
    /**
     * Check if a partial exists
     * @param string $index
     * @return boolean
     */
    public function exists($index)
	{
        return array_key_exists($index, $this->_partials);
    }
    
    /**
     * Set the template file
     * @param string $template
     */
    public function set_template($template)
	{
        $this->_template = $template;
    }
    
    /**
     * Publish the template with the current partials
     * You can manually pass a template file with extra data, or use the default template from the config file
     * @param string $template
     * @param array $data
     */
    public function publish($template = FALSE, $data = array()) {
        if (is_array($template) || is_object($template)) {
            $data = $template;
        } else if ($template) {
            $this->_template = $template;
        }
        
        if (!$this->_template) {
            show_error('There was no template file selected for the current template');
        }
        
        if (is_array($data) || is_object($data)) {
            foreach ($data as $name => $content) {
                $this->partial($name)->set($content);
            }
        }
        
        unset($data);
        
        if ($this->_parser) {
            $this->_ci->parser->parse($this->_template, $this->_partials);
        } else {
            $this->_ci->load->view($this->_template, $this->_partials);
        }
    }
    
    /**
     * Create a partial object with an optional default content
     * Can be usefull to use straight from the template file
     * @param string $name
     * @param string $default
     * @return Partial
     */
    public function partial($name, $default = FALSE) {
        if ($this->exists($name)) {
            $partial = $this->_partials[$name];
        } else {
            // create new partial
            $partial = new Partial($name);
            if ($this->_cache_ttl) {
                $partial->cache($this->_cache_ttl);
            }
            
            // detect local triggers
            if (method_exists($this, 'trigger_' . $name)) {
                $partial->bind($this, 'trigger_' . $name);
            }
            
            $this->_partials[$name] = $partial;
        }
        
        if (!$partial->content() && $default) {
            $partial->set($default);
        }
        
        return $partial;
    }
    
    /**
     * Create a widget object with optional parameters
     * Can be usefull to use straight from the template file
     * @param string $name
     * @param array $data
     * @param array $htmlArgs
     * @return Widget
     */
    public function widget($name, $data = array(), $htmlArgs = array())
    {
        $class = str_replace('.php', '', trim($name, '/'));
        
        // determine path and widget class name
        $path = $this->_widget_path;
        if (($last_slash = strrpos($class, '/')) !== FALSE) {
            $path += substr($class, 0, $last_slash);
            $class = substr($class, $last_slash + 1);
        }
        
        // new widget
        if (!class_exists($class)) {
            // try both lowercase and capitalized versions
            foreach (array(ucfirst($class), strtolower($class)) as $class) {
                if (file_exists($path . $class . '.php')) {
                    include_once ($path . $class . '.php');
                    
                    // found the file, stop looking
                    break;
                }
            }
        }
        
        if (!class_exists($class)) {
            show_error("Widget '" . $class . "' was not found.");
        }
        
        return new $class($class, $data, $htmlArgs);
    }
    
    /**
     * 
     */
    public function UDFWidget($args, $htmlArgs = array())
    {
		if (!empty($args[UDFWidgetTpl::SCHEMA_ARG_NAME])
			&& !empty($args[UDFWidgetTpl::TABLE_ARG_NAME])
			&& !empty($args[UDFWidgetTpl::FIELD_ARG_NAME]))
		{
			return $this->widget(
				UDFWidgetTpl::WIDGET_NAME,
				$args,
				$htmlArgs
			);
		}
		else
		{
			if (empty($args[UDFWidgetTpl::SCHEMA_ARG_NAME]))
			{
				show_error(UDFWidgetTpl::SCHEMA_ARG_NAME.' parameter is missing!');
			}
			if (empty($args[UDFWidgetTpl::TABLE_ARG_NAME]))
			{
				show_error(UDFWidgetTpl::TABLE_ARG_NAME.' parameter is missing!');
			}
			if (empty($args[UDFWidgetTpl::FIELD_ARG_NAME]))
			{
				show_error(UDFWidgetTpl::FIELD_ARG_NAME.' parameter is missing!');
			}
		}
    }
    
    /**
     * Enable cache for all partials with TTL, default TTL is 60
     * @param int $ttl
     * @param mixed $identifier
     */
    public function cache($ttl = 60, $identifier = '') {
        foreach ($this->_partials as $partial) {
            $partial->cache($ttl, $identifier);
        }
        
        $this->_cache_ttl = $ttl;
    }
    
    // ---- TRIGGERS -----------------------------------------------------------------

    /**
     * Stylesheet trigger
     * @param string $source
     */
    public function trigger_stylesheet($url, $attributes = FALSE) {
        // array support
        if (is_array($url)) {
            $return = '';
            foreach ($url as $u) {
                $return .= $this->trigger_stylesheet($u, $attributes);
            }
            return $return;
        }
        
        if (!stristr($url, 'http://') && !stristr($url, 'https://') && substr($url, 0, 2) != '//') {
            $url = $this->_ci->config->item('base_url') . $url;
        }
        
        // legacy support for media
        if (is_string($attributes)) {
            $attributes = array('media' => $attributes);
        }
        
        if (is_array($attributes)) {
        	$attributeString = "";
        	
        	foreach ($attributes as $key => $value) {
	        	$attributeString .= $key . '="' . $value . '" ';
        	}
        	
            return '<link rel="stylesheet" href="' . htmlspecialchars(strip_tags($url)) . '" ' . $attributeString . '>' . "\n\t";
        } else {
            return '<link rel="stylesheet" href="' . htmlspecialchars(strip_tags($url)) . '">' . "\n\t";
        }
    }
    
    /**
     * Javascript trigger
     * @param string $source
     */
    public function trigger_javascript($url) {
        // array support
        if (is_array($url)) {
            $return = '';
            foreach ($url as $u) {
                $return .= $this->trigger_javascript($u);
            }
            return $return;
        }
        
        if (!stristr($url, 'http://') && !stristr($url, 'https://') && substr($url, 0, 2) != '//') {
            $url = $this->_ci->config->item('base_url') . $url;
        }
        
        return '<script src="' . htmlspecialchars(strip_tags($url)) . '"></script>' . "\n\t";
    }
    
    /**
     * Meta trigger
     * @param string $name
     * @param mixed $value
     * @param enum $type
     */
    public function trigger_meta($name, $value, $type = 'meta') {
        $name = htmlspecialchars(strip_tags($name));
        $value = htmlspecialchars(strip_tags($value));
        
        if ($name == 'keywords' and !strpos($value, ',')) {
            $content = preg_replace('/[\s]+/', ', ', trim($value));
        }
        
        switch ($type) {
            case 'meta' :
                $content = '<meta name="' . $name . '" content="' . $value . '">' . "\n\t";
                break;
            case 'link' :
                $content = '<link rel="' . $name . '" href="' . $value . '">' . "\n\t";
                break;
        }
        
        return $content;
    }
    
    /**
     * Title trigger, keeps it clean
     * @param string $name
     * @param mixed $value
     * @param enum $type
     */
    public function trigger_title($title) {
        return htmlspecialchars(strip_tags($title));
    }
    
    /**
     * Title trigger, keeps it clean
     * @param string $name
     * @param mixed $value
     * @param enum $type
     */
    public function trigger_description($description) {
        return htmlspecialchars(strip_tags($description));
    }

}

class Partial
{
    protected $_ci, $_content, $_name, $_cache_ttl = 0, $_cached = false, $_identifier, $_trigger;
    protected $_args = array();
    
    /**
     * Construct with optional parameters
     * @param array $args
     */
    public function __construct($name, $args = array())
    {
        $this->_ci = &get_instance();
        $this->_args = $args;
        $this->_name = $name;
    }
    
    /**
     * Gives access to codeigniter's functions from this class if needed
     * This will be handy in extending classes
     * @param string $index
     */
    function __get($name) {
        return $this->_ci->$name;
    }
    
    /**
     * Alias methods
     */
    function __call($name, $args) {
        switch ($name) {
            case 'default' :
                return call_user_func_array(array($this, 'set_default'), $args);
                break;
            case 'add' :
                return call_user_func_array(array($this, 'append'), $args);
                break;
        }
    }
    
    /**
     * Returns the content when converted to a string
     * @return string
     */
    public function __toString() {
        return (string) $this->content();
    }
    
    /**
     * Returns the content
     * @return string
     */
    public function content() {
        if ($this->_cache_ttl && !$this->_cached) {
            $this->cache->save($this->cache_id(), $this->_content, $this->_cache_ttl);
        }
        
        return $this->_content;
    }
    
    /**
     * Overwrite the content
     * @param mixed $content
     * @return Partial
     */
    public function set() {
        if (!$this->_cached) {
            $this->_content = (string) $this->trigger(func_get_args());
        }
        
        return $this;
    }
    
    /**
     * Append something to the content
     * @param mixed $content
     * @return Partial
     */
    public function append() {
        if (!$this->_cached) {
            $this->_content .= (string) $this->trigger(func_get_args());
        }
        
        return $this;
    }
    
    /**
     * Prepend something to the content
     * @param mixed $content
     * @return Partial
     */
    public function prepend() {
        if (!$this->_cached) {
            $this->_content = (string) $this->trigger(func_get_args()) . $this->_content;
        }
        
        return $this;
    }
    
    /**
     * Set content if partial is empty
     * @param mixed $default
     * @return Partial
     */
    public function set_default($default) {
        if (!$this->_cached) {
            if (!$this->_content) {
                $this->_content = $default;
            }
        }
        
        return $this;
    }
    
    /**
     * Load a view inside this partial, overwrite if wanted
     * @param string $view
     * @param array $data
     * @param bool $overwrite
     * @return Partial
     */
    public function view($view, $data = array(), $overwrite = false) {
        if (!$this->_cached) {
            
            // better object to array
            if (is_object($data)) {
                $array = array();
                foreach ($data as $k => $v) {
                    $array[$k] = $v;
                }
                $data = $array;
            }
            
            $content = $this->_ci->load->view($view, $data, true);
            
            if ($overwrite) {
                $this->set($content);
            } else {
                $this->append($content);
            }
        }
        return $this;
    }
    
    /**
     * Parses a view inside this partial, overwrite if wanted
     * @param string $view
     * @param array $data
     * @param bool $overwrite
     * @return Partial
     */
    public function parse($view, $data = array(), $overwrite = false) {
        if (!$this->_cached) {
            if (!class_exists('CI_Parser')) {
                $this->_ci->load->library('parser');
            }
            
            // better object to array
            if (is_object($data)) {
                $array = array();
                foreach ($data as $k => $v) {
                    $array[$k] = $v;
                }
                $data = $array;
            }
            
            $content = $this->_ci->parser->parse($view, $data, true);
            
            if ($overwrite) {
                $this->set($content);
            } else {
                $this->append($content);
            }
        }
        
        return $this;
    }
    
    /**
     * Loads a widget inside this partial, overwrite if wanted
     * @param string $name
     * @param array $data
     * @param bool $overwrite
     * @return Partial
     */
    public function widget($name, $data = array(), $overwrite = false) {
        if (!$this->_cached) {
            $widget = $this->template->widget($name, $data);
            
            if ($overwrite) {
                $this->set($widget->content());
            } else {
                $this->append($widget->content());
            }
        }
        return $this;
    }
    
    /**
     * Enable cache with TTL, default TTL is 60
     * @param int $ttl
     * @param mixed $identifier
     */
    public function cache($ttl = 60, $identifier = '') {
        if (!class_exists('CI_Cache')) {
            $this->_ci->load->driver('cache', array('adapter' => 'file'));
        }
        
        $this->_cache_ttl = $ttl;
        $this->_identifier = $identifier;
        
        if ($cached = $this->_ci->cache->get($this->cache_id())) {
            $this->_cached = true;
            $this->_content = $cached;
        }
        return $this;
    }
    
    /**
     * Used for cache identification
     * @return string
     */
    private function cache_id() {
        if ($this->_identifier) {
            return $this->_name . '_' . $this->_identifier . '_' . md5(get_class($this) . implode('', $this->_args));
        } else {
            return $this->_name . '_' . md5(get_class($this) . implode('', $this->_args));
        }
    }
    
    /**
     * Trigger returns the result if a trigger is set
     * @param array $args
     * @return string
     */
    public function trigger($args) {
        if (!$this->_trigger) {
            return implode('', $args);
        } else {
            return call_user_func_array($this->_trigger, $args);
        }
    }
    
    /**
     * Bind a trigger function
     * Can be used like bind($this, "function") or bind("function")
     * @param mixed $arg
     */
    public function bind() {
        if ($count = func_num_args()) {
            if ($count >= 2) {
                $args = func_get_args();
                $obj = array_shift($args);
                $func = array_pop($args);
                
                foreach ($args as $trigger) {
                    $obj = $obj->$trigger;
                }
                
                $this->_trigger = array($obj, $func);
            } else {
            	$args = func_get_args();
                $this->_trigger = reset($args);
            }
        } else {
            $this->_trigger = FALSE;
        }
    }
}

/**
 * The mother of all widgets
 * it represent a generic HTML element
 */
class Widget extends Partial
{
	// The name of the array present in the data array given to the view that will render this widget
    const HTML_ARG_NAME = 'HTML';
	const HTML_DEFAULT_VALUE = ''; // Default value of the html element
    const HTML_NAME = 'name'; // HTML name attribute
    const HTML_ID = 'id'; // HTML id attribute
    
    /**
     * It gets also the htmlArgs array as parameter, it will be used to set the HTML properties
     */
    public function __construct($name, $args, $htmlArgs = array())
	{
		parent::__construct($name, $args);
		
		// Initialising HTML properties
		$this->_setHtmlProperties($htmlArgs);
        
        // Loads helper message to manage returning messages
		$this->load->helper('message');
	}
	
	/**
	 * (non-PHPdoc)
	 * @see Partial::content()
	 */
	public function content()
	{
		if (!$this->_cached)
		{
			if (method_exists($this, 'display'))
			{
				// capture output
				ob_start();
				$this->display($this->_args);
				$buffer = ob_get_clean();
				
				// if no content is produced but there was direct ouput we set 
				// that output as content
				if (!$this->_content && $buffer)
				{
					$this->set($buffer);
				}
			}
		}
		
		return parent::content();
	}
	
    /**
     * Initialising html properties, such as the id and name attributes of the HTML element
     */
    private function _setHtmlProperties($htmlArgs)
    {
		// If $htmlArgs wasn't already stored in $this->_args
		if (!isset($this->_args[Widget::HTML_ARG_NAME]))
		{
			$this->_args[Widget::HTML_ARG_NAME] = array();
			
			// Avoids that elements in a HTML page have the same name or id
			$randomIdentifier = uniqid(rand(0, 1000));
			$this->_args[Widget::HTML_ARG_NAME][Widget::HTML_ID] = $randomIdentifier;
			$this->_args[Widget::HTML_ARG_NAME][Widget::HTML_NAME] = $randomIdentifier;
			
			foreach($htmlArgs as $argName => $argValue)
			{
				$this->_args[Widget::HTML_ARG_NAME][$argName] = $argValue;
			}
		}
    }
    
    /**
	 * 
	 */
	public static function printAttribute($htmlArgs, $attribute, $isValuePresent = true)
	{
		if ($attribute != null)
		{
			if (isset($htmlArgs[$attribute]))
			{
				if ($isValuePresent === true)
				{
					$value = $htmlArgs[$attribute];
					
					if (is_bool($value))
					{
						$value = $value ? 'true' : 'false';
					}
					
					echo sprintf('%s="%s"', $attribute, $value);
				}
				else
				{
					echo $attribute;
				}
			}
		}
	}
}

/**
 * It exends the Widget class to represent an HTML dropdown
 */
class DropdownWidget extends Widget
{
	// The name of the element of the data array given to the view
	// this element is an array of elements to be place inside the dropdown
	const WIDGET_DATA_ELEMENTS_ARRAY_NAME = 'ELEMENTS_ARRAY';
	// Name of the property that will be used to store the value attribute of the option tag
	const ID_FIELD = 'id';
	// Name of the property that will be used to store the value between the option tags
	const DESCRIPTION_FIELD = 'description';
	// The name of the element of the data array given to the view
	// this element is used to tell what element of the dropdown is selected
	const SELECTED_ELEMENT = 'selectedElement';
	
	const SIZE = 'size'; // 
	const MULTIPLE = 'multiple'; // 
	
	/**
	 * 
	 */
	public function __construct($name, $args, $htmlArgs = array())
	{
		parent::__construct($name, $args, $htmlArgs);
		
		// 
		if (!isset($this->_args[DropdownWidget::SELECTED_ELEMENT]))
		{
			$this->_args[DropdownWidget::SELECTED_ELEMENT] = Widget::HTML_DEFAULT_VALUE;
		}
	}
	
    /**
     * 
     */
    public function setMultiple()
    {
		$this->_args[Widget::HTML_ARG_NAME][DropdownWidget::MULTIPLE] = 'multiple';
    }
    
    /**
	 * Add the correct select to the model used to load a list of elemets for this dropdown
	 * @param model $model the model used to load elements
	 * @param string $idName the name of the field that will used to be the value of the option tag
	 * @param string $descriptionName the name of the field that will used to be displayed in the dropdown
	 */
	protected function addSelectToModel($model, $idName, $descriptionName)
	{
		$model->addSelect(
			sprintf(
				'%s AS %s, %s AS %s',
				$idName,
				DropdownWidget::ID_FIELD,
				$descriptionName,
				DropdownWidget::DESCRIPTION_FIELD
			)
		);
	}
    
	/**
	 * Set the array used to populate the dropdown
	 * @param array $elements list used to populate this dropdown
	 * @param boolean $emptyElement if an empty element must be added at the beginning of the dropdown
	 * @param string $stdDescription description of the empty element
	 * @param string $noDataDescription description if no data are found
	 * @param string $id value of the attribute value of the empty element
	 */
	protected function setElementsArray(
		$elements, $emptyElement = false, $stdDescription = '' , $noDataDescription = 'No data found' , $id = Widget::HTML_DEFAULT_VALUE
	)
	{
		$tmpElements = array();
		
		if (isError($elements))
		{
			if (is_object($elements) && isset($elements->retval))
			{
				show_error($elements->retval);
			}
			else if (is_string($elements))
			{
				show_error($elements);
			}
			else
			{
				show_error('Generic error occurred');
			}
		}
		else
		{
			if ($emptyElement === true)
			{
				$tmpElements = $this->addElementAtBeginning(
					$elements,
					$stdDescription,
					$noDataDescription,
					$id
				);
			}
			else
			{
				$tmpElements = $elements->retval;
			}
			
			$this->_args[DropdownWidget::WIDGET_DATA_ELEMENTS_ARRAY_NAME] = $tmpElements;
		}
	}
	
	/**
     * Adds an element to the beginning of the array
     */
	protected function addElementAtBeginning($elements, $stdDescription, $noDataDescription, $id)
	{
		$element = new stdClass();
		$element->{DropdownWidget::ID_FIELD} = $id;
		$element->{DropdownWidget::DESCRIPTION_FIELD} = $stdDescription;
		
		if (!hasData($elements))
		{
			$element->{DropdownWidget::DESCRIPTION_FIELD} = $noDataDescription;
		}
		
		array_unshift($elements->retval, $element);
		
		return $elements->retval;
	}
	
	/**
	 * Loads the dropdown view with all the elements to be displayed
	 */
	protected function loadDropDownView()
	{
		$this->view('widgets/dropdown', $this->_args);
	}
}

/**
 * 
 */
class DropdownWidgetUDF extends DropdownWidget
{
	/**
	 * 
	 */
	public function render($parameters)
	{
		$tmpElements = array();
		
		// 
		foreach($parameters as $parameter)
		{
			// 
			if ((is_array($parameter) && count($parameter) == 2)
				|| (is_string($parameter) || is_numeric($parameter))
				|| (is_object($parameter) && isset($parameter->{PARENT::ID_FIELD}) && isset($parameter->{PARENT::DESCRIPTION_FIELD})))
			{
				$element = new stdClass(); // 
				// 
				if (is_array($parameter) && count($parameter) == 2)
				{
					$element->{PARENT::ID_FIELD} = $parameter[0]; // 
					$element->{PARENT::DESCRIPTION_FIELD} = $parameter[1]; // 
				}
				// 
				else if (is_string($parameter) || is_numeric($parameter))
				{
					$element->{PARENT::ID_FIELD} = $parameter; // 
					$element->{PARENT::DESCRIPTION_FIELD} = $parameter; // 
				}
				// 
				else if (is_object($parameter) && isset($parameter->{PARENT::ID_FIELD}) && isset($parameter->{PARENT::DESCRIPTION_FIELD}))
				{
					$element->{PARENT::ID_FIELD} = $parameter->{PARENT::ID_FIELD}; // 
					$element->{PARENT::DESCRIPTION_FIELD} = $parameter->{PARENT::DESCRIPTION_FIELD}; // 
				}
				
				array_push($tmpElements, $element); // 
			}
		}
		
		$this->setElementsArray(
			success($tmpElements),
			true,
			$this->_args[Widget::HTML_ARG_NAME][UDFWidgetTpl::PLACEHOLDER],
			'No data found for this UDF'
		);
		
		$this->loadDropDownView();
		
		echo $this->content();
    }
}

/**
 * It exends the Widget class to represent an HTML textarea
 */
class TextareaWidget extends Widget
{
	const TEXT = 'text'; // 
	const ROWS = 'rows'; // 
	const COLS = 'cols'; // 
	
	/**
	 * 
	 */
	protected function setText($text)
	{
		$this->_args[TextareaWidget::TEXT] = $text;
	}
	
	/**
	 * 
	 */
	protected function loadTextareaView()
	{
		$this->view('widgets/textarea', $this->_args);
	}
}

/**
 * 
 */
class TextareaWidgetUDF extends TextareaWidget
{
	/**
	 * 
	 */
	public function render($parameters)
	{
		if ($parameters != null)
		{
			$this->setText($parameters);
		}
		else
		{
			$this->setText('');
		}
		
		$this->loadTextareaView();
		
		echo $this->content();
    }
}

/**
 * It exends the Widget class to represent an HTML textarea
 */
class TextfieldWidget extends Widget
{
	const VALUE = 'text'; // 
	const SIZE = 'size'; // 
	
	/**
	 * 
	 */
	protected function setValue($value)
	{
		$this->_args[TextfieldWidget::VALUE] = $value;
	}
	
	/**
	 * 
	 */
	protected function loadTextfieldView()
	{
		$this->view('widgets/textfield', $this->_args);
	}
}

/**
 * 
 */
class TextfieldWidgetUDF extends TextfieldWidget
{
	/**
	 * 
	 */
	public function render($parameters)
	{
		if ($parameters != null)
		{
			$this->setValue($parameters);
		}
		else
		{
			$this->setValue('');
		}
		
		$this->loadTextfieldView();
		
		echo $this->content();
    }
}

/**
 * It exends the Widget class to represent an HTML dropdown
 */
class CheckboxWidget extends Widget
{
	// The name of the element of the data array given to the view
	// this element is an array of elements to be place inside the dropdown
	const WIDGET_DATA_VALUES_ARRAY_NAME = 'VALUES_ARRAY';
	// The name of the element of the data array given to the view
	// this element is used to tell what element of the dropdown is selected
	const CHECKED_VALUE = 'checkedValue';
	
	/**
	 * 
	 */
	public function __construct($name, $args, $htmlArgs = array())
	{
		parent::__construct($name, $args, $htmlArgs);
		
		// 
		if (!isset($this->_args[CheckboxWidget::CHECKED_VALUE]))
		{
			$this->_args[CheckboxWidget::CHECKED_VALUE] = Widget::HTML_DEFAULT_VALUE;
		}
	}

	/**
	 * 
	 */
	protected function setValuesArray($values)
	{
		if (isError($values))
		{
			if (is_object($values) && isset($values->retval))
			{
				show_error($values->retval);
			}
			else if (is_string($values))
			{
				show_error($values);
			}
			else
			{
				show_error('Generic error occurred');
			}
		}
		else
		{
			$this->_args[CheckboxWidget::WIDGET_DATA_VALUES_ARRAY_NAME] = $values->retval;
		}
	}
	
	/**
	 * 
	 */
	protected function loadCheckboxView()
	{
		$this->view('widgets/checkbox', $this->_args);
	}
}

/**
 * 
 */
class CheckboxWidgetUDF extends CheckboxWidget
{
	/**
	 * 
	 */
	public function render($parameters)
	{
		$tmpValues = array();
		
		// 
		foreach($parameters as $parameter)
		{
			// 
			if (is_string($parameter) || is_numeric($parameter))
			{
				array_push($tmpValues, $parameter); // 
			}
		}
		
		$this->setValuesArray(success($tmpValues));
		
		$this->loadCheckboxView();
		
		echo $this->content();
    }
}

/**
 * 
 */
abstract class UDFWidgetTpl extends Widget
{
	const WIDGET_NAME = 'UDFWidget';
	const SCHEMA_ARG_NAME = 'schema';
	const TABLE_ARG_NAME = 'table';
	const FIELD_ARG_NAME = 'field';
	
	const TITLE = 'description';
	const LABEL = 'title';
	const PLACEHOLDER = 'placeholder';
	const DEFAULT_VALUE = 'defaultValue';
	const REGEX = 'regex';
	const REQUIRED = 'required';
	const MAX_VALUE = 'max-value';
	const MIN_VALUE = 'min-value';
}