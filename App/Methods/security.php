<?php
// Made by VoltPHP. Do not touch!

namespace App\Base\Security;

if (empty($env)) {
    die("Please make sure to require App/kernel/autoload.php before this file.");
}
if ($env["DB_HOST"] == "" || $env["DB_PORT"] == "" || $env["DB_PASSWORD"] == "" || $env["DB_USER"] == "" || $env["DB_NAME"] == "") {
    die("Please make sure to fill in your database credentials in your .env file.");
}

use App\Base\db\MysqliInstance;
use App\Base\db\PDOInstance;
use App\Base\db\DBInstance;
use Random\RandomException;

/**
 * Enum representing different modes of operation.
 */
enum mode: int
{
    case NORMAL = 0;
    case OAUTH2 = 1;
    case TOKEN = 2;
    case CREATE = 3;
    case OAUTH2_CREATE = 4;
    case KEY = 5;
}

/**
 * Enum representing different error codes.
 */
enum errors: int
{
    case BAD_CREDENTIALS = 1;
    case BAD_TOKEN = 2;
    case ALREADY_EXISTS = 3;
    case NO_CREDENTIALS = 4;
    case SERVER_ERROR = 5;
}

/**
 * Class User
 *
 * Handles user authentication and management operations.
 *
 * **WARNING:** Do not instantiate this class directly as it may lead to unexpected behavior or break functionality.
 * Use the static constructor methods (e.g., `login()`, `forceLogin()`, `tokenLogin()`, etc.) instead to ensure proper initialization.
 */
class User
{
    /**
     * @var int|null The user ID.
     */
    public $user_id;

    /**
     * @var string|null The username of the user.
     */
    public $username;

    /**
     * @var string|null The generated key for the user.
     */
    public $key;

    /**
     * @var MysqliInstance|PDOInstance The database connection instance.
     */
    private $conn;

    /**
     * @var array|null The user data retrieved from the database.
     */
    public $data;

    /**
     * @var errors|null Error code if any error occurs during operations.
     */
    public $error;

    /**
     * @var bool Indicates whether the user is logged in.
     */
    public $loggedIn = false;

    /**
     * @var bool Allows the user to change passwords.
     */
    private $pwauth = false;

