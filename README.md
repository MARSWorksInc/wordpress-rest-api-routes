# MarsPress FrontEndRoute
### Installation
Require the composer package in your composer.json with `marspress/rest-api-route` with minimum `dev-main` OR run `composer require marspress/rest-api-route`

### Resources
* https://developer.wordpress.org/rest-api/extending-the-rest-api/adding-custom-endpoints/

There are WordPress constants for the allowed request methods. It is recommended that you use the constants below, concatenating where necessary. 
```PHP
\WP_REST_Server::READABLE = 'GET'

\WP_REST_Server::EDITABLE = 'POST, PUT, PATCH'

\WP_REST_Server::DELETABLE = 'DELETE'

\WP_REST_Server::ALLMETHODS = 'GET, POST, PUT, PATCH, DELETE'
```

### Usage
You will first need to create a Rest Namespace. Then you will add Endpoint objects to it, and add Parameter objects to your Endpoints.

#### Rest Namespace
`new \MarsPress\RestAPI\Rest_Namespace()` takes 4 parameters, 2 required and 2 optional.
* Namespace (required)(string)
  * The slug for your namespace, this should be unique in the whole WordPress theme and plugin scope.
  * E.g. `seminars` would result in your namespace being at `/wp-json/seminars`.
* Version (required)(string)
  * The version string for your namespace, generally these are just `v1` etc.
  * It is recommended that you keep all versions of your namespaces available.
  * E.g. `v1` would result in your namespace being at `/wp-json/seminars/v1`.
* Permissions Callback (optional)(callable)
  * The method to call to check if the namespace endpoints are able to get accessed.
  * This method should return a bool.
  * Defaults to a method that returns `true`, making the endpoints public to all.
  * Your permission callback should take 1 parameter of the type `\WP_REST_Request`.
  * This can be a Closure function, or `[ $this, '<public_method_name>' ]` for non-static classes or `[ __CLASS__, '<public_method_name>' ]` for static classes.
* Override (required)(bool)
  * Whether the namespace should be able to override other namespaces and endpoints.
  * Defaults to `false`.

##### Available Methods
* `$namespace->add_endpoints()`
  * Adds Endpoint objects to the Rest Namespace instance.
  * Takes any umber of `\MarsPress\RestAPI\Endpoint` objects.

#### Endpoint
`new \MarsPress\RestAPI\Endpoint()` takes 5 parameters, 3 required and 2 optional.
* Endpoint (required)(string)
  * The slug of the endpoint.
  * E.g. `upcoming` would result in the endpoint being at `/wp-json/seminars/v1/upcoming`.
* Allowed Methods (required)(string)
  * The allowed HTTP Request methods for the endpoint.
  * This should be a comma seperated string of the methods. You may use the WordPress constants.
  * E.g. `GET`.
* Callback (required)(callable)
  * The method to be called for the endpoint.
  * The callback should take 1 parameter of the type `\WP_REST_Request`.
  * Your method should return a new instance of `\WP_REST_Response`.
  * This can be a Closure function, or `[ $this, '<public_method_name>' ]` for non-static classes or `[ __CLASS__, '<public_method_name>' ]` for static classes.
* Override (optional)(bool)
  * If the endpoint should override existing endpoints of the same name and namespace.
  * Defaults to `false`.
* Permissions Callback (optional)(callable)
  * The method to call to check if the endpoint is able to get accessed.
  * This method should return a bool.
  * Defaults to `null`, falling back to the Rest Namespace permission callback.
  * Your permission callback should take 1 parameter of the type `\WP_REST_Request`.
  * This can be a Closure function, or `[ $this, '<public_method_name>' ]` for non-static classes or `[ __CLASS__, '<public_method_name>' ]` for static classes.

##### Available Methods
* `$endpoint->add_parameters()`
  * Adds Parameter objects to the Endpoint instance.
  * Takes any umber of `\MarsPress\RestAPI\Parameter` objects.

#### Parameter
`new \MarsPress\RestAPI\Parameter()` takes 7 parameters, 1 required and 6 optional.
* Name (required)(string)
  * The name of the parameter.
  * This should be unique to the Endpoint.
* Type (optional)(string)
  * Whether the parameter should be passed in the URL or as a URL parameter.
  * Defaults to `?`, having the parameter have to be passed as `?<param_name>=x`.
  * Valid values are `?` and `/`.
  * `/` will pass the parameter into the URL as such: `/wp-json/seminars/v1/upcoming/<param_name>`
  * You may have multiple `/` parameters, they will just be registered in the order they are in the array of Parameter objects in the Endpoint class.
* Default (optional)(mixed)
  * The default value for the parameter.
  * Default is no default: `null`.
* Required (optional)(bool)
  * If the parameter is required or not.
  * Defaults to `false`.
* Match (optional)(string)
  * The regex type matching for the parameter.
  * This is only used in Parameters of the type `/`.
  * Defaults to `.{1,128}`, meaning it will match any character 1 to 128 times.
  * Other useful matches are:
    * `.+`, match any character any amount of times.
    * `\d{1,8}`, match any digit 1 to 8 times.
* Validation Callback (optional)(callable)
  * Defaults to no callback: `null`.
  * If any of your parameters fail their validation, your Endpoint's callback will not be called.
  * Your validation callback should take 3 parameters:
    * Parameter Value (string)
    * Request Object (\WP_REST_Request)
    * Parameter Name (string)
  * IMPORTANT: The limitation of the validation callback in WordPress is that the framework does not test if your return is `empty` or `nullable`. Another limitation is that it only outputs a vague message if you return `false`.
    * Return `true` specifically if the Parameter Value matches your validation.
    * If it does not meet your validation, it is recommended that you return a new instance of `\WP_Error`, allowing you to return specific status codes and messages.
  * This can be a Closure function, or `[ $this, '<public_method_name>' ]` for non-static classes or `[ __CLASS__, '<public_method_name>' ]` for static classes.
