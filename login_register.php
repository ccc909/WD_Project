<?php
//start session so user data can be stored and remember accross the session
session_start();
//set variables as empty to avoid errors
$message = ''; 
$action = '';
    
//check if the user wants to login or register

if (isset($_POST['login'])) {
    $action = 'login';
} elseif (isset($_POST['register'])) {
    $action = 'register';
}

//read data based on choice
if ($action === 'login') {
    $username = $_POST['username'];
    $password = $_POST['password'];
} elseif ($action === 'register') {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

   //check is passwords are the same
    if ($password !== $confirm_password) {
        $message = 'Passwords do not match';
    }
}

//connect to database
$conn = mysqli_connect('localhost', 'root', '', 'medical_clinic');

//if connection is unsuccsfull close it
if (!$conn) {
    die('Database connection failed');
}
//check if user wantrs to login
if ($action === 'login') {
    //prepare query
    $query = "SELECT * FROM users WHERE username = '$username'";
    //run query
    $result = mysqli_query($conn, $query);

    //check if quey succeeds
    if ($result && mysqli_num_rows($result) > 0) {
        //fetch users record
        $user = mysqli_fetch_assoc($result);

        //verify password
        if (password_verify($password, $user['password'])) {
            //store the data in the users sessin
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['admin'] = $user['admin'];
            $_SESSION['message'] = '';
            //redirect user to the main page
            header('Location: index.php');
            exit;
            //save error messages
        } else {
            $_SESSION['message'] = 'Invalid password';
            header('Location: login_register.php');
        }
    } else {
        $_SESSION['message'] = 'User not found';
        header('Location: login_register.php');
        
    }
} elseif ($action === 'register') {
    //use previously defined variable to check if passwords match
if ($password == $confirm_password) {
        //prepare query
        $query = "SELECT * FROM users WHERE username = '$username'";
        //run query
        $result = mysqli_query($conn, $query);
        //check if username already exists
        if ($result && mysqli_num_rows($result) > 0) {
             $_SESSION['message'] = 'Username already exists';
        } else {
            //has the password
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

           
            $query = "INSERT INTO users (username, password,admin) VALUES ('$username', '$hashed_password',0)";
            $result = mysqli_query($conn, $query);

            if ($result) {
                $_SESSION['message'] = 'Registration successful. Please login.';
            } else {
                $_SESSION['message'] = 'Registration failed';
                
                header('Location: login_register.php');
                
            }
        }
    }
}

// Close the database connection
mysqli_close($conn);
?>


<!DOCTYPE html>
<html>
<head>
    <title>Medical Clinic - Login/Register</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="lstyle.css">
</head>
<body>
    <div class="container">
        <?php if (!empty($_SESSION['message'])) : ?>
    <p><?php echo $_SESSION['message']; ?></p>

<?php endif; ?>

        
                
                <div id="register-form" style="display:none;">
                    <h2>Register</h2>
                    <form method="POST" action="login_register.php">
                        <label for="username">Username:</label>
                        <input type="text" name="username" required>
                        <label for="password">Password:</label>
                        <input type="password" name="password" required>
                        <label for="confirm_password">Confirm Password:</label>
                        <input type="password" name="confirm_password" required>
                        <input type="submit" name="register" value="Register">
                        <p>Already have an account? <a href="#" onclick="toggleForm()">Login here</a>.</p>
                    </form>
                </div>
           
                <div id="login-form">
                    <h2>Login</h2>
                    <form method="POST" action="login_register.php">
                        <label for="username">Username:</label>
                        <input type="text" name="username" required>
                        <label for="password">Password:</label>
                        <input type="password" name="password" required>
                        <input type="submit" name="login" value="Login">
                        <p>Don't have an account? <a href="#" onclick="toggleForm()">Register here</a>.</p>
                    </form>
                </div>
                <a href="index.php">Back to Main Page</a>
        
    </div>
    
    <script>
       function toggleForm() {
            
            //function to switch from one form to other
            var registerForm = document.getElementById('register-form');
            var loginForm = document.getElementById('login-form');

            if (registerForm.style.display === 'none') {
                registerForm.style.display = 'block';
                loginForm.style.display = 'none';
            } else {
                registerForm.style.display = 'none';
                loginForm.style.display = 'block';
            }
        }
    </script>

</body>
</html>
</html>

</body>
</html>