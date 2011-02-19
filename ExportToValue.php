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

require_once('CF7FilterParser.php');
require_once('DereferenceShortcodeVars.php');
require_once('ExportBase.php');
require_once('CFDBExport.php');

class ExportToValue extends ExportBase implements CFDBExport {

    public function export($formName, $options = null) {
        $this->setOptions($options);
        $this->setCommonOptions($options);

        // Security Check
        if (!$this->isAuthorized()) {
            $this->assertSecurityErrorMessage();
            return;
        }

        // Headers
        $this->echoHeaders('Content-Type: text/plain; charset=UTF-8');

        // Get the data
        $this->setFilteredData($formName);

        $outputData = array();
        foreach ($this->filteredData as $row) {
            foreach ($row as $aCell) {
                if ($aCell) {
                    $outputData[] = $aCell;
                }
            }
        }
        //print_r($outputData); // debug

        if ($this->isFromShortCode) {
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

        if ($this->isFromShortCode) {
            // If called from a shortcode, need to return the text,
            // otherwise it can appear out of order on the page
            $output = ob_get_contents();
            ob_end_clean();
            return $output;
        }
    }

}
