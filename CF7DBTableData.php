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

class CF7DBTableData {

    /**
     * All the table data pivotted from how it is represented in the database (where it is one row per entry)
     * @var array (submitTime => array(column => value))
     */
    var $pivot;

    /**
     * Column name metadata
     * @var array (columnName1, columnName2, ...)
     */
    var $columns;

    /**
     * @var array (columnName1 => filePath1, columnName2 => filePath2, ...)
     * only including columns that have a file associated
     */
    var $files;

    function __construct() {
        $this->pivot = array();
        $this->columns = array();
        $this->files = array();
    }    
}
