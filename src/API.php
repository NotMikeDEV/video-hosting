<?php
require_once("database.php");
require_once("init.php");
session_start();
if (!isset($_SESSION['user'])) {
    die("Authentication Required");
}
$Request = file_get_contents("php://input");
$Request = json_decode($Request);
if ($Request->API == "UploadVideo")
    include("API/UploadVideo.php");
if ($Request->API == "DeleteVideo")
    include("API/DeleteVideo.php");
if ($Request->API == "EditVideoTitle")
    include("API/EditVideoTitle.php");
if ($Request->API == "Set")
    include("API/Set.php");
if ($Request->API == "Get")
    include("API/Get.php");
if ($Request->API == "AddUser")
    include("API/AddUser.php");
if ($Request->API == "DeleteUser")
    include("API/DeleteUser.php");
if ($Request->API == "SetUserPassword")
    include("API/SetUserPassword.php");
