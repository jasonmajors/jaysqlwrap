<?php
namespace Jaywrap;

class Jaywrap
{
    /**
    * @var PDO connection instance
    */
    private $_conn;
    
    /**
    * Make a PDO connection with the DB information from environmental variables
    *
    * @return Jaywrap instance
    */
    public function __construct($conn=null)
    {
        if ($conn) {
            $this->_conn = $conn;
        } else {
            // Set database information
            $username = getenv('DB_USER');
            $password = getenv('DB_PASS');
            $host = getenv('DB_HOST');
            $dbname = getenv('DB_NAME');
            
            try {
                $this->_conn = new \PDO("mysql:host=$host;dbname=$dbname", 
                                        $username, 
                                        $password, 
                                        array(
                                            \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION
                                        )
                                    );
            }  catch(PDOException $e) {
                echo 'Connection failed: ' . $e->getMessage();
            }
        }
    }

    /**
    * Insert an entry to a table in the database. Note that array keys must
    * match the table column names
    *
    * @param string $table Name of the table
    * @param array $data Associative array $key = column name, $val = data to be inserted
    * @return bool
    */
    public function insert($table, array $data)
    {
        $prepStatement = $this->getInsertStatement($table, $data);
        $statement = $this->_conn->prepare($prepStatement);
       
        return $statement->execute($data);
    }

    /**
    * Creates a SQL INSERT statement 
    *
    * @param string $table Name of the table
    * @param array $data Associative array $key = column name, $val = data to be inserted
    * @return string The SQL insert statement
    */
    private function getInsertStatement($table, array $data)
    {
        $values = array();
        foreach(array_keys($data) as $key) {
            $values[] = ':' . $key;
        }

        $prepStatement = "INSERT INTO $table (" .
                        join(", ", array_keys($data)) . ") VALUES (" .
                        join(", ", $values) . ")";

        return $prepStatement;
    }

    /**
    * Select data from a table
    *
    * @param string $table Name of the table
    * @param array $conditions Associative array where $key is the column name and $val is the value of interest
    * @return Array of the results with each row as an array of k => v pairs
    */
    public function select($table, array $conditions=Null)
    {
        if (isset($conditions)) {
            list($prepStatement, $executeValues) = $this->getSelectStatement($table, $conditions);
            $statement = $this->_conn->prepare($prepStatement);
            $statement->execute($executeValues);

            return $statement->fetchAll();
        } else {
            $statement = $this->_conn->prepare("SELECT * FROM $table");
            $statement->execute();
            
            return $statement->fetchAll();
        }
    }

    /**
    * Create a SELECT statement
    *
    * @param string $table Name of the table to select from
    * @param array $conditions Associative array where $key is the column name and $val is the value of interest
    * If $val is an array, the SQL statement will use IN and include results where $key is in the $val array
    * @return Array $prepStatement The SQL statement to execute and $executeValues is an array to pass into 
    * the execute() function
    */
    private function getSelectStatement($table, array $conditions)
    {
        $prepStatement = "SELECT * FROM $table WHERE ";
        $executeValues = array();
        
        foreach($conditions as $key => $value) {
            // If $value is an array, generate IN statement
            if (is_array($value)) {
                // Merge the $value array containing the values for the IN statement into
                // the executeValues array
                $executeValues = array_merge($executeValues, $value);
                // Create a string with ? for a placeholder for each value in the IN statement
                $placeholders = rtrim(str_repeat('?, ', count($value)), ' ,'); 
                $prepStatement .= $key . " IN ($placeholders) AND ";
            // Value is not an array, generate basic WHERE column = value statement 
            } else {
                $prepStatement .= $key . ' = ' . '?' . ' AND ';
                // Add the value to be executed into the placeholder
                $executeValues[] = $value;
            }    
        }
        // Remove the trailing 'AND';
        $prepStatement = rtrim($prepStatement, 'AND ');

        return array($prepStatement, $executeValues);
    }

    /**
    * Update an entry in the database
    *
    * @param string $table Name of the table
    * @param array $updates An array of $key => $val pairs that corresponds to column => value changes to be made
    * @param array $conditions An array of $key => $value pairs where $key is the column name
    * and $value is the value to search for where the rows should be updated
    * @return bool
    */
    public function update($table, array $updates, array $conditions)
    {
        list($prepStatement, $executeValues) = $this->getUpdateStatement($table, $updates, $conditions);
        $statement = $this->_conn->prepare($prepStatement);
        
        return $statement->execute($executeValues);
    }

    /**
    * Create an UPDATE statement 
    *
    * @param string $table Name of the table
    * @param array $updates An array of $key => $val pairs that corresponds to column => value changes to be made
    * @param array $conditions An array of $key => $value pairs where $key is the column name
    * and $value is the value to search for where the rows should be updated
    * @return Array $prepStatement is the SQL statement to execute and $executeValues is an array to pass into 
    * the execute() function
    */
    private function getUpdateStatement($table, array $updates, array $conditions)
    {
        $executeValues = array();
        $prepStatement = "UPDATE $table SET ";

        foreach($updates as $key => $value) {
            $prepStatement .= $key . ' = ' . '?, ';
            $executeValues[] = $value;
        }
        // Get rid of the trailing comma
        $prepStatement = rtrim($prepStatement, ', ');
        $prepStatement .= " WHERE ";

        foreach($conditions as $key => $value) {
            $prepStatement .= $key . ' = '. '?' . ' AND ';
            $executeValues[] = $value;
        }
        // Get rid of the trailing 'AND'
        $prepStatement = rtrim($prepStatement, 'AND ');
    
        return array($prepStatement, $executeValues);
    }

    /**
    * Delete entries from the database given a set of conditions
    *
    * @param string Name of the table
    * @param array $conditions An array of key => value pairs that correspond to column => value of
    * entries to delete
    * @return bool
    */
    public function delete($table, array $conditions)
    {
        list($prepStatement, $executeValues) = $this->getDeleteStatement($table, $conditions);
        $statement = $this->_conn->prepare($prepStatement);
        
        return $statement->execute($executeValues);
    }

    /**
    * Generate a DELETE statement given a table and conditions
    *
    * @param string $table Name of the table
    * @param array $conditions An array of key => value pairs that correspond to column => value of
    * entries to delete
    * @return Array $prepStatement is the SQL statement to execute and $executeValues is an array to pass into 
    * the execute() function
    */
    private function getDeleteStatement($table, array $conditions)
    {
        $executeValues = array();
        $prepStatement = "DELETE FROM $table WHERE ";

        foreach($conditions as $key => $value) {
            $prepStatement .= $key . ' = ' . '?' . ' AND ';
            $executeValues[] = $value;
        }

        $prepStatement = rtrim($prepStatement, 'AND ');

        return array($prepStatement, $executeValues);
    }

    /**
    * Executes a PDO statement with an array of the values to replace the ?s with
    *
    * @param string $table Name of the table
    * @param string PDO statement with ? for value placeholders
    * @param Array The values in the same order as the ?s to replace
    * @return PDO Statement
    */
    public function query($table, $pdoStatement, array $values)
    {
        $statement = $this->_conn->prepare($pdoStatement);
        $statement->execute($values);

        return $statement;
    }
}
}
