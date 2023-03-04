<?php
if (!isset($_SESSION['user']['Admin']) || !$_SESSION['user']['Admin'])
    die("Access Denied");

if ($Request->Name) {
    SetSetting($Request->Name, $Request->Value);
    die(json_encode(['OK'=>true]));
}