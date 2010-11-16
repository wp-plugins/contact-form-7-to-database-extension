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

    public function export($formName, $canDelete = false) {
        $plugin = new CF7DBPlugin();
        if (!$plugin->canUserDoRoleOption('CanSeeSubmitData')) {
            wp_die(__('You do not have sufficient permissions to access this page.', 'contact-form-7-to-database-extension'));
        }
        if (!headers_sent()) {
            header('Expires: 0');
            header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
            header('Content-Type: text/html; charset=UTF-8');
        }

        // Query DB for the data for that form
        $tableData = $plugin->getRowsPivot($formName);

        $style = 'padding:5px; border-width:1px; border-style:solid; border-color:gray; font-size:x-small;';
        $thStyle = $style . ' background-color:#E8E8E8;';

        ?>

        <table cellspacing="0"
               style="margin-top:1em; border-width:0; border-style:solid; border-color:gray; font-size:x-small;">
            <thead>
            <?php if ($canDelete) { ?>
            <th>
                <input type="image" src="<?php echo $plugin->getPluginDirUrl() ?>delete.gif"
                       alt="<?php _e('Delete Selected', 'contact-form-7-to-database-extension')?>"
                       onchange="this.form.submit()"/>
            </th>
            <?php } ?>
            <th style="<?php echo $thStyle ?>">Submitted</th>
            <?php foreach ($tableData->columns as $aCol) {
                echo "<th style=\"$thStyle\">$aCol</th>";
            } ?>
            </thead>
            <tbody>
            <?php foreach ($tableData->pivot as $submitTime => $data) {
                ?>
                <tr>
                <?php if ($canDelete) { ?>
                    <td align="center">
                        <input type="checkbox" name="<?php echo $submitTime ?>" value="row"/>
                    </td>
                <?php } ?>
                    <td style="<?php echo $style ?>">
                        <div style="max-height:100px; overflow:auto;"><?php echo $plugin->formatDate($submitTime) ?></div>
                    </td>
                <?php
                $showLineBreaks = $plugin->getOption('ShowLineBreaksInDataTable');
                $showLineBreaks = 'false' != $showLineBreaks;
                foreach ($tableData->columns as $aCol) {
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
                    echo "<td style=\"$style\"><div style=\"max-height:100px; overflow:auto;\">$cell</div></td>";
                }
                ?></tr><?php
            } ?>
            </tbody>
        </table>
        <?php
    }
}