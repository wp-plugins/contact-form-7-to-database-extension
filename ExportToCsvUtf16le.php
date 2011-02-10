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

include_once('../../../wp-config.php');
include_once('../../../wp-includes/functions.php');
require_wp_db();
require_once('CF7DBPlugin.php');

class ExportToCsvUtf16le {

    public function export($formName) {
        $plugin = new CF7DBPlugin();
        if (!$plugin->canUserDoRoleOption('CanSeeSubmitData')) {
            wp_die(__('You do not have sufficient permissions to access this page.', 'contact-form-7-to-database-extension'));
        }
        header('Expires: 0');
        header('Cache-Control: no-store, no-cache, must-revalidate');
        header('Content-Type: text/csv; charset=UTF-16LE');
        $fileName = $formName . '.csv';
        header("Content-Disposition: attachment; filename=\"$fileName\"");

        // todo: make this work
//        $fileName = $this->encodeWordRfc2231($formName) . '.csv';
//        header("Content-Disposition: attachment; filename*=UTF-8''$fileName");

        //Bytes FF FE (UTF-16LE BOM)
        echo chr(255) . chr(254);
        $eol = $this->encode(utf8_encode("\n"));
        $delimiter = $this->encode(utf8_encode("\t"));

        // Column Headers
        echo $this->prepareCsvValue(utf8_encode(__('Submitted', 'contact-form-7-to-database-extension', 'contact-form-7-to-database-extension')));
        echo $delimiter;
        $tableData = $plugin->getRowsPivot($formName);
        foreach ($tableData->columns as $aCol) {
            echo $this->prepareCsvValue($aCol);
            echo $delimiter;
        }
        echo $eol;


        // Rows
        $showFileUrlsInExport = $plugin->getOption('ShowFileUrlsInExport') == 'true';
        foreach ($tableData->pivot as $submitTime => $data) {
            $st = $plugin->formatDate($submitTime);
            if (!is_numeric($st)) {
                $st = $this->prepareCsvValue($st);
            }
            else {
                $st = $this->encode($st);
            }
            echo $st;
            echo $delimiter;
            foreach ($tableData->columns as $aCol) {
                $cell = isset($data[$aCol]) ? $data[$aCol] : '';
                if ($showFileUrlsInExport && $tableData->files[$aCol] && '' != $cell) {
                    $cell = $plugin->getFileUrl($submitTime, $formName, $aCol);
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