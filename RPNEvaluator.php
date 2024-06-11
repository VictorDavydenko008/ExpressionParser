<?php

namespace ExpressionParser;

use Exception;
use InvalidArgumentException;
use SplStack;

/**
 * Class RPNEvaluator
 *
 * Evaluate a mathematical expression in Reverse Polish Notation (RPN)
 *
 * @package ExpressionParser
 */
class RPNEvaluator {
    use Math;
    use ValidationPattern;
    /**
     * @var array|null $rpn The RPN tokens to be evaluated
     */
    private ?array $rpn;

    /**
     * @var array|null $variables The variables to be used in the evaluation
     */
    private ?array $variables;

    /**
     * Initialize the RPN tokens to be evaluated and possible variables to be used in the evaluation
     *
     * @param array|null $rpn The RPN tokens to be evaluated
     * @param array|null $variables The variables to be used in the evaluation
     */
    public function __construct(array $rpn = null, array $variables = null) {
        $this->rpn = $rpn;
        $this->variables = $variables;
    }

    /**
     * Evaluate the RPN expression
     *
     * @param array|null $rpn The RPN tokens to be evaluated
     * @param array|null $variables The variables to be used in the evaluation
     * @return float The result of the evaluation
     * @throws InvalidArgumentException If the tokens are not provided
     *      or there is an error during evaluation
     */
    public function EvaluateRPN(array $rpn = null, array $variables = null): float {
        if (!$this->rpn) {
            if (!$rpn) {
                throw new InvalidArgumentException("Tokens in postfix notation must be provided");
            }
            $this->rpn = $rpn;
        } else {
            if (!$rpn) {
                $rpn = $this->rpn;
            }
        }

        $stack = new SplStack();

        foreach ($rpn as $token) {
            if (is_numeric($token)) {
                $this->handleNumber($token, $stack);
            } elseif ($this->isOperator($token)) {
                $this->handleOperator($token, $stack);
            } elseif ($this->isFunction($token)) {
                $this->handleFunction($token, $stack, $variables);
            } elseif ($this->isConstant($token)) {
                $this->handleConstant($token, $stack);
            } else  { // token is variable
                $this->handleVariable($token, $stack, $variables);
            }
        }

        return floatval($stack->pop());
    }


    /**
     * Clear the resources
     */
    public function __destruct() {
        $this->rpn = null;
    }

    /**
     * Handle a number token by pushing it to the stack
     *
     * @param string $token The number token
     * @param SplStack &$stack The stack used for evaluation
     */
    private function handleNumber(string $token, SplStack &$stack) : void {
        $stack->push(floatval($token));
    }

    /**
     * Handle an operator token by:
     * poping from the stack necessary operands from the stack
     * and applying this operation to them
     *
     * @param string $token The operator token
     * @param SplStack &$stack The stack used for evaluation
     * @throws InvalidArgumentException If there is an error during operator application
     */
    private function handleOperator(string $token, SplStack &$stack) : void {
        $operand2 = floatval($stack->pop());
        $operand1 = floatval($stack->pop());
        $result = $this->applyOperator($token, $operand1, $operand2);
        $stack->push($result);
    }

    /**
     * Handle a function token by:
     * poping from the stack necessary operand from the stack
     * and applying this function to it
     *
     * @param string $token The function token
     * @param SplStack &$stack The stack used for evaluation
     * @param array|null $variables The variables to be used in the evaluation
     * @throws InvalidArgumentException If there is an error during function application
     */
    private function handleFunction(string $token, SplStack &$stack, ?array $variables) : void {
        $value = floatval($stack->pop());
        $result = $this->applyFunction($token, $value, $variables);
        $stack->push($result);
    }


    /**
     * Handle a constant token by pushing its value tio the stack
     *
     * @param string $token The constant token
     * @param SplStack &$stack The stack used for evaluation
     */
    private function handleConstant(string $token, SplStack &$stack) : void {
        $stack->push($this->mathConstants[$token]);
    }

