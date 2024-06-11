<?php

namespace ExpressionParser;

enum Operator : string
{
    case ADDITION = '+';

    case SUBTRACTION = '-';

    case MULTIPLICATION = '*';

    case DIVISION = '/';

    case EXPONENTIATION = '^';

    case DOT = '.';

    case LEFT_PARENTHESIS = '(';

    case RIGHT_PARENTHESIS = ')';
}
