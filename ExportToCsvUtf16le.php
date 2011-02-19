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

class ExportToCsvUtf16le extends ExportBase implements CFDBExport {

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
            array('Content-Type: text/csv; charset=UTF-16LE',
                 "Content-Disposition: attachment; filename=\"$formName.csv\""));
        // todo: make this work
//        $fileName = $formName . '.csv';
//        $fileName = $this->encodeWordRfc2231($formName) . '.csv';
//        header("Content-Disposition: attachment; filename*=UTF-8''$fileName");

        //Bytes FF FE (UTF-16LE BOM)
        echo chr(255) . chr(254);
        $eol = $this->encode(utf8_encode("\n"));
        $delimiter = $this->encode(utf8_encode("\t"));

        // Query DB for the data for that form
        $submitTimeKeyName = "Submit_Time_Key";
        $this->setFilteredData($formName, $submitTimeKeyName);

        // Column Headers
        foreach ($this->columns as $aCol) {
            echo $this->prepareCsvValue($aCol);
            echo $delimiter;
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
                echo $delimiter;
            }
            echo $eol;
        }
    }


    protected function &prepareCsvValue($text) {
        // Excel does not like \n characters in UTF-16LE, so we replace with a space
        $text = str_replace("\n", ' ', $text);

        // In CSV, escape double-quotes by putting two double quotes together
        $quote = '"';
        $text = str_replace($quote, $quote . $quote, $text);

        // Quote it to escape delimiters
        $text = $quote . $text . $quote;

        // Encode UTF-16LE
        $text = $this->encode($text);

        return $text;
    }

    protected function encode($text) {
        return mb_convert_encoding($text, 'UTF-16LE', 'UTF-8');
    }

    protected function &encodeWordRfc2231($word) {
        $binArray = unpack("C*", $word);
        $hex = '';
        foreach ($binArray as $chr) {
            $hex .= '%' . sprintf("%02X", base_convert($chr, 2, 16));
        }
        return $hex;
    }

}