    /**
     * User constructor.
     *
     * Initializes the user based on the given mode.
     *
     * **WARNING:** Direct instantiation of this class is strongly discouraged and may lead to unexpected behavior.
     * Always use the provided static methods (`login()`, `forceLogin()`, etc.) to create a user instance.
     * These static methods handle essential setup tasks that are not covered when calling the constructor directly.
     *
     * @param string|null $username The username for the user.
     * @param string|null $loginKey The login key (password or token).
     * @param mode $mode The mode of operation.
     */
    public function __construct($username, $loginKey, mode $mode)
    {
        require_once ROOT . "/App/Methods/db.php";

        global $env;  // Assuming $env is defined globally

        if (DB_EXTENSION === "mysqli") {
            $this->conn = new MysqliInstance(file_get_contents(ROOT . "/App/Schemas/security.sql"), [$env["DB_HOST"] . ":" . $env["DB_PORT"], $env["DB_USER"], $env["DB_PASS"], $env["DB_NAME"]]);
        } elseif (DB_EXTENSION === "PDO") {
            $this->conn = new PDOInstance(file_get_contents(ROOT . "/App/Schemas/security.sql"), [$env["DB_HOST"] . ":" . $env["DB_PORT"], $env["DB_USER"], $env["DB_PASS"], $env["DB_NAME"]]);
        }

        switch ($mode) {
            case mode::OAUTH2:
                if (!$this->setUsername($username, true)) {
                    $this->selfDestruct();
                    $this->error = errors::BAD_CREDENTIALS;
                    $this->loggedIn = false;
                    return false;
                }
                $this->key = bin2hex(random_bytes(32));
                $this->conn->query("UPDATE voltphp_users SET key = '" . DBInstance::clean($this->key) . "' WHERE user_id = " . DBInstance::clean($this->data["user_id"]) . ";");
                $this->loggedIn = true;
                $this->pwauth = true;
                break;
            case mode::TOKEN:
                if (!$this->checkToken($loginKey)) {
                    $this->selfDestruct();
                    $this->error = errors::BAD_TOKEN;
                    $this->loggedIn = false;
                    return false;
                }
                $this->loggedIn = true;
                $this->pwauth = false;
                break;
            case mode::NORMAL:
                if (!$this->setUsername($username)) {
                    $this->selfDestruct();
                    $this->error = errors::BAD_CREDENTIALS;
                    $this->loggedIn = false;
                    return false;
                }
                if (!$this->checkPassword($loginKey)) {
                    $this->loggedIn = false;
                    $this->selfDestruct();
                    $this->error = errors::BAD_CREDENTIALS;
                    return false;
                }
                $this->key = bin2hex(random_bytes(32));
                $this->conn->query("UPDATE voltphp_users SET key = '{$this->key}' WHERE user_id = " . DBInstance::clean($this->data["user_id"]) . ";");
                $this->loggedIn = true;
                $this->pwauth = true;
                break;
            case mode::OAUTH2_CREATE:
                if (empty($username)) {
                    $this->error = errors::NO_CREDENTIALS;
                    $this->selfDestruct();
                    return false;
                }
                $this->username = $username;
                $this->key = bin2hex(random_bytes(32));
                $this->conn->query("INSERT INTO voltphp_users (username, key, created_at, oauth2) VALUES ('" . DBInstance::clean($this->username) . ", ''{$this->key}', NOW(), TRUE);");
                $this->loggedIn = true;
                $this->pwauth = true;
                break;
            case mode::CREATE:
                if (empty($username) || empty($loginKey)) {
                    $this->error = errors::NO_CREDENTIALS;
                    $this->selfDestruct();
                    return false;
                }
                $this->username = $username;
                $this->key = bin2hex(random_bytes(32));
                $this->conn->query("INSERT INTO voltphp_users (username, password, key, created_at, oauth2) VALUES ('" . DBInstance::clean($this->username) . "', '" . password_hash($loginKey, PASSWORD_BCRYPT) . "', '{$this->key}', NOW(), FALSE);");
                $this->loggedIn = true;
                $this->pwauth = true;
                break;
            case mode::KEY:
                if (empty($loginKey)) {
                    $this->error = errors::NO_CREDENTIALS;
                    $this->selfDestruct();
                    return false;
                }
                if (!$this->getDataByApiKey($loginKey)) {
                    $this->error = errors::BAD_TOKEN;
                    $this->selfDestruct();
                    return false;
                }
                $this->loggedIn = true;
                $this->pwauth = false;
                break;

        }
        if ($this->loggedIn = true) {
            $this->conn->query("UPDATE voltphp_users SET last_login = NOW() WHERE user_id = " . DBInstance::clean($this->data["user_id"]) . ";");
        }
    }

    /**
     * Retrieves user data based on an API key.
     *
     * @param string $key The API key.
     * @return bool True if the user data was found, false otherwise.
     */
    private function getDataByApiKey($key)
    {
        // Query to check if the API key exists and retrieve the associated user data
        $result = $this->conn->query("
        SELECT u.* 
        FROM voltphp_users u 
        JOIN voltphp_users_apikeys ak ON u.user_id = ak.user_id 
        WHERE ak.key = '" . DBInstance::clean($key) . "';"
        );

        // Check if the result is valid and contains data
        if ($result && count($result) > 0) {
            // Populate the class properties with the retrieved data
            $this->data = $result[0];
            $this->username = $this->data["username"];
            $this->user_id = $this->data["user_id"];
            return true;
        } else {
            // Return false if no data was found
            return false;
        }
    }

    /**
     * Logs in a user with a username and password.
     *
     * @param string $username The username.
     * @param string $password The password.
     * @return User|false The User object on success, false on failure.
     */
    public static function login($username, $password)
    {
        $user = new User($username, $password, mode::NORMAL);
        if (!$user) {
            return false;
        }
        return $user;
    }

