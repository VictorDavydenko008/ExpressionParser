<?php

namespace ExpressionParser;

use InvalidArgumentException;
use SplStack;

/**
 * Class RPNConverter
 *
 * Convert an infix expression into Reverse Polish Notation (RPN)
 * using the Shunting-Yard algorithm
 *
 * Algorithm source: https://inspirnathan.com/posts/151-shunting-yard-algorithm-in-javascript
 *
 * @package ExpressionParser
 */
class RPNConverter {
    use Math;
    /**
     * @var array|null $tokens The tokens to be converted to RPN
     */
    private ?array $tokens;

    /**
     * Initialize the tokens to be converted to RPN
     *
     * @param array|null $tokens The tokens to be converted
     */
    public function __construct(array $tokens = null) {
        $this->tokens = $tokens;
    }

    /**
     * Convert the infix expression to RPN using the Shunting-Yard algorithm
     *
     * @param array|null $tokens The tokens to be converted
     * @return array The converted tokens in RPN
     * @throws InvalidArgumentException If the tokens are not provided
     *      or there are mismatched parentheses
     */
    public function ConvertToRPN(array $tokens = null): array {
        if (!$this->tokens) {
            if (!$tokens) {
                throw new InvalidArgumentException("Tokens must be provided");
            }
            $this->tokens = $tokens;
        } else {
            if (!$tokens) {
                $tokens = $this->tokens;
            }
        }

        $rpn = [];
        $operatorStack = new SplStack();

        foreach ($tokens as $token) {
            if ($this->isOperator($token)) {
                $this->handleOperator($token, $rpn, $operatorStack);
            } elseif ($this->isFunction($token) || $token === Operator::LEFT_PARENTHESIS->value) {
                $this->pushToStack($token, $operatorStack);
            } elseif ($token === Operator::RIGHT_PARENTHESIS->value) {
                $this->handleRightParentheses($rpn, $operatorStack);
            } else { // token is variable or number
                $this->addToRPN($token, $rpn);
            }
        }

        // add remaining operators to the stack
        while (!$operatorStack->isEmpty()) {
            if ($operatorStack->top() === Operator::LEFT_PARENTHESIS->value) {
                throw new InvalidArgumentException("Mismatched parentheses");
            }
            $rpn[] = $operatorStack->pop();
        }

        return $rpn;
    }

    /**
     * Clear the resources
     */
    public function __destruct() {
        $this->tokens = null;
    }

    /**
     * Add a token to the RPN output
     *
     * @param string $token The token to add
     * @param array &$rpn The RPN output
     */
    private function addToRPN(string $token, array &$rpn) : void {
        $rpn[] = $token;
    }

    /**
     * Pushes a token onto the operator stack
     *
     * @param string $token The token to push
     * @param SplStack &$operatorStack The operator stack
     */
    private function pushToStack(string $token, SplStack &$operatorStack) : void {
        $operatorStack->push($token);
    }

    /**
     * Handle an operator token according to the main algorithm
     *
     * @param string $token The operator token
     * @param array &$rpn The RPN output
     * @param SplStack &$operatorStack The operator stack
     */
    private function handleOperator(string $token, array &$rpn, SplStack &$operatorStack) : void {
        while (!$operatorStack->isEmpty()
            && ($operatorStack->top() !== Operator::LEFT_PARENTHESIS->value
                && ($this->precedence($operatorStack->top()) > $this->precedence($token) )
                || ($this->precedence($operatorStack->top()) === $this->precedence($token)
                    && !($this->isRightAssociative($token))))) {
            $rpn[] = $operatorStack->pop();
        }
        $operatorStack->push($token);
    }

    /**
     * Handle a right parenthesis token according to the main algorithm
     *
     * @param array &$rpn The RPN output
     * @param SplStack &$operatorStack The operator stack
     * @throws InvalidArgumentException If there are mismatched parentheses
     */
    private function handleRightParentheses(array &$rpn, SplStack &$operatorStack) : void {
        while (!$operatorStack->isEmpty() && $operatorStack->top() !== Operator::LEFT_PARENTHESIS->value) {
            $rpn[] = $operatorStack->pop();
        }
        if (!$operatorStack->isEmpty() && $operatorStack->top() === Operator::LEFT_PARENTHESIS->value) {
            $operatorStack->pop();
        } else {
            throw new InvalidArgumentException("Mismatched parentheses");
        }
    }
}