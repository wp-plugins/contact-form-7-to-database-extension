<?php
/*
    "Contact Form to Database" Copyright (C) 2011-2014 Michael Simpson  (email : michael.d.simpson@gmail.com)

    This file is part of Contact Form to Database.

    Contact Form to Database is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    Contact Form to Database is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with Contact Form to Database.
    If not, see <http://www.gnu.org/licenses/>.
*/

//require_once('CFDBTransform.php');
require_once('CFDBDataIteratorDecorator.php');

class CFDBTransformByClassIterator extends CFDBDataIteratorDecorator {

    /**
     * @var CFDBTransform
     */
    var $transformObject;

    /**
     * @var array[array[name=>value], ...] transformed data set
     */
    var $transformedData;

    /**
     * @var int
     */
    var $count;

    /**
     * @var int
     */
    var $idx;

    /**
     * @param $transformObject CFDBTransform interface but allow for duck-typing
     */
    public function setTransformObject($transformObject) {
        $this->transformObject = $transformObject;
    }

    /**
     * Fetch next row into variable
     * @return bool if next row exists
     */
    public function nextRow() {
        if (!$this->transformedData) {
            // On first iteration, loop the entire $source data set and transform it.
            while ($this->source->nextRow()) {
                $this->transformObject->addEntry($this->source->row);
            }
            $this->transformedData = $this->transformObject->getTransformedData();
            $this->count = count($this->transformedData);
            if ($this->count > 0) {
                $this->idx = 0;
                $this->row =& $this->transformedData[$this->idx];
                return true;
            } else {
                return false;
            }
        } else {
            if (++$this->idx < $this->count) {
                $this->row =& $this->transformedData[$this->idx];
                return true;
            } else {
                return false;
            }
        }
    }

}