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
            $_parameters = [],
            $_override = false,
            $_permissionsCallback = null
        )
        {

            $this->endpoint = $_endpoint;
            $this->allowedMethods = $_allowedMethods;
            $this->callback = $_callback;
            $this->parameters = $_parameters;
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

        public function get_parameters(): array
        {

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

    }

}