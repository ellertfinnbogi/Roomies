<?php





class LoginApplication
{

   // session_start();


    private $db_type = "sqlite";
    private $db_path = "users1.db";

    private $db_connection = null;

    private $userIsLoggedIn = false;

    public $feedback = "";

    public function __construct()
    {
        if ($this->performMinimumRequirementsCheck()) {
            $this->runApplication();
        }
    }


    private function performMinimumRequirementsCheck()
    {
    /*    if (version_compare(PHP_VERSION, '5.3.7', '<')) {
            echo "Sorry, Simple PHP Login does not run on a PHP version older than 5.3.7 !";
        } elseif (version_compare(PHP_VERSION, '5.5.0', '<')) {
            require_once("libraries/password_compatibility_library.php");
            return true;
        } elseif (version_compare(PHP_VERSION, '5.5.0', '>=')) {
            return true;
        }
        // default return*/
        return true;
    }


    public function runApplication()
    {
        if (isset($_POST["login"]))
        {
            $this->doStartSession();
            $this->doLogin();
        }
    }


    public function doStartSession()
    {
        session_start();
    }

    private function createDatabaseConnection()
    {

        try {
            $this->db_connection = new PDO($this->db_type . ':' . $this->db_path);
            return true;
           // echo ' conn worked';
        } catch (PDOException $e) {
            $this->feedback = "PDO database connection problem: " . $e->getMessage();
        } catch (Exception $e) {
            $this->feedback = "General problem: " . $e->getMessage();
        }
        return false;
    }


private function doLogin()
{
    if ($this->checkLoginFormDataNotEmpty()) {
        if ($this->createDatabaseConnection()) {
            if($this->checkPasswordCorrectnessAndLogin())
            {
                $this->showPageLoggedin();
            }
        }
    }
}

private function checkLoginFormDataNotEmpty()
{
    if (!empty($_POST['user_name']) && !empty($_POST['user_password'])) {
        return true;
    } elseif (empty($_POST['user_name'])) {
        $this->feedback = "Username field was empty.";
    } elseif (empty($_POST['user_password'])) {
        $this->feedback = "Password field was empty.";
    }
    // default return
    return false;
}

    private function checkPasswordCorrectnessAndLogin()
    {
        // remember: the user can log in with username or email address
        $sql = 'SELECT user_name, user_email, user_password_hash
                FROM users
                WHERE user_name = :user_name OR user_email = :user_name
                LIMIT 1';
        $query = $this->db_connection->prepare($sql);
        $query->bindValue(':user_name', $_POST['user_name']);
        $query->execute();
        // Btw that's the weird way to get num_rows in PDO with SQLite:
        // if (count($query->fetchAll(PDO::FETCH_NUM)) == 1) {
        // Holy! But that's how it is. $result->numRows() works with SQLite pure, but not with SQLite PDO.
        // This is so crappy, but that's how PDO works.
        // As there is no numRows() in SQLite/PDO (!!) we have to do it this way:
        // If you meet the inventor of PDO, punch him. Seriously.
        $result_row = $query->fetchObject();
        if ($result_row) {
            // using PHP 5.5's password_verify() function to check password
            if (password_verify($_POST['user_password'], $result_row->user_password_hash)) {
                // write user data into PHP SESSION [a file on your server]
                $_SESSION['user_name'] = $result_row->user_name;
                $_SESSION['user_email'] = $result_row->user_email;
                $_SESSION['user_is_logged_in'] = true;
                $this->user_is_logged_in = true;
                return true;
            } else {
                $this->feedback = "Wrong password.";
            }
        } else {
            $this->feedback = "This user does not exist.";
        }
        // default return
        return false;

    }

    public function showPageLoggedIn()
    {
        header('Location:temp.php');
    }
}
$application = new LoginApplication();
?>