<?php

/**
 * 
 * @author Blackeye aka Blackstareye
 * @package TransactionManager
 * 
 */


use wpdb;

/**
 * handles transactions 
 */
class TransactionManager
{
    // private $logger = null;
    private $error_state = false;
    private $error_count = 0;
    function __construct()
    {
        // if you want to use a logger, just instantiate it here and uncommend the loggerlines
        //$this->logger = your_logger_handler;
    }
    public function resetError()
    {
        $this->error_state = false;
    }
    public function setError()
    {
        $this->error_state = true;
    }
    private function error()
    {

        // $this->logger->warning("Warning ErrorState not solved. Please refer to the log. Transaction aborted.");
        $this->error_count++;
    }
    public  function beginTransaction()
    {
        if ($this->error_state) {
            $this->error();
            return false;
        }

        global $wpdb;
        try {
            //code...
            $wpdb->query('START TRANSACTION');
        } catch (\Throwable $e) {
            // $this->logger->warning("Database Transaction could not be established!", ["error" => $e]);
            $this->setError();
            $this->rollback();
            throw $e;
        }
    }
    public  function rollback($second_order = false)
    {
        if ($second_order) {
            // $this->logger->info("Rollback second order. Check log for errors.");
            return;
        }
        global $wpdb;
        try {
            //code...
            $wpdb->query("ROLLBACK");
            // $this->logger->info("Rollback performed.");
        } catch (\Throwable $e) {
            // $this->logger->warning("Database Rollback could not be established!", ["error" => $e]);
            $this->setError();
            $this->rollback(true);
            throw $e;
        }
    }
    public  function commit()
    {
        global $wpdb;
        try {
            //code...
            $wpdb->query('COMMIT');
        } catch (\Throwable $e) {
            $this->logger->warning("Database Commit could not be established!", ["error" => $e]);
            $this->setError();
            $this->rollback();
            throw $e;
        }
    }
    /**
     * data needs to be sanitized if use prepare is not used. Cannot use LIKE in prepare mode
     *
     * @param  $type
     * @param  $query_string
     * @param array $data
     * @param boolean $use_prepare
     * @return void
     */
    public  function performTransaction_use_prepare($query_string, array $data)
    {
        if ($this->error_state) {
            $this->error();
            return false;
        }
        global $wpdb;
        $sql = $wpdb->prepare($query_string, $data);
        if ($wpdb->query($sql) === false) {
            // $this->logger->warning("Error with Query: ", ["query" => $sql]);
            $this->setError();
            $this->rollback();
            return false;
        }
        return true;

    }
    /**
     * data needs to be sanitized 
     *
     * @param  $type
     * @param  $query_string
     * @param array $data
     * @param boolean $use_prepare
     * @return void
     */
    public  function performTransaction_use_query($query_string)
    {
        if ($this->error_state) {
            $this->error();
            return false;
        }
        global $wpdb;
        $result = $wpdb->query($query_string);
        if ($result === false) {
            // $this->logger->warning("Error with Query: ", ["query" => $query_string]);
            $this->setError();
            $this->rollback();
            return false;
        }
        return true;
    }
    /**
     *  performs a transaction based insert. 
     *
     * @param [type] $tablename
     * @param array $data
     * @param array $formats like %s for the values that will be inserted
     * @return void
     */
    public  function performTransaction_insert($tablename, array $data, array $formats = null)
    {
        if ($this->error_state) {
            $this->error();
            return false;
        }
        global $wpdb;
        $tablename_prefixed = $wpdb->prefix . $tablename;

        $result = false;
        if ($formats) {
            $result = $wpdb->insert($tablename_prefixed, $data, $formats);
        } else {
            $result = $wpdb->insert($tablename_prefixed, $data);
        }
        if (!$result) {
            // $this->logger->warning("Error with Query: ", ["table" => $tablename_prefixed, "data" => $data, "formats" => $formats]);
            $this->rollback();
            return false;
        }
        return true;
    }
    /**
     *  performs a transaction based update. 
     *
     * @param [type] $tablename
     * @param array $data
     * @param array $where
     * @param array $formats like %s for the values that will be updated
     * @param array $where_formats like %s for the values that will be updated
     * @return void
     */
    public  function performTransaction_update($tablename, array $data, array $where, array $formats = null, array $where_formats = null)
    {
        if ($this->error_state) {
            $this->error();
            return false;
        }
        global $wpdb;
        $tablename_prefixed = $wpdb->prefix . $tablename;
        $result = false;
        if ($where_formats) {
            if ($formats) {
                $result = $wpdb->update($tablename_prefixed, $data, $where, $formats);
            } else {
                $result = $wpdb->update($tablename_prefixed, $data, $where, null, $where_formats);
            }
        } else {
            if ($formats) {
                $result = $wpdb->update($tablename_prefixed, $data, $where, $formats);
            } else {
                $result = $wpdb->update($tablename_prefixed, $data, $where);
            }
        }

        if ($result === false) {
            // $this->logger->warning("Error with Query: ", ["table" => $tablename_prefixed, "data" => $data, "formats" => $formats, "where" => $where, "where_formats" => $where_formats]);
            $this->rollback();
            return false;
        }
        return true;
    }
    /**
     * 
     * performs a transaction based delete. 
     * @param [type] $tablename
     * @param array $data
     * @param array $where
     * @param array $where_formats like %s for the values that will be deleted
     * @return void
     */
    public  function performTransaction_delete($tablename, array $where, array $where_formats = null)
    {
        if ($this->error_state) {
            $this->error();
            return false;
        }
        global $wpdb;
        $tablename_prefixed = $wpdb->prefix . $tablename;
        $result = false;
        if ($where_formats) {
            $result = $wpdb->delete($tablename_prefixed, $where,  $where_formats);
        } else {
            $result = $wpdb->delete($tablename_prefixed, $where);
        }

        if ($result === false) {
            // $this->logger->warning("Error with Query: ", ["table" => $tablename_prefixed,  "where" => $where, "where_formats" => $where_formats]);
            $this->rollback();
            return false;
        }
        return true;
    }

    /**
     * this tests if transactions are possible
     * this needs to be done with a xDebug. It would be possible to do that with sql queries but this could be an IMPROVEMENT
     *
     * @return void
     */
    public function testTransactionModule($test_table_name) {
        $this->beginTransaction();
        $this->performTransaction_use_query("INSERT INTO $test_table_name (test_data) VALUES (1)");
        // $this->logger->info("HOLD Position. now check if the data is there. It should not be there.");
        $this->commit();
        // $this->logger->info("HOLD Position. now check if the data is there. It should be there.");

        // testing rollback
        $this->beginTransaction();
        $this->performTransaction_use_query("INSERT INTO $test_table_name (test_data) VALUES (0)");
        // $this->logger->info("HOLD Position. now check if the data is there. It should not be there.");
        $this->rollback();
        // $this->logger->info("HOLD Position. now check if the data is there. It should be there.");
    }
}
