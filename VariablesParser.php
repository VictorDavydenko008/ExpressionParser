<?php

namespace ExpressionParser;

use InvalidArgumentException;

/**
 * Class VariablesParser
 *
 * Parse and validate variables for mathematical expressions
 *
 * @package ExpressionParser
 */
class VariablesParser {
    use Math;
    use ValidationPattern;
    /**
     * @var array|null $variables The variables to be parsed
     */
    private ?array $variables;

    /**
     * Initialize the variables to be parsed
     *
     * @param array|null $variables The variables to be parsed
     */
    public function __construct(array $variables = null) {
        $this->variables = $variables;
    }

    /**
     * Retrieve the variables map by validating and checking the input variables
     *
     * @param array|null $variables The variables to be parsed and validated
     * @return array The parsed and validated variables map
     * @throws InvalidArgumentException If variables are not provided
     *      or if there is an error during validation
     */
    public function GetVariablesMap(array $variables = null) : array {
        if (!$this->variables) {
            if (!$variables) {
                throw new InvalidArgumentException("Variables must be provided");
            }
            $this->variables = $variables;
        } else {
            if (!$variables) {
                $variables = $this->variables;
            }
        }

        $variablesMap = [];

        foreach ($variables as $variable) {
            if (!is_string($variable)) {
                throw new InvalidArgumentException("Variable must be provided as string");
            }

            $variable = $this->validateVariable($variable);
            $variableParts = explode('=', $variable);

            if (count($variableParts) != 2) {
                throw new InvalidArgumentException("Invalid variable " . $variableParts[0]);
            }

            $this->checkVariableParts($variableParts[0], $variableParts[1]);

            if (is_numeric($variableParts[1])) {
                $variablesMap[$variableParts[0]] = floatval($variableParts[1]);
                continue;
            }

            $isNegative = (int)str_starts_with($variableParts[1], '-');
            $constantName = substr($variableParts[1], $isNegative);
            $variablesMap[$variableParts[0]] = floatval($this->mathConstants[$constantName]) * pow(-1, $isNegative);
        }

        return $variablesMap;
    }

    /**
     * Clear the resources
     */
    public function __destruct() {
        $this->variables = null;
    }

    /**
     * Validate the format of a variable by replacing some symbols
     *
     * @param string &$variable The variable to be validated
     * @return string The validated variable
     */
    private function validateVariable(string &$variable): string {
        return str_replace([' ', ',', '-'], ['', '.', '-'], $variable);
    }

    /**
     * Check the parts of a variable for validity
     *
     * @param string $name The name of the variable
     * @param string $value The value of the variable
     * @throws InvalidArgumentException If the variable name or value is invalid
     */
    private function checkVariableParts(string $name, string $value) : void {
        // check variable name
        if (!preg_match(self::CORRECT_VAR_NAME_STRUCTURE_PATTERN, $name)) {
            throw new InvalidArgumentException("Invalid variable name: $name");
        }

        // check variable value
        if (!$this->isConstant(substr($value, (int)(str_starts_with($value, '-'))))
            && !is_numeric($value)) {
            throw new InvalidArgumentException("Invalid variable value: $value");
        }
    }

}