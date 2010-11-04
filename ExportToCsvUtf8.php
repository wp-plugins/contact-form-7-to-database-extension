<?php

include_once('../../../wp-config.php');
include_once('../../../wp-load.php');
include_once('../../../wp-includes/wp-db.php');
require_once('CF7DBPlugin.php');

class ExportToCsvUtf8 {

    public function export($formName) {
        $plugin = new CF7DBPlugin();
        if (!$plugin->canUserDoRoleOption('CanSeeSubmitData')) {
            wp_die(__('You do not have sufficient permissions to access this page.'));
        }
        header("Expires: 0");
        header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
        header("Content-Type: text/csv; charset=UTF-8");
        header("Content-Disposition: attachment; filename=\"$formName.csv\"");

        // File encoding UTF-8 Byte Order Mark (BOM) http://wiki.sdn.sap.com/wiki/display/ABAP/Excel+files+-+CSV+format
        echo chr(239) . chr(187) . chr(191);

        $eol = "\n";
        $comma = ",";

        // Column Headers
        echo $this->prepareCsvValue(__("Submitted", 'contact-form-7-to-database-extension'));
        echo $comma;
        $tableData = $plugin->getRowsPivot($formName);
        foreach ($tableData->columns as $aCol) {
            echo $this->prepareCsvValue($aCol);
            echo $comma;
        }
        echo $eol;


        // Rows
        foreach ($tableData->pivot as $submitTime => $data) {
            echo $plugin->formatDate($submitTime);
            echo $comma;
            foreach ($tableData->columns as $aCol) {
                $cell = isset($data[$aCol]) ? $data[$aCol] : "";
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