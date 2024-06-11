<?php

namespace ExpressionParser;

use InvalidArgumentException;

/**
 * Class Tokenizer
 *
 * Tokenize an expression string into a sequence of tokens
 *
 * @package ExpressionParser
 */
class Tokenizer {
    use Math;
    /**
     * @var string|null $expression The validated expression to be tokenized
     */
    private ?string $expression;

    /**
     * Initialize the expression to be tokenized
     *
     * @param string|null $expression The expression to be tokenized
     */
    public function __construct(string $expression = null) {
        $this->expression = $expression;
    }

    /**
     * Tokenize the input expression string
     *
     * @param string|null $expression The expression to be tokenized
     * @return array The tokens extracted from the expression
     * @throws InvalidArgumentException If the expression is not provided or contains invalid symbols
     */
    public function Tokenize(string $expression = null): array {
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

        // tokens extracted from the expression
        $tokens = [];
        $token = '';
        // type of the token: number or variable
        $isTokenNumber = true;
        // indexes of $parenthesesToSkip array
        $negativeParentheses = -1;
        // determine whether it's negative token or negative parentheses
        $negativeToken = 0;
        // each element of this array symbolizes the amount of braces to skip to put new ones
        $parenthesesToSkip = [];

        $expressionLen = strlen($expression);
        
        for ($i = 0; $i < $expressionLen; $i++) {
            $ch = $expression[$i];
            if (is_numeric($ch)) { // character is digit
                $this->handleNumber($ch, $token, $isTokenNumber);
            }else if ($ch === Operator::DOT->value) { // character is a dot
                $this->handleDot($token, $expression, $i, $expressionLen);
            }elseif (ctype_alpha($ch)) { // character is a letter
                $this->handleLetter($ch, $token, $tokens, $isTokenNumber, $negativeToken);
            } elseif ($this->isOperator($ch)) { // character is an operator
                $this->handleOperator($ch, $token, $tokens, $expression, $i, $expressionLen,
                    $negativeToken, $negativeParentheses);
            } elseif ($ch === Operator::LEFT_PARENTHESIS->value) { // character is (
                $this->handleLeftParenthesis($token, $tokens,
                    $negativeToken, $negativeParentheses, $parenthesesToSkip);
            } elseif ($ch === Operator::RIGHT_PARENTHESIS->value) { // token is )
                $this->handleRightParenthesis($token, $tokens, $expression, $i, $expressionLen,
                    $negativeToken, $negativeParentheses, $parenthesesToSkip);
            } else {
                throw new InvalidArgumentException("Unknown symbol: $ch");
            }
        }

        $this->addToken($token, $tokens);
        if ($negativeToken > 0) {
            $tokens[] = Operator::RIGHT_PARENTHESIS->value;
        }

        return $tokens;
    }

    /**
     * Clear the resources
     */
    public function __destruct() {
        $this->expression = null;
    }

    /**
     * Handle a number character in the expression by:
     * definition of token type
     * and adding it to the token being built
     *
     * @param string $ch The current character
     * @param string &$token The current token being built
     * @param bool &$isTokenNumber Whether the current token is a number
     */
    private function handleNumber(string $ch, string &$token, bool &$isTokenNumber) : void {
        if (strlen($token) === 0) { // first character of a token is a number
            $isTokenNumber = true;
        }
        $token .= $ch;
    }

    /**
     * Handle a dot character in the expression by:
     * checking its correct usage,
     * ensuring possible validation
     * and adding it to the token being built
     *
     * @param string &$token The current token being built
     * @param string $expression The expression being tokenized
     * @param int $i The current position in the expression
     * @param int $expressionLen The length of the expression
     * @throws InvalidArgumentException If the dot is used incorrectly
     */
    private function handleDot(string &$token, string $expression, int $i, int $expressionLen) : void {
        if ($i + 1 < $expressionLen && is_numeric($expression[$i + 1])) { // next character is a number
            if ($i - 1 >= 0) {
                if (is_numeric($expression[$i - 1])) { // between digits
                    $token .= Operator::DOT->value;
                } else if ($this->isOperator($expression[$i - 1])
                    || $expression[$i - 1] === Operator::LEFT_PARENTHESIS->value) { // after an operator
                    $token .= '0' . Operator::DOT->value;
                } else {
                    throw new InvalidArgumentException("Invalid usage of floating point");
                }
            } else { // start of the expression
                $token .= '0' . Operator::DOT->value;
            }
        } else {
            throw new InvalidArgumentException("Invalid usage of floating point");
        }
    }