    /**
     * Forces a login using only the username (for OAuth2).
     *
     * **CAUTION:** This method does not check for a password or token, which might expose the system to security risks.
     * It is intended for OAuth2 scenarios where the user has already been authenticated by an external provider.
     *
     * **WARNING:** Users logged in via `forceLogin` can change their password without providing the old one, which may be a security risk.
     * Ensure this method is used only in secure environments where the OAuth2 authentication is trustworthy.
     *
     * @param string $username The username.
     * @return User|false The User object on success, false on failure.
     */
    public static function forceLogin($username)
    {
        $user = new User($username, "", mode::OAUTH2);
        if (!$user) {
            return false;
        }
        return $user;
    }

    /**
     * Logs in a user using a token.
     *
     * @param string $token The token.
     * @return User|false The User object on success, false on failure.
     */
    public static function tokenLogin($token)
    {
        $user = new User("", $token, mode::TOKEN);
        if (!$user) {
            return false;
        }
        return $user;
    }

    /**
     * Creates a new user account with a username and password.
     *
     * @param string $username The username.
     * @param string $password The password.
     * @return User|false The User object on success, false on failure.
     */
    public static function create($username, $password)
    {
        $user = new User($username, $password, mode::CREATE);
        if (!$user) {
            return false;
        }
        return $user;
    }

    /**
     * Creates a new OAuth2 user account with a username.
     *
     * **WARNING:** The password for the user is randomly generated and cannot be used for normal password-based login.
     * Users created through this method can only log in using `forceLogin` (OAuth2) and will not be able to change their password unless logged in via OAuth2 again.
     *
     * **SECURITY NOTICE:** This method allows users to bypass traditional password-based login, so be cautious about who can access this method.
     * Additionally, users created this way cannot change their password through normal means as they do not know the old passwords.
     * This disallows users authenticated via API keys or tokens from changing passwords, reducing the attack surface for compromised credentials.
     *
     * @param string $username The username.
     * @return User|false The User object on success, false on failure.
     */
    public static function oauth2Create($username)
    {
        $user = new User($username, "", mode::OAUTH2_CREATE);
        if (!$user) {
            return false;
        }
        return $user;
    }

    /**
     * Sets the OAuth2 status for the user.
     *
     * @param bool $oauth2 The OAuth2 status.
     * @return bool True on success, false on failure.
     */
    public function setOauth2(bool $oauth2)
    {
        if ($this->username == null) {
            return false;
        }
        return $this->conn->query("UPDATE voltphp_users SET oauth2 = " . ($oauth2));
    }

    /**
     * Updates the password for the user.
     *
     * **WARNING:** Only users who are logged in with OAuth2 (via `forceLogin`) or with a valid password can change their password.
     * Users authenticated via API keys or tokens cannot change their passwords, which prevents potential misuse of API keys or tokens to escalate privileges.
     *
     * **SECURITY NOTICE:** This restriction is implemented to ensure that password changes are securely handled and to prevent unauthorized password changes through compromised API keys or tokens.
     *
     * @param string $newPassword The new password.
     * @return bool True on success, false on failure.
     */
    public function updatePassword($newPassword)
    {
        if ($this->username == null || !$this->pwauth) {
            return false;
        }
        return $this->conn->query("UPDATE voltphp_users SET password = '" . password_hash($newPassword, PASSWORD_BCRYPT) . "' WHERE user_id = " . DBInstance::clean($this->data["user_id"]) . ";");
    }

    /**
     * Retrieves all API keys associated with the user.
     *
     * @return array|false An array of API keys on success, false on failure.
     */
    public function getApiKeys()
    {
        if ($this->username == null) {
            return false;
        }
        // Query to retrieve all API keys associated with the user
        $result = $this->conn->query("SELECT * FROM voltphp_users_apikeys WHERE user_id = " . DBInstance::clean($this->data["user_id"]) . ";");

        // Return the retrieved keys
        return $result;
    }

