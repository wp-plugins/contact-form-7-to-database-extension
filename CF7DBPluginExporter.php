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

class CF7DBPluginExporter {

    static function export($formName, $encoding, $options) {

        switch ($encoding) {
            case 'HTML':
                require_once('ExportToHtml.php');
                $exporter = new ExportToHtml();
                $exporter->export($formName, $options);
                break;
            case 'IQY':
                require_once('ExportToIqy.php');
                $exporter = new ExportToIqy();
                $exporter->export($formName, $options);
                break;
            case 'CSVUTF8BOM':
                require_once('ExportToCsvUtf8.php');
                $exporter = new ExportToCsvUtf8();
                $exporter->setUseBom(true);
                $exporter->export($formName, $options);
                break;
            case 'TSVUTF16LEBOM':
                require_once('ExportToCsvUtf16le.php');
                $exporter = new ExportToCsvUtf16le();
                $exporter->export($formName, $options);
                break;
            case 'GLD':
                require_once('ExportToGoogleLiveData.php');
                $exporter = new ExportToGoogleLiveData();
                $exporter->export($formName, $options);
                break;
            case 'GSS':
                require_once('ExportToGoogleSS.php');
                $exporter = new ExportToGoogleSS();
                $exporter->export($formName, $options);
                break;
            case 'JSON':
                require_once('ExportToJson.php');
                $exporter = new ExportToJson();
                $exporter->export($formName, $options);
                break;
            case 'VALUE':
                require_once('ExportToValue.php');
                $exporter = new ExportToValue();
                $exporter->export($formName, $options);
                break;
            case 'CSVUTF8':
            default:
                require_once('ExportToCsvUtf8.php');
                $exporter = new ExportToCsvUtf8();
                $exporter->setUseBom(false);
                $exporter->export($formName, $options);
                break;
        }
    }
}
