<?php
function GenerateID() {
    $Length = 16;
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $randomString = '';
    
    for ($i = 0; $i < $Length; $i++) {
        $index = rand(0, strlen($characters) - 1);
        $randomString .= $characters[$index];
    }
    return $randomString;
}

if ($Request->Command == "Start") {
    if (!$Request->Title)
        die(json_encode(['Error'=>"No title"]));
    if (!$Request->Chunks)
        die(json_encode(['Error'=>"Invalid size"]));
    if (!$Request->SHA256)
        die(json_encode(['Error'=>"No hash specified"]));
    $UploadData = [
        'TotalChunks'=>(int)$Request->Chunks,
        'ChunksDone'=>0
    ];
    $ID = GenerateID();
    
    Database("INSERT INTO Videos (ID, Filename, SHA256, Size, Date, Title, UploadData) VALUES (:ID, :Filename, :SHA256, :Size, :Date, :Title, :UploadData)", [
        ':ID'=>$ID,
        ':Filename'=>$ID."_".GenerateID().".mp4",
        ':SHA256'=>$Request->SHA256,
        ':Size'=>$Request->Size,
        ':Date'=>time(),
        ':Title'=>$Request->Title,
        ':UploadData'=>json_encode($UploadData)
    ]);
    die(json_encode(['ID'=>$ID]));
}


if ($Request->Command == "Chunk") {
    $Video = Database("SELECT Filename, SHA256, UploadData FROM Videos WHERE ID = :ID", [':ID'=>$Request->ID]);
    if (!count($Video))
        die(json_encode(['Error'=>"Unable to find file"]));
    $Video = $Video[0];
    $UploadData = json_decode($Video['UploadData']);
    if ($Request->Chunk != $UploadData->ChunksDone)
        die(json_encode(['Error'=>"Incorrect Chunk"]));

    $Data = base64_decode($Request->Data);
    $Hash = hash('sha256', $Data);
    if ($Hash != $Request->SHA256) {
        die(json_encode([
            'Error'=>"Checksum failed for chunk " . $Request->Chunk,
            'ClientHash'=>$Request->SHA256,
            'ServerHash'=>$Hash
        ]));
    }
    file_put_contents("files/".$Video['Filename'], $Data, FILE_APPEND);
    $UploadData->ChunksDone++;
    if ($UploadData->ChunksDone == $UploadData->TotalChunks) {
        $UploadData=['Transcoding'=>true];
        Database("UPDATE Videos SET UploadData = :UploadData WHERE ID = :ID", [
            ':UploadData'=>json_encode($UploadData),
            ':ID'=>$Request->ID
        ]);
            $FileHash = hash_file("sha256", "files/".$Video['Filename']);
        if ($FileHash != $Video['SHA256']) {
            die(json_encode(['Error'=>"Checksum failed!"]));
        }
        $NewFilename = $Request->ID . "_" . GenerateID() . ".mp4";
        exec("("
            . "ffmpeg -i files/".$Video['Filename']." -c:v libx264 -crf 23 -profile:v baseline -level 3.0 -pix_fmt yuv420p -preset slow -crf 22 -c:a aac -ac 2 -b:a 128k -movflags faststart -y files/$NewFilename "
            . "&& sqlite3 files/database.db \"UPDATE Videos SET Filename='$NewFilename' WHERE Filename='$Video[Filename]'\" "
            . "&& rm -f files/$Video[Filename] "
            . "&& sqlite3 files/database.db \"UPDATE Videos SET UploadData = NULL WHERE Filename='$NewFilename'\" "
            . "&& sqlite3 files/database.db \"UPDATE Videos SET Size = $(stat -c %s files/$NewFilename) WHERE Filename='$NewFilename'\" "
            . "&& echo \"$Video[Filename] replaced with $NewFilename\" "
            . ")  > log.txt 2>&1 &");
        die(json_encode(['OK'=>true, 'Hash'=>$Hash, 'Done'=>true]));
    }
    Database("UPDATE Videos SET UploadData = :UploadData WHERE ID = :ID", [
        ':UploadData'=>json_encode($UploadData),
        ':ID'=>$Request->ID
    ]);
    die(json_encode(['OK'=>true, 'Hash'=>$Hash]));
}
