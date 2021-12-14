<?php
/*
 * @package marspress/rest-api-endpoint
 */

namespace MarsPress\RestAPI;

if( ! class_exists( 'Endpoint' ) )
{

    final class Endpoint
    {

        private string $endpoint;

        private string $allowedMethods;

        /**
         * @var callable $callback
         */
        private $callback;

        /**
         * @var Parameter[] $parameters
         */
        private array $parameters;

        private bool $override;

        /**
         * @var callable|null $permissionsCallback
         */
        private $permissionsCallback;

        public function __construct(
            string $_endpoint,
            string $_allowedMethods,
            $_callback,
            $_override = false,
            $_permissionsCallback = null
        )
        {

            $this->endpoint = $_endpoint;
            $this->allowedMethods = $_allowedMethods;
            $this->callback = $_callback;
            $this->override = $_override;
            $this->permissionsCallback = $_permissionsCallback;

        }

        public function get_endpoint(): string
        {

            return $this->endpoint;

        }

        public function get_allowed_methods(): string
        {

            return $this->allowedMethods;

        }

        public function get_callback(): callable
        {

            return $this->callback;

        }

        public function get_parameters(): ?array
        {

            if( ! isset( $this->parameters ) ){ return []; }

            return $this->parameters;

        }

        public function get_override(): bool
        {

            return $this->override;

        }

        public function get_permissions_callback(): ?callable
        {

            return $this->permissionsCallback;

        }

        private function get_parameter_object( $_parameter ): ?Parameter
        {

            if( ! isset( $this->parameters ) ){ return null; }

            if( array_key_exists( $_parameter, $this->parameters ) ){

                return $this->parameters[$_parameter];

            }

            return null;

        }

        /**
         * @class \MarsPress\RestAPI\Endpoint
         * @function add_parameters
         * @param Parameter[] $_parameters
         * @return Endpoint
         */
        public function add_parameters( \MarsPress\RestAPI\Parameter ...$_parameters ): Endpoint
        {

            if( ! isset( $this->parameters ) ){

                $this->parameters = [];

            }

            if( count( $_parameters ) > 0 ){

                foreach ( $_parameters as $_parameter){

                    if( ! array_key_exists( $_parameter->get_name(), $this->parameters ) ){

                        if(
                            ! is_null( $_parameter->get_validation_callback() ) &&
                            ! is_callable( $_parameter->get_validation_callback() )
                        ){

                            $message = "The parameter <strong><em>{$_parameter->get_name()}</em></strong> for the endpoint <strong><em>{$this->endpoint}</em></strong> has a non-callbacle validation callback. Please update your parameter's validation callback to a callable function.";
                            add_action( 'admin_notices', function () use ($message){
                                $output = $this->output_admin_notice($message);
                                echo $output;
                            }, 10, 0 );
                            continue;

                        }

                        if(
                            ! is_null( $_parameter->get_sanitization_callback() ) &&
                            ! is_callable( $_parameter->get_sanitization_callback() )
                        ){

                            $message = "The parameter <strong><em>{$_parameter->get_name()}</em></strong> for the endpoint <strong><em>{$this->endpoint}</em></strong> has a non-callbacle sanitization callback. Please update your parameter's sanitization callback to a callable function.";
                            add_action( 'admin_notices', function () use ($message){
                                $output = $this->output_admin_notice($message);
                                echo $output;
                            }, 10, 0 );
                            continue;

                        }

                        $this->parameters[$_parameter->get_name()] = $_parameter;

                    }else{

                        $message = "The parameter <strong><em>{$_parameter->get_name()}</em></strong> for the endpoint <strong><em>{$this->endpoint}</em></strong> already exists. Please update your parameter's name to a unique value for the endpoint.";
                        add_action( 'admin_notices', function () use ($message){
                            $output = $this->output_admin_notice($message);
                            echo $output;
                        }, 10, 0 );

                    }


                }

            }

            return $this;

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