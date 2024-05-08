<?php
/* Copyright (C) 2008 Technikum-Wien
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307, USA.
 *
 * Authors: Christian Paminger 	< christian.paminger@technikum-wien.at >
 *          Andreas Oesterreicher 	< andreas.oesterreicher@technikum-wien.at >
 *          Rudolf Hangl 		< rudolf.hangl@technikum-wien.at >
 *          Gerald Simane-Sequens 	< gerald.simane-sequens@technikum-wien.at >
 */
 
  /*
   This file is part of, or distributed with, libXMLRPC - a C library for 
   xml-encoded function calls.

   Author: Dan Libby (dan@libby.com)
   Epinions.com may be contacted at feedback@epinions-inc.com
  */

 
// ensure extension is loaded.
// a function to ensure the xmlrpc extension is loaded.
// xmlrpc_epi_dir = directory where libxmlrpc.so.0 is located
// xmlrpc_php_dir = directory where xmlrpc-epi-php.so is located
function xu_load_extension($xmlrpc_php_dir="") {
    $bSuccess = false;
    if(!extension_loaded('xmlrpc')) {
        $bSuccess = true;
        @putenv("LD_LIBRARY_PATH=/usr/lib/php4/apache/xmlrpc/");
        if ($xmlrpc_php_dir) {
            $xmlrpc_php_dir .= '/';
        }
        if (!extension_loaded("xmlrpc")) {
            $bSuccess = @dl($xmlrpc_php_dir . "xmlrpc-epi-php.so");
        }
    }
    return $bSuccess;
}

/* generic function to call an http server with post method */
function xu_query_http_post($request, $host, $uri, $port, $debug, 
                            $timeout, $user, $pass, $secure=false) {
    $response_buf = "";
    if ($host && $uri && $port) {
        $content_len = strlen($request);

        $fsockopen = $secure ? "fsockopen_ssl" : "fsockopen";
	    if (!function_exists($fsockopen)) {
		    dbg1("opening socket not OK : change from $fsockopen to fsockopen", $debug);
			$fsockopen="fsockopen";
		}
		
	    dbg1("opening socket to host: $host, port: $port, uri: $uri", $debug);
        $query_fd = $fsockopen($host, $port, $errno, $errstr, 10);

        if ($query_fd) {

            $auth = "";
            if ($user) {
	            dbg1("sending User Authorization:</h3> <xmp>\n$user\n</xmp>", $debug);
                $auth = "Authorization: Basic " .base64_encode($user . ":" . $pass) . "\r\n";
            }

            $http_request = 
                "POST $uri HTTP/1.0\r\n" .
                "User-Agent: xmlrpc-epi-php/0.2 (PHP)\r\n" .
                "Host: $host".($port!=''?":$port":"")."\r\n" .
                $auth .
                "Content-Type: text/xml\r\n" .
                "Content-Length: $content_len\r\n" . 
                "\r\n" .
                $request;

            dbg1("sending http request:</h3> <xmp>\n$http_request\n</xmp>", $debug);
            fputs($query_fd, $http_request, strlen($http_request));
            dbg1("receiving response...", $debug);
            $header_parsed = false;
            $line = fgets($query_fd, 4096);
			$response_buf="";
            while ($line) {
                if (!$header_parsed) {
                    if ($line === "\r\n" || $line === "\n") {
                        $header_parsed = 1;
                    }
                    dbg2("got header - $line", $debug);
                }
                else {
                    $response_buf .= $line;
                }
                $line = fgets($query_fd, 4096);
           	}
			if (isset($query_fd))
	            fclose($query_fd);
        }
        else {
            dbg1("socket open failed", $debug);
			return false;			
        }
    }
    else {
        dbg1("missing param(s)", $debug);
		return false;
    }
    dbg1("got response:</h3>. <xmp>\n$response_buf\n</xmp>\n", $debug);
    return $response_buf;
}

function xu_fault_code($code, $string) {
    return array('faultCode' => $code,
                 'faultString' => $string);
}

