<?php

require_once('../CFDBAbstractQueryResultsIterator.php');

/**
 * Class MockQueryResultIterator mock for QueryResultIterator
 */
class MockQueryResultIterator extends CFDBAbstractQueryResultsIterator {

    var $data;

    var $columns;

    function __construct(&$data) {
        $this->data =& $data;
        if (count($data) > 0) {
            $this->columns = array_keys($data[0]);
        }
    }

    /**
     * Execute the query
     * @param $sql string query
     * @param $queryOptions array associative
     * @return void
     */
    protected function queryDataSource(&$sql, $queryOptions) {
        // Do nothing. $data injected.
    }

    /**
     * Get the next row from query results
     * @return array associative
     */
    protected function fetchRow() {
        $row = array_shift($this->data);
        return $row;
    }

    /**
     * @return boolean
     */
    protected function hasResults() {
        return !empty($this->data);
    }

    /**
     * If you do not iterate over all the rows returned, be sure to call this function
     * on all remaining rows to free resources.
     * @return void
     */
    protected function freeResult() {
    }
}