    /**
     * Handle a letter character in the expression by:
     * definition of token type,
     * performing possible validation
     * and adding it to the token being built
     *
     * @param string $ch The letter character
     * @param string &$token The current token being built
     * @param array &$tokens The list of tokens
     * @param bool &$isTokenNumber Whether the current token is a number
     * @param int &$negativeToken The negative token definer
     */
    private function handleLetter(string $ch, string &$token, array &$tokens,
                                  bool &$isTokenNumber, int &$negativeToken) : void {
        if (strlen($token) === 0) { // first character of token is a letter
            $isTokenNumber = false;
        }

        if ($isTokenNumber) { // previous characters of the token are numbers => number * variable
            $tokens[] = $token;

            $this->closeNegativeTokenParentheses($negativeToken, $tokens);

            $tokens[] = Operator::MULTIPLICATION->value;
            $token = '';
            $isTokenNumber = false;
        }
        $token .= $ch;
    }

    /**
     * Handle an operator character in the expression by:
     * checking its correct position in expression
     * and adding it to the list of tokens
     *
     * @param string $ch The operator character
     * @param string &$token The current token being built
     * @param array &$tokens The list of tokens
     * @param string $expression The expression being tokenized
     * @param int $i The current position in the expression
     * @param int $expressionLen The length of the expression
     * @param int &$negativeToken The negative token definer
     * @param int &$negativeParentheses The count of negative parentheses
     * @throws InvalidArgumentException If the operator is used incorrectly
     */
    private function handleOperator(string $ch, string &$token, array &$tokens, string $expression, int $i, int $expressionLen,
                                    int &$negativeToken, int &$negativeParentheses) : void {
        if ($i === $expressionLen - 1) {
            throw new InvalidArgumentException("Operator at the end of expression");
        }

        if ($ch === Operator::SUBTRACTION->value) {
            $this->handleMinusOperator($ch, $token, $tokens, $expression, $i,
                $negativeToken, $negativeParentheses);
            return;
        }

        if ($i - 1 >= 0) {
            if ($expression[$i - 1] === Operator::LEFT_PARENTHESIS->value) {
                throw new InvalidArgumentException("Operator after opening parenthesis");
            }
        } else {
            throw new InvalidArgumentException("Operator at the beginning of the expression");
        }

        if ($this->isOperator($expression[$i + 1])
            && $expression[$i + 1] != Operator::SUBTRACTION->value) {
            throw new InvalidArgumentException("Sequence of operators, second of which is not minus");
        }

        $this->addToken($token, $tokens);
        $this->closeNegativeTokenParentheses($negativeToken, $tokens);
        $tokens[] = $ch;
    }

    /**
     * Handle a minus operator character in the expression by:
     * checking its correct position in expression,
     * performing possible validation of unary minus,
     * and adding it to the list of tokens
     *
     * @param string $ch The minus operator character
     * @param string &$token The current token being built
     * @param array &$tokens The list of tokens
     * @param string $expression The expression being tokenized
     * @param int $i The current position in the expression
     * @param int &$negativeToken The negative token definer
     * @param int &$negativeParentheses The count of negative parentheses
     * @throws InvalidArgumentException If the minus operator is used incorrectly
     */
    private function handleMinusOperator(string $ch, string &$token, array &$tokens, string $expression, int $i,
                                         int &$negativeToken, int &$negativeParentheses) : void {
        if ($i === 0) { // unary minus at the beginning of expression
            $tokens[] = "0";
        }

        // invalid following characters
        if ($expression[$i + 1] === Operator::RIGHT_PARENTHESIS->value
            || $this->isOperator($expression[$i + 1])
            && $expression[$i + 1] != Operator::SUBTRACTION->value){
            throw new InvalidArgumentException("Invalid subtraction operands");
        }

        if ($expression[$i - 1] === Operator::LEFT_PARENTHESIS->value) { // previous character is (
            $tokens[] = "0";
        } else if ($this->isOperator($expression[$i - 1])) { // previous character is operator
            if ($expression[$i + 1] === Operator::LEFT_PARENTHESIS->value) {
                $negativeParentheses++;
            } else {
                $negativeToken++;
            }
            $tokens[] = Operator::LEFT_PARENTHESIS->value;
            $tokens[] = "0";
        } else{ // just minus operator
            $this->addToken($token, $tokens);
            $this->closeNegativeTokenParentheses($negativeToken, $tokens);
        }

        $tokens[] = $ch;
    }

