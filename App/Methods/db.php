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
    protected $tableCreationQuery;

    /**
     * Constructor for the DBInstance class.
     *
     * @param string $tableCreationQuery The SQL query used to create tables.
     * @param string $prefix An optional prefix for the table names.
     * @return bool Returns true if the instance is successfully created, false otherwise.
     */
    abstract protected function __construct(string $tableCreationQuery, string $prefix = "");

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

    protected function __construct(string $tableCreationQuery, string $prefix = "")
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

class PDOInstance extends DBInstance
{
    private $connection;

    protected function __construct(string $tableCreationQuery, string $prefix = "")
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