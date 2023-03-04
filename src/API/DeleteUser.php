<?php
if (!$_SESSION['user']['Admin'])
    die("Access Denied");

if ($Request->Username == $_SESSION['user']['Username'])
    die(json_encode(['OK'=>true]));
if (isset($Request->Username)) {
    $Result = Database("DELETE FROM Users WHERE Username = :Username", [':Username'=>$Request->Username]);
    die(json_encode(['OK'=>true]));
}