* Sanitization Callback (optional)(callable)
  * Defaults to no callback: `null`.
  * Sanitize any of the parameter values before they are passed to your Endpoint's callback.
  * Your sanitization callback should take 3 parameters:
    * Parameter Value (string)
    * Request Object (\WP_REST_Request)
    * Parameter Name (string)
  * This can be a Closure function, or `[ $this, '<public_method_name>' ]` for non-static classes or `[ __CLASS__, '<public_method_name>' ]` for static classes.

#### Example
First we make a new Namespace:
```PHP
$myApiNamespace = new \MarsPress\RestAPI\Rest_Namespace(
    'my_api_namespace',
    'v1'
);
```

Then we can create an Endpoint:
```PHP
$myApiNamespace_Endpoint = new \MarsPress\RestAPI\Endpoint(
    'my_endpoint',
    \WP_REST_Server::READABLE,
    function ( \WP_REST_Request $_request ){
        return new \WP_REST_Response([
            'message'   => 'PID is ' . $_request->get_param("pid"),
        ],200);
    }
);
```

Then we can create a Parameter:
```PHP
$myApiNamespace_Endpoint_PID = new \MarsPress\RestAPI\Parameter(
    'pid',
    '/',
    12,
    true,
    '.+',
    function( string $_parameterValue, \WP_REST_Request $_request, string $_parameterName ){
        if( ! \is_numeric($_parameterValue) ){
            return new \WP_Error(
                503,
                "$_parameterName must be a numeric value."
            );
        }
        return true;
    },
    function( string $_parameterValue, \WP_REST_Request $_request, string $_parameterName ){
        return \number_format( \floatval( $_parameterValue ), 2 );
    }
);
```

Then we can add Parameters to the newly created Endpoint:
```PHP
$myApiNamespace_Endpoint->add_parameters(
    $myApiNamespace_Endpoint_PID,
);
```

Finally, we can add the Endpoint to the Rest Namespace:
```PHP
$myApiNamespace->add_endpoints(
    $myApiNamespace_Endpoint,
);
```

In the above example, the REST API endpoint we created will be at `/wp-json/my_api_namespace/v1/my_endpoint/<pid>`. The PID parameter will be passed inside the URL, and it must be a numeric value, finally it will be sanitized into a two decimal formatted float. It then returns a new `\WP_REST_Response` with a message and a status code of `200`.

### Method / Class Chaining
It is highly recommended that you use method and class chaining when creating your Rest Namespace, Endpoints, and Parameters.

Here is the above example, but using method and class chaining:
```PHP
$myApiNamespace = (new \MarsPress\RestAPI\Rest_Namespace(
    'my_api_namespace',
    'v1'
))->add_endpoints(
    (new \MarsPress\RestAPI\Endpoint(
        'my_endpoint',
        \WP_REST_Server::READABLE,
        function ( \WP_REST_Request $_request ){
            return new \WP_REST_Response([
                'message'   => 'PID is ' . $_request->get_param("pid"),
            ],200);
        }
    ))->add_parameters(
        new \MarsPress\RestAPI\Parameter(
            'pid',
            '/',
            12,
            true,
            '.+',
            function( string $_parameterValue, \WP_REST_Request $_request, string $_parameterName ){
                if( ! \is_numeric($_parameterValue) ){
                    return new \WP_Error(
                        503,
                        "$_parameterName must be a numeric value."
                    );
                }
                return true;
            },
            function( string $_parameterValue, \WP_REST_Request $_request, string $_parameterName ){
                return \number_format( \floatval( $_parameterValue ), 2 );
            }
        ),
    ),
);
```

### Advanced Topics

#### X-WP-Nonce Header
If you need to use current user sessions in your Endpoint and Parameter callbacks (e.g. `current_user_can()` or `get_current_user_id()`), you must send a generated nonce in the header of your request.

This is particularly useful if you only want users to be able to access their own data through your REST endpoint. E.g. I could call `/wp-json/my_api_namespace/v1/my_endpoint/18` to get user data for a user with the ID of 18, but if I am not logged in as the user with the ID 18, I would not be able to access the endpoint.

To generate a nonce and make is usable in your JS scripts, you will need to localize your script as such:
```PHP
wp_register_script( 'my_js_script', '<script_uri>', ['<dependencies>'], '<version>', true );
wp_localize_script( 'my_js_script', '<localized_object_name>', [
    'my_nonce'  => \wp_create_nonce('wp_rest'),
]);
wp_enqueue_script( 'my_js_script' );
```

It is important to use `wp_rest` as the key as that is the key WordPress will use to validate the nonce in the REST API.

In a jQuery AJAX example, you would set it as such:
```JS
jQuery.ajax({
  type: 'GET',
  url: '/wp-json/my_api_namespace/v1/my_endpoint/18',
  async: true,
  dataType: 'json',
  data: ({}),
  beforeSend: function( _request ) {
    _request.setRequestHeader( 'X-WP-Nonce', '<localized_object_name>.my_nonce' );
  },
  complete: function ( _response ) {},
});
```

Nothing further is needed, if you generated and localized your nonce and set the `X-WP-Nonce` header correctly, your REST endpoint callbacks should automatically know the current user session that is trying to access the endpoint.
