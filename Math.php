<?php

namespace ExpressionParser;

use InvalidArgumentException;

/**
 * Trait Math
 *
 * Provide a set of mathematical functions, constants, and utility methods for handling mathematical expressions
 *
 * @package ExpressionParser
 */
trait Math {
    /**
     * @var array<string> Array of supported mathematical functions
     */
    protected array $functions = ["sin", "cos", "tg", "ctg", "arcsin", "arccos", "arctg", "arcctg", "ln", "lg", "log", "sqrt", "abs"];

    /**
     * @var array<string, float> Array of mathematical constants with their values
     */
    protected array $mathConstants = ["pi" => M_PI, "e" => M_E];

    /**
     * Check if a character is a supported operator
     *
     * @param string $token The character to check
     * @return bool True if the character is an operator, false otherwise
     */
    protected function isOperator(string $token) : bool {
        return match ($token) {
            Operator::ADDITION->value,
            Operator::SUBTRACTION->value,
            Operator::MULTIPLICATION->value,
            Operator::DIVISION->value,
            Operator::EXPONENTIATION->value => true,
            default => false,
        };
    }

    /**
     * Check if a token is a supported function
     *
     * @param string $token The token to check
     * @return bool True if the token is a function, false otherwise
     */
    protected function isFunction(string $token): bool {
        return in_array($token, $this->functions) || str_starts_with($token, "log");
    }

    /**
     * Check if a token is a supported mathematical constant
     *
     * @param string $token The token to check
     * @return bool True if the token is a constant, false otherwise
     */
    protected function isConstant(string $token): bool {
        return array_key_exists($token, $this->mathConstants);
    }

    /**
     * Apply sine function to the angle value in degrees
     *
     * @param float $angle The angle in degrees
     * @return float The sine of the angle
     */
    protected function sin(float $angle): float {
        return sin(deg2rad($angle));
    }

    /**
     * Apply arcsine function to the value
     * Check for possible errors
     *
     * @param float $value The value
     * @return float The arcsine of the value
     * @throws InvalidArgumentException If the value is out of range
     */
    protected function arcsin(float $value): float {
        if (abs($value) > 1) {
            throw new InvalidArgumentException("Arcsine of $value is undefined");
        }

        return asin($value);
    }

    /**
     * Apply cosine function to the angle value in degrees
     *
     * @param float $angle The angle in degrees
     * @return float The cosine of the angle
     */
    protected function cos(float $angle): float {
        return cos(deg2rad($angle));
    }

    /**
     * Apply arccosine function to the value
     * Check for possible errors
     *
     * @param float $value The value
     * @return float The arccosine of the value
     * @throws InvalidArgumentException If the value is out of range
     */
    protected function arccos(float $value): float {
        if (abs($value)> 1) {
            throw new InvalidArgumentException("Arccosine of $value is undefined");
        }

        return acos($value);
    }

    /**
     * Apply tangent function to the angle value in degrees
     * Check for possible errors
     *
     * @param float $angle The angle in degrees
     * @return float The tangent of the angle
     */
    protected function tg(float $angle): float {
        $remainder = ($angle - 180 * floor($angle / 180.0)); // angle remainder 180
        if ($remainder != 90.0 && $remainder != -90.0) {
            return tan(deg2rad($angle));
        } else {
            throw new InvalidArgumentException("Tangent of $angle degrees is undefined");
        }
    }

    /**
     * Apply arctangent function to the value
     *
     * @param float $value The value
     * @return float The arctangent of the value
     * @throws InvalidArgumentException If the value is out of range
     */
    protected function arctg(float $value): float {
        return atan($value);
    }

    /**
     * Apply cotangent function to the angle value in degrees
     * Check for possible errors
     *
     * @param float $angle The angle in degrees
     * @return float The cotangent of the angle
     */
    protected function ctg(float $angle): float {
        if (($angle - 180 * floor($angle / 180.0)) != 0.0) {
            return 1 / tan(deg2rad($angle));
        } else {
            throw new InvalidArgumentException("Cotangent of $angle degrees is undefined");
        }
    }

    /**
     * Apply arccotangent function to the value
     *
     * @param float $value The value
     * @return float The arccotangent of the value
     * @throws InvalidArgumentException If the value is out of range
     */
    protected function arcctg(float $value) : float {
        return M_PI_2 - atan($value);
    }

    /**
     * Apply natural logarithm function to the value
     * Check for possible errors
     *
     * @param float $value The value
     * @return float The natural logarithm of the value
     * @throws InvalidArgumentException If the value is out of range
     */
    protected function ln(float $value) : float {
        if ($value <= 0) {
            throw new InvalidArgumentException("Natural logarithm of $value is undefined");
        }

        return log($value);
    }

    /**
     * Apply logarithm function with a specified base to the value
     * Check for possible errors
     *
     * @param float $value The value
     * @param float $base The base of the logarithm
     * @return float The logarithm of the value with the specified base
     * @throws InvalidArgumentException If the value or base is out of range
     */
    protected function log(float $value, float $base): float {
        if ($value <= 0) {
            throw new InvalidArgumentException("Logarithm of $value is undefined");
        }

        if ($base <= 0 || $base === 1.0) {
            throw new InvalidArgumentException(
                "The base of the logarithmic function should be greater than 0 and not equal to 1"
            );
        }

        return log($value, $base);
    }

    /**
     * Apply common logarithm (base 10) function to the value
     * Check for possible errors
     *
     * @param float $value The value
     * @return float The common logarithm of the value
     * @throws InvalidArgumentException If the value is out of range
     */
    protected function lg(float $value): float {
        if ($value <= 0) {
            throw new InvalidArgumentException("Common logarithm of $value is undefined");
        }

        return log($value, 10);
    }

    /**
     * Apply square root function to the value
     * Check for possible errors
     *
     * @param float $value The value
     * @return float The square root of the value
     * @throws InvalidArgumentException If the value is negative
     */
    protected function sqrt(float $value) : float {
        if ($value < 0) {
            throw new InvalidArgumentException("Even root of negative number is undefined");
        }

        return sqrt($value);
    }

    /**
     * Apply absolute value function to the value
     *
     * @param float $value The value
     * @return float The absolute value
     */
    protected function abs(float $value): float {
        return abs($value);
    }

    /**
     * Determine the precedence of a supported operator
     *
     * @param string $operator The operator
     * @return int The precedence of the operator
     */
    protected function precedence(string $operator): int {
        return match ($operator) {
            Operator::ADDITION->value, Operator::SUBTRACTION->value => 1,
            Operator::MULTIPLICATION->value, Operator::DIVISION->value => 2,
            Operator::EXPONENTIATION->value => 3,
            default => 4, // for the functions
        };
    }

    /**
     * Check if an operator is right-associative
     *
     * @param string $token The operator
     * @return bool True if the operator is right-associative, false otherwise
     */
    protected function isRightAssociative(string $token) : bool {
        return $token === Operator::EXPONENTIATION->value;
    }
}