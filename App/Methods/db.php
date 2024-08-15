<?php
//db static classes.


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

    /**
     * Constructor for the DBInstance class.
     *
     * @param string $schema The SQL query used to create tables.
     * @param string $prefix An optional prefix for the table names.
     * @return bool Returns true if the instance is successfully created, false otherwise.
     */
    abstract protected function __construct(string $schema, array $credentials, string $prefix = "", bool $unsafe = false);

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
    abstract protected function __destruct();

    /**
     * Executes a query on the database.
     *
     * @param string $query The SQL query to be executed.
     * @return mixed Returns the result of the query execution.
     */
    public function query(string $query)
    {
        $this->tables($this->tables);
        return $this->unsafeQuery($query);
    }

    /**
     * Executes a query on the database without any safety checks.
     *
     * @param string $query The SQL query to be executed.
     * @return mixed Returns the result of the unsafe query execution.
     */
    abstract protected function unsafeQuery(string $query);
}


class MysqliInstance extends DBInstance
{
    private $connection;

    // Constructor for the MysqliInstance class.

    /**
     * @throws Exception Throws an exception if the MySQLi extension is not loaded.
     */
    protected function __construct(string $schema, array $credentials, string $prefix = "", bool $unsafe = false)
    {
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


    protected function __destruct()
    {
        $this->kill();
    }

    public function unsafeQuery(string $query)
    {
        $this->connection->query($query);
    }


    public function kill(): bool
    {
        if (empty($this->connection)) {
            return false;
        }
        $this->connection->close();
    }

    public function tables(): bool
    {
        return $this->connection->multi_query($this->schema);
    }

    protected function connect(array $credentials): bool
    {
        if (!$this->connection = new mysqli($credentials['host'], $credentials['username'], $credentials['password'], $credentials['database'])) {
            return false;
        } else {
            return true;
        }
    }
}

class PDOInstance extends DBInstance
{
    private $connection;

    protected function __construct(string $schema, array $credentials, string $prefix = "", bool $unsafe = false)
    {
    }


    protected function __destruct()
    {
        // TODO: Implement __destruct() method.
    }

    public function unsafeQuery(string $query)
    {
        // TODO: Implement query() method.
    }


    public function kill(): bool
    {
        // TODO: Implement kill() method.
    }

    public function tables(): bool
    {
        // TODO: Implement tables() method.
    }

    protected function connect(array $credentials): bool
    {
        // TODO: Implement connect() method.
    }
}
