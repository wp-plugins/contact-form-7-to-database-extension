<?php
/*
    Contact Form 7 to Database Extension
    Copyright 2010 Michael Simpson  (email : michael.d.simpson@gmail.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
*/

include_once('../../../wp-config.php');
include_once('../../../wp-includes/functions.php');
require_wp_db();
require_once('CF7DBPlugin.php');

class ExportToCsvUtf8 {

    var $useBom = false;
    var $plugin;

    function __construct() {
        $this->plugin = new CF7DBPlugin();
    }

    public function setUseBom($use) {
        $this->useBom = $use;
    }

    public function export($formName) {
        if (!$this->plugin->canUserDoRoleOption('CanSeeSubmitData')) {
            wp_die(__('You do not have sufficient permissions to access this page.', 'contact-form-7-to-database-extension'));
        }
        header('Expires: 0');
        header('Cache-Control: no-store, no-cache, must-revalidate');
        header('Content-Type: text/csv; charset=UTF-8');
        header("Content-Disposition: attachment; filename=\"$formName.csv\"");

        $this->echoCsv($formName);
    }

    public function echoCsv($formName) {
        if ($this->useBom) {
            // File encoding UTF-8 Byte Order Mark (BOM) http://wiki.sdn.sap.com/wiki/display/ABAP/Excel+files+-+CSV+format
            echo chr(239) . chr(187) . chr(191);
        }

        $eol = "\n";
        $comma = ',';

        // Column Headers
        echo $this->prepareCsvValue(__('Submitted', 'contact-form-7-to-database-extension'));
        echo $comma;
        $tableData = $this->plugin->getRowsPivot($formName);
        foreach ($tableData->columns as $aCol) {
            echo $this->prepareCsvValue($aCol);
            echo $comma;
        }
        echo $eol;


        // Rows
        $showFileUrlsInExport = $this->plugin->getOption('ShowFileUrlsInExport') == 'true';
        foreach ($tableData->pivot as $submitTime => $data) {
            $st = $this->plugin->formatDate($submitTime);
            if (!is_numeric($st)) {
                $st = $this->prepareCsvValue($st);
            }
            echo $st;
            echo $comma;
            foreach ($tableData->columns as $aCol) {
                $cell = isset($data[$aCol]) ? $data[$aCol] : '';
                if ($showFileUrlsInExport && $tableData->files[$aCol] && '' != $cell) {
                    $cell = $this->plugin->getFileUrl($submitTime, $formName, $aCol);
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