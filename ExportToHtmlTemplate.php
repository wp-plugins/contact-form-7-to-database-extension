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

class ExportToHtmlTemplate extends ExportBase implements CFDBExport {

    /**
     * @param $formName string
     * @param $options array of option_name => option_value
     * @return void
     */
    public function export($formName, $options = null) {
        $this->setOptions($options);
        $this->setCommonOptions(true);

        // Security Check
        if (!$this->isAuthorized()) {
            $this->assertSecurityErrorMessage();
            return;
        }

        // Headers
        $this->echoHeaders('Content-Type: text/html; charset=UTF-8');


        if (empty($options) || !isset($options['content'])) {
            return;
        }

        if ($this->isFromShortCode) {
            ob_start();
        }

        // Get the data
        $this->setDataIterator($formName);


        $matches = array();
        preg_match_all('/\$\{([^}]+)\}/', $options['content'], $matches);

        $colNamesToSub = array();
        $varNamesToSub = array();
        if (!empty($matches) && is_array($matches[1])) {
            foreach ($matches[1] as $aSubVar) {
                // Each is expected to be a name of a column
                if (in_array($aSubVar, $this->dataIterator->displayColumns)) {
                    $colNamesToSub[] = $aSubVar;
                    $varNamesToSub[] = '${' . $aSubVar . '}';
                }
            }
        }


        while ($this->dataIterator->nextRow()) {
            if (empty($colNamesToSub)) {
                echo $options['content'];
            }
            else {
                $replacements = array();
                foreach ($colNamesToSub as $aCol) {
                    $replacements[] = htmlentities($this->dataIterator->row[$aCol], null, 'UTF-8');
                }
                echo str_replace($varNamesToSub, $replacements, $options['content']);
            }

        }

        if ($this->isFromShortCode) {
            // If called from a shortcode, need to return the text,
            // otherwise it can appear out of order on the page
            $output = ob_get_contents();
            ob_end_clean();
            return $output;
        }

    }


}
