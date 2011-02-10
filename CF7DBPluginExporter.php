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

require_once('ExportToCsvUtf8.php');
require_once('ExportToCsvUtf16le.php');
require_once('ExportToIqy.php');
require_once('ExportToGoogleSS.php');
require_once('ExportToGoogleLiveData.php');

class CF7DBPluginExporter {

    static function export($formName, $encoding, $guser = null, $gpwd = null) {

        switch ($encoding) {
            case 'HTML':
                $exporter = new ExportToHtml();
                $exporter->export($formName);
                break;
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
            case 'JSON':
                $exporter = new ExportToJson();
                $exporter->export($formName);
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
