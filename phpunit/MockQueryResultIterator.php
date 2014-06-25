<?php

require_once('../CFDBDataIterator.php');

/**
 * Class MockQueryResultIterator mock for QueryResultIterator
 */
class MockQueryResultIterator extends CFDBDataIterator {

    var $data;

    var $columns;

    function __construct(&$data) {
        $this->data =& $data;
        if (count($data) > 0) {
            $this->columns = array_keys($data[0]);
        }
    }

    public function nextRow() {
        $this->row = array_shift($this->data);
        return $this->row != null;
    }

    public function query(&$sql, $rowFilter, $queryOptions = array()) {
        // Do nothing. Mock results already in $this->data
    }

}