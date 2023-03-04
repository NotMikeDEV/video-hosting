<?php
try {
    Database("SELECT * FROM Users");
} catch (Exception $e) {
    $error = false;
    if (isset($_POST['username']) && isset($_POST['password']) && isset($_POST['confirm_password'])) {
        if ($_POST['password'] != $_POST['confirm_password']) {
            $error = "Password and confirmation do not match!";
        }
        if (strlen($_POST['password']) < 8) {
            $error = "Password must be at least 8 characters!";
        }
        if (!$error) {
            header("Refresh: 1");
            Database("CREATE TABLE Users(Username, Password, Admin)");
            Database("CREATE TABLE Videos(ID NOT NULL PRIMARY KEY, Filename, SHA256, Size, Date, Title, UploadData)");
            Database("CREATE TABLE Settings(Name NOT NULL PRIMARY KEY, Value)");
            $Username = strtolower($_POST['username']);
            $Password = $_POST['password'];
            $passwordhash = hash("sha512", $Username . $Password);
            Database("INSERT INTO Users(Username, Password, Admin) VALUES (:username, :passwordhash, 1)", [
                ':username'=>$_POST['username'],
                ':passwordhash'=>$passwordhash
            ]);
            SetSetting("Title", "Video Player");
            die("Database Initialised");
        }
    }
?>
<html>
    <head>
        <title>Setup Required</title>
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
        <p>Setup Required.</p>
        <div class="error"><?php if ($error) echo $error?></div>
        <form method="post">
            <p>
                <div class="field">Username:</div>
                <input type="text" name="username" placeholder="Username" />
            </p>
            <p>
                <div class="field">Password:</div>
                <input type="password" name="password" placeholder="Password" />
                <br>
                <div class="field">Confirm:</div>
                <input type="password" name="confirm_password" placeholder="Password" />
            </p>
            <p>
                <div class="field"></div>
                <input type="submit" value="Initialise System" />
            </p>
        </form>
    </body>
</html>
<?php
    die();
}