<?php
use ExpressionParser\RPNEvaluator;

require_once 'ValidationPattern.php';
require 'RPNEvaluator.php';

it('evaluates a addition correctly', function () {
    $evaluator = new RPNEvaluator();
    $result = $evaluator->EvaluateRPN(['3', '4', '+', '5', '+']);

    expect($result)->toBe(12.0);
});

it('evaluates a subtraction correctly', function () {
    $evaluator = new RPNEvaluator();
    $result = $evaluator->EvaluateRPN(['5', '2', '-']);

    expect($result)->toBe(3.0);
});

it('evaluates a multiplication correctly', function () {
    $evaluator = new RPNEvaluator();
    $result = $evaluator->EvaluateRPN(['6', '3', '*', '2', '*']);

    expect($result)->toBe(36.0);
});

it('evaluates a division correctly', function () {
    $evaluator = new RPNEvaluator();
    $result = $evaluator->EvaluateRPN(['8', '2', '/']);

    expect($result)->toBe(4.0);
});

it('throws an exception when dividing by zero', function () {
    $evaluator = new RPNEvaluator();
    $evaluator->EvaluateRPN(['8', '0', '/']);
})->throws(InvalidArgumentException::class, 'Division by zero');

it('evaluates an exponentiation correctly', function () {
    $evaluator = new RPNEvaluator();
    $result = $evaluator->EvaluateRPN(['2', '3', '^']);

    expect($result)->toBe(8.0);
});

it('evaluates a root calculation correctly', function () {
    $evaluator = new RPNEvaluator();
    $result = $evaluator->EvaluateRPN(['9', '0.5', '^']);

    expect($result)->toBe(3.0);
});

it('throws an exception when calculating the root of a negative number', function () {
    $evaluator = new RPNEvaluator();
    $evaluator->EvaluateRPN(['-9', '0.5', '^']);
})->throws(InvalidArgumentException::class, 'Root of negative number can`t be calculated');

it('evaluates an expression with variables correctly', function () {
    $evaluator = new RPNEvaluator();
    $result = $evaluator->EvaluateRPN(['x', 'y', '*'], ['x' => 5, 'y' => 3]);

    expect($result)->toBe(15.0);
});

it('throws an exception for an invalid variable', function () {
    $evaluator = new RPNEvaluator();
    $evaluator->EvaluateRPN(['a', '5', '+'], ['b = 4']);
})->throws(InvalidArgumentException::class, 'No value for variable \'a\'');

it('evaluates an expression with constants correctly', function () {
    $evaluator = new RPNEvaluator();
    $result = $evaluator->EvaluateRPN(['pi', '2', '*']);

    expect($result)->toBe(M_PI * 2);
});
