<?php
/*
    "Contact Form to Database Extension" Copyright (C) 2011 Michael Simpson  (email : michael.d.simpson@gmail.com)

    This file is part of Contact Form to Database Extension.

    Contact Form to Database Extension is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    Contact Form to Database Extension is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with Contact Form to Database Extension.
    If not, see <http://www.gnu.org/licenses/>.
*/

require_once('CF7DBPlugin.php');

class ExportBase {

    var $options;
    var $debug = false;
    var $showColumns;
    var $hideColumns;
    var $htmlTableId;
    var $htmlTableClass;
    var $style;
    var $filterParser;
    var $isFromShortCode = false;

    /**
     * This method is the first thing to call after construction to set state for other methods to work
     * @param  $options array
     * @return void
     */
    protected function setOptions($options) {
        $this->options = $options;
    }

    protected function setCommonOptions($htmlOptions = false) {
        $this->filterParser = new CF7FilterParser;
        $this->filterParser->setComparisonValuePreprocessor(new DereferenceShortcodeVars);

        if ($this->options && is_array($this->options)) {
            if (isset($this->options['debug']) && $this->options['debug'] != 'false') {
                $this->debug = true;
            }

            if (isset($this->options['showColumns'])) {
                $this->showColumns = $this->options['showColumns'];
            }
            else if (isset($this->options['show'])) {
                $this->showColumns = preg_split('/,/', $this->options['show'], -1, PREG_SPLIT_NO_EMPTY);
            }

            if (isset($this->options['hideColumns'])) {
                $this->hideColumns = $this->options['hideColumns'];
            }
            else if (isset($this->options['hide'])) {
                $this->hideColumns = preg_split('/,/', $this->options['hide'], -1, PREG_SPLIT_NO_EMPTY);
            }

            $this->isFromShortCode = isset($this->options['fromshortcode']) &&
                    $this->options['fromshortcode'] === true;

            if ($htmlOptions) {
                if (isset($this->options['class'])) {
                    $this->htmlTableClass = $this->options['class'];
                }
                else {
                    $this->htmlTableClass = 'cfdb-table';
                }

                if (isset($this->options['id'])) {
                    $this->htmlTableId = $this->options['id'];
                }

                if (isset($this->options['style'])) {
                    $this->style = $this->options['style'];
                }
            }


            if (isset($this->options['filter'])) {
                $this->filterParser->parseFilterString($this->options['filter']);
                if ($this->debug) {
                    echo '<pre>';
                    print_r($this->filterParser->getFilterTree());
                    echo '</pre>';
                }
            }
        }
    }

    protected function isAuthorized() {
        $plugin = new CF7DBPlugin();
        return $this->isFromShortCode ?
                $plugin->canUserDoRoleOption('CanSeeSubmitDataViaShortcode') :
                $plugin->canUserDoRoleOption('CanSeeSubmitData');
    }

    protected function assertSecurityErrorMessage() {
        $errMsg = __('You do not have sufficient permissions to access this data.', 'contact-form-7-to-database-extension');
        if ($this->isFromShortCode) {
            echo $errMsg;
        }
        else {
            wp_die($errMsg);
        }
    }


    /**
     * @param string|array|null $headers mixed string header-string or array of header strings.
     * E.g. Content-Type, Content-Disposition, etc.
     * @return void
     */
    protected function echoHeaders($headers = null) {
        if (!headers_sent()) {
            header('Expires: 0');
            header('Cache-Control: no-store, no-cache, must-revalidate');
            // Hoping to keep the browser from timing out if connection from Google SS Live Data
            // script is calling this page to get information
            header("Keep-Alive: timeout=60"); // Not a standard HTTP header; browsers may disregard

            if ($headers) {
                if (is_array($headers)) {
                    foreach ($headers as $aheader) {
                        header($aheader);
                    }
                }
                else {
                    header($headers);
                }
            }
            flush();
        }
    }

    protected function getColumnsToDisplay(&$dataColumns) {
        // Get the columns to display
        $columns = array();
        if ($this->hideColumns == null || !is_array($this->hideColumns)) { // no hidden cols specified
            $tmpArray = ($this->showColumns != null) ? $this->showColumns : $dataColumns;
            foreach ($tmpArray as $aCol) {
                if ($aCol != 'Submitted') {
                    $columns[] = $aCol;
                }
            }
        }
        else {
            $tmpArray = ($this->showColumns != null) ? $this->showColumns : $dataColumns;
            foreach ($tmpArray as $aCol) {
                if ($aCol != 'Submitted' && !in_array($aCol, $this->hideColumns)) {
                    $columns[] = $aCol;
                }
            }
        }
        return $columns;
    }

    protected function getShowSubmitField() {
        $showSubmitField = true;
        if ($this->hideColumns != null && is_array($this->hideColumns) && in_array('Submitted', $this->hideColumns)) {
            $showSubmitField = false;
        }
        else if ($this->showColumns != null && is_array($this->showColumns)) {
            $showSubmitField = in_array('Submitted', $this->showColumns);
        }
        return $showSubmitField;
    }

    protected function &getFilteredData($formName) {
        $plugin = new CF7DBPlugin();
        $tableData = $plugin->getRowsPivot($formName);
        $columns = $this->getColumnsToDisplay($tableData->columns);
        $showSubmitField = $this->getShowSubmitField();
        $filteredData = array();

        foreach ($tableData->pivot as $submitTime => $data) {
            // Determine if row is filtered
            if (!$this->filterParser->evaluate($data)) {
                continue;
            }
            $row = array();

            if ($showSubmitField) {
                $row['Submitted'] = $plugin->formatDate($submitTime);
            }

            foreach ($columns as $aCol) {
                $cell = isset($data[$aCol]) ? $data[$aCol] : "";
                $row[$aCol] = $cell;
            }
            $filteredData[] = $row;
        }
        return $filteredData;
    }

}
