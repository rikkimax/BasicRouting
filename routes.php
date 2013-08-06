<?php
	namespace Routes;

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
	const MovedUrl = 403;
	
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