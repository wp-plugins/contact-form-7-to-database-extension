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

class CF7DBTableData {

    /**
     * All the table data pivoted from how it is represented in the database (where it is one row per entry)
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
