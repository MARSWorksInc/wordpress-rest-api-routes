<?php
/*
 * @package marspress/rest-api-endpoint
 */

namespace MarsPress\RestAPI;

if( ! class_exists( 'Rest_Namespace' ) )
{

    final class Rest_Namespace
    {

        private string $namespace;

        private string $version;

        /**
         * @var callable $permissionsCallback
         */
        private $permissionsCallback;

        private bool $override;

        /**
         * @var Endpoint[] $endpoints
         */
        private array $endpoints;

        public function __construct(
            string $_namespace,
            string $_version,
           $_permissionsCallback = null,
            bool $_override = false
        )
        {

            if( is_null( $_permissionsCallback ) ){

                $_permissionsCallback = [ $this, 'default_permissions' ];

            }

            $this->namespace = $_namespace;
            $this->version = $_version;
            $this->permissionsCallback = $_permissionsCallback;
            $this->override = $_override;

            if( ! is_callable( $this->permissionsCallback ) ){

                $message = "The permissions callback for the REST API namespace <strong><em>{$this->namespace}</em></strong> is not callable. Please update your namespace's permissions callback to a callable function.";
                add_action( 'admin_notices', function () use ($message){
                    $output = $this->output_admin_notice($message);
                    echo $output;
                }, 10, 0 );

            }else{

                add_action( 'rest_api_init', [ $this, 'register_rest_api_endpoints' ], 10, 0 );

            }

        }

        private function does_namespace_existence(): bool
        {

            $currentNamespace = $this->namespace . '/' . $this->version;

            global $wp_rest_server;

            if(
                is_callable( [ $wp_rest_server, 'get_namespaces' ] ) &&
                is_array( $namespaces = $wp_rest_server->get_namespaces() ) &&
                in_array( $currentNamespace, $namespaces )
            ){

                return true;

            }

            return false;

        }

        /**
         * @action rest_api_init
         * @class \MarsPress\RestAPI\Rest_Namespace
         * @function register_rest_api_endpoints
         * @return void
         */
        public function register_rest_api_endpoints()
        {

            if(
                ! $this->override &&
                $this->does_namespace_existence()
            ){

                return;

            }

            if( isset( $this->endpoints ) ){

                foreach ( $this->endpoints as $_endpoint ){

                    $namespace = $this->namespace . '/' . $this->version;
                    $route = '/' . $_endpoint->get_endpoint();

                    $permissionsCallback = $this->permissionsCallback;

                    if(
                        ! is_null( $_endpoint->get_permissions_callback() ) &&
                        is_callable( $_endpoint->get_permissions_callback() )
                    ){

                        $permissionsCallback = $_endpoint->get_permissions_callback();

                    }

                    $parameters = $_endpoint->get_parameters();
                    $parsedParameters = [];

                    if( count( $parameters ) > 0 ){

                        foreach ( $parameters as $_parameter ){

                            if( $_parameter->get_type() === '/' ){

                                $route .= "/(?P<{$_parameter->get_name()}>{$_parameter->get_match()})";

                            }

                            $parsedParameters[$_parameter->get_name()] = [
                                'required'  => $_parameter->get_required(),
                            ];

                            if( ! is_null( $_parameter->get_default() ) ){

                                $parsedParameters[$_parameter->get_name()]['default'] = $_parameter->get_default();

                            }

                            if(
                                ! is_null( $_parameter->get_validation_callback() ) &&
                                is_callable( $_parameter->get_validation_callback() )
                            ){

                                $parsedParameters[$_parameter->get_name()]['validate_callback'] = $_parameter->get_validation_callback();

                            }

                            if(
                                ! is_null( $_parameter->get_sanitization_callback() ) &&
                                is_callable( $_parameter->get_sanitization_callback() )
                            ){

                                $parsedParameters[$_parameter->get_name()]['sanitize_callback'] = $_parameter->get_sanitization_callback();

                            }

                        }

                    }

                    \register_rest_route(
                        $namespace,
                        $route,
                        [
                            'methods'               => $_endpoint->get_allowed_methods(),
                            'callback'              => $_endpoint->get_callback(),
                            'permission_callback'   => $permissionsCallback,
                            'args'                  => $parsedParameters,
                        ],
                        $_endpoint->get_override()
                    );

                }

            }

        }

        private function get_endpoint_object( $_endpoint ): ?Endpoint
        {

            if( ! isset( $this->endpoints ) ){ return null; }

            if( array_key_exists( $_endpoint, $this->endpoints ) ){

                return $this->endpoints[$_endpoint];

            }

            return null;

        }

        /**
         * @class \MarsPress\RestAPI\Rest_Namespace
         * @function add_endpoints
         * @param Endpoint[] $_endpoints
         * @return void
         */
        public function add_endpoints( \MarsPress\RestAPI\Endpoint ...$_endpoints )
        {

            if( ! isset( $this->endpoints ) ){

                $this->endpoints = [];

            }

            if( count( $_endpoints ) > 0 ){

                foreach ( $_endpoints as $_endpoint ){

                    if( ! array_key_exists( $_endpoint->get_endpoint(), $this->endpoints ) ){

                        if( ! is_callable( $_endpoint->get_callback() ) ){

                            $message = "The callback for the endpoint <strong><em>{$_endpoint->get_endpoint()}</em></strong> in the REST API namespace <strong><em>{$this->namespace}</em></strong> is not callable. Please update your endpoint's callback to a callable function.";
                            add_action( 'admin_notices', function () use ($message){
                                $output = $this->output_admin_notice($message);
                                echo $output;
                            }, 10, 0 );
                            continue;

                        }

                        if(
                            ! is_null( $_endpoint->get_permissions_callback() ) &&
                            ! is_callable( $_endpoint->get_permissions_callback() )
                        ){

                            $message = "The permissions callback for the endpoint <strong><em>{$_endpoint->get_endpoint()}</em></strong> in the REST API namespace <strong><em>{$this->namespace}</em></strong> is not callable. Please update your endpoint's permissions callback to a callable function.";
                            add_action( 'admin_notices', function () use ($message){
                                $output = $this->output_admin_notice($message);
                                echo $output;
                            }, 10, 0 );
                            continue;

                        }

                        $this->endpoints[$_endpoint->get_endpoint()] = $_endpoint;

                    }else{

                        $message = "The endpoint <strong><em>{$_endpoint->get_endpoint()}</em></strong> in the REST API namespace <strong><em>{$this->namespace}</em></strong> already exists. Please update your endpoint to a unique value for the namespace.";
                        add_action( 'admin_notices', function () use ($message){
                            $output = $this->output_admin_notice($message);
                            echo $output;
                        }, 10, 0 );

                    }


                }

            }

        }

        /**
         * @class \MarsPress\RestAPI\Rest_Namespace
         * @function default_permissions
         * @param \WP_REST_Request $_request
         * @return bool
         */
        public function default_permissions( \WP_REST_Request $_request ): bool
        {

            return true;

        }

        private function output_admin_notice( string $_message ): string
        {

            if( strlen( $_message ) > 0 && \current_user_can( 'administrator' ) ){

                return "<div style='background: white; padding: 12px 20px; border-radius: 3px; border-left: 5px solid #dc3545;' class='notice notice-error is-dismissible'><p style='font-size: 16px;'>$_message</p><small><em>This message is only visible to site admins</em></small></div>";

            }

            return '';

        }

    }

}