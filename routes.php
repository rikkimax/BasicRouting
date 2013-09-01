<?php
	namespace Routes;
	
	/*
	* Copyright (c) 2013 Richard Andrew Cattermole
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
	

	/*
	 * Matches a url to a paramaterised url
	 *
	 * Arguments:
	 * url: The url being compared to
	 * urlMatch: The match being ran
	 *
	 * Example usage:
	 * match('/test/hi/heyyy', '/test/[:name]/[:t2]')
         * match('/testa', '/testa')
	 */
	function match($url, $urlMatch) {
		if (strpos($urlMatch, '[:') !== FALSE) {
			$matches = array();
			$args = preg_replace('/(\[:(\w*)\])+/', '(?<${2}>\w*)', $urlMatch);
			preg_match_all('/' . addcslashes($args, '/[]') . '/', $url, $matches);
			
			if (count($matches[0]) > 0)
				return $matches;
		} else {
			return $url === $urlMatch;
		}
		return FALSE;
	}
	
	$routes = array();
	const Ok = 200;
	const InternalError = 500;
	const UnknownPage = 404;
	const MovedUrl = 301;
	
	/*
	 * Adds a route.
	 * 
	 * Arguments:
	 * urlMatch: The url match associated with this function
	 * func: The function that will be called
	 * file: A file that is loaded instead of a function. Global values are replaced instead of arguments.
	 *
	 * Example usage:
	 * \Routes\add('/test/[:name]/[:t2]', function($args) {
	 *    print_r($args);
	 * });
	 * \Routes\add('/test/[:name]/[:t2]', function($args, &$error, &$redirectTo, &$contentType) {
	 *    print_r($args);
         *    $error = \Routes\InternalError;
	 *    $error = \Routes\Ok;
	 *    $error = \Routes\UnknownPage;
	 *    $error = \Routes\MovedUrl;
	 *
	 *    $redirectTo = 'http://example.com';
	 * });
	 * \Routes\add(\Routes\Ok, function(&$error, &$redirectTo, &$contentType) {
	 *    echo 'GOT error 400';
	 * });
	 * \Routes\add('/test/[:name]/[:t2]', function($args) {
	 *    print_r($args);
	 * }, 'afile.php');
	 */
	function add($urlMatch, $func=null, $file=null) {
		global $routes;
		$routes[$urlMatch] = array($func, $file);
	}
	
	/*
	 * Runs the routes
	 *
	 * Arugments:
	 * url: The url to match against
	 *
	 * Example usage:
	 * \Routes\run('/test/hi/heyyy');
	 * \Routes\run($_SERVER['REQUEST_URI']);
	 */
	function run($url) {
		global $routes;
		$error = UnknownPage;
		$redirectTo = '';
		$contentType = '';
		foreach($routes as $urlMatch => $vals) {
			$args = match($url, $urlMatch);
			if ($args !== FALSE) {
				$error = Ok;
				if ($vals[0] !== null)
					$vals[0]($args, $error, $redirectTo, $contentType);
				if ($vals[1] !== null) {
					$_GLOBALS['args'] = $args;
					$_GLOBALS['error'] = $error;
					$_GLOBALS['redirectTo'] = $redirectTo;
					$_GLOBALS['contentType'] = $contentType;
					include_once($vals[1]);
					$error = $_GLOBALS['error'];
					$redirectTo = $_GLOBALS['redirectTo'];
					$_GLOBALS['contentType'] = $contentType;
				}
				break;
			}
		}
		
		if (array_key_exists($error, $routes)) {
			$routes[$error]($error, $redirectTo);	
		}
		
		http_response_code($error);
		
		if ($redirectTo !== '') {
			header('Location: ' . $redirectTo);
		}
		
		if ($contentType !== '') {
			header('Content-type: ' . $contentType);
		}
	}
	
		
if (!function_exists('http_response_code')) {
		
        function http_response_code($code = NULL) {
            if ($code !== NULL) {
                switch ($code) {
                    case 100: $text = 'Continue'; break;
                    case 101: $text = 'Switching Protocols'; break;
                    case 200: $text = 'OK'; break;
                    case 201: $text = 'Created'; break;
                    case 202: $text = 'Accepted'; break;
                    case 203: $text = 'Non-Authoritative Information'; break;
                    case 204: $text = 'No Content'; break;
                    case 205: $text = 'Reset Content'; break;
                    case 206: $text = 'Partial Content'; break;
                    case 300: $text = 'Multiple Choices'; break;
                    case 301: $text = 'Moved Permanently'; break;
                    case 302: $text = 'Moved Temporarily'; break;
                    case 303: $text = 'See Other'; break;
                    case 304: $text = 'Not Modified'; break;
                    case 305: $text = 'Use Proxy'; break;
                    case 400: $text = 'Bad Request'; break;
                    case 401: $text = 'Unauthorized'; break;
                    case 402: $text = 'Payment Required'; break;
                    case 403: $text = 'Forbidden'; break;
                    case 404: $text = 'Not Found'; break;
                    case 405: $text = 'Method Not Allowed'; break;
                    case 406: $text = 'Not Acceptable'; break;
                    case 407: $text = 'Proxy Authentication Required'; break;
                    case 408: $text = 'Request Time-out'; break;
                    case 409: $text = 'Conflict'; break;
                    case 410: $text = 'Gone'; break;
                    case 411: $text = 'Length Required'; break;
                    case 412: $text = 'Precondition Failed'; break;
                    case 413: $text = 'Request Entity Too Large'; break;
                    case 414: $text = 'Request-URI Too Large'; break;
                    case 415: $text = 'Unsupported Media Type'; break;
                    case 500: $text = 'Internal Server Error'; break;
                    case 501: $text = 'Not Implemented'; break;
                    case 502: $text = 'Bad Gateway'; break;
                    case 503: $text = 'Service Unavailable'; break;
                    case 504: $text = 'Gateway Time-out'; break;
                    case 505: $text = 'HTTP Version not supported'; break;
                    default: $text = 'Internal Server Error';
                    break;
                }

                $protocol = (isset($_SERVER['SERVER_PROTOCOL']) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0');
                header($protocol . ' ' . $code . ' ' . $text);
                $GLOBALS['http_response_code'] = $code;
				
            } else {
                $code = (isset($GLOBALS['http_response_code']) ? $GLOBALS['http_response_code'] : 200);
            }

            return $code;
        }
    }
?>
