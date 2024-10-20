<?php

use ExpressionParser\ParenthesesChecker;

require_once 'Operator.php';
require 'ParenthesesChecker.php';

it('returns true for expressions with balanced parentheses', function () {
    $checker = new ParenthesesChecker();
    expect($checker->Check("sin(2+3)-6"))->toBeTrue();
    expect($checker->Check("2*(9-(4+3))"))->toBeTrue();
    expect($checker->Check("(2+(4+6)(1-3))*7"))->toBeTrue();
    expect($checker->Check("(4+6)(7*9-3)"))->toBeTrue();
});

it('returns false for expressions with unbalanced parentheses', function () {
    $checker = new ParenthesesChecker();
    expect($checker->Check("2*(2+7"))->toBeFalse();
    expect($checker->Check("4+6)^3"))->toBeFalse();
    expect($checker->Check("9/(5*(4+1)"))->toBeFalse();
    expect($checker->Check("cos(45)/)-9"))->toBeFalse();
    expect($checker->Check("(5*(4+6)/2))"))->toBeFalse();
});

it('returns true for expressions with balanced empty parentheses', function () {
    $checker = new ParenthesesChecker();
    expect($checker->Check("()"))->toBeTrue();
    expect($checker->Check("(())"))->toBeTrue();
    expect($checker->Check("(()())"))->toBeTrue();
    expect($checker->Check("(())()(())"))->toBeTrue();
});

it('returns false for expressions with unbalanced empty parentheses', function () {
    $checker = new ParenthesesChecker();
    expect($checker->Check("("))->toBeFalse();
    expect($checker->Check(")"))->toBeFalse();
    expect($checker->Check("(()"))->toBeFalse();
    expect($checker->Check("())"))->toBeFalse();
    expect($checker->Check("(()))"))->toBeFalse();
});

it('throws an exception for null input', function () {
    $checker = new ParenthesesChecker();
    $checker->Check(null);
})->throws(InvalidArgumentException::class, 'Expression cannot be empty');

it('throws an exception for an empty string input', function () {
    $checker = new ParenthesesChecker("");
    $checker->Check();
})->throws(InvalidArgumentException::class, 'Expression cannot be empty');