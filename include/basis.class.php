<?php
/**
* Implementation super class
*
* Provides basic functionality for all other classes
*
* What you will want to use (see the actual methods documentation for details):
* <pre>
*   debug                   <- we just love it!
*   getAbsoluteURL          <- get an absolute URL with the complete protocol and host stuff
*   getLink                 <- get a link with parameters in the backend
*   getWebLink              <- get a link with parameters to frontend pages
*   getWebMediaLink         <- get a link to a media file
*   getXHTMLString          <- to make a string xhtml compatible
*   _gt                     <- get a translation for a string (phrases)
*   _gtf                    <- get a translation and fill in the vars (sprintf-like)
*   initializeParams        <- loads params from _GET/_POST for this module (paramName)
*   initializeSessionParam  <- registers a parameter to the session and resets others
*   get-/setSessionValue    <- if you want to store data in the session or read it
*                              make pretty sure the sessionParamName is not in use!
*   logMsg                  <- messages show up in the system protocol for info or error tracking
*   parseRequestURI         <- in some cases you may need information about the URI, nicely prepared
* </pre>
* ...anything else?
*
* @copyright 2002-2007 by papaya Software GmbH - All rights reserved.
* @link http://www.papaya-cms.com/
* @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License, version 2
*
* You can redistribute and/or modify this script under the terms of the GNU General Public
* License (GPL) version 2, provided that the copyright and license notes, including these
* lines, remain unmodified. papaya is distributed in the hope that it will be useful, but
* WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
* FOR A PARTICULAR PURPOSE.
*
* @package papaya_base
* @author Thomas Weinert <info@papaya-cms.com>
* @version $Id: sys_base_object.php 24106 2008-12-11 11:40:26Z weinert $
*/

/**
* log type for user messages (login/logout)
*/
define('PAPAYA_LOGTYPE_USER', 1);
/**
* log type for page messages (published)
*/
define('PAPAYA_LOGTYPE_PAGES', 2);
/**
* log type for database messages (errors)
*/
define('PAPAYA_LOGTYPE_DATABASE', 3);
/**
* log type for calendar messages
*/
define('PAPAYA_LOGTYPE_CALENDAR', 4);
/**
* log type for cronjob messages
*/
define('PAPAYA_LOGTYPE_CRONJOBS', 5);
/**
* log type for surfer/community messages
*/
define('PAPAYA_LOGTYPE_SURFER', 6);
/**
* log type for system messages
*/
define('PAPAYA_LOGTYPE_SYSTEM', 7);
/**
* log type for system messages
*/
define('PAPAYA_LOGTYPE_MODULES', 8);

/**
* line break string constant
*/
define('LF', "\n");

/**
* Include string functions class library
*/
//require_once(PAPAYA_INCLUDE_PATH.'system/papaya_strings.php');

/**
* Implementation super class
*
* Provides basic functionality for all other classes
*
* @package papaya_base
* @author Thomas Weinert <info@papaya-cms.com>
*/
class basis {
  /**
  * Error message
  * @var base_errors $msgs
  */
  var $errormsg;

  /**
  * Parameters
  * @var array $params
  */
  var $params = array();
  /**
  * Parameter name
  * @var string $paramName
  */
  var $paramName = 'p';
  /**
  * Request method
  * @var string $requestMethod
  */
  var $requestMethod = 'get';
  /**
  * Session parameter name
  * @var string $sessionParamName
  */
  var $sessionParamName = NULL;

  /**
  * allowed url level separators
  * @access private
  * @var array
  */
  var $urlLevelSeparators = array(',', ':', '*', '!', "'", "/");

  /**
  * Constructor
  *
  * @access public
  */
  function __construct($db_system='pgsql')
  {

    //empty implementation for all child classes
  }

	function getErrorMsg()
	{
		return $this->errormsg;
	}
	//include_once('pgsql.methods.php');



 /**
  * PHP 4 constuctor redirect
  *
  * @access public
  */
  function base_object() {
    $args = func_get_args();
    call_user_func_array(array(&$this, '__construct'), $args);
  }

  /**
  * Get session value
  *
  * @param string $name
  * @access public
  * @return mixed session value or NULL
  */
  function getSessionValue($name)
  {
    include_once(PAPAYA_INCLUDE_PATH.'system/sys_session.php');
    $this->sessionObj = &rewrite_session::getInstance();
    return $this->sessionObj->getValue($name);
  }

  /**
  * Set session value
  *
  * @param string $name
  * @param mixed $value session value
  * @access public
  */
  function setSessionValue($name, $value) {
    include_once(PAPAYA_INCLUDE_PATH.'system/sys_session.php');
    $this->sessionObj = &rewrite_session::getInstance();
    return $this->sessionObj->setValue($name, $value);
  }

  /**
  * Phrasetranslator - Fetch translation for all phrase
  *
  * @param string $phrase Phrase
  * @param mixed $module Modul optional, default value NULL
  * @access public
  * @return string
  */
  function _gt($phrase, $module = NULL) {
    if (@is_object($GLOBALS[PAPAYA_GLOBAL_PHRASEOBJECT]) && trim($phrase) != '') {
      return $GLOBALS[PAPAYA_GLOBAL_PHRASEOBJECT]->getText($phrase, $module);
    }
    return $phrase;
  }

