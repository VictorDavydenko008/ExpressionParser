<?php

namespace ExpressionParser;

use InvalidArgumentException;

/**
 * Class ExpressionValidator
 *
 * Validate mathematical expressions ensuring they follow the required structure
 *
 * @package ExpressionParser
 */
class ExpressionValidator {
    use ValidationPattern;
    /**
     * @var string|null $expression The mathematical expression to be validated
     */
    private ?string $expression;

    /**
     * Initialize the expression to be validated
     *
     * @param string|null $expression The mathematical expression to be validated
     */
    public function __construct(string $expression = null) {
        $this->expression = $expression;
    }

    /**
     * Validate the given expression by:
     * replacing some characters
     * and checking for mistakes
     *
     * @param string|null $expression The mathematical expression to be validated
     * @return string The validated expression
     * @throws InvalidArgumentException If the expression is not provided or contains mistakes
     */
    public function Validate(string $expression = null): string {
        if (!$this->expression) {
            if (!$expression) {
                throw new InvalidArgumentException("Expression cannot be empty");
            }
            $this->expression = $expression;
        } else {
            if (!$expression) {
                $expression = $this->expression;
            }
        }

        // replace symbols which could cause an error
        $validatedExpression = str_replace([' ', ',', '−'], ['', '.', '-'], $expression);

        // check for mistakes in expression:
        //  extra symbols,
        //  consecutive operators (>= 3)
        foreach (self::ERROR_TYPE_PATTERNS as $pattern => $errorMsg) {
            if (preg_match($pattern, $validatedExpression, $matches)) {
                throw new InvalidArgumentException( $errorMsg . "'{$matches[0]}'\n");
            }
        }

        return $validatedExpression;
    }

    /**
     * Clear the resources
     */
    public function __destruct() {
        $this->expression = null;
    }
}