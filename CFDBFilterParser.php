<?php
/*
    "Contact Form to Database" Copyright (C) 2011-2012 Michael Simpson  (email : michael.d.simpson@gmail.com)

    This file is part of Contact Form to Database.

    Contact Form to Database is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    Contact Form to Database is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with Contact Form to Database.
    If not, see <http://www.gnu.org/licenses/>.
*/

include_once('CFDBEvaluator.php');
include_once('CFDBValueConverter.php');
require_once('CFDBPermittedFunctions.php');

/**
 * Used to parse boolean expression strings like 'field1=value1&&field2=value2||field3=value3&&field4=value4'
 * Where logical AND and OR are represented by && and || respectively.
 * Individual expressions (like 'field1=value1') are of the form $name . $operator . $value where
 * $operator is any PHP comparison operator or '=' which is interpreted as '=='.
 * $value has a special case where if it is 'null' it is interpreted as the value null
 */
class CFDBFilterParser implements CFDBEvaluator {

    /**
     * @var array of arrays of string where the top level array is broken down on the || delimiters
     */
    var $tree;

    /**
     * @var CFDBValueConverter callback that can be used to pre-process values in the filter string
     * passed into parseFilterString($filterString).
     * For example, a function might take the value '$user_email' and replace it with an actual email address
     * just prior to checking it against input data in call evaluate($data)
     */
    var $compValuePreprocessor;

    /**
     * @var CFDBPermittedFunctions
     */
    var $permittedFilterFunctions;

    public function hasFilters() {
        return count($this->tree) > 0; // count is null-safe
    }

    public function getFilterTree() {
        return $this->tree;
    }

    /**
     * Parse a string with delimiters || and/or && into a Boolean evaluation tree.
     * For example: aaa&&bbb||ccc&&ddd would be parsed into the following tree,
     * where level 1 represents items ORed, level 2 represents items ANDed, and
     * level 3 represent individual expressions.
     * Array
     * (
     *     [0] => Array
     *         (
     *             [0] => Array
     *                 (
     *                     [0] => aaa
     *                     [1] => =
     *                     [2] => bbb
     *                 )
     *
     *         )
     *
     *     [1] => Array
     *         (
     *             [0] => Array
     *                 (
     *                     [0] => ccc
     *                     [1] => =
     *                     [2] => ddd
     *                 )
     *
     *             [1] => Array
     *                 (
     *                     [0] => eee
     *                     [1] => =
     *                     [2] => fff
     *                 )
     *
     *         )
     *
     * )
     * @param  $filterString string with delimiters && and/or ||
     * which each element being an array of strings broken on the && delimiter
     */
    public function parseFilterString($filterString) {
        $this->tree = array();
        $arrayOfORedStrings = $this->parseORs($filterString);
        foreach ($arrayOfORedStrings as $anANDString) {
            $arrayOfANDedStrings = $this->parseANDs($anANDString);
            $andSubTree = array();
            foreach ($arrayOfANDedStrings as $anExpressionString) {
                $exprArray = $this->parseExpression($anExpressionString);
                $count = count($exprArray);
                if ($count > 0) {
                    $exprArray[0] = $this->parseValidFunction($exprArray[0]);
                    if ($count > 2) {
                        $exprArray[2] = $this->parseValidFunction($exprArray[2]);
                    } else {
                        $exprArray[1] = '==='; // need === with boolean true during evaluation
                        $exprArray[2] = true;
                    }

                    // if one side of the operation is a function and the other is 'true' or 'false'
                    // then convert to Boolean true or false which signals to not try to dereference
                    // true or false during evaluateComparison()
                    if (is_array($exprArray[0])) {
                        if ($exprArray[2] === 'true') {
                            $exprArray[2] = true;
                        } else if ($exprArray[2] === 'false') {
                            $exprArray[2] = false;
                        }
                    }
                    if (is_array($exprArray[2])) {
                        if ($exprArray[0] === 'true') {
                            $exprArray[0] = true;
                        }
                        if ($exprArray[0] === 'false') {
                            $exprArray[0] = false;
                        }
                    }
                }
                $andSubTree[] = $exprArray;
            }
            $this->tree[] = $andSubTree;
        }
    }