  /**
  * Phrasetranslator - Fetch translation of one phrase and insert variable
  *
  * @param string $phrase Phrase
  * @param array $vals Parameter
  * @param mixed $module Modul
  * @access public
  * @return string
  */
  function _gtf($phrase, $vals, $module = NULL) {
    if (@is_object($GLOBALS[PAPAYA_GLOBAL_PHRASEOBJECT]) &&
        trim($phrase) != '') {
      return $GLOBALS[PAPAYA_GLOBAL_PHRASEOBJECT]->getTextFmt($phrase, $vals,
        $module);
    }
    return $phrase;
  }

  /**
  * Phrases - Locate files
  *
  * @param string $fileName filename
  * @access public
  * @return string file content
  */
  function _gtfile($fileName) {
    if (isset($this->authUser)) {
      $lng = $this->authUser->options['PAPAYA_UI_LANGUAGE'];
    } else {
      $lng = PAPAYA_UI_LANGUAGE;
    }
    $fileName = $_SERVER['DOCUMENT_ROOT'].PAPAYA_PATH_WEB.PAPAYA_PATH_ADMIN.
      '/data/'.$lng.'/'.$fileName;
    $fileName = str_replace('//', '/', $fileName);
    if ($fileName) {
      if ($fh = @fopen($fileName, 'r')) {
        $data = fread($fh, filesize($fileName));
        fclose($fh);
        return papaya_strings::ensureUTF8($data);
      }
    }
    return '';
  }


  /**
  * Log events
  *
  * @param integer $level message priority, {@see sys_error.php}
  * @param integer $type message typ (groups)
  * @param string $short message short (for lists)
  * @param string $long message detailed
  * @access public
  */
  function logMsg($level, $type, $short, $long = '', $addBacktrace = FALSE, $backtraceOffset = 2) {
    if (function_exists('is_a') && is_a($this, 'base_log')) {
      return 0;
    }
    if (defined('PAPAYA_GLOBAL_LOGOBJECT') &&
       (trim(PAPAYA_GLOBAL_LOGOBJECT) != '')) {
      if (isset($GLOBALS[PAPAYA_GLOBAL_LOGOBJECT]) &&
          is_object($GLOBALS[PAPAYA_GLOBAL_LOGOBJECT])) {
        $GLOBALS[PAPAYA_GLOBAL_LOGOBJECT]->logMessage($level, $type,
          $short, $long, $addBacktrace, $backtraceOffset);
      }
    }
  }

  function logVariable($level, $type, $title, $variable, $addBacktrace = FALSE, $backtraceOffset = 3) {
    if (function_exists('is_a') && is_a($this, 'base_log')) {
      return 0;
    }
    if (defined('PAPAYA_GLOBAL_LOGOBJECT') &&
       (trim(PAPAYA_GLOBAL_LOGOBJECT) != '')) {
      if (isset($GLOBALS[PAPAYA_GLOBAL_LOGOBJECT]) &&
          is_object($GLOBALS[PAPAYA_GLOBAL_LOGOBJECT])) {
        $GLOBALS[PAPAYA_GLOBAL_LOGOBJECT]->logVariable($level, $type,
          $title, $variable, $addBacktrace, $backtraceOffset);
      }
    }
  }

  /**
  * Adds a message in the message object.
  * All messages will be displayed in programm run
  *
  * @param integer $level message priority
  * @param string $text message
  * @access public
  */
  function addMsg($level, $text, $log = FALSE, $type = PAPAYA_LOGTYPE_SYSTEM) {
    if (is_object($this->msgs)) {
      $this->msgs->add($level, $text);
    }
    if ($log) {
      $this->logMsg($level, $type, $text);
    }
  }