    /**
     * Adds a new API key for the current user.
     *
     * This method generates a new API key, inserts it into the `voltphp_users_apikeys` table,
     * and returns the key ID and key value if successful.
     *
     * @param string $prefix The prefix to use for the API key.
     * @return array|false An associative array with 'key_id' and 'key' on success, or false on failure.
     * @throws RandomException If the random bytes generation fails.
     */
    public function addApiKey($prefix)
    {
        if ($this->username === null) {
            return false;
        }

        $apiKey = DBInstance::clean($prefix) . bin2hex(random_bytes(64));

        $query = "INSERT INTO voltphp_users_apikeys (user_id, `key`) VALUES (" . DBInstance::clean($this->data["user_id"]) . ", '" . $apiKey . "');";

        if ($this->conn->query($query)) {
            $result = $this->conn->query("SELECT key FROM voltphp_users_apikeys WHERE user_id = " . DBInstance::clean($this->data["user_id"]) . " AND `key` = '" . DBInstance::clean($apiKey) . "';");

            if ($result && count($result) > 0) {
                $keyId = $result[0]['key_id'];

                return [
                    'key_id' => $keyId,
                    'key' => $apiKey
                ];
            }
        }

        // Return false if the insertion or retrieval failed
        return false;
    }

    /**
     * Deletes an API key for the current user.
     *
     * @param int $key_id The key ID to delete.
     * @return bool True on success, false on failure.
     */
    public function deleteApiKey($key_id)
    {
        if ($this->username === null) {
            return false;
        }
        return $this->conn->query("DELETE FROM voltphp_users_apikeys WHERE user_id = " . DBInstance::clean($this->data["user_id"]) . " AND key_id = '" . DBInstance::clean($key_id) . "';");
    }

    /**
     * Deletes the user account and associated API keys.
     *
     * @return bool True on success, false on failure.
     */
    public function delete()
    {
        // Start a transaction
        $this->conn->begin_transaction();

        try {
            $result1 = $this->conn->query("DELETE FROM voltphp_users_apikeys WHERE user_id = " . DBInstance::clean($this->data["user_id"]) . ";");

            if (!$result1) {
                $this->conn->rollback();
                return false;
            }

            $result2 = $this->conn->query("DELETE FROM voltphp_users WHERE user_id = " . DBInstance::clean($this->data["user_id"]) . ";");

            if (!$result2) {
                $this->conn->rollback();
                return false;
            }

            $this->conn->commit();
            return true;
        } catch (Exception $e) {
            $this->conn->rollback();
            return false;
        }
    }

    /**
     * Sets the username for the user based on the database record.
     *
     * @param string $username The username to set.
     * @return bool True on success, false on failure.
     */
    private function setUsername($username)
    {
        $result = $this->conn->query("SELECT * FROM voltphp_users WHERE username = " . DBInstance::clean($username) . ";");
        if ($result && count($result) > 0) {
            $this->data = $result[0];
            $this->username = $this->data["username"];
            $this->user_id = $this->data["user_id"];
            return true;
        } else {
            return false;
        }
    }

    /**
     * Checks if the provided password matches the stored password hash.
     *
     * @param string $password The password to check.
     * @return bool True if the password matches, false otherwise.
     */
    private function checkPassword($password)
    {
        $hash = $this->data["password"];
        return password_verify($password, $hash, PASSWORD_BCRYPT);
    }

    /**
     * Destroys the user session and clears the user data.
     */
    private function selfDestruct()
    {
        $this->conn->kill();
        $this->username = null;
        $this->user_id = null;
        $this->key = null;
        $this->loggedIn = false;
        $this->data = null;
    }

    /**
     * Checks if the provided token matches the stored token.
     *
     * @param string $loginKey The token to check.
     * @return bool True if the token matches, false otherwise.
     */
    private function checkToken($loginKey)
    {
        if ($this->data["token"] === $loginKey) {
            return true;
        } else return false;
    }

    /**
     * Destructor for the User class.
     *
     * Closes the database connection when the object is destroyed.
     */
    public function __destruct()
    {
        $this->conn->kill();
    }
}
