<?php

class  CF7DBPluginExporter {
    static function export($formName, $encoding, $guser=null, $gpwd=null) {

        switch ($encoding) {
            case 'IQY':
                $exporter = new ExportToIqy();
                $exporter->export($formName);
                break;
            case 'CSVUTF8BOM':
                $exporter = new ExportToCsvUtf8();
                $exporter->setUseBom(true);
                $exporter->export($formName);
                break;
            case 'TSVUTF16LEBOM':
                $exporter = new ExportToCsvUtf16le();
                $exporter->export($formName);
                break;
            case 'GLD':
                $exporter = new ExportToGoogleLiveData();
                $exporter->export($formName);
                break;
            case 'GSS':
                $exporter = new ExportToGoogleSS();
                $exporter->export($formName, $guser, $gpwd);
                break;
            case 'CSVUTF8':
            default:
                $exporter = new ExportToCsvUtf8();
                $exporter->setUseBom(false);
                $exporter->export($formName);
                break;
        }
    }
}