  /**
  * Parameter initialisation
  *
  * All global parameters for the object will be read out of the global
  * namespace and put in $this->params. The current will script filename
  * is stored in the $this->baseLink.
  * If here is a parameter $sessionParamName or a property $this->sessionParamName,
  * the value will be registered in the session object.
  *
  * @param mixed $sessionParamName optional, default value NULL
  * @access public
  */
  function initializeParams($sessionParamName = NULL) {
    if (isset($this->paramName)) {
      if (!is_array($this->params = $this->getRequestParameters($this->paramName, 'GP'))) {
        $this->params = array();
      }
      if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] == 'post') {
        $this->requestMethod = 'post';
      }
    } else {
      $this->params = array();
    }
    if (isset($sessionParamName) && trim($sessionParamName) != '') {
      $this->sessionParamName = $sessionParamName;
    }
    $this->baseLink = $this->getBaseLink();
  }

  /**
  * get a parameter array from superglobal arrays,
  * remove magic quotes from the parameter values
  * and make that they are all utf-8
  *
  * the parameter $modes specifies the superglobals and the order
  *  Examples:
  *    GP = $_GET and $_POST, $_POST values override $_GET values
  *    CGP = $_COOKIE, $_GET and $_POST, $_POST overrides both, $_GET overrides $_COOKIE
  *
  * @param string $paramName
  * @param string $modes optional, default value 'GP'
  * @access public
  * @return array | NULL
  */
  function getRequestParameters($paramName, $modes = 'GP') {
    $sourceCount = strlen($modes);
    $result = NULL;
    for ($i = 0; $i < $sourceCount; ++$i) {
      $params = NULL;
      switch ($modes[$i]) {
      case 'S' :
        if (isset($_SESSION[$paramName])) {
          $params = $_SESSION[$paramName];
        }
        break;
      case 'C' :
        if (isset($_COOKIE[$paramName])) {
          $params = $_COOKIE[$paramName];
        }
        $params = $_COOKIE;
        break;
      case 'G' :
        if (isset($_GET[$paramName])) {
          $params = $_GET[$paramName];
        }
        if (defined('PAPAYA_URL_LEVEL_SEPARATOR') &&
            in_array(PAPAYA_URL_LEVEL_SEPARATOR, $this->urlLevelSeparators) &&
            isset($_GET) &&
            is_array($_GET)) {
          foreach ($_GET as $paramKey => $paramValue) {
            if (0 === strpos($paramKey, $paramName.PAPAYA_URL_LEVEL_SEPARATOR)) {
              $paramSubKeys = explode(PAPAYA_URL_LEVEL_SEPARATOR, $paramKey);
              if (is_array($paramSubKeys) && count($paramSubKeys) > 1) {
                if (!is_array($params)) {
                  $params = array();
                }
                $paramArray = &$params;
                for ($k = 1, $max = count($paramSubKeys) - 1; $k < $max && $k < 40; $k++) {
                  if (!isset($paramArray[$paramSubKeys[$k]])) {
                    $paramArray[$paramSubKeys[$k]] = array();
                  }
                  $paramArray = &$paramArray[$paramSubKeys[$k]];
                }
                $paramArray[end($paramSubKeys)] = $paramValue;
              }
            }
          }
        }
        break;
      case 'P' :
        if (isset($_POST[$paramName])) {
          $params = $_POST[$paramName];
        }
        break;
      }
      if (get_magic_quotes_gpc() || get_magic_quotes_runtime()) {
        $params = $this->stripSlashes($params);
      } else {
        $params = $this->ensureParamsUTF8($params);
      }
      if (empty($params)) {
        continue;
      } elseif (is_array($params) && count($params) > 0) {
        if (!is_array($result)) {
          $result = $params;
        } else {
          foreach ($params as $key => $value) {
            $result[$key] = $value;
          }
        }
      } else {
        $result = $params;
      }
    }
    return $result;
  }

  /**
  * Ensure UTF8 in parameters
  *
  * @param mixed $var array or string
  * @access public
  * @return mixed string or array or NULL
  */
  function ensureParamsUTF8($var, $recursionCount = 5) {
    if (isset($var)) {
      if (is_array($var)) {
        if ($recursionCount > 0) {
          $result = array();
          foreach ($var as $key => $value) {
            $result[$key] = $this->ensureParamsUTF8($value, $recursionCount - 1);
          }
          return $result;
        }
      } else {
        return papaya_strings::decodeInputData($var);
      }
    }
    return NULL;
  }

  /**
  * Strip slashes
  *
  * @param mixed $var array or string
  * @access public
  * @return mixed string or array or NULL
  */
  function stripSlashes($var, $recursionCount = 40) {
    if (isset($var)) {
      $result = array();
      if (is_array($var)) {
        foreach ($var as $key => $value) {
          if ($recursionCount > 0) {
            $result[$key] = $this->stripSlashes($value, $recursionCount - 1);
          }
        }
        return $result;
      } else {
        return $this->ensureParamsUTF8(stripslashes($var));
      }
    }
    return NULL;
  }

  /**
  * Initialisation of session-parameter
  *
  * If the parameter this->params[name] is set all variables within $resetParams get reset.
  * Otherwise if $this->sessionParams[$name] is set the parameter will replace
  * this->params[$name]. If there is no parameter set with name $name, this->params set to NULL
  *
  * @param string $name name of the parameter
  * @param array $resetParams array parameter optional, default NULL
  * @access public
  */
  function initializeSessionParam($name, $resetParams = NULL) {
    if (isset($this->params[$name])) {
      if (isset($resetParams) && is_array($resetParams) &&
          ((!isset($this->sessionParams[$name])) ||
          ($this->params[$name] != $this->sessionParams[$name]))) {
        foreach ($resetParams as $paramName) {
          if (isset($this->params[$paramName])) {
            unset($this->params[$paramName]);
          }
          if (isset($this->sessionParams[$paramName])) {
            unset($this->sessionParams[$paramName]);
          }
        }
      }
      $this->sessionParams[$name] = $this->params[$name];
    } elseif (isset($this->sessionParams[$name])) {
      $this->params[$name] = $this->sessionParams[$name];
    } else {
      $this->params[$name] = NULL;
    }
  }

  /**
  * Get base link
  *
  * @param integer $pageId
  * @param integer $categId
  * @access public
  * @return string $baseLink URL
  */
  function getBaseLink($pageId = 0, $categId = 0) {
    $data = $this->parseRequestURI();
    if (isset($data['output']) &&
        ($data['output'] == 'media' || $data['output'] == 'image')) {
      return $data['filename'];
    } elseif ($pageId > 0) {
      $pId = (int)$pageId;
    } elseif ($data['page_id'] > 0) {
      $pId = (int)$data['page_id'];
    } elseif (defined('PAPAYA_ADMIN_PAGE') && PAPAYA_ADMIN_PAGE) {
      $pId = 0;
    } elseif (isset($GLOBALS['PAPAYA_PAGE']) &&
              is_object($GLOBALS['PAPAYA_PAGE']) &&
              is_a($GLOBALS['PAPAYA_PAGE'], 'papaya_page')) {
      $pId = $GLOBALS['PAPAYA_PAGE']->topicId;
    } elseif (isset($_GET['p_id']) && $_GET['p_id'] > 0) {
      $pId = (int)$_GET['p_id'];
    } else {
      $pId = 0;
    }
    if ($categId > 0) {
      $cId = $categId;
    } elseif (isset($data['categ_id']) && $data['categ_id'] > 0) {
      $cId = (int)$data['categ_id'];
    } else {
      $cId = 0;
    }
    $baseLink = $data['filename'];
    if ($pId > 0 && $cId > 0) {
      $baseLink .= '.'.$cId;
    }
    if ($pId > 0) {
      $baseLink .= '.'.$pId;
    }
    if (isset($data['language']) && $data['language'] != '') {
      $baseLink .= '.'.$data['language'];
    }
    $baseLink .= '.'.$data['ext'];
    if ($data['preview']) {
      if ($data['datetime']) {
        $baseLink .= '.'.(int)$data['datetime'];
      }
      $baseLink .= '.preview';
    }
    return $baseLink;
  }

  /**
  * get base path to current script
  *
  * @param boolean $withDocumentRoot optional, default value FALSE
  * @access public
  * @return string
  */
  function getBasePath($withDocumentRoot = FALSE) {
    $path = dirname($_SERVER['SCRIPT_FILENAME']);
    if ($withDocumentRoot) {
      $result = $path;
    } else {
      if (preg_match('~^\w:~', $_SERVER['DOCUMENT_ROOT']) &&
          (!preg_match('~^\w:~', $_SERVER['SCRIPT_FILENAME']))) {
        $result = substr($path, strlen($_SERVER['DOCUMENT_ROOT']) - 2);
      } else {
        $result = substr($path, strlen($_SERVER['DOCUMENT_ROOT']));
      }
    }
    $result = str_replace('\\', '/', $result);
    if (substr($result, 0, 1) != '/' && substr($result, 1, 1) != ':') {
      $result = '/'.$result;
    }
    if (substr($result, -1) != '/') {
      $result .= '/';
    }
    $result = strtr($result, array('////' => '/', '///' => '/', '//' => '/'));

    return $result;
  }

  /**
  * Parse request URI to filter data out of it
  *
  * @access public
  * @return array
  */
  function parseRequestURI($URI = NULL) {
    if (empty($URI) && isset($_SERVER['REQUEST_URI'])) {
      $URI = $_SERVER['REQUEST_URI'];
    }
    if (defined('PAPAYA_PATH_WEB')) {
      $webPathQuoted = preg_quote(PAPAYA_PATH_WEB);
    } else {
      $webPathQuoted = '/';
    }
    $pSid = '[a-zA-Z\d,-]{20,40}';
    $pImage = '(/?(sid([a-z]*?('.$pSid.')))?
      ('.$webPathQuoted.')
      ([a-z\d_-]+)\.(image)(\.(gif|jpg|png)(\.preview)?)?)ix';
    $pMedia = '~^/?(sid([a-z]*?('.$pSid.')))?
      ('.$webPathQuoted.')
      ([a-z\d_-]+\.(media|thumb|download|popup)(\.(preview))?
      (\.(([a-z\d_]+)(\.([a-z\d]+))?)))~ix';
    $pPage = '~^/?(sid([a-z]*?('.$pSid.')))? #session id
      ('.$webPathQuoted.') # path
      (([a-z\d_-]+) #file title
        ((\.([\d]+))?\.([\d]+))? # category and page id
        ((\.([a-z]+))?\.(([a-oq-z]+|p(?!review))[a-z]*))
        ((\.([\d]+))?\.(preview))?)~ix';
    if (preg_match($pImage, $URI, $regs)) {
      $result = array(
        'sid'      => $regs[2],
        'path'     => $regs[5],
        'filename' => '',
        'ident'    => $regs[5],
        'language' => '',
        'ext'      => 'image',
        'output'   => 'image',
        'preview'  => ((@$regs[9] == '.preview') ? TRUE : FALSE),
      );
    } elseif (preg_match($pMedia, $URI, $regs)) {
      $mediaTypes = array('media', 'thumb', 'download', 'popup');
      $result = array(
        'sid'      => $regs[2],
        'path'     => $regs[4],
        'filename' => $regs[5],
        'media_id' => $regs[10],
        'categ_id' => 0,
        'page_id'  => 0,
        'language' => '',
        'ext'      => (in_array($regs[6], $mediaTypes) ? $regs[6] : 'media'),
        'preview'  => ((@$regs[8] == 'preview') ? TRUE : FALSE),
        'datetime' => 0,
        'output'   => 'media'
      );
    } elseif (preg_match($pPage, $URI, $regs)) {
      $result = array(
        'sid' => $regs[2],
        'path' => $regs[4],
        'filename' => $regs[6],
        'categ_id' => (int)$regs[9],
        'page_id' => (int)$regs[10],
        'language' => ($regs[13] != '') ? $regs[13] : '',
        'ext' => $regs[14],
        'preview' => ((@$regs[19] == 'preview') ? TRUE : FALSE),
        'datetime' => ((@$regs[18] > 0) ? (int)$regs[18] : 0),
        'output' => 'page'
      );
      if ($pos = strpos($URI, 'catalog=')) {
        $result['categ_id'] = (int)substr($URI,
          $pos + strlen('catalog='));
      }
    } else {
      $fileName = 'index';
      $ext = 'php';
      if (defined('PAPAYA_ADMIN_PAGE') && PAPAYA_ADMIN_PAGE) {
        if (preg_match('~([a-z_\.-]+)\.([a-z_-]+)(\?|$)~',
              $URI, $regs)) {
          $fileName = $regs[1];
          $ext = $regs[2];
        } elseif (preg_match('~([a-z_\.-]+)\.([a-z_-]+)(\?|$)~',
                    $_SERVER['SCRIPT_FILENAME'], $regs)) {
          $fileName = $regs[1];
          $ext = $regs[2];
        }
      } elseif (defined('PAPAYA_URL_EXTENSION') &&
                trim(PAPAYA_URL_EXTENSION) != '') {
        $ext = PAPAYA_URL_EXTENSION;
      }
      $result = array(
        'sid'      => NULL,
        'path'     => '',
        'filename' => $fileName,
        'page_id'  => 0,
        'categ_id' => 0,
        'language' => '',
        'ext'      => $ext,
        'preview'  => FALSE,
        'datetime' => 0,
        'output'   => 'page'
      );
    }
    return $result;
  }

  /**
  * Get web link
  *
  * @param mixed $pageId optional, page id, default value NULL
  * @param integer $lng optional, language id, default value NULL
  * @param string $mode optional, default value 'page'
  * @param mixed $params optional, default value NULL
  * @param mixed $paramName optional, default value NULL
  * @param string $text optional, default value empty string
  * @param integer $categId optional, default value NULL
  * @access public
  * @return string Weblink
  */
  function getWebLink($pageId = NULL, $lng = NULL, $mode = NULL, $params = NULL,
                      $paramName = NULL, $text = '', $categId = NULL) {
    if (!isset($pageId)) {
      if (isset($GLOBALS['PAPAYA_PAGE']) &&
          isset($GLOBALS['PAPAYA_PAGE']->requestData['page_id'])) {
        $pageId = (int)$GLOBALS['PAPAYA_PAGE']->requestData['page_id'];
      } else {
        $pageId = 0;
      }
    }
    if (!isset($categId)) {
      if (isset($GLOBALS['PAPAYA_PAGE']) &&
          isset($GLOBALS['PAPAYA_PAGE']->requestData['categ_id'])) {
        $categId = (int)$GLOBALS['PAPAYA_PAGE']->requestData['categ_id'];
      } else {
        $categId = 0;
      }
    }
    if (isset($mode) && strpos($mode, '.preview') !== FALSE) {
      $mode = substr($mode, 0, -8);
      $preview = '.preview';
    } elseif ((isset($GLOBALS['PAPAYA_PAGE']) &&
        $GLOBALS['PAPAYA_PAGE']->public == FALSE) ||
        $mode == 'preview' || $mode == 'xmlpreview') {
      if (isset($GLOBALS['PAPAYA_PAGE']) &&
          $GLOBALS['PAPAYA_PAGE']->versionDateTime > 0) {
        $preview = '.'.((int)$GLOBALS['PAPAYA_PAGE']->versionDateTime).'.preview';
      } else {
        $preview = '.preview';
      }
    } else {
      $preview = '';
    }
    $subCateg = ($categId > 0) ? '.'.(int)$categId : '';
    $subPage = ($pageId > 0) ? '.'.(int)$pageId : '';
    if (isset($lng) && trim($lng) != '') {
      $lngString = '.'.$lng;
    } elseif (isset($_REQUEST['language']) &&
              preg_match('/^[a-z]{1,3}$/', $_REQUEST['language'])) {
      $lngString = '.'.$_REQUEST['language'];
    } else {
      $lngString = '';
    }
    $pageModes = array(
      'page' => '.'.PAPAYA_URL_EXTENSION,
      'preview' => '.'.PAPAYA_URL_EXTENSION,
      'xmlpreview' => '.xml'
    );
    if (isset($mode) && isset($pageModes[$mode])) {
      $ext = $pageModes[$mode];
    } elseif (isset($mode) && preg_match('~^[a-z]+$~', $mode)) {
      $ext = '.'.$mode;
    } elseif (isset($GLOBALS['PAPAYA_PAGE']) &&
              is_object($GLOBALS['PAPAYA_PAGE']) &&
              isset($GLOBALS['PAPAYA_PAGE']->mode)) {
      $ext = '.'.$GLOBALS['PAPAYA_PAGE']->mode;
    } elseif (defined('PAPAYA_URL_EXTENSION') &&
              trim(PAPAYA_URL_EXTENSION) != '') {
      $ext = '.'.PAPAYA_URL_EXTENSION;
    } else {
      $ext = '.php';
    }
    $path = (defined('PAPAYA_ADMIN_PAGE') && PAPAYA_ADMIN_PAGE) ? '../' : '';
    $fileName = $this->escapeForFilename($text, 'index').$subCateg.$subPage.
      $lngString.$ext.$preview;
    return $path.$fileName.$this->encodeQueryString($params, $paramName);
  }

  /**
  * Encode query string
  *
  * @param array &$params array of params
  * @param string $paramName optional param name
  * @param integer $maxDepth optional default 5
  * @access public
  * @return string
  */
  function encodeQueryString($params, $paramName = NULL, $maxDepth = 5) {
    if (isset($params) && is_array($params)) {
      $ignore = array('p_id', 'p_pagemode', 'preview');
      foreach ($ignore as $key) {
        if (isset($params[$key])) {
          unset($params[$key]);
        }
      }
      $ignoreEmpty = array('preview_date');
      foreach ($ignoreEmpty as $key) {
        if (isset($params[$key]) && $params[$key] == '') {
          unset($params[$key]);
        }
      }
      $queryString = $this->encodeQueryStringRec($params, $paramName, NULL,
        $maxDepth);
      if (strlen($queryString) > 0) {
        return '?'.substr($queryString, 1);
      }
    }
    return '';
  }

  /**
  * Encode/Create the QueryString params (rekursive function)
  *
  * @param array &$params array of params
  * @param mixed $paramName base param name
  * @param mixed $prefix paramname prefix
  * @param integer $maxDepth depth levels left
  * @access private
  * @return string
  */
  function encodeQueryStringRec(&$params, $paramName, $prefix, $maxDepth) {
    if ($maxDepth < 1) {
      return '';
    }
    if (isset($params) && is_array($params) && count($params) > 0) {
      $result = '';
      if (isset($paramName)) {
        if (isset($prefix)) {
          if (defined('PAPAYA_URL_LEVEL_SEPARATOR') &&
              in_array(PAPAYA_URL_LEVEL_SEPARATOR, $this->urlLevelSeparators)) {
            $namebase = $prefix.PAPAYA_URL_LEVEL_SEPARATOR.urlencode($paramName);
          } else {
            $namebase = $prefix.'['.urlencode($paramName).']';
          }
        } else {
          $namebase = urlencode($paramName);
        }
        foreach ($params as $name=>$value) {
          if (is_array($value)) {
            $result .= $this->encodeQueryStringRec($value, $name, $namebase,
              $maxDepth - 1);
          } else {
            if (defined('PAPAYA_URL_LEVEL_SEPARATOR') &&
                in_array(PAPAYA_URL_LEVEL_SEPARATOR, $this->urlLevelSeparators)) {
              $result .= '&'.$namebase.PAPAYA_URL_LEVEL_SEPARATOR.
                urlencode($name).'='.urlencode($value);
            } else {
              $result .= '&'.$namebase.'['.urlencode($name).']='.urlencode($value);
            }
          }
        }
      } else {
        foreach ($params as $name => $value) {
          if (is_array($value)) {
            $result .= $this->encodeQueryStringRec($value, $name, NULL,
              $maxDepth - 1);
          } else {
            $result .= '&'.urlencode($name).'='.urlencode($value);
          }
        }
      }
      return $result;
    }
    return '';
  }

  /**
  * Recode query string
  *
  * @param string $queryString
  * @access public
  * @return string
  */
  function recodeQueryString($queryString, $newQueryParams = array()) {
    $queryParams = array();
    $queryString = urldecode($queryString);
    $listParamCounts = array();
    if (preg_match_all('~(^|[?&])(([^=&?]+)=([^&]+))|([^&]+)~', $queryString,
          $matches, PREG_SET_ORDER)) {
      foreach ($matches as $match) {
        if (isset($match[3]) && $match[3] != '') {
          if (substr($match[3], -2) == '[]') {
            if (isset($listParamCounts[$match[3]])) {
              ++$listParamCounts[$match[3]];
            } else {
              $listParamCounts[$match[3]] = 0;
            }
            $paramKey = substr($match[3], 0, -2).'['.$listParamCounts[$match[3]].']=';
            $queryParams[$paramKey] = $match[4];
          } else {
            $queryParams[$match[3].'='] = $match[4];
          }
        } elseif (isset($match[5])) {
          $queryParams[$match[5]] = TRUE;
        }
      }
    }
    $result = '';
    if (count($queryParams) > 0) {
      foreach ($queryParams as $paramKey => $paramValue) {
        if (substr($paramKey, -1) == '=') {
          $paramName = substr($paramKey, 0, -1);
          if (array_key_exists($paramName, $newQueryParams)) {
            if ($newQueryParams[$paramName] !== NULL) {
              $result .= '&'.urlencode($paramName).'='.
                urlencode($newQueryParams[$paramName]);
              unset($newQueryParams[$paramName]);
            }
          } else {
            $result .= '&'.urlencode($paramName).'='.
              urlencode($paramValue);
          }
        } else {
          $result .= '&'.urlencode($paramKey);
        }
      }
    }
    if (count($newQueryParams) > 0) {
      foreach ($newQueryParams as $paramName => $paramValue) {
        if ($paramValue !== NULL) {
          $result .= '&'.urlencode($paramName).'='.
            urlencode($paramValue);
        }
      }
    }
    if (strlen($result) > 1) {
      return '?'.substr($result, 1);
    } else {
      return '';
    }
  }

  /**
  * Escape chars in a string to use it in a filename
  *
  * @param string $str
  * @param string $default returned if str is empty
  * @access public
  * @return string
  */
  function escapeForFilename($str, $default = 'index') {
    if (defined('PAPAYA_URL_NAMELENGTH') && PAPAYA_URL_NAMELENGTH > 0) {
      $str = papaya_strings::normalizeString($str, PAPAYA_URL_NAMELENGTH);
    } else {
      $str = papaya_strings::normalizeString($str, 50);
    }
    if ($str != '') {
      return strtolower($str);
    } else {
      return $default;
    }
  }

  /**
  * Get web media link
  *
  * @param string $mid GUID
  * @param string $mode optional, default value 'media'
  * @param string $text optional, default value ''
  * @param string $ext optional, default value ''
  * @return mixed
  */
  function getWebMediaLink($mid, $mode = 'media', $text = '', $ext = '') {
    $pageModes = array(
      'media'    => '.media',
      'download' => '.download',
      'popup'    => '.popup',
      'thumb'    => '.thumb'
    );
    if (!empty($ext)) {
      if (substr($mid, (strlen($ext) + 1) * -1) == '.'.$ext) {
        $ext = '';
      } else {
        $ext = '.'.$ext;
      }
    }
    $mode = (isset($pageModes[$mode])) ? $pageModes[$mode]: $pageModes['media'];
    $public = (isset($GLOBALS['PAPAYA_PAGE']) &&
      $GLOBALS['PAPAYA_PAGE']->public) ? '' : '.preview';
    $text = preg_replace('#\.[a-z]{1,4}$#', '', $text);
    $path = (defined('PAPAYA_ADMIN_PAGE') && PAPAYA_ADMIN_PAGE) ? '../' : '';
    $result = $path.$this->escapeForFilename($text, 'index').$mode.$public.
      '.'.$mid.$ext;
    return $result;
  }

  /**
  * Return absolute URL
  *
  * @param string $url
  * @param string $text optional, default value ''
  * @param boolean $sid optional, default value TRUE
  * @access public
  * @return string URL
  */
  function getAbsoluteURL($url, $text = '', $sid = TRUE) {
    $protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') ?
      'https' : 'http';
    $baseHref = $protocol."://".$_SERVER['HTTP_HOST'];

    include_once(PAPAYA_INCLUDE_PATH.'system/sys_session.php');
    $this->sessionObj = &rewrite_session::getInstance();
    if ($this->sessionObj->started && $this->sessionObj->checkSIDPath() && $sid) {
      $sidStr = '/'.$this->sessionObj->sessionName.$this->sessionObj->sessionId;
    } else {
      $sidStr = '';
    }

    if (FALSE !== ($pos = strpos($url, '?'))) {
      $urlAppend = substr($url, $pos);
      $url = substr($url, 0, $pos);
    } elseif (FALSE !== ($pos = strpos($url, '#'))) {
      $urlAppend = substr($url, $pos);
      $url = substr($url, 0, $pos);
    } else {
      $urlAppend = '';
    }

    $pageId = (int)$url;
    include_once(PAPAYA_INCLUDE_PATH.'system/sys_checkit.php');
    if ($url == '/') {
      $href = $baseHref.PAPAYA_PATH_WEB;
    } elseif (preg_match("#^(\d+)\.(\d+)$#", trim($url), $regs)) {
      $href = $baseHref.$sidStr.PAPAYA_PATH_WEB.$this->getWebLink(
        (int)$regs[2], NULL, NULL, NULL, NULL, $text, (int)$regs[1]);
    } elseif ($pageId > 0 && preg_match("#^\d+$#", trim($url))) {
      $href = $baseHref.$sidStr.PAPAYA_PATH_WEB.$this->getWebLink(
        $pageId, NULL, NULL, NULL, NULL, $text);
    } elseif (checkit::isHTTPX($url)) {
      $href = $url;
    } elseif (preg_match("#^/#", $url)) {
      $href = $baseHref.$sidStr.$url;
    } else {
      $iUrl = $_SERVER['REQUEST_URI'];
      if (FALSE === ($pos = strpos($iUrl, '?'))) {
        $pos = strpos($iUrl, '#');
      }
      if (FALSE !== $pos) {
        $iUrl = substr($iUrl, 0, $pos);
      }
      $iUrl = preg_replace('([^/]+$)', '', $iUrl);
      $href = $baseHref.$iUrl.$url;
    }
    $href = preg_replace('~'.preg_quote(PAPAYA_PATH_ADMIN).'/../~', '/', $href);
    return $href.$urlAppend;
  }

  /**
  * Get link
  *
  * @param mixed $params optional, default value NULL (no Query String)
  * @param mixed $paramName optional, default value NULL ($this->paramName)
  * @param string $fileName
  * @param integer $pageId
  * @access public
  * @return string
  */
  function getLink($params = NULL, $paramName = NULL, $fileName = '', $pageId = NULL) {
    if (isset($fileName) && trim($fileName) != '') {
      $link = $fileName;
    } else {
      $link = $this->getBaseLink(0);
    }
    if ((!isset($paramName)) && isset($this->paramName)) {
      $queryString = $this->encodeQueryString($params, $this->paramName);
    } elseif ($paramName == '') {
      $queryString = $this->encodeQueryString($params, NULL);
    } else {
      $queryString = $this->encodeQueryString($params, $paramName);
    }
    if (isset($pageId) && $pageId > 0) {
      if (trim($queryString) != '') {
        return $link.$queryString.'&p_id='.(int)$pageId;
      } else {
        return $link.'?p_id='.(int)$pageId;
      }
    } else {
      return $link.$queryString;
    }
  }

  /**
  * Show the debug of the variable &$var
  *
  * @param mixed $var auszugebende Variable
  * @access public
  */
  function debug() {
    if ((!isset($this)) || is_a($this, 'base_log')) {
      return 0;
    }
    if (defined('PAPAYA_GLOBAL_LOGOBJECT') &&
       (trim(PAPAYA_GLOBAL_LOGOBJECT) != '')) {
      if (isset($GLOBALS[PAPAYA_GLOBAL_LOGOBJECT]) &&
          is_object($GLOBALS[PAPAYA_GLOBAL_LOGOBJECT])) {
        $params = func_get_args();
        call_user_func_array(array(&$GLOBALS[PAPAYA_GLOBAL_LOGOBJECT], 'debug'), $params);
      }
    }
  }

  function debugMsg($msg) {
    if ((!isset($this)) || is_a($this, 'base_log')) {
      return 0;
    }
    if (defined('PAPAYA_GLOBAL_LOGOBJECT') &&
       (trim(PAPAYA_GLOBAL_LOGOBJECT) != '')) {
      if (isset($GLOBALS[PAPAYA_GLOBAL_LOGOBJECT]) &&
          is_object($GLOBALS[PAPAYA_GLOBAL_LOGOBJECT])) {
        $GLOBALS[PAPAYA_GLOBAL_LOGOBJECT]->debugMsg($msg);
      }
    }
  }

  /**
  * prepends another directory to include path (if it doesn`t exist yet)
  * but leaves the current directory ('.') at position one
  *
  * @param string $newPath the new include directory to be added
  * @return boolean $wasModified whether the include path has been modified or not
  */
  function addIncludePath($newPath) {
    if (is_string($newPath) && trim($newPath) != '') {
      $completePath = get_include_path();
      if (substr(PHP_OS, 0, 3) == 'WIN') {
        $sep = ';';
      } else {
        $sep = ':';
      }
      $paths = explode($sep, $completePath);
      if (is_array($paths) && count($paths) > 0) {
        if (in_array($newPath, $paths)) {
          return FALSE;
        } else {
          if ($paths[0] == '.') {
            array_shift($paths);
            $paths = array_merge(array('.', $newPath), $paths);
          } else {
            $paths = array_merge($newPath, $paths);
          }
        }
      } else {
        $paths = $newPath;
      }
      if (set_include_path(implode($sep, $paths))) {
        return TRUE;
      }
    }
    return FALSE;
  }

  /**
  * Create XHTML-compliant string.
  *
  * @param string $str
  * @param boolean $nlToBr Convert line break in XHTML
  * @access public
  * @return string
  */
  function getXHTMLString($str, $nlToBr = FALSE) {
    $iStr = papaya_strings::entityToXML($str);
    if ($nlToBr) {
      $iStr = preg_replace_callback("/((<[^>]*)|\r\n|\n|\r)/",
        array(&$this, 'htmlLinebreakToBr'), $iStr);
    }
    include_once(PAPAYA_INCLUDE_PATH.'system/papaya_strings.php');
    include_once(PAPAYA_INCLUDE_PATH.'system/sys_simple_xmltree.php');
    if (trim($iStr) == '') {
      return '';
    } elseif (simple_xmltree::isXML("<dummy>".$iStr."</dummy>", $this)) {
      return $iStr;
    } elseif (defined('PAPAYA_DBG_XML_USERINPUT') &&
              PAPAYA_DBG_XML_USERINPUT === '1') {
      include_once(PAPAYA_INCLUDE_PATH.'system/papaya_xsl.php');
      $lineOut = (papaya_xsl::getFormattedLines(
        $iStr, $this->lastXMLError[1]['line'], FALSE));
      return '<papaya-error type="xml" msg="'.
        papaya_strings::escapeHTMLChars($this->lastXMLError[1]['error']).
        '">'.$lineOut.'</papaya-error>';
    } else {
      return papaya_strings::escapeHTMLChars($iStr);
    }
  }

  /**
  * Callback-function - transform \r\n in &lt;br /&gt;.
  *
  * @param array $regs Trefferdaten
  * @return string $result
  */
  function htmlLinebreakToBr($regs) {
    $result = @($regs[2] == $regs[1]) ? $regs[1] : '<br />';
    return $result;
  }
}
?>