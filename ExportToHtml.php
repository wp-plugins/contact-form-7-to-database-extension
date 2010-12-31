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


class ExportToHtml {

    /**
     * Echo a table of submitted form data
     * @param string $formName
     * @param bool $canDelete
     * @param array $showColumns
     * @param array $hideColumns
     * @return void
     */
    public function export(&$formName, $canDelete = false, &$showColumns = null, &$hideColumns = null) {
//        print_r($showColumns); // debug
//        print_r($hideColumns); // debug
        $plugin = new CF7DBPlugin();
        if (!$plugin->canUserDoRoleOption('CanSeeSubmitData')) {
            wp_die(__('You do not have sufficient permissions to access this page.', 'contact-form-7-to-database-extension'));
        }
        if (!headers_sent()) {
            header('Expires: 0');
            header('Cache-Control: no-store, no-cache, must-revalidate');
            header('Content-Type: text/html; charset=UTF-8');
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
                $showSubmitField =  in_array('Submitted', $showColumns);
            }
        }
        ?>
        <style type="text/css">
            table.cf7-db-table {
                margin-top:1em; border-spacing:0; border: 0 solid gray; font-size:x-small;
            }
            table.cf7-db-table th {
                padding:5px; border: 1px solid gray; font-size:x-small;
                background-color:#E8E8E8;
            }
            table.cf7-db-table td {
                padding:5px; border: 1px solid gray; font-size:x-small;
            }
            table.cf7-db-table div.cf7-db-cell {
                max-height:100px; overflow:auto;
            }
        </style>

        <table class="cf7-db-table">
            <thead>
            <?php if ($canDelete) { ?>
            <th>
                <input type="image" src="<?php echo $plugin->getPluginDirUrl() ?>delete.gif"
                       alt="<?php _e('Delete Selected', 'contact-form-7-to-database-extension')?>"
                       onchange="this.form.submit()"/>
            </th>
            <?php }
            if ($showSubmitField) {
                echo "<th>Submitted</th>";
            }
            foreach ($columns as $aCol) {
                echo "<th>$aCol</th>";
            }
            ?>
            </thead>
            <tbody>
            <?php foreach ($tableData->pivot as $submitTime => $data) {
                ?>
                <tr>
                <?php if ($canDelete) { ?>
                    <td align="center">
                        <input type="checkbox" name="<?php echo $submitTime ?>" value="row"/>
                    </td>
                <?php
                }
                if ($showSubmitField) {
                    ?>
                        <td>
                            <div class="cf7-db-cell"><?php echo $plugin->formatDate($submitTime) ?></div>
                        </td>
                    <?php
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
                    if ($tableData->files[$aCol] && "" != $cell) {
                        $fileUrl = $plugin->getFileUrl($submitTime, $formName, $aCol);
                        $cell = "<a href=\"$fileUrl\">$cell</a>";
                    }
                    echo "<td><div class=\"cf7-db-cell\">$cell</div></td>";
                }
                ?></tr><?php
            } ?>
            </tbody>
        </table>
        <?php
    }
}