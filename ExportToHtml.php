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

class ExportToHtml extends ExportBase implements CFDBExport {

    /**
     * Echo a table of submitted form data
     * @param string $formName
     * @param array $options
     * @return void
     */
    public function export($formName, $options = null) {
        $this->setOptions($options);
        $this->setCommonOptions(true);

        $canDelete = false;
        $useDT = false;

        if ($options && is_array($options)) {
            if (isset($options['useDT'])) {
                $useDT = $options['useDT'];
                $this->htmlTableClass = '';
            }

            if (isset($options['canDelete'])) {
                $canDelete = $options['canDelete'];
            }
        }

        // Security Check
        if (!$this->isAuthorized()) {
            $this->assertSecurityErrorMessage();
            return;
        }

        // Headers
        $this->echoHeaders('Content-Type: text/html; charset=UTF-8');

        if ($this->isFromShortCode) {
            ob_start();
        }

        // Query DB for the data for that form
        $submitTimeKeyName = "Submit_Time_Key";
        $this->setFilteredData($formName, $submitTimeKeyName);

        if ($useDT) {
            $dtJsOptions = $options['dt_options'];
            if (!$dtJsOptions) {
                $dtJsOptions = '"bJQueryUI": true';
                $i18nUrl = $this->plugin->getDataTableTranslationUrl();
                if ($i18nUrl) {
                    $dtJsOptions = $dtJsOptions . ", \"oLanguage\": { \"sUrl\":  \"$i18nUrl\" }";
                }
            }
            ?>
            <script type="text/javascript" language="Javascript">
                jQuery(document).ready(function() {
                    jQuery('#<?php echo $this->htmlTableId ?>').dataTable({
                        <?php echo $dtJsOptions ?> })
                });
            </script>
            <?php
        }

        if ($this->htmlTableClass == $this->defaultTableClass) {
            ?>
            <style type="text/css">
                table.<?php echo $this->defaultTableClass ?> {
                    margin-top: 1em;
                    border-spacing: 0;
                    border: 0 solid gray;
                    font-size: x-small;
                }

                table.<?php echo $this->defaultTableClass ?> th {
                    padding: 5px;
                    border: 1px solid gray;
                }

                table.<?php echo $this->defaultTableClass ?> th > td {
                    font-size: x-small;
                    background-color: #E8E8E8;
                }

                table.<?php echo $this->defaultTableClass ?> tbody td {
                    padding: 5px;
                    border: 1px solid gray;
                    font-size: x-small;
                }

                table.<?php echo $this->defaultTableClass ?> tbody td > div {
                    max-height: 100px;
                    overflow: auto;
                }
            </style>
            <?php

        }

        if ($this->style) {
            ?>
            <style type="text/css">
                <?php echo $this->style ?>
            </style>
            <?php
        }
        ?>

        <table <?php if ($this->htmlTableId) echo "id=\"$this->htmlTableId\" "; if ($this->htmlTableClass) echo "class=\"$this->htmlTableClass\"" ?> >
            <thead><tr>
            <?php if ($canDelete) { ?>
            <th>
                <button id="delete" name="delete" onclick="this.form.submit()"><?php _e('Delete', 'contact-form-7-to-database-extension')?></button>
            </th>
            <?php

            }
            foreach ($this->columns as $aCol) {
                printf('<th><div title="%s">%s</div></th>', $aCol, $aCol);
            }
            ?>
            </tr></thead>
            <tbody>
            <?php
            $showLineBreaks = $this->plugin->getOption('ShowLineBreaksInDataTable');
            $showLineBreaks = 'false' != $showLineBreaks;
            foreach ($this->filteredData as $aRow) {
                ?>
                <tr>
                <?php if ($canDelete) { // Put in the delete checkbox ?>
                    <td align="center">
                        <input type="checkbox" name="<?php echo $aRow[$submitTimeKeyName] ?>" value="row"/>
                    </td>
                <?php

                }
                //foreach ($row as $cell) {
                foreach ($this->columns as $aCol) {
                    $cell = htmlentities($aRow[$aCol], null, 'UTF-8'); // no HTML injection
                    if ($showLineBreaks) {
                        $cell = str_replace("\r\n", '<br/>', $cell); // preserve DOS line breaks
                        $cell = str_replace("\n", '<br/>', $cell); // preserve UNIX line breaks
                    }
                    if (isset($this->tableData->files[$aCol]) && '' != $cell) {
                        $fileUrl = $this->plugin->getFileUrl($aRow[$submitTimeKeyName], $formName, $aCol);
                        $cell = "<a href=\"$fileUrl\">$cell</a>";
                    }
                    printf('<td title="%s"><div>%s</div></td>', $aCol, $cell);
                }
                ?></tr><?php

            } ?>
            </tbody>
        </table>
        <?php

        if ($this->isFromShortCode) {
            // If called from a shortcode, need to return the text,
            // otherwise it can appear out of order on the page
            $output = ob_get_contents();
            ob_end_clean();
            return $output;
        }
    }
}

