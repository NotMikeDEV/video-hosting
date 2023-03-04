<?php
if (strstr($Request->ID, '.'))
    die("Umm..");
if (isset($Request->ID)) {
    $Video = Database("SELECT Filename FROM Videos WHERE ID = :ID", [':ID'=>$Request->ID]);
    if (count($Video))
    {
        $Video = $Video[0];
        foreach (glob("files/".$Request->ID."*.mp4") as $filename) {
            @unlink($filename);
        }
        $Result = Database("DELETE FROM Videos WHERE ID = :ID", [':ID'=>$Request->ID]);
        die(json_encode(['OK'=>true]));
    }
}
