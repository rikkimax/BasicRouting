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
	 *
	 * Example usage:
	 * \Routes\add('/test/[:name]/[:t2]', function($args) {
	 *    print_r($args);
	 * });
	 * \Routes\add('/test/[:name]/[:t2]', function($args, &$error, &$redirectTo) {
	 *    print_r($args);
         *    $error = \Routes\InternalError;
	 *    $error = \Routes\Ok;
	 *    $error = \Routes\UnknownPage;
	 *    $error = \Routes\MovedUrl;
	 *
	 *    $redirectTo = 'http://example.com';
	 * });
	 * \Routes\add(\Routes\Ok, function(&$error, &$redirectTo) {
	 *    echo 'GOT error 400';
	 * });
	 */
	function add($urlMatch, $func) {
		global $routes;
		$routes[$urlMatch] = $func;
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
		foreach($routes as $urlMatch => $func) {
			$args = match($url, $urlMatch);
			if ($args !== FALSE) {
				$error = Ok;
				$func($args, $error, $redirectTo);
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
	}
?>
