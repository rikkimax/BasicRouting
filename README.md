BasicRouting
============

A very basic routing framework for php


## Adding a route

### Basic
```php
\Routes\add('/say/hello/[:name]', function($args) {
  echo 'Hello ' . $args['name'] . '!';
});
```

### Loading a file based on a route
The file which is ran occurs after the function calling.
All arguments that would have been passed to the function are set as global values for the file.
Function and file can be null for none.
```php
\Routes\add('/say/hello/[:name]', function($args) {
  echo 'Hello ' . $args['name'] . '!';
}, 'afile.php');
```

### Special routes
Special routes are the error codes given for http.
E.g. 404, 403, 500 and 200.

They can be found at:
* \Routes\Ok = 200
* \Routes\InternalError = 500
* \Routes\UnknownPage = 404
* \Routes\MovedUrl = 301

### Special route 404
```php
\Routes\add(\Routes\UnknownPage, function() {
  echo 'Error 404';
});
```

### Error and redirects
You can manually set the error code or redirect to url via the route function.
```php
\Routes\add(\Routes\UnknownPage, function($args, &$error, &$redirectTo) {
  $error = \Routes\MovedUrl;
  $redirectTo = '/actual';
});
```


## Running the routes
```php
\Routes\run($_SERVER['REQUEST_URI']);
```
