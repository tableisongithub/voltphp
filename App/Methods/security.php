<?php
//Made by VoltPHP. Do not touch!
if (!defined(ROOT)) {
    die("You have to define the ROOT constant in your public/index.php file. Use it like this: define('ROOT', __DIR__ . '/../');");
}
if (empty($env)) {
    die("Please make sure to require App/kernel/autoload.php before this file.");
}
if ($env["DB_HOST"] == "" || $env["DB_PORT"] == "" || $env["DB_PASSWORD"] == "" || $env["DB_USER"] == "" || $env["DB_NAME"] == "") {
    die("Please make sure to fill in your database credentials in your .env file.");
}

enum mode: int
{
    case NORMAL = 0;
    case OAUTH2 = 1;
    case TOKEN = 2;
}

;

class User
{
    public $uuid;
    public $username;
    public $key;
    public $conn;
    public $data;

    public function __construct($username, $loginKey, mode $oauth2)
    {
        require_once ROOT . "/App/Methods/db.php";

        global $env;  // Assuming $env is defined globally

        if (DB_EXTENSION === "mysqli") {
            $this->conn = new MysqliInstance(file_get_contents(ROOT . "/App/Schemas/security.sql"), [$env["DB_HOST"] . ":" . $env["DB_PORT"], $env["DB_USER"], $env["DB_PASS"], $env["DB_NAME"]]);
        } elseif (DB_EXTENSION === "PDO") {
            $this->conn = new PDOInstance(file_get_contents(ROOT . "/App/Schemas/security.sql"), [$env["DB_HOST"] . ":" . $env["DB_PORT"], $env["DB_USER"], $env["DB_PASS"], $env["DB_NAME"]]);
        }

        switch ($oauth2) {
            case mode::OAUTH2:
                if (!$this->setUsername($username)) {
                    $this->selfDestruct();
                    return false;
                }
                $this->key = bin2hex(random_bytes(32));
                $this->conn->query("UPDATE voltphp_users SET key = '{$this->key}' WHERE uuid = '{$this->data["uuid"]}';");
                break;
            case mode::TOKEN:
                if (!$this->checkToken($loginKey)) {
                    $this->selfDestruct();
                    return false;
                }
                break;
            default:
                if (!$this->setUsername($username)) {
                    $this->selfDestruct();
                    return false;
                }
                if (!$this->checkPassword($loginKey)) {
                    $this->selfDestruct();
                    return false;
                }
                $this->key = bin2hex(random_bytes(32));
                $this->conn->query("UPDATE voltphp_users SET key = '{$this->key}' WHERE uuid = '{$this->data["uuid"]}';");
                return false;
        }
        $this->conn->query("UPDATE voltphp_users SET last_login = NOW() WHERE uuid = '{$this->data["uuid"]}';");
    }

    public static function login($username, $password)
    {
        $user = new User($username, $password);
        if (!$user) {
            return false;
        }
        return $user;
    }

    public static function oauth2login($username)
    {
        $user = new User($username, "", true);
        if (!$user) {
            return false;
        }
        return $user;
    }

    public static function tokenLogin($token)
    {
        $user = new User("", $token, mode::TOKEN);
        if (!$user) {
            return false;
        }
        return $user;
    }

    private function setUsername($username)
    {
        $result = $this->conn->query("SELECT * FROM voltphp_users WHERE username = '$username'");
        if ($result && count($result) > 0) {
            $this->data = $result[0];
            $this->username = $this->data["username"];
            $this->uuid = $this->data["uuid"];
            return true;
        } else {
            return false;
        }
    }

    private function checkPassword($password)
    {
        $hash = $this->data["password"];
        return password_verify($password, $hash, PASSWORD_BCRYPT);
    }

    private function selfDestruct()
    {
        $this->conn->kill();
        foreach ($this as $key => $value) {
            unset($this->$key);
        }
    }

    private function checkToken($loginKey)
    {
        if ($this->data["token"] === $loginKey) {
            return true;
        } else return false;
    }
}