function find_and_decode_xml($buf, $debug) {
	$retval='';
    if (strlen($buf)) {
        $xml_begin = substr($buf, strpos($buf, "<?xml"));
        if (strlen($xml_begin)) {
            $retval = xmlrpc_decode($xml_begin);
        }
        else {
            dbg1("xml start token not found", $debug);
        }
    }
    else {
        dbg1("no data", $debug);
    }
    return $retval;
}

 
/**
 * @param params   a struct containing 3 or more of these key/val pairs:
 * @param host		 remote host (required)
 * @param uri		 remote uri	 (required)
 * @param port		 remote port (required)
 * @param method   name of method to call
 * @param args	    arguments to send (parameters to remote xmlrpc server)
 * @param debug	 debug level (0 none, 1, some, 2 more)
 * @param timeout	 timeout in secs.  (0 = never)
 * @param user		 user name for authentication.  
 * @param pass		 password for authentication
 * @param secure	 secure. wether to use fsockopen_ssl. (requires special php build).
 * @param output	 array. xml output options. can be null.  details below:
 *
 *     output_type: return data as either php native data types or xml
 *                  encoded. ifphp is used, then the other values are ignored. default = xml
 *     verbosity:   determine compactness of generated xml. options are
 *                  no_white_space, newlines_only, and pretty. default = pretty
 *     escaping:    determine how/whether to escape certain characters. 1 or
 *                  more values are allowed. If multiple, they need to be specified as
 *                  a sub-array. options are: cdata, non-ascii, non-print, and
 *                  markup. default = non-ascii | non-print | markup
 *     version:     version of xml vocabulary to use. currently, three are
 *                  supported: xmlrpc, soap 1.1, and simple. The keyword auto is also
 *                  recognized to mean respond in whichever version the request came
 *                  in. default = auto (when applicable), xmlrpc
 *     encoding:    the encoding that the data is in. Since PHP defaults to
 *                  iso-8859-1 you will usually want to use that. Change it if you know
 *                  what you are doing. default=iso-8859-1
 *
 *   example usage
 *
 *                   $output_options = array('output_type' => 'xml',
 *                                           'verbosity' => 'pretty',
 *                                           'escaping' => array('markup', 'non-ascii', 'non-print'),
 *                                           'version' => 'xmlrpc',
 *                                           'encoding' => 'utf-8'
 *                                         );
 *                   or
 *
 *                   $output_options = array('output_type' => 'php');
 */
function xu_rpc_http_concise($params) {
    $host = $uri = $port = $method = $args = $debug = null;
    $timeout = $user = $pass = $secure = $debug = $output= null;
   
    extract($params);
   
    // default values
    if(!$port) {
        $port = 80;
    }
    if(!$uri) {
        $uri = '/';
    }
    if(!$output) {
        $output = array('version' => 'xmlrpc');
    }

    $response_buf = "";
	$retval= "fail: utils.php Param not OK ";
    if ($host && $uri && $port) {
        if (!$request_xml = xmlrpc_encode_request($method, $args, $output))
			return  "fail: xmlrpc_encode_request ";
			
        if (!$response_buf = xu_query_http_post($request_xml, 
                                           $host, 
                                           $uri, 
                                           $port, 
                                           $debug,
                                           $timeout, 
                                           $user, 
                                           $pass, 
                                           $secure))
			return  "fail: xu_query_http_post ";
										   
        $retval = find_and_decode_xml($response_buf, $debug);
    }
    return $retval;
}

/* call an xmlrpc method on a remote http server. legacy support. */
function xu_rpc_http($method, $args, $host, $uri="/", $port=80, $debug=false, 
                     $timeout=0, $user=false, $pass=false, $secure=false) {
    return xu_rpc_http_concise(
                               array(
                                     method  => $method,
                                     args    => $args,
                                     host    => $host,
                                     uri     => $uri,
                                     port    => $port,
                                     debug   => $debug,
                                     timeout => $timeout,
                                     user    => $user,
                                     pass    => $pass,
                                     secure  => $secure
                                     ));
}

function xu_is_fault($arg) {
    // xmlrpc extension finally supports this.
    return is_array($arg) ? xmlrpc_is_fault($arg) : false;
}

/* sets some http headers and prints xml */
function xu_server_send_http_response($xml) {
    header("Content-type: text/xml");
    header("Content-length: " . strlen($xml) );
    echo $xml;
}

function dbg($msg) {
    echo "<h3>$msg</h3>"; flush();
}
function dbg1($msg, $debug_level) {
    if ($debug_level >= 1) {
        dbg($msg);
    }
}
function dbg2($msg, $debug_level) {
    if ($debug_level >= 2) {
        dbg($msg);
    }
}
?>
