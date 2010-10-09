<?php

include_once('../../../wp-config.php');
include_once('../../../wp-load.php');
include_once('../../../wp-includes/wp-db.php');
require_once('CF7DBTableData.php');
require_once('CF7DBPlugin.php');

class ExportToCsvUtf16le {

    public function export($formName) {
        $plugin = new CF7DBPlugin();
        $tableData = $plugin->getRowsPivot($formName);

        header("Content-Type: text/csv; charset=UTF-16LE");
        header("Expires: 0");
        header("Cache-Control: must-revalidate, post-check=0, pre-check=0");

        $fileName = $formName . ".csv";
        header("Content-Disposition: attachment; filename=\"$fileName\"");

        // todo: make this work
//        $fileName = $this->encodeWordRfc2231($formName) . ".csv";
//        header("Content-Disposition: attachment; filename*=UTF-8''$fileName");

        //Bytes FF FE (UTF-16LE BOM)
        echo chr(255) . chr(254);
        $eol = $this->encode(utf8_encode("\n"));
        $delimiter = $this->encode(utf8_encode("\t"));

        // Column Headers
        echo $this->prepareCsvValue(utf8_encode(__("Submitted", 'contact-form-7-to-database-extension')));
        echo $delimiter;
        foreach ($tableData->columns as $aCol) {
            echo $this->prepareCsvValue($aCol);
            echo $delimiter;
        }
        echo $eol;


        // Rows
        foreach ($tableData->pivot as $submitTime => $data) {
            echo $this->encode(utf8_encode($plugin->formatDate($submitTime)));
            echo $delimiter;
            foreach ($tableData->columns as $aCol) {
                $cell = isset($data[$aCol]) ? $data[$aCol] : "";
                echo $this->prepareCsvValue($cell);
                echo $delimiter;
            }
            echo $eol;
        }
    }


    protected function &prepareCsvValue($text) {
        // Excel does not like \n characters in UTF-16LE, so we replace with a space
        $text = str_replace("\n", " ", $text);

        // In CSV, escape double-quotes by putting two double quotes together
        $quote = '"';
        $text = str_replace($quote, $quote . $quote, $text);

        // Quote it to escape delimiters
        $text = $quote . $text . $quote;

        // Encode UTF-16LE
        $text = $this->encode($text);

        return $text;
    }

    protected function &encode($text) {
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