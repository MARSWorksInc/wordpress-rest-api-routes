<?php
/*
 * @package marspress/rest-api-endpoint
 */

namespace MarsPress\RestAPI;

if( ! class_exists( 'Parameter' ) )
{

    final class Parameter
    {

        private string $name;

        private string $type;

        /**
         * @var mixed $default
         */
        private $default;

        private bool $required;

        private string $match;

        /**
         * @var callable|null $validationCallback
         */
        private $validationCallback;

        /**
         * @var callable|null $sanitizationCallback
         */
        private $sanitizationCallback;

        public function __construct(
            string $_name,
            $_type = '?',
            $_default = null,
            bool $_required = false,
            string $_match = '.{1,128}',
            $_validationCallback = null,
            $_sanitizationCallback = null
        )
        {

            $this->name = $_name;
            $this->type = $_type;
            $this->default = $_default;
            $this->required = $_required;
            $this->match = $_match;
            $this->validationCallback = $_validationCallback;
            $this->sanitizationCallback = $_sanitizationCallback;

        }

        public function get_name(): string
        {

            return $this->name;

        }

        public function get_type(): string
        {

            return $this->type;

        }

        public function get_default()
        {

            return $this->default;

        }

        public function get_required(): bool
        {

            return $this->required;

        }

        public function get_match(): string
        {

            return $this->match;

        }

        public function get_validation_callback(): ?callable
        {

            return $this->validationCallback;

        }

        public function get_sanitization_callback(): ?callable
        {

            return $this->sanitizationCallback;

        }

    }

}