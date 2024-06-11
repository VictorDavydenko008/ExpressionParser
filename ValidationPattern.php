<?php

namespace ExpressionParser;

/**
 * Trait ValidationPattern
 *
 * Provide regular expression patterns for parser support
 *
 * @package ExpressionParser
 */
trait ValidationPattern {
    /**
     * Regular expression pattern to validate variable structure - letters followed by optional digits
     */
    const CORRECT_VAR_NAME_STRUCTURE_PATTERN = "/^[a-zA-Z]+[0-9]*$/";

    /**
     * Regular expression pattern to process logarithmic expressions
     */
    const LOG_PATTERN = "/^(log)([0-9\.]*)([a-zA-Z]+[0-9]*)?$/";

    /**
     * Array of patterns and corresponding error messages for input validation
     *
     * @var array<string, string>
     */
    const ERROR_TYPE_PATTERNS = ["/[^a-zA-Z0-9\+\-\*\/\:\^\(\)\.]/" => "extra symbol: ",
    "/[-|\+|\*|\/|\:|\^]{3,}/" => "consecutive operators: "];
}