    /**
     * To prevent a security hole, not all functions are permitted
     * @param $functionName string
     * @return bool
     */
    public function functionIsPermitted($functionName) {
        if ($this->permittedFilterFunctions) {
            return $this->permittedFilterFunctions->isFunctionPermitted($functionName);
        }
        return true;
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
        // Deal with various && encoding problems
        $filterString = html_entity_decode($filterString);

        $retVal = preg_split('/&&/', $filterString, -1, PREG_SPLIT_NO_EMPTY);
        //echo "<pre>Parsed '$filterString' into " . print_r($retVal, true) . '</pre>';
        return $retVal;
    }

    /**
     * Parse a comparison expression into its three components
     * @param  $comparisonExpression string in the form 'value1' . 'operator' . 'value2' where
     * operator is a php comparison operator or '='
     * @return array of string [ value1, operator, value2 ]
     */
    public function parseExpression($comparisonExpression) {
        // Sometimes get HTML codes for greater-than and less-than; replace them with actual symbols
        $comparisonExpression = str_replace('&gt;', '>', $comparisonExpression);
        $comparisonExpression = str_replace('&lt;', '<', $comparisonExpression);
        return preg_split('/(===)|(==)|(=)|(!==)|(!=)|(<>)|(<=)|(<)|(>=)|(>)|(~~)/',
                          $comparisonExpression, -1, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE);
    }

    public function parseValidFunction($filterString) {
        $parsed = $this->parseFunction($filterString);
        if (is_array($parsed)) {
            if (!is_callable($parsed[0]) || !$this->functionIsPermitted($parsed[0])) {
                return $filterString;
            }
        }
        return $parsed;
    }