    /**
     * Handle a variable token by:
     * checking its structure
     * and pushing its value to the stack
     *
     * @param string $token The variable token
     * @param SplStack &$stack The stack used for evaluation
     * @param array|null $variables The variables to be used in the evaluation
     * @throws InvalidArgumentException If the variable is not valid
     *      or no value is provided for it
     */
    private function handleVariable(string $token, SplStack &$stack, ?array $variables) : void {
        if (preg_match(self::CORRECT_VAR_NAME_STRUCTURE_PATTERN, $token)) { // is variable
            if ($variables && array_key_exists($token, $variables)) {
                // replace variable with its value
                $stack->push($variables[$token]);
            }  else {
                throw new InvalidArgumentException("No value for variable '$token'");
            }
        } else {
            throw new InvalidArgumentException("Invalid variable structure");
        }
    }

    /**
     * Apply a function to an operand by:
     * checking if it`s supported
     * and call the corresponding function
     *
     * @param string $function The function to apply
     * @param float $value The operand value
     * @param array|null $variables The variables to be used in the evaluation
     * @return float The result of the function application
     * @throws InvalidArgumentException If the function is not supported
     *      or there is an error during application
     */
    private function applyFunction(string $function, float $value, ?array $variables = null) : float {
        if (!$this->isFunction($function)) {
            throw new InvalidArgumentException("Function '$function' is not supported");
        }

        // handle special cases for logarithmic functions
        if (str_starts_with($function, "log")){
            return $this->applyLogFunction($function, $value, $variables);
        } else {
            return $this->$function($value);
        }
    }

    /**
     * Apply a logarithmic function to an operand by
     * checking and processing its base
     * and call the corresponding function with necessary parameters
     *
     * @param string $function The logarithmic function to apply
     * @param float $value The operand value
     * @param array|null $variables The variables to be used in the evaluation
     * @return float The result of the logarithmic function application
     * @throws InvalidArgumentException If there is an error during logarithmic function application
     */
    private function applyLogFunction(string $function, float $value, ?array $variables): float {
        if (preg_match(self::LOG_PATTERN, $function, $logParts)) { // function is logarithm
            $base = 1;

            if (!empty($logParts[2])) { // there is a coefficient of the base
                try {
                    $base *= floatval($logParts[2]);
                } catch (Exception $e) {
                    throw new InvalidArgumentException("Invalid base of logarithm");
                }
            }

            // logarithm contains a constant or a variable
            if (!empty($logParts[3])) {
                if ($variables && array_key_exists($logParts[3], $variables)) { // logarithm base contains variable
                    $base *= floatval($variables[$logParts[3]]);
                } else if (self::isConstant($logParts[3])){ // logarithm base contains constant
                    $base *= $this->mathConstants[$logParts[3]];
                }else {
                    throw new InvalidArgumentException("No value for variable '$logParts[3]'");
                }
            }

            // logarithm without base
            if (count($logParts) === 3 && empty($logParts[2])) {
                throw new InvalidArgumentException("Invalid base of logarithm");
            }

            return $this->log($value, $base);
        } else {
            throw new InvalidArgumentException("Invalid base of logarithm");
        }
    }

    /**
     * Apply an operator to two operands with checking for possible arithmetic errors
     *
     * @param string $operator The operator to apply
     * @param float $operand1 The first operand
     * @param float $operand2 The second operand
     * @return float|object|int The result of the operator application
     * @throws InvalidArgumentException If there is an error during operator application
     */
    private function applyOperator(string $operator, float $operand1, float $operand2): float|object|int {
        switch ($operator) {
            case Operator::ADDITION->value:
                return $operand1 + $operand2;
            case Operator::SUBTRACTION->value:
                return $operand1 - $operand2;
            case Operator::MULTIPLICATION->value:
                return $operand1 * $operand2;
            case Operator::DIVISION->value:
                if ($operand2 == 0) {
                    throw new InvalidArgumentException("Division by zero");
                }
                return $operand1 / $operand2;
            case Operator::EXPONENTIATION->value:
                if ($operand2 != floor($operand2)) { // check if decimal => root
                    if ($operand1 < 0) {
                        throw new InvalidArgumentException("Root of negative number can`t be calculated");
                    }
                }
                return pow($operand1, $operand2);
            default:
                throw new InvalidArgumentException("Unknown operator: " . $operator);
        }
    }
}