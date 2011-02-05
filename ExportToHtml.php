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

require_once('CF7DBPlugin.php');
require_once('CF7FilterParser.php');
require_once('DereferenceShortcodeVars.php');

class ExportToHtml {

    /**
     * Echo a table of submitted form data
     * @param string $formName
     * @param array $options an optional map of options with keys (each one optional):
     *  'canDelete' = true|false,
     *  'showColumns' = array of string column names to explicitly show
     *  'hideColumns' = array of string column names to explicitly hide (which trumps showColumns)
     *  'class' = string html table class, i.e. <table class="$class"> so you can override the default table styles
     *  'id' = string html table id, i.e. <table id="$id"> (a hook for you to define CSS based on that #id)
     *  'filter' = string of format "column-name=value" used to filter rows of the table
     *           SPECIAL CASE: if a value is 'null', then it is interpreted to be the value null, not the string 'null'
     *  'filter' operators in the expression are the same as PHP Comparison Operators with the exception that you can
     *           use '=' to mean '=='
     *           Examples:
     *              'field1=value1'
     *              'field1==value1'
     *              'field1===value1'
     *              'field1!=value1'
     *              'field1!==value1'
     *              'field1<>value1'
     *              'field1<value1'
     *              'field1<=value1'
     *              'field1>value1'
     *              'field1>=value1'
     *  'filter' can have boolean expressions such as:
     *              'field1=value1&&field2!=value2'  (use && for logical AND)
     *              'field1=value1||field2!=value2'  (use || for logical OR)
     *
     *      * [cfdb-table form="your-form" filter="field1=value1"]      (show only rows where field1=value1)
     * [cfdb-table form="your-form" filter="field1!=value1"]      (show only rows where field1!=value1)
     * [cfdb-table form="your-form" filter="field1=value1&&field2!=value2"] (Logical AND the filters using '&&')
     * [cfdb-table form="your-form" filter="field1=value1||field2!=value2"] (Logical OR the filters using '||')
     * [cfdb-table form="your-form" filter="field1=value1&&field2!=value2||field3=value3&&field4=value4"] (Mixed &&, ||)

     * @return void
     */
    public function export(&$formName, $options = null) {

        $debug = false;
        $canDelete = false;
        $showColumns = null;
        $hideColumns = null;
        $htmlTableId = null;
        $htmlTableClass = 'cf7-db-table';
        $style = null;
        $useDT = false;
        $filterParser = new CF7FilterParser;
        $filterParser->setComparisonValuePreprocessor(new DereferenceShortcodeVars);

        if ($options && is_array($options)) {
            if (isset($options['debug']) && $options['debug'] != 'false') {
                $debug = true;
            }
            if (isset($options['useDT'])) {
                $useDT = $options['useDT'];
                $htmlTableClass = '';
            }
            if (isset($options['canDelete'])) {
                $canDelete = $options['canDelete'];
            }
            if (isset($options['showColumns'])) {
                $showColumns = $options['showColumns'];
            }
            if (isset($options['hideColumns'])) {
                $hideColumns = $options['hideColumns'];
            }
            if (isset($options['class'])) {
                $htmlTableClass = $options['class'];
            }
            if (isset($options['id'])) {
                $htmlTableId = $options['id'];
            }
            if (isset($options['style'])) {
                $style = $options['style'];
            }
            if (isset($options['filter'])) {
                $filterParser->parseFilterString($options['filter']);
                if ($debug) {
                    echo '<pre>';
                    print_r($filterParser->getFilterTree());
                    echo '</pre>';
                }
            }
        }
        if ($useDT && !$htmlTableId) {
            $htmlTableId = 'cftble_' . rand();
        }

        // Security Check
        $plugin = new CF7DBPlugin();
        $securityCheck = isset($options['fromshortcode']) ?
                $plugin->canUserDoRoleOption('CanSeeSubmitDataViaShortcode') :
                $plugin->canUserDoRoleOption('CanSeeSubmitData');
        if (!$securityCheck) {
            wp_die(__('You do not have sufficient permissions to access this page.', 'contact-form-7-to-database-extension'));
        }
        if (!headers_sent()) {
            header('Expires: 0');
            header('Cache-Control: no-store, no-cache, must-revalidate');
            header('Content-Type: text/html; charset=UTF-8');

            // Hoping to keep the browser from timing out
            header("Keep-Alive: timeout=60"); // Not a standard HTTP header; browsers may disregard
            flush();
        }

        if (isset($options['fromshortcode'])) {
            ob_start();
        }

        // Query DB for the data for that form
        $tableData = $plugin->getRowsPivot($formName);

        // Get the columns to display
        if ($hideColumns == null || !is_array($hideColumns)) { // no hidden cols specified
            $columns = ($showColumns != null) ? $showColumns : $tableData->columns;
        }
        else {
            $tmpArray = ($showColumns != null) ? $showColumns : $tableData->columns;
            $columns = array();
            foreach ($tmpArray as $aCol) {
                if (!in_array($aCol, $hideColumns)) {
                    $columns[] = $aCol;
                }
            }
        }

        $showSubmitField = true;
        {
            if ($hideColumns != null && is_array($hideColumns)) {
                if (in_array('Submitted', $hideColumns)) {
                    $showSubmitField = false;
                }
            }
            if ($showColumns != null && is_array($showColumns)) {
                $showSubmitField = in_array('Submitted', $showColumns);
            }
        }

        if ($useDT) {
            $dtJsOptions = $options['dt_options'];
            if (!$dtJsOptions) {
                $dtJsOptions = '"bJQueryUI": true';
            }
            ?>
            <script type="text/javascript" language="Javascript">
                jQuery(document).ready(function() {
                    jQuery('#<?php echo $htmlTableId ?>').dataTable({
                        <?php echo $dtJsOptions ?> })
                });
            </script>
            <?php
        }

        if ($htmlTableClass == 'cf7-db-table') {
            ?>
            <style type="text/css">
                table.cf7-db-table {
                    margin-top: 1em;
                    border-spacing: 0;
                    border: 0 solid gray;
                    font-size: x-small;
                }

                table.cf7-db-table th {
                    padding: 5px;
                    border: 1px solid gray;
                }

                table.cf7-db-table th > td {
                    font-size: x-small;
                    background-color: #E8E8E8;
                }

                table.cf7-db-table td {
                    padding: 5px;
                    border: 1px solid gray;
                    font-size: x-small;
                }

                table.cf7-db-table td > div {
                    max-height: 100px;
                    overflow: auto;
                }
            </style>
            <?php

        }

        if ($style) {
            ?>
            <style type="text/css">
                <?php echo $style ?>
            </style>
            <?php
        }
        ?>

        <table <?php if ($htmlTableId) echo "id=\"$htmlTableId\" "; if ($htmlTableClass) echo "class=\"$htmlTableClass\"" ?> >
            <thead>
            <?php if ($canDelete) { ?>
            <th>
                <input type="image" src="<?php echo $plugin->getPluginDirUrl() ?>delete.gif"
                       alt="<?php _e('Delete Selected', 'contact-form-7-to-database-extension')?>"
                       onchange="this.form.submit()"/>
            </th>
            <?php

            }
            if ($showSubmitField) {
                echo '<th title="Submitted"><div>Submitted</div></th>';
            }
            foreach ($columns as $aCol) {
                printf('<th><div title="%s">%s</div></th>', $aCol, $aCol);
            }
            ?>
            </thead>
            <tbody>
            <?php foreach ($tableData->pivot as $submitTime => $data) {
                // Determine if row is filtered
                if (!$filterParser->evaluate($data)) {
                    continue;
                }
                ?>
                <tr>
                <?php if ($canDelete) { // Put in the delete checkbox ?>
                    <td align="center">
                        <input type="checkbox" name="<?php echo $submitTime ?>" value="row"/>
                    </td>
                <?php

                }
                if ($showSubmitField) {
                    printf('<td title="Submitted"><div>%s</div></td>', $plugin->formatDate($submitTime));
                }
                $showLineBreaks = $plugin->getOption('ShowLineBreaksInDataTable');
                $showLineBreaks = 'false' != $showLineBreaks;
                foreach ($columns as $aCol) {
                    $cell = isset($data[$aCol]) ? $data[$aCol] : "";
                    $cell = htmlentities($cell, null, 'UTF-8'); // no HTML injection
                    if ($showLineBreaks) {
                        $cell = str_replace("\r\n", "<br/>", $cell); // preserve DOS line breaks
                        $cell = str_replace("\n", "<br/>", $cell); // preserve UNIX line breaks
                    }
                    if (isset($tableData->files[$aCol]) && "" != $cell) {
                        $fileUrl = $plugin->getFileUrl($submitTime, $formName, $aCol);
                        $cell = "<a href=\"$fileUrl\">$cell</a>";
                    }
                    printf('<td title="%s"><div>%s</div></td>', $aCol, $cell);
                }
                ?></tr><?php

            } ?>
            </tbody>
        </table>
        <?php

        if (isset($options['fromshortcode'])) {
            // If called from a shortcode, need to return the text,
            // otherwise it can appear out of order on the page
            $output = ob_get_contents();
            ob_end_clean();
            return $output;
        }
    }
}

