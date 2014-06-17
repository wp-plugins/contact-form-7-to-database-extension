<?php
/*
    "Contact Form to Database" Copyright (C) 2011-2014 Michael Simpson  (email : michael.d.simpson@gmail.com)

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
require_once('CFDBParserBase.php');

class CFDBTransformParser extends CFDBParserBase {

    var $tree = array();

    public function getExpressionTree() {
        return $this->tree;
    }

    public function parse($string) {
        $arrayOfANDedStrings = $this->parseANDs($string); // e.g. "xx=yy()&&zz()" -> ["xx=yy(a,b,c)", "zz"]
        foreach ($arrayOfANDedStrings as $expressionString) {
            $rawExpression = $this->parseExpression(trim($expressionString)); // e.g. ["xx" "=" "yy(a,b,c)"] or ["zz"]
            if (empty($rawExpression)) {
                continue;
            }
            $expression = array();
            $function = null;
            if (count($rawExpression) >= 3) { // e.g. ["xx" "=" "yy(a,b,c)"]
                $expression[] = trim($rawExpression[0]); // field name
                $expression[] = trim($rawExpression[1]); // =
                $function = trim($rawExpression[2]); // function call
            } else {
                $function = trim($rawExpression[0]); // function call
            }
            $function = $this->parseFunctionOrClass($function); // ["zz(a,b,c)"] -> ["zz", "a", "b", "c"]
            if (is_array($function)) {
                $expression = array_merge($expression, $function);
            } else {
                $expression[] = $function;
            }
            $this->tree[] = $expression;
        }
    }


    /**
     * Parse a comparison expression into its three components
     * @param  $comparisonExpression string in the form 'value1' . 'operator' . 'value2' where
     * operator is a php comparison operator or '='
     * @return array of string [ value1, operator, value2 ]
     */
    public function parseExpression($comparisonExpression) {
        return preg_split('/(=)/', $comparisonExpression, -1,
                PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE);
    }

    public function parseFunctionOrClass($function) {
        // TODO: parseFunction needs to allow for Classes
        return $this->parseFunction($function);
    }


    public function evaluate(&$data) {
        $this->setTimezone();
        if ($this->tree) {
            foreach ($this->tree as $xformArray) {
                $this->transform($data, $xformArray);
            }
        }
    }

    public function transform(&$data, $xformArray) {
        // if we have just a function, pass the whole data set
        // TODO

        // if we have a "field=function(...)" then iterate over the data set and call for each function
        // TODO
    }


} 