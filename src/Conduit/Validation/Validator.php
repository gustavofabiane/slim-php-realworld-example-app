<?php

namespace Conduit\Validation;

use Psr\Http\Message\ServerRequestInterface;
use Respect\Validation\Exceptions\NestedValidationException;

class Validator
{

    /** @var array Validations errors */
    protected $errors = [];

    /**
     * Validate request params based on provided rules and fields
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @param array                                    $rules
     *
     * @return static
     */
    public function validate(ServerRequestInterface $request, array $rules)
    {
        $data = (array) $request->getParsedBody() + $request->getQueryParams();

        /** @var \Respect\Validation\Validator $rule */
        foreach ($rules as $field => $rule) {
            try {
                $rule->setName($field)->assert($data[$field] ?? null);
            } catch (NestedValidationException $e) {
                $this->errors[$field] = $e->getMessages();
            }
        }

        return $this;
    }

    /**
     * Validate an array of values and fields
     *
     * @param array $values
     * @param array $rules
     *
     * @return static
     */
    public function validateArray(array $values, array $rules)
    {
        /** @var \Respect\Validation\Validator $rule */
        foreach ($rules as $field => $rule) {
            try {
                $rule->setName($field)->assert($this->getValue($values, $field));
            } catch (NestedValidationException $e) {
                $this->errors[$field] = $e->getMessages();
            }
        }

        return $this;
    }

    /**
     * Check if there is any validation error
     *
     * @return bool
     */
    public function failed()
    {
        return ! empty($this->errors);
    }

    /**
     * Return all validations errors if any
     *
     * @return array
     */
    public function getErrors()
    {
        return $this->errors;
    }

    /**
     * get the value of the array
     *
     * @param $values
     * @param $field
     *
     * @return string|null
     */
    private function getValue($values, $field)
    {
        return isset($values[$field]) ? $values[$field] : null;
    }

}