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
