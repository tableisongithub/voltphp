<?php
//Made by VoltPHP. Do not touch!

namespace App\Base\db;


class DB
{
    private static $instance;
    private $manager;
}

/**
 * An abstract class representing a database instance.
 * It only supports SQL dialects probably?
 */
abstract class DBInstance
{
    protected $schema;

    protected $credentials;

    protected $unsafe;

    /**
     * Constructor for the DBInstance class.
     *
     * @param string $schema The SQL query used to create tables.
     * @param string $prefix An optional prefix for the table names.
     * @return bool Returns true if the instance is successfully created, false otherwise.
     */
    abstract public function __construct(string $schema, array $credentials, bool $unsafe = false);

    /**
     * Creates the necessary tables in the database.
     *
     * @return bool Returns true if the tables are successfully created, false otherwise.
     */
    abstract protected function tables(): bool;

    /**
     * Connects to the database using the provided credentials.
     *
     * @param array $credentials An associative array containing the database connection credentials.
     * @return bool Returns true if the connection is successfully established, false otherwise.
     */
    abstract protected function connect(array $credentials): bool;

    /**
     * Terminates the database connection.
     *
     * @return bool Returns true if the connection is successfully terminated, false otherwise.
     */
    abstract protected function kill(): bool;

    /**
     * Destructor for the DBInstance class.
     *
     */
    abstract public function __destruct();

    /**
     * Executes a query on the database.
     *
     * @param string $query The SQL query to be executed.
     * @return mixed Returns the result of the query execution.
     */
    final public function query(string $query): mixed
    {
        if (!$this->tables()) {
            if ($this->unsafe) {
                trigger_error("Failed to create tables, using unsafe query. To disable this, create the instance with \$unsafe set to true.", E_WARNING);
            }
        }
        return $this->unsafeQuery($query);
    }

    /**
     * Executes a query on the database without any safety checks.
     *
     * @param string $query The SQL query to be executed.
     * @return mixed Returns the result of the unsafe query execution.
     */
    abstract protected function unsafeQuery(string $query): mixed;


/**
 * Escapes special characters in a string or array of strings to make them safe for SQL queries.
 *
 * @param mixed $data The data to be sanitized, either a string or an array of strings.
 * @return mixed The sanitized data with special characters escaped.
 */
final public function clean($data)
{
    if (is_array($data)) {
        foreach ($data as &$value) {
            $value = clean($value);
        }
        return $data;
    } else {
        $data = str_replace("\\", "\\\\", $data);
        $data = str_replace("'", "\\'", $data);
        $data = str_replace("\"", "\\\"", $data);
        $data = str_replace("\x00", "\\0", $data);
        $data = str_replace("\n", "\\n", $data);
        $data = str_replace("\r", "\\r", $data);
        $data = str_replace("\x1a", "\\Z", $data);
        return $data;
    }
}

    abstract protected function beginTransaction(): bool;

    abstract protected function commit(): bool;

    abstract protected function rollback(): bool;

}


class MysqliInstance extends DBInstance
{
    protected $connection;

    // Constructor for the MysqliInstance class.

    /**
     * @param string $schema The SQL query used to create tables.
     * @param array $credentials An associative array containing the database connection credentials.
     *   Format: ['host' => 'host:port', 'username' => '', 'password' => '', 'database' => '']
     * @param bool $unsafe An optional flag to disable safety checks.
     * @throws Exception Throws an exception if the MySQLi extension is not loaded.
     */
    public function __construct(string $schema, array $credentials, bool $unsafe = false)
    {
        $this->unsafe = $unsafe;
        if (!extension_loaded('mysqli')) {
            throw new Exception('The MySQLi extension is not loaded.');
        }
        if (!$unsafe) {
            if (!str_contains($schema, 'CREATE TABLE')) {
                trigger_error("The provided schema does not contain a CREATE TABLE statement. Is it a bug? To disable this, create the instance with \$unsafe set to true.", E_WARNING);
            }
            // Check if the schema contains any CREATE TABLE statements without IF NOT EXISTS
            if (preg_match("/CREATE\s+TABLE\s+(?!IF\s+NOT\s+EXISTS)/i", $schema)) {
                trigger_error("The schema may fail, correcting. To disable this, create the instance with \$unsafe set to true.", E_WARNING);

                // Replace all CREATE TABLE statements without IF NOT EXISTS with CREATE TABLE IF NOT EXISTS
                $schema = preg_replace("/CREATE\s+TABLE\s+(?!IF\s+NOT\s+EXISTS)/i", 'CREATE TABLE IF NOT EXISTS ', $schema);
            }
        }
        $this->schema = $schema;
        $this->connect($credentials);
        $this->tables();
    }

    /**
     * Destructor for the MysqliInstance class.
     * Terminates the database connection.
     */
    public function __destruct()
    {
        $this->kill();
    }

    /**
     * Executes a query on the database without any safety checks.
     *
     *
     * @param string $query The SQL query to be executed.
     * @return mixed Returns either a bool orn an array of associative arrays.
     * If the result only had one line, access it in result[0]["data"]
     */
    public function unsafeQuery(string $query): mixed
    {
        try {
            $result = $this->connection->query($query);
            $rows = [];
            if ($result === true) {
                return true;
            } elseif ($result->num_rows === 1) {
                $rows[0] = $result->fetch_assoc();
                return $rows;
            } else {
                while ($row = $result->fetch_assoc()) {
                    $rows[] = $row;
                }
                return $rows;
            }
        } catch (mysqli_sql_exception $e) {
            throw new Exception("MySQLi Error: " . $e->getMessage());
        }
    }

