<?php

use ExpressionParser\ExpressionParser;

require 'ExpressionParser.php';

    $invalidExpressionTests = [
        // expression, variables array
        [ "(2 + 5)a",    ["a = 7"] ],
        [ "2.b + 2",     ["b = 8"] ],
        [ "(2 + 5)4",    [] ],
        [ "5.-4",        [] ],
        [ "3-*  54",     [] ],
        [ "4+*5",        [] ],
        [ "(/6 * 2)",    [] ],
        [ "5. ^4",       [] ],
        [ "a(6 + 7)",    [] ],
        [ "8(6 * 2)",    [] ],
        [ "24.(4 + 1)",  [] ],
        [ "(4 +6 -)",    [] ],
        [ "( 1 + 3 *)",  [] ],
        [ "() + 9",      [] ],
        [ "(4 + 5.)",    [] ],
        [ "a.5 + 1",     ["a= 8"] ],
        [ "(2 + 4).4",   [] ],
        [ "2..4 + 1",    [] ],
        [ "5a + 1 -",    ["a = 7"] ],
        [ "*3 / 9",      [] ],
        [ "2 +9 ^",      [] ],
        [ "5 + (7 + 2(", [] ],
        [ ") 5 * 3)",    [] ],
        [ "4 + 4.",      [] ]
    ];

    $mathTests = [
        // expression, variables array
        ["2 + arcsin(5)",   [] ],
        ["arccos(-2) * 6",  [] ],
        ["5 + tg(270)",     [] ],
        ["ctg(360)",        [] ],
        [" ln(a)",          ["a = -2"] ],
        ["log2(-8)",        [] ],
        ["loga(9)",         ["a = -9"] ],
        ["log1(9)",         [] ],
        ["lg(-8)",          [] ],
        ["sqrt(-4)",        [] ],
        ["(-5)^0.5",        [] ],
        ["50 / 0",          [] ]
    ];

    $easyTests = [
        // expression, variables array, expected result
        [ "2 + 2",          [], "4" ],
        [ "5.2 + (4 + 6) ", [], "15.2" ],
        [ "4 / 5 ^ 3",      [], "0.032" ],
        [ "3 ^ 2 ^ 3",      [], "6561" ]
    ];

    $hardTests = [
        // expression, variables array, expected result
        [ "(3 + 4 )(5-2)  / 2", [] , "10.5" ],
        [ "sqrt(25) + 3 ^2 - 4 / ln(2)", [], "8.22921983..." ],
        [ "sin(30) + cos(60) * tg(45)/ 16 ^ (1 / 2)", [], "0.625"],
        [ "2 ^ 3 / log10(1000) + 1 / 16 ^ (1 / 2)", [], "2.916666..." ],
        [ " 1 + 2 * 3 ^ 2 / (sqrt(4) - ln(e))", [], "19" ],
        [ "-sin(45) * -cos(45) / (arcsin(0,5) + arccos(0,5))", [], "0.3180988..." ],
        [ "(2^(1  /2)) ^ -sin(45) + tg(30) * ctg(30) / (ln(e ^ 4) - log10(1000))", [], "1.78265402..." ],
        [ "1+(-2+3*4+5^-sin(45*cos(a^b)))/7", ["a = 2", "b = 8.3"], "2.48957799"],
        [ "(3*4+b^-((-9)^0.5))", ["a = 2"], "Exception" ],
        [ "2 + log3a2(4 * 9)", ["a2 = 2"], "4" ],
        [ "2 + log3a2n(4 * 9)", ["a2 = 2"], "Exception" ],
        [ "(3*4+5^(-(-(9^0.5))))", ["a = 2"], "137" ],
        [ "(5*-(-sin(56*-(45/tg(40^(cos(45))))+6))/(-5+(-cos(7))))/3", [], "-0.05703185" ],
        [ "5^(-(-sin(80 + (-(5* tg(30) /9)))*-(-2)))", ["a = 2"], "23.73130631" ],
    ];

    $ee = new ExpressionParser();

    // for Exception tests
    /*foreach ($invalidExpressionTests as $index => $test) {
        echo PHP_EOL . $index + 1 . ") Expression: \"$test[0]\"\n";
        $ee->SetExpression($test[0]);
        echo "\tResult: ";
        try {
            echo $ee->Evaluate(...$test[1]) . PHP_EOL;
        } catch (Exception $e) {
            echo $e->getMessage() . PHP_EOL;
        }
    }*/

    // for evaluation tests
    /*foreach ($easyTests as $index => $test) {
        echo PHP_EOL . $index + 1 . ") Expression: \"$test[0]\"\n";
        echo "\tExpected: $test[2]\n";
        $ee->SetExpression($test[0]);
        echo "\tResult: ";
        try {
            echo $ee->Evaluate(...$test[1]) . PHP_EOL;
        } catch (Exception $e) {
            echo $e->getMessage() . PHP_EOL;
        }
    }*/

    $ee->SetExpression("2 + 2");
    echo $ee->Evaluate() . PHP_EOL;