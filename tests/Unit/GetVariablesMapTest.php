<?php
use ExpressionParser\VariablesParser;

require_once 'ValidationPattern.php';
require 'VariablesParser.php';

it('returns a valid variables map for numeric values', function () {
    $parser = new VariablesParser();
    $variables = ['x=5', 'y=3.14', 'z=-10'];
    $result = $parser->GetVariablesMap($variables);

    expect($result)->toBe([
        'x' => 5.0,
        'y' => 3.14,
        'z' => -10.0,
    ]);
});

it('returns a valid variables map for mathematical constants', function () {
    $parser = new VariablesParser();
    $variables = ['x=pi', 'y=-e'];
    $result = $parser->GetVariablesMap($variables);

    expect($result)->toBe([
        'x' => M_PI,
        'y' => -M_E,
    ]);
});

it('throws an exception when variables are not provided', function () {
    $parser = new VariablesParser();
    $parser->GetVariablesMap(null);
})->throws(InvalidArgumentException::class, 'Variables must be provided');

it('throws an exception for invalid variable format', function () {
    $parser = new VariablesParser();
    $variables = ['x=5', 'y=4=7'];
    $parser->GetVariablesMap($variables);
})->throws(InvalidArgumentException::class, 'Invalid variable y');

it('throws an exception for invalid variable name', function () {
    $parser = new VariablesParser();
    $variables = ['1invalid=5'];
    $parser->GetVariablesMap($variables);
})->throws(InvalidArgumentException::class, 'Invalid variable name: 1invalid');

it('throws an exception for invalid variable value', function () {
    $parser = new VariablesParser();
    $variables = ['x=sin(15)'];
    $parser->GetVariablesMap($variables);
})->throws(InvalidArgumentException::class, 'Invalid variable value: sin(15)');

it('correctly parses variables with spaces, commas, and minuses', function () {
    $parser = new VariablesParser(['a = 5.0', 'b=-3,14']);
    $result = $parser->GetVariablesMap();

    expect($result)->toBe([
        'a' => 5.0,
        'b' => -3.14,
    ]);
});
