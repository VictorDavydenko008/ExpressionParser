<?php
use ExpressionParser\RPNConverter;

require_once 'Math.php';
require 'RPNConverter.php';


it('converts a simple infix expression to RPN correctly', function () {
    $converter = new RPNConverter();
    $tokens = ['3', '+', '4'];
    $result = $converter->ConvertToRPN($tokens);

    expect($result)->toBe(['3', '4', '+']);
});

it('converts an expression with multiple operators to RPN correctly', function () {
    $converter = new RPNConverter();
    $tokens = ['3', '+', '4', '*', '2', '/', '(', '1', '-', '5', ')'];
    $result = $converter->ConvertToRPN($tokens);

    expect($result)->toBe(['3', '4', '2', '*', '1', '5', '-', '/', '+']);
});

it('converts an expression with multiple parentheses to RPN correctly', function () {
    $converter = new RPNConverter();
    $tokens = ['(', 'cos', '(', '(',  '3', '+', '4', ')', '*', '9', ')', '*', '2', ')', '^', '(', '1', '/', '3', ')' ];
    $result = $converter->ConvertToRPN($tokens);

    expect($result)->toBe(['3', '4', '+', '9', '*', 'cos', '2', '*', '1', '3', '/', '^']);
});

it('handles function tokens correctly', function () {
    $converter = new RPNConverter();
    $tokens = ['sin', '(', '3', '+', '4', ')', '*', '2'];
    $result = $converter->ConvertToRPN($tokens);

    expect($result)->toBe(['3', '4', '+', 'sin', '2', '*']);
});

it('throws an exception for mismatched parentheses', function () {
    $converter = new RPNConverter();
    $tokens = ['(', '3', '+', '4'];
    $converter->ConvertToRPN($tokens);
})->throws(InvalidArgumentException::class, 'Mismatched parentheses');

it('throws an exception when no tokens are provided', function () {
    $converter = new RPNConverter();
    $converter->ConvertToRPN(null);
})->throws(InvalidArgumentException::class, 'Tokens must be provided');

it('converts an expression with different operator precedence to RPN correctly', function () {
    $converter = new RPNConverter();
    $tokens = ['3', '+', '4', '*', '2', '-', '1'];
    $result = $converter->ConvertToRPN($tokens);

    expect($result)->toBe(['3', '4', '2', '*', '+', '1', '-']);
});

it('handles right-associative operators correctly', function () {
    $converter = new RPNConverter();
    $tokens = ['2', '^', '3', '^', '4'];
    $result = $converter->ConvertToRPN($tokens);

    expect($result)->toBe(['2', '3', '4', '^', '^']);
});
