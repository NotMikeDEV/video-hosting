<?php
if (!$_SESSION['user']['Admin'])
    die("Access Denied");

if (isset($Request->Username)) {
    $Username = strtolower($Request->Username);
    $Password = $Request->Password;
    $passwordhash = hash("sha512", $Username . $Password);
    $Video = Database("UPDATE Users SET Password = :Password WHERE Username = :Username", [
        ':Password'=>$passwordhash,
        ':Username'=>$Username,
    ]);
    die(json_encode(['OK'=>true]));
}
