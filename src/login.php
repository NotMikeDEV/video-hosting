<?php
session_start();
if ($_SERVER['QUERY_STRING'] == 'logout') {
    unset($_SESSION['user']);
    header("Refresh: 1; url=?");
    die("<a href='?'>Click here to continue</a>");
}
$error = false;
if (!isset($_SESSION['user']) && isset($_POST['username']) && isset($_POST['password'])) {
    $Username = strtolower($_POST['username']);
    $Password = $_POST['password'];
    $passwordhash = hash("sha512", $Username . $Password);
    $result = Database("SELECT Username, Admin FROM Users WHERE Username = :username AND Password = :passwordhash", [
        ':username'=>$_POST['username'],
        ':passwordhash'=>$passwordhash
]);
    if ($result && count($result) == 1) {
        $_SESSION['user'] = $result[0];
    }
}
if (!isset($_SESSION['user'])) {
    ?>
<html>
    <head>
        <title>Log In</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-GLhlTQ8iRABdZLl6O3oVMWSktQOp6b7In1Zl3/Jr59b6EGGoI1aFkw7cmDA6j6gD" crossorigin="anonymous">
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js" integrity="sha384-w76AqPfDkMBDXo30jS1Sgez6pr3x5MlQ1ZAGC+nuZB+EYdgRZgiwxhTBTkF7CXvN" crossorigin="anonymous"></script>
        <style>
        .field {
            display: inline-block;
            width: 100px;
        }
        .error {
            color: red;
        }
        </style>
    </head>
    <body>
        <div class="h-100 d-flex align-items-center justify-content-center">
            <div class="">
                <h1 class="text-center">Log In</h1>
                <div class="error"><?php if ($error) echo $error?></div>
                <form method="post">
                    <p>
                        <div class="field">Username:</div>
                        <input type="text" name="username" placeholder="Username" />
                    </p>
                    <p>
                        <div class="field">Password:</div>
                        <input type="password" name="password" placeholder="Password" />
                    </p>
                    <p>
                        <div class="field"></div>
                        <input class="btn btn-primary" type="submit" value="Log In" />
                    </p>
                </form>
            </div>
        </div>
    </body>
</html>
    <?php
    die();
}