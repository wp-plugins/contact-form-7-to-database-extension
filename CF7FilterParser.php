<?php
/*
    Contact Form 7 to Database Extension
    Copyright 2011 Michael Simpson  (email : michael.d.simpson@gmail.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
*/

/**
 * Used to parse boolean expression strings like 'field1=value1&&field2=value2||field3=value3&&field4=value4'
 * Where logical AND and OR are represented by && and || respectively.
 * Individual expressions (like 'field1=value1') are of the form $name . $operator . $value where
 * $operator is any PHP comparison operator or '=' which is interpreted as '=='.
 * $value has a special case where if it is 'null' it is interpreted as the value null
 */
class CF7FilterParser {

    /**
     * @var array of arrays of string where the top level array is broken down on the || delimiters
     */
    var $tree;

//    public function __construct() {
//        $this->tree = null;
//    }


    public function hasFilters() {
        return count($this->tree) > 0; // count is null-safe
    }


    public function getFilterTree() {
        return $this->tree;
    }

    /**
     * Parse a string with delimiters || and/or &&. For example: aaa&&bbb||ccc&&ddd into a tree.
     * this represents a logical AND OR expression
     * @param  $filterString string with delimiters && and/or ||
     * which each element being an array of strings broken on the && delimiter
     */
    public function parseFilterString($filterString) {
        $this->tree = array();
        $ors = $this->parseORs($filterString);
        foreach ($ors as $anOr) {
            $this->tree[] = $this->parseANDs($anOr);
        }
    }

    /**
     * @param  $filterString
     * @return array
     */
    public function parseORs($filterString) {
        return preg_split('/\|\|/', $filterString, -1, PREG_SPLIT_NO_EMPTY);
    }

    /**
     * @param  $filterString
     * @return array
     */
    public function parseANDs($filterString) {
        return preg_split('/&&/', $filterString, -1, PREG_SPLIT_NO_EMPTY);
    }

    /**
     * Parse a comparison expression into its three components
     * @param  $comparisonExpression string in the form 'value1' . 'operator' . 'value2' where
     * operator is a php comparison operator or '='
     * @return array of string [ value1, operator, value2 ]
     */
    public function parseExpression($comparisonExpression) {
        return preg_split('/(===)|(==)|(=)|(!==)|(!=)|(<>)|(<=)|(<)|(>=)|(>)|(~~)/',
                          $comparisonExpression, -1, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE);
    }


    /**
     * Evaluate expression against input data. Assumes parseFilterString was called to set up the expression to
     * evaluate. Expression should have key . operator . value tuples and input $data should have the same keys
     * with values to check against them.
     * For example, an expression in this object is 'name=john' and the input data has [ 'name' => 'john' ]. In
     * this case true is returned. if $data has [ 'name' => 'fred' ] then false is returned.
     * @param  $data array [ key => value]
     * @return boolean result of evaluating $data against expression tree
     */
    public function evaluate($data) {
        $retVal = true;
        if ($this->tree) {
            $retVal = false;
            foreach ($this->tree as $andArray) { // loop each OR'ed $andArray
                $andBoolean = true;
                // evaluation the list of AND'ed expressions
                foreach ($andArray as $andString) {
                    $andBoolean = $this->evaluateComparison($andString, $data); //&& $andBoolean
                    if (!$andBoolean) {
                        break; // short-circuit AND expression evaluation
                    }
                }
                $retVal = $retVal || $andBoolean;
                if ($retVal) {
                    break; // short-circuit OR expression evaluation
                }
            }
        }
        return $retVal;
    }

    public function evaluateComparison($andString, &$data) {
        $andExpr = $this->parseExpression($andString);
        if (is_array($andExpr) && count($andExpr) == 3) {
            return $this->evaluateLeftOpRightComparison($data[$andExpr[0]], $andExpr[1], $andExpr[2]);
        }
        trigger_error("Invalid expression: '$andString'", E_USER_NOTICE);
        return false;
    }


    /**
     * @param  $left mixed
     * @param  $operator string representing any PHP comparison operator or '=' which is taken to mean '=='
     * @param  $right $mixed. SPECIAL CASE: if it is the string 'null' it is taken to be the value null
     * @return bool evaluation of comparison $left $operator $right
     */
    public function evaluateLeftOpRightComparison($left, $operator, $right) {
        // Could do this easier with eval() but I want since this text ultimately
        // comes form a shortcode's user-entered attributes, I want to avoid a security hole
        if ($right == 'null') {
            // special case
            $right = null;
        }
        switch ($operator) {
            case '=' :
            case '==':
                return $left == $right;

            case '===':
                return $left === $right;

            case '!=':
                return $left != $right;

            case '!==':
                return $left !== $right;

            case '<>':
                return $left <> $right;

            case '>':
                return $left > $right;

            case '>=':
                return $left >= $right;

            case '<':
                return $left < $right;

            case '<=':
                return $left <= $right;

            case '~~':
                return preg_match($right, $left) > 0;

            default:
                trigger_error("Invalid operator: '$operator'", E_USER_NOTICE);
                return false;
        }
    }
}
