<?php
if (!isset($_SESSION['user']['Admin']) || !$_SESSION['user']['Admin'])
    die("Access Denied");

$Username = strtolower($Request->Username);
$Password = $Request->Password;
if ($Username && $Password) {
    $passwordhash = hash("sha512", $Username . $Password);
    Database("INSERT INTO Users (Username, Password, Admin) VALUES (:Username, :Password, :Admin)", [
        ':Username'=>$Username,
        ':Password'=>$passwordhash,
        ':Admin'=>$Request->Admin?1:0
    ]);
    die(json_encode(['OK'=>true]));
}