    /**
     * @param $filterString string
     * @return string|array if a function like "funct(arg1, arg2, ...)" then returns array['funct', arg1, arg2, ...]
     * otherwise just returns the string passed in
     */
    public function parseFunction($filterString) {
        $matches = array();
        // Parse function name
        if (preg_match('/^(\w+)\((.*)\)$/', trim($filterString), $matches)) {
            $functionArray = array();
            $functionArray[] = $matches[1]; // function name
            // Parse function parameters
            $matches[2] = trim($matches[2]);
            if ($matches[2] != '') {
                $paramMatches = explode(',', $matches[2]);
                foreach ($paramMatches as $param) {
                    $param = trim($param);
                    if ($param != '') {
                        $functionArray[] = $param;
                    }
                }
            }
            return $functionArray;
        }
        return $filterString;
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
    public function evaluate(&$data) {
        if (function_exists('get_option')) {
            $tz = get_option('CF7DBPlugin_Timezone'); // see CFDBPlugin->setTimezone()
            if (!$tz) {
                $tz =  get_option('timezone_string');
            }
            if ($tz) {
                date_default_timezone_set($tz);
            }
        }
        
        $retVal = true;
        if ($this->tree) {
            $retVal = false;
            foreach ($this->tree as $andArray) { // loop each OR'ed $andArray
                $andBoolean = true;
                // evaluation the list of AND'ed comparison expressions
                foreach ($andArray as $comparison) {
                    $andBoolean = $this->evaluateComparison($comparison, $data); //&& $andBoolean
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

    public function evaluateComparison($andExpr, &$data) {
        if (is_array($andExpr) && count($andExpr) == 3) {
            // $andExpr = [$left $op $right]

            // Left operand
            $left = $andExpr[0];
            // Boolean type means it was set in parseFilterString in response
            // to a filter like "function(x)" that was turned into an expression
            // like "function(x) === true"
            if ($left !== true && $left !== false) {
                if (is_array($left)) { // function call
                    $left = $this->evaluateFunction($left, $data);
                } else {
                    $left = $this->preprocessValues($left);
                    // Dereference $left assuming it is the name of a form field
                    // and set it to the value of the field. When not found make it null
                    $left = isset($data[$left]) ? $data[$left] : null;
                }
            }

            // Operator
            $op = $andExpr[1];

            // Right operand
            $right = $andExpr[2];
            if (is_array($right)) { // function call
                $right = $this->evaluateFunction($right, $data);
            } else {
                $right = $this->preprocessValues($right);
            }

            if ($andExpr[0] === 'submit_time') {
                if (!is_numeric($right)) {
                    $right = strtotime($right);
                }
            }

            if ($left === null && $right === null) {
                // Addresses case where 'Submitted Login' = $user_login but there exist some submissions
                // with no 'Submitted Login' field. Without this clause, those rows where 'Submitted Login' == null
                // would be returned when what we really want to is affirm that there is a 'Submitted Login' value ($left)
                // But we want to preserve the correct behavior for the case where 'field'=null is the constraint.
                return false;
            }
            return $this->evaluateLeftOpRightComparison($left, $op, $right);
        }
        return false;
    }

    /**
     * @param $text string
     * @return mixed
     */
    public function preprocessValues($text) {
        if ($this->compValuePreprocessor) {
            try {
                $text = $this->compValuePreprocessor->convert($text);
            } catch (Exception $ex) {
                trigger_error($ex, E_USER_NOTICE);
            }
        }
        return $text;
    }

    /**
     * @param $functionArray array ['function name', 'param1', 'param2', ...]
     * @param $data array [name => value]
     * @return mixed
     */
    public function evaluateFunction($functionArray, &$data) {
        $functionName = array_shift($functionArray);
        for ($i=0; $i<count($functionArray); $i++) {
            $functionArray[$i] = $this->preprocessValues($functionArray[$i]);

            // See if the parameter is a field name that can be dereferenced.
            $functionArray[$i] = isset($data[$functionArray[$i]]) ?
                    $data[$functionArray[$i]] :
                    $functionArray[$i];

            // Dereference PHP Constants
            if (defined($functionArray[$i])) {
                $functionArray[$i] = constant($functionArray[$i]);
            }
        }
        if (empty($functionArray)) {
            // If function has no parameters, pass in the whole form entry associative array
            $functionArray[] = $data;
        }
        return call_user_func_array($functionName, $functionArray);
    }


    /**
     * @param  $left mixed
     * @param  $operator string representing any PHP comparison operator or '=' which is taken to mean '=='
     * @param  $right $mixed. SPECIAL CASE: if it is the string 'null' it is taken to be the value null
     * @return bool evaluation of comparison $left $operator $right
     */
    public function evaluateLeftOpRightComparison($left, $operator, $right) {
        if ($right === 'null') {
            // special case
            $right = null;
        }

        // Try to do numeric comparisons when possible
        if (is_numeric($left) && is_numeric($right)) {
            $left = (float)$left;
            $right = (float)$right;
        }

        // Could do this easier with eval() but since this text ultimately
        // comes form a shortcode's user-entered attributes, I want to avoid a security hole
        $retVal = false;
        switch ($operator) {
            case '=' :
            case '==':
                $retVal = $left == $right;
                break;

            case '===':
                $retVal = $left === $right;
                break;

            case '!=':
                $retVal = $left != $right;
                break;

            case '!==':
                $retVal = $left !== $right;
                break;

            case '<>':
                $retVal = $left <> $right;
                break;

            case '>':
                $retVal = $left > $right;
                break;

            case '>=':
                $retVal = $left >= $right;
                break;

            case '<':
                $retVal = $left < $right;
                break;

            case '<=':
                $retVal = $left <= $right;
                break;

            case '~~':
                $retVal = @preg_match($right, $left) > 0;
                break;

            default:
                trigger_error("Invalid operator: '$operator'", E_USER_NOTICE);
                $retVal = false;
                break;
        }

        return $retVal;
    }

    /**
     * @param  $converter CFDBValueConverter
     * @return void
     */
    public function setComparisonValuePreprocessor($converter) {
        $this->compValuePreprocessor = $converter;
    }

    /**
     * @param $cFDBPermittedFilterFunctions CFDBPermittedFunctions
     * @return void
     */
    public function setPermittedFilterFunctions($cFDBPermittedFilterFunctions) {
        $this->permittedFilterFunctions = $cFDBPermittedFilterFunctions;
    }

}
