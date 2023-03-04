<?php
$dsn = 'sqlite:files/database.db';
$dbh = new PDO($dsn);
function Database($sql, $params=[]) {
    global $dbh;
    $stmt = $dbh->prepare($sql);
    if (!$stmt)
        throw new Exception("Invalid SQL: " . $sql);
    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value);
    }
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
function SetSetting($Name, $Value) {
    if ($Value === null) {
        Database("DELETE FROM Settings WHERE Name = :Name", [
            ':Name'=>$Name,
        ]);
    }
    Database("INSERT OR REPLACE INTO Settings (Name, Value) VALUES (:Name, :Value)", [
        ':Name'=>$Name,
        ':Value'=>$Value,
    ]);
    return Setting($Name);
}
function Setting($Name) {
    $Result = Database("SELECT Value FROM Settings WHERE Name = :Name", [
        ':Name'=>$Name,
    ]);
    if (!count($Result))
        return null;
    return $Result[0]['Value'];
}