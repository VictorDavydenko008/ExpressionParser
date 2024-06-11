<?php

namespace ExpressionParser;

require 'Operator.php';
require 'ValidationPattern.php';
require 'Math.php';
require 'VariablesParser.php';
require 'ExpressionValidator.php';
require 'ParenthesesChecker.php';
require 'Tokenizer.php';
require 'RPNConverter.php';
require 'RPNEvaluator.php';

use Exception;
use InvalidArgumentException;

/**
 * Class ExpressionParser
 *
 * A class to evaluate mathematical expressions, optionally including variables, using Reverse Polish Notation (RPN)
 *
 * @package ExpressionParser
 */
class ExpressionParser{
    /**
     * @var ExpressionValidator $expressionValidator Tool for validating the expression and checking for mistakes
     */
    protected ExpressionValidator $expressionValidator;

    /**
     * @var ParenthesesChecker $parenthesesChecker Tool for checking balance of parentheses
     */
    protected ParenthesesChecker $parenthesesChecker;

    /**
     * @var Tokenizer $tokenizer Tool for tokenizing the expression
     */
    protected Tokenizer $tokenizer;

    /**
     * @var VariablesParser $variablesParser Tool for validating and checking variables
     */
    protected VariablesParser $variablesParser;

    /**
     * @var RPNConverter $rpnConverter Tool for converting the expression to postfix notation
     */
    protected RPNConverter $rpnConverter;

    /**
     * @var RPNEvaluator $rpnEvaluator Tool for evaluating the RPN expression
     */
    protected RPNEvaluator $rpnEvaluator;

    /**
     * @var string|null $expression The mathematical expression to be evaluated
     */
    private ?string $expression;

    /**
     * @var array $rpn The RPN representation of the expression
     */
    private array $rpn;

    /**
     * Initialize the necessary components and optionally set the expression
     *
     * @param string|null $expression The mathematical expression to be evaluated
     */
    public function __construct(string $expression = null){
        $this->expressionValidator = new ExpressionValidator();
        $this->parenthesesChecker = new ParenthesesChecker();
        $this->tokenizer = new Tokenizer();
        $this->rpnConverter = new RPNConverter();
        $this->rpnEvaluator = new RPNEvaluator();
        $this->variablesParser = new VariablesParser();

        $this->SetExpression($expression);
    }

    /**
     * Set the mathematical expression to be evaluated
     *
     * @param string|null $expression The mathematical expression to be evaluated
     */
    public function SetExpression(?string $expression) : void {
        // clear rpn of another expression that was set
        $this->rpn = [];
        $this->expression = $expression;
    }

    /**
     * Evaluate the expression with optional variables
     *
     * @param string ...$variables Variables to be used in the expression
     * @return float The result of the expression evaluating
     * @throws InvalidArgumentException If the expression is not set
     *      or variables are not provided correctly
     *      or there is a problem while evaluating the expression
     */
    public function Evaluate(string ...$variables) : float {
        if (!$this->expression) {
            throw new InvalidArgumentException("Expression cannot be empty");
        }

        if (empty($this->rpn)) {
            $this->convertToRPN();
        }

        $variablesMap = [];
        // if variables are provided, parse and validate them
        if (!empty($variables)) { // with variables
            try {
                $variablesMap = $this->variablesParser->GetVariablesMap($variables);
            } catch (InvalidArgumentException $e) {
                throw new InvalidArgumentException($e->getMessage());
            }
        }

        try {
            return $this->rpnEvaluator->EvaluateRPN($this->rpn, $variablesMap);
        } catch(Exception $e) {
            throw new InvalidArgumentException($e->getMessage());
        }
    }

    /**
     * Check if the parentheses in the expression are balanced
     *
     * @return bool True if the parentheses are balanced, false otherwise
     */
    public function CheckParentheses() : bool {
        return $this->parenthesesChecker->Check($this->expression);
    }

    /**
     * Clear the resources
     */
    public function __destruct() {
        unset($this->expressionValidator,
            $this->parenthesesChecker,
            $this->tokenizer,
            $this->RPNConverter,
            $this->RPNEvaluator,
            $this->variablesParser);

        $this->expression = "";
        $this->rpn = [];
    }

    /**
     * Convert the expression to Reverse Polish Notation (RPN) using class tools
     *
     * @throws InvalidArgumentException If the expression has invalid parentheses or fails notation conversion
     */
    protected function convertToRPN() : void {
        if (!$this->CheckParentheses()) {
            throw new InvalidArgumentException("Invalid parentheses");
        }

        try {
            $validatedExpression = $this->expressionValidator->Validate($this->expression);
            $tokens = $this->tokenizer->Tokenize($validatedExpression);
            $this->rpn = $this->rpnConverter->ConvertToRPN($tokens);
        } catch (Exception $e) {
            throw new InvalidArgumentException($e->getMessage());
        }
    }
}