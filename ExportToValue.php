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
require_once('CF7FilterParser.php');
require_once('DereferenceShortcodeVars.php');
require_once('ExportBase.php');
require_once('CFDBExport.php');

class ExportToValue extends ExportBase implements CFDBExport {

    public function export($formName, $options = null) {
        $debug = false;
        $showColumns = null;
        $hideColumns = null;
        $filterParser = new CF7FilterParser;
        $filterParser->setComparisonValuePreprocessor(new DereferenceShortcodeVars);

        if ($options && is_array($options)) {
            if (isset($options['debug']) && $options['debug'] != 'false') {
                $debug = true;
            }

            if (isset($options['showColumns'])) {
                $showColumns = $options['showColumns'];
            }
            if (isset($options['show'])) {
                $showColumns = preg_split('/,/', $options['show'], -1, PREG_SPLIT_NO_EMPTY);
            }

            if (isset($options['hideColumns'])) {
                $hideColumns = $options['hideColumns'];
            }
            if (isset($options['hide'])) {
                $hideColumns = preg_split('/,/', $options['hide'], -1, PREG_SPLIT_NO_EMPTY);
            }

            if (isset($options['filter'])) {
                $filterParser->parseFilterString($options['filter']);
                if ($debug) {
                    echo '<pre>';
                    print_r($filterParser->getFilterTree());
                    echo '</pre>';
                }
            }
        }

        // Security Check
        if (!$this->isAuthorized($options)) {
            $this->assertSecurityErrorMessage($options);
            return;
        }

        // Headers
        $this->echoHeaders('Content-Type: text/plain; charset=UTF-8');

        // Query DB for the data for that form
        $plugin = new CF7DBPlugin();
        $tableData = $plugin->getRowsPivot($formName);

        // Get the columns to display
        $columns = $this->getColumnsToDisplay($hideColumns, $showColumns, $tableData->columns);
        $showSubmitField = $this->getShowSubmitField($hideColumns, $showColumns);

        $outputData = array();
        foreach ($tableData->pivot as $submitTime => $data) {
            // Determine if row is filtered
            if (!$filterParser->evaluate($data)) {
                continue;
            }

            if ($showSubmitField) {
                $outputData[] = $plugin->formatDate($submitTime);
            }

            foreach ($columns as $aCol) {
                if (isset($data[$aCol])) {
                    $outputData[] = $data[$aCol];
                }
            }
        }

        //print_r($outputData); // debug

        if (isset($options['fromshortcode'])) {
            ob_start();
        }

        switch(count($outputData)) {
            case 0:
                echo '';
                break;
            case 1:
                echo $outputData[0];
                break;
            default:
                echo implode($outputData, ', ');
                break;
        }

        if (isset($options['fromshortcode'])) {
            // If called from a shortcode, need to return the text,
            // otherwise it can appear out of order on the page
            $output = ob_get_contents();
            ob_end_clean();
            return $output;
        }
    }

}
