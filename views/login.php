<?php session_start();

require_once(__DIR__ . '/../persistence/DAO.php');

$loginErr = "";
// Validate that access is only through a POST request,
// redirect to index.php if not
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // These are required so no need for isset
    $username = $_POST["username"];
    $password = $_POST["password"];

    login($username, $password);
}

/**
 * Secure login function
 *
 * @param $username
 * @param $password
 */
function login($username, $password) {
    $dao = new DAO();

    // Use DAO to get login attempts
    if ($dao->findLoginAttempts($username) >= 3) {
        echo "
            <div class='alert alert-danger' role='alert'>
                You've had too many login attempts. This account
                has been locked until further notice.
            </div>
        ";
    } else {
        $hash = $dao->findHash($username);
        // Authenticate user, update time
        if (!password_verify($password, $hash)) {
            // Increment attempts
            $dao->updateLoginAttemptsIncrement($username);
            // Redirect to error page, or back to login with error message
            echo "
            <div class='alert alert-danger' role='alert'>
                Wrong password, please try again!
            </div>
        ";
        } else {
            $dao->updateResetLoginAttempts($username);

            // Save in session
            $_SESSION['username'] = $username;
            $_SESSION['lineId'] = $dao->findLineIdForAccount($_SESSION['username']);
            session_regenerate_id();

            header('Location:/'.'../views/reader.php');
            exit();
        }
    }
}

?>
<link rel='stylesheet' href='https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-beta.2/css/bootstrap.min.css' integrity='sha384-PsH8R72JQ3SOdhVi3uxftmaW6Vc51MKb0q5P2rRUpPvrszuE4W1povHYgTpBfshb' crossorigin='anonymous'>

<div class='jumbotron' style='display: flex; height: 100vh; align-items: center'>
    <div class='container'>
        <h1>Sign In</h1>
        <form action="<?php echo htmlentities($_SERVER["PHP_SELF"]);?>" method='POST' id='loginForm'>
          <div class='form-group'>
            <label for='username'>Username</label>
            <input type='text' class='form-control' id='username' name='username' required>
          </div>
          <div class='form-group'>
            <label for='password'>Password</label>
            <input type='password' class='form-control' id='password' name='password' required>
          </div>
          <button type='submit' class='btn btn-primary'>Login</button>
          <div class='dropdown-divider'></div>
          <a class='dropdown-item' href='register.php'>New around here? Register instead</a>
        </form>
    </div>
</div>
";