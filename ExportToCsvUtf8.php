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

require_once('ExportBase.php');
require_once('CFDBExport.php');

class ExportToCsvUtf8 extends ExportBase implements CFDBExport {

    var $useBom = false;

    public function setUseBom($use) {
        $this->useBom = $use;
    }

    public function export($formName, $options = null) {
        $this->setOptions($options);
        $this->setCommonOptions();

        // Security Check
        if (!$this->isAuthorized()) {
            $this->assertSecurityErrorMessage();
            return;
        }

        // Headers
        $this->echoHeaders(
            array('Content-Type: text/html; charset=UTF-8',
                 "Content-Disposition: attachment; filename=\"$formName.csv\""));

        $this->echoCsv($formName);
    }

    public function echoCsv($formName) {
        if ($this->useBom) {
            // File encoding UTF-8 Byte Order Mark (BOM) http://wiki.sdn.sap.com/wiki/display/ABAP/Excel+files+-+CSV+format
            echo chr(239) . chr(187) . chr(191);
        }

        $eol = "\n";
        $comma = ',';

        // Query DB for the data for that form
        $submitTimeKeyName = "Submit_Time_Key";
        $this->setFilteredData($formName, $submitTimeKeyName);

        // Column Headers
        foreach ($this->columns as $aCol) {
            echo $this->prepareCsvValue($aCol);
            echo $comma;
        }
        echo $eol;


        // Rows
        $showFileUrlsInExport = $this->plugin->getOption('ShowFileUrlsInExport') == 'true';
        foreach ($this->filteredData as $aRow) {
            foreach ($this->columns as $aCol) {
                $cell = isset($aRow[$aCol]) ? $aRow[$aCol] : '';
                if ($showFileUrlsInExport && $this->tableData->files[$aCol] && $cell) {
                    $cell = $this->plugin->getFileUrl($aRow[$submitTimeKeyName], $formName, $aCol);
                }
                echo $this->prepareCsvValue($cell);
                echo $comma;
            }
            echo $eol;
        }
    }


    protected function &prepareCsvValue($text) {
        // In CSV, escape double-quotes by putting two double quotes together
        $quote = '"';
        $text = str_replace($quote, $quote . $quote, $text);

        // Quote it to escape line breaks
        $text = $quote . $text . $quote;

        return $text;
    }

}