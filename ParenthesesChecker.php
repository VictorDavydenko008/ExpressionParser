<?php

namespace ExpressionParser;

use InvalidArgumentException;
use SplStack;

/**
 * Class ParenthesesChecker
 *
 * Check if the parentheses in a given expression are balanced
 *
 * @package ExpressionParser
 */
class ParenthesesChecker {
    /**
     * @var string|null $expression The expression to be checked for balanced parentheses
     */
    private ?string $expression;

    /**
     * Initialize the expression to be checked
     *
     * @param string|null $expression The expression to be checked
     */
    public function __construct(string $expression = null) {
        $this->expression = $expression;
    }

    /**
     * Check if the parentheses in the given expression are balanced using stack
     *
     * @param string|null $expression The expression to be checked
     * @return bool True if the parentheses are balanced, false otherwise
     * @throws InvalidArgumentException If the expression is not provided
     */
    public function Check(string $expression = null) : bool {
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

        if (!$this->isCountsMatch($expression)) {
            return false;
        }

        $stack = new SplStack();

        for ($i = 0; $i < strlen($expression); $i++) {
            if (($expression)[$i] === Operator::LEFT_PARENTHESIS->value) {
                $stack->push(($expression)[$i]);
            } else if (($expression)[$i] === Operator::RIGHT_PARENTHESIS->value) {
                if ($stack->isEmpty() || $stack->top() != Operator::LEFT_PARENTHESIS->value) {
                    return false;
                }
                $stack->pop();
            }
        }

        return $stack->isEmpty();
    }

    /**
     * Static method to check if the parentheses in the given expression are balanced
     *
     * @param string $expression The expression to be checked
     * @return bool True if the parentheses are balanced, false otherwise
     */
    public static function CheckParentheses(string $expression) : bool {
        return (new self($expression))->Check();
    }

    /**
     * Clear the resources
     */
    public function __destruct() {
        $this->expression = null;
    }

    /**
     * Check if the number of left and right parentheses in the expression are equal
     *
     * @param string $expression The expression to be checked
     * @return bool True if the counts of left and right parentheses are equal, false otherwise
     */
    protected function isCountsMatch(string $expression) : bool {
        // count the number of left and right parentheses
        $leftCount = substr_count($expression, Operator::LEFT_PARENTHESIS->value);
        $rightCount = substr_count($expression, Operator::RIGHT_PARENTHESIS->value);

        // if the counts are not equal, the parentheses are not balanced
        return $leftCount === $rightCount;
    }
}