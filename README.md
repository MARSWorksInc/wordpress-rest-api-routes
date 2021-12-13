# MarsPress FrontEndRoute
### Installation
Require the composer package in your composer.json with `marspress/rest-api-route` with minimum `dev-main` OR run `composer require marspress/rest-api-route`

### Resources
* https://developer.wordpress.org/rest-api/extending-the-rest-api/adding-custom-endpoints/
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

#### Endpoint
`new \MarsPress\RestAPI\Endpoint()` takes 6 parameters, 3 required and 3 optional.

#### Parameter
`new \MarsPress\RestAPI\Parameter()` takes 7 parameters, 1 required and 6 optional.