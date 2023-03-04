<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
include("src/database.php");

$Video = Database("SELECT Filename, Title, SHA256, UploadData FROM Videos WHERE ID = :ID", [':ID'=>$_GET['id']]);
if (!count($Video)) {
    die("Video not found.");
}
$Video = $Video[0];

session_start();
if (!$_SESSION['videos'] || !$_SESSION['videos'][$Video['Filename']])
    die("Unauthorised");
$fp = fopen("files/".$Video['Filename'], 'rb');
//header("Content-Type: video/mp4");
header("Content-Length: " . filesize("files/".$Video['Filename']));
session_write_close();
fpassthru($fp);
die();