    /**
     * Handle a left parenthesis character in the expression by:
     * checking the positions of some other characters relative to it,
     * handling possible function,
     * updating the array of parentheses to skip
     * and adding it to the list of tokens
     *
     * @param string &$token The current token being built
     * @param array &$tokens The list of tokens
     * @param int &$negativeToken The negative token definer
     * @param int &$negativeParentheses The count of negative parentheses
     * @param array &$parenthesesToSkip The array of parentheses to skip
     * @throws InvalidArgumentException If the left parenthesis is used incorrectly
     */
    private function handleLeftParenthesis(string &$token, array &$tokens, int &$negativeToken,
                                           int &$negativeParentheses, array &$parenthesesToSkip) : void {
        if (strlen($token) > 0) {
            if ($this->isFunction($token)) {
                $tokens[] = $token;
                $token = '';

                // switch from negative token to negative parentheses
                if ($negativeToken > 0) {
                    $negativeToken--;
                    $negativeParentheses++;
                }
            } else {
                if (is_numeric($token)) {
                    throw new InvalidArgumentException("No operator between parenthesis and operand");
                } else {
                    throw new InvalidArgumentException("Unknown function '$token'");
                }
            }
        }

        if ($negativeParentheses >= 0) {
            for ($index = 0; $index <= $negativeParentheses; $index++) {
                if (isset($parenthesesToSkip[$index])) {
                    $parenthesesToSkip[$index]++;
                } else {
                    $parenthesesToSkip[] = 1;
                }
            }
        }

        $tokens[] = Operator::LEFT_PARENTHESIS->value;
    }

    /**
     * Handles a right parenthesis character in the expression by:
     * checking the positions of some other characters relative to it,
     * handling possible function,
     * updating the array of parentheses to skip
     * and adding it to the list of tokens
     *
     * @param string &$token The current token being built
     * @param array &$tokens The list of tokens
     * @param string $expression The expression being tokenized
     * @param int $i The current position in the expression
     * @param int $expressionLen The length of the expression
     * @param int &$negativeToken The negative token definer
     * @param int &$negativeParentheses The count of negative parentheses
     * @param array &$parenthesesToSkip The array of parentheses to skip
     * @throws InvalidArgumentException If the right parenthesis is used incorrectly.
     */
    private function handleRightParenthesis(string &$token, array &$tokens, string $expression, int $i, int $expressionLen,
                                            int &$negativeToken, int &$negativeParentheses, array &$parenthesesToSkip) : void {
        if ($expression[$i - 1] === Operator::LEFT_PARENTHESIS->value) {
            throw new InvalidArgumentException("Empty parentheses");
        } else if ($this->isOperator($expression[$i - 1])) {
            throw new InvalidArgumentException("Operator before closing parenthesis");
        }

        $this->addToken($token, $tokens);

        $this->closeNegativeTokenParentheses($negativeToken, $tokens);

        if ($negativeParentheses >= 0) {
            if ($parenthesesToSkip[$negativeParentheses] === 1) {
                $tokens[] = Operator::RIGHT_PARENTHESIS->value;
                $negativeParentheses--;
            } else {
                for ($index = 0; $index <= $negativeParentheses; $index++) {
                    if (isset($parenthesesToSkip[$index])) {
                        $parenthesesToSkip[$index]--;
                    } else {
                        $parenthesesToSkip[] = 1;
                    }
                }
            }
        }

        $tokens[] = Operator::RIGHT_PARENTHESIS->value;

        if ($i + 1 < $expressionLen) {
            if ($expression[$i + 1] === Operator::LEFT_PARENTHESIS->value) {
                $tokens[] = Operator::MULTIPLICATION->value;
            }
            if (is_numeric($expression[$i + 1])
                || ctype_alpha($expression[$i + 1])
                || $expression[$i + 1] === Operator::DOT->value) {
                throw new InvalidArgumentException("No operator between parenthesis and operand");
            }
        }
    }

    /**
     * Add the current token to the list of tokens
     *
     * @param string &$token The current token being built
     * @param array &$tokens The list of tokens
     */
    private function addToken(string &$token, array &$tokens) : void {
        if (strlen($token) > 0) {
            $tokens[] = $token;
            $token = '';
        }
    }

    /**
     * Close any open negative token parentheses if needed
     *
     * @param int &$negativeToken The negative token definer
     * @param array &$tokens The list of tokens
     */
    private function closeNegativeTokenParentheses(int &$negativeToken, array &$tokens) : void {
        if ($negativeToken > 0) {
            $tokens[] = Operator::RIGHT_PARENTHESIS->value;
            $negativeToken--;
        }
    }
}