    /**
     * Terminates the database connection.
     *
     * @return bool Returns true if the connection is successfully terminated, false otherwise.
     */
    public function kill(): bool
    {
        if (empty($this->connection)) {
            return false;
        }
        $this->connection->close();
        return true;
    }

    /**
     * Creates the necessary tables in the database.
     *
     * @return bool Returns true if the tables are successfully created, false otherwise.
     */
    public function tables(): bool
    {
        return $this->connection->multi_query($this->schema);
    }

    /**
     * Connects to the database using the provided credentials.
     *
     * @param array $credentials An associative array containing the database connection credentials.
     *  Format: ['host' => 'host:port', 'username' => '', 'password' => '', 'database' => '']
     * @return bool Returns true if the connection is successfully established, false otherwise.
     */
    protected function connect(array $credentials): bool
    {
        if (!$this->connection = new mysqli($credentials['host'], $credentials['username'], $credentials['password'], $credentials['database'])) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * Begins a transaction.
     *
     * @return bool Returns true if the transaction is successfully started, false otherwise.
     */
    public function beginTransaction(): bool
    {
        return $this->connection->begin_transaction();
    }

    /**
     * Commits the current transaction.
     *
     * @return bool Returns true if the transaction is successfully committed, false otherwise.
     */
    public function commit(): bool
    {
        return $this->connection->commit();
    }

    /**
     * Rolls back the current transaction.
     *
     * @return bool Returns true if the transaction is successfully rolled back, false otherwise.
     */
    public function rollback(): bool
    {
        return $this->connection->rollback();
    }
}

class PDOInstance extends DBInstance
{
    private $connection;

    /**
     * Constructor for the PDOInstance class.
     *
     * @param string $schema The SQL query used to create tables.
     * @param array $credentials An associative array containing the database connection credentials.
     *   Format: ['host' => 'host:port', 'username' => '', 'password' => '', 'database' => '']
     * @param bool $unsafe An optional flag to disable safety checks.
     * @throws Exception Throws an exception if the MySQLi extension is not loaded.
     */
    public function __construct(string $schema, array $credentials, bool $unsafe = false)
    {
        $this->unsafe = $unsafe;
        if (!extension_loaded('mysqli')) {
            throw new Exception('The MySQLi extension is not loaded.');
        }
        if (!$unsafe) {
            if (!str_contains($schema, 'CREATE TABLE')) {
                trigger_error("The provided schema does not contain a CREATE TABLE statement. Is it a bug? To disable this, create the instance with \$unsafe set to true.", E_WARNING);
            }
            // Check if the schema contains any CREATE TABLE statements without IF NOT EXISTS
            if (preg_match("/CREATE\s+TABLE\s+(?!IF\s+NOT\s+EXISTS)/i", $schema)) {
                trigger_error("The schema may fail, correcting. To disable this, create the instance with \$unsafe set to true.", E_WARNING);

                // Replace all CREATE TABLE statements without IF NOT EXISTS with CREATE TABLE IF NOT EXISTS
                $schema = preg_replace("/CREATE\s+TABLE\s+(?!IF\s+NOT\s+EXISTS)/i", 'CREATE TABLE IF NOT EXISTS ', $schema);
            }
        }
        $this->schema = $schema;
        $this->connect($credentials);
        $this->tables();
    }

    /**
     * Destructor for the PDOInstance class.
     * Terminates the database connection.
     */
    public function __destruct()
    {
        $this->kill();
    }

    /**
     * Executes a query on the database without any safety checks.
     *
     * @param string $query The SQL query to be executed.
     * @return mixed Returns the result of the query execution or false if the query fails.
     */
    public function unsafeQuery(string $query): mixed
    {
        try {
            $stmt = $this->connection->query($query);
            $rows = [];
            if ($stmt === true) {
                return true;
            }
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
            if (count($result) === 1) {
                $rows[0] = $result[0];
                return $rows;
            } else {
                foreach ($result as $row) {
                    $rows[] = $row;
                }
                return $rows;
            }
        } catch (PDOException $e) {
            throw new Exception("PDO Error: " . $e->getMessage());
        }
    }

    /**
     * Terminates the database connection.
     *
     * @return bool Returns true if the connection is successfully terminated, false otherwise.
     */
    public function kill(): bool
    {
        if (empty($this->connection)) {
            return false;
        }
        $this->connection = null;
        return true;
    }

    /**
     * Creates the necessary tables in the database.
     *
     * @return bool Returns true if the tables are successfully created, false otherwise.
     */
    public function tables(): bool
    {
        return $this->connection->exec($this->schema);
    }

    /**
     * Connects to the database using the provided credentials.
     *
     * @param array $credentials An associative array containing the database connection credentials.
     *  Format: ['host' => 'host:port', 'username' => '', 'password' => '', 'database' => '']
     * @return bool Returns true if the connection is successfully established, false otherwise.
     */
    protected function connect(array $credentials): bool
    {
        if (!$this->connection = new PDO("mysql:host={$credentials['host']};dbname={$credentials['database']}", $credentials['username'], $credentials['password'])) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * Begins a transaction.
     *
     * @return bool Returns true if the transaction is successfully started, false otherwise.
     */
    public function beginTransaction(): bool
    {
        return $this->connection->beginTransaction();
    }

    /**
     * Commits the current transaction.
     *
     * @return bool Returns true if the transaction is successfully committed, false otherwise.
     */
    public function commit(): bool
    {
        return $this->connection->commit();
    }

    /**
     * Rolls back the current transaction.
     *
     * @return bool Returns true if the transaction is successfully rolled back, false otherwise.
     */
    public function rollback(): bool
    {
        return $this->connection->rollBack();
    }
}
