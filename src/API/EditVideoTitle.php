<?php
if (isset($Request->ID)) {
    $Video = Database("UPDATE Videos SET Title = :Title WHERE ID = :ID", [
        ':Title'=>$Request->Title,
        ':ID'=>$Request->ID,
    ]);
    die(json_encode(['OK'=>true]));
}
