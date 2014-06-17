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

abstract class CFDBParserBase {

    /**
     * @var CFDBValueConverter callback that can be used to pre-process values in the filter string
     * passed into parse($filterString).
     * For example, a function might take the value '$user_email' and replace it with an actual email address
     * just prior to checking it against input data in call evaluate($data)
     */
    var $compValuePreprocessor;

    /**
     * @var CFDBPermittedFunctions
     */
    var $permittedFilterFunctions;

    public abstract function parse($string);

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
     * @param  $string
     * @return array
     */
    public function parseORs($string) {
        return preg_split('/\|\|/', $string, -1, PREG_SPLIT_NO_EMPTY);
    }

    /**
     * @param  $string
     * @return array
     */
    public function parseANDs($string) {
        // Deal with various && encoding problems
        $string = html_entity_decode($string);

        $retVal = preg_split('/&&/', $string, -1, PREG_SPLIT_NO_EMPTY);
        //echo "<pre>Parsed '$filterString' into " . print_r($retVal, true) . '</pre>';
        return $retVal;
    }

    public function setTimezone() {
        if (function_exists('get_option')) {
            $tz = get_option('CF7DBPlugin_Timezone'); // see CFDBPlugin->setTimezone()
            if (!$tz) {
                $tz = get_option('timezone_string');
            }
            if ($tz) {
                date_default_timezone_set($tz);
            }
        }
    }

}