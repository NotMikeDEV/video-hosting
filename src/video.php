<style>
.progresswrapper {
    background-color: #FF0000;
}
.progressbar {
    background-color: #00FF00;
}
.fullwidth {
    width:100%;
}
</style>
<h1 class="title">Upload Video:</h1>
<form id="fileupload" method="POST">
    <div class="container">
        <div id="error" class="has-text-danger is-size-2"></div>
        <div id="upload">
            <div class="progresswrapper">
                <div class="progressbar" id="uploadprogress"></div>
            </div>
        </div>
        <div class="field">
            <input type="text" id="videotitle" class="input" style="width:600px" placeholder="Video Title" />
        </div>
        <input type="file" id="file" class="input" style="width:300px" />
        <input type="submit" id="uploadbutton" class="button is-success"  style="width:295px" value="Upload Video" />
    </div>
</form>
<script>
function ab2str(buf) {
  return String.fromCharCode.apply(null, new Uint16Array(buf));
}
function str2ab(str) {
  var buf = new ArrayBuffer(str.length); // 2 bytes for each char
  var bufView = new Uint8Array(buf);
  for (var i=0, strLen=str.length; i<strLen; i++) {
    bufView[i] = str.charCodeAt(i);
  }
  return buf;
}
function Base64Encode(DataArray) {
    const Base64Chunks = []
    const ChunkSize = 120
    for (let Offset = 0; Offset < DataArray.byteLength; Offset += ChunkSize) {
        const PlainString = String.fromCharCode(...new Uint8Array(DataArray.slice(Offset, Offset + ChunkSize)))
        const Base64String = btoa(PlainString)
        Base64Chunks.push(Base64String)
    }
    return Base64Chunks.join('')
}
async function sha256(str) {
    const buf = await crypto.subtle.digest("SHA-256", str)
    return Array.prototype.map.call(new Uint8Array(buf), x=>(('00'+x.toString(16)).slice(-2))).join('')
}

$('#upload').hide()
$('#fileupload').submit(()=>{
    var myFile = $('#file').prop('files')[0]
    if (!myFile) {
        $('#error').text('Please select a file!')
        return false
    }
    const VideoTitle = $('#videotitle').val()
    if (!VideoTitle) {
        $('#error').text('Please specify a title!')
        return false
    }
    $('#error').empty();
    $('#upload').show()
    $('#uploadbutton').prop('disabled', true);
    $('#uploadprogress').width("100%")
    $('#uploadprogress').text("Preparing to upload...")
    async function UploadFile(FileBuffer) {
        //const FileBuffer = str2ab(FileData)
        const Hash = await sha256(FileBuffer)
        const Chunks = []
        let ChunkSize = 1024*1024
        if (myFile.size > ChunkSize * 50) {
            ChunkSize = myFile.size/50
        }
        if (ChunkSize > 5*1024*1024)
            ChunkSize = 5*1024*1024

        console.log("FileBuffer")
        for (let Offset = 0; Offset < myFile.size; Offset += ChunkSize) {
            Chunks.push(FileBuffer.slice(Offset, Offset + ChunkSize))
        }
        console.log("Chunks:")
        $('#uploadprogress').text("Starting upload...");
        var UploadSession = await fetch("API.php", {
            method: "POST",
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                API: "UploadVideo",
                Command: "Start",
                Title: VideoTitle,
                Size: myFile.size,
                Chunks: Chunks.length,
                SHA256: Hash
            })
        })
        UploadSession = await UploadSession.json()
        console.log("UploadSession", UploadSession)
        if (UploadSession.Error) {
            $('#upload').hide()
            $('#error').text(UploadSession.Error)
            $('#uploadbutton').prop('disabled', false);
            return
        }
        const UploadID = UploadSession.ID
        async function UpdateProgress(ChunksDone) {
            const Percentage = Math.floor(ChunksDone / Chunks.length * 100)
            $('#uploadprogress').width(Percentage + "%")
            $('#uploadprogress').text(Percentage + "%")
        }
        for (let Chunk=0; Chunk < Chunks.length; Chunk++) {
            const ChunkHash = await sha256(Chunks[Chunk])
            var UploadSession = await fetch("API.php", {
                method: "POST",
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    API: "UploadVideo",
                    Command: "Chunk",
                    Chunk: Chunk,
                    ID: UploadID,
                    SHA256: ChunkHash,
                    Data: Base64Encode(Chunks[Chunk])
                })
            })
            UpdateProgress(Chunk)
            const Result = await UploadSession.json()
            console.log("ChunkUpload", Chunk, Result)
            if (Result.Hash != ChunkHash) {
                $('#error').text("Hash failure on chunk " + Chunk)
            }
            if (Result.Error) {
                $('#error').text(Result.Error)
                $('#uploadbutton').prop('disabled', false);
                return
            }
        }
        UpdateProgress(Chunks.length)
        setTimeout(()=>{
            $('#uploadbutton').prop('disabled', false);
            location.href="?uploaded="+UploadID
        }, 1000)
    }
    const FileURL = URL.createObjectURL(myFile)
    const FileContents = new Uint8Array(myFile.size);
    let Offset = 0
    fetch(FileURL).then(async(File)=>{
        const Reader = File.body.getReader()
        const Chunks = []
        Reader.read().then(function processText({ done, value }) {
            if (done) {
                if (Offset != myFile.size)
                    return $('#error').text("Error loading file")
                UploadFile(FileContents)
                return
            }
            FileContents.set(value, Offset)
            Offset += value.length
            Reader.read().then(processText);
            return
        })
    })
    return false
})
</script>

<?php
function formatBytes($bytes) {
    $units = ['B', 'KB', 'MB', 'GB', 'TB'];
    $i = 0;
    while ($bytes >= 1024 && $i < count($units) - 1) {
        $bytes /= 1024;
        $i++;
    }
    return round($bytes, 1) . ' ' . $units[$i];
}

if (isset($_GET['recover']) && isset($_GET['filename']) && !strstr($_GET['filename'], "..")) {
    
    $Videos = Database("INSERT INTO Videos (ID, Size, Filename, Date, Title) VALUES (:ID, :Size, :Filename, :Date, :Title)", [
        ':ID'=>$_GET['recover'],
        ':Size'=>filesize("files/".$_GET['filename']),
        ':Filename'=>$_GET['filename'],
        ':Date'=>time(),
        ':Title'=>"Recovered Video " . $_GET['recover'],
    ]);
    header("Location: ?");
}
$Videos = Database("SELECT ID, Size, Date, Title, UploadData FROM Videos ORDER BY Date DESC");
$TotalSize = 0;
$Vid = [];
foreach ($Videos as $Video) {
    $TotalSize += $Video['Size'];
    $Vid[$Video['ID']] = $Video;
}
?>
<div class="modal" id="modal">
  <div class="modal-background"></div>
  <div class="modal-card">
    <header class="modal-card-head">
      <p class="modal-card-title" id="modaltitle">Modal title</p>
      <button class="delete" aria-label="close" onclick="$('#modal').removeClass('is-active')"></button>
    </header>
    <section class="modal-card-body" id="modalbody">
      <!-- Content ... -->
    </section>
    <footer class="modal-card-foot" id="modalfoot">
    </footer>
  </div>
</div>
<h1 class="title">Manage Videos: (Total: <?=formatBytes($TotalSize);?>)</h1>
<?php
foreach (glob("files/*.mp4") as $filename) {
    $filename = substr($filename, 6);
    $parts = explode(".", $filename);
    if (count($parts) == 2 && $parts[1] == 'mp4') {
        $parts = explode("_", $parts[0]);
        if (!isset($Vid[$parts[0]])) {
            echo "<h3>Found video: " . $parts[0] . " <a href='?recover=".urlencode($parts[0])."&filename=".urlencode($filename)."'>Recover</a></h3>";
        }
    }
}
?>

<table class="table fullwidth">
    <thead>
        <tr>
            <td width="150">ID</td>
            <td width="100">Size</td>
            <td width="170">Date</td>
            <td>Title</td>
            <td width="150" class="has-text-right">Manage</td>
        </tr>
    </thead>
    <tbody>
        <?php
        foreach ($Videos as $Video) {
            ?>
            <tr>
                <td><a href="?id=<?=$Video['ID']?>" target="_blank" class="has-text-link"><?=$Video['ID']?></a></td>
                <td><a href="?id=<?=$Video['ID']?>" target="_blank" class="has-text-link"><?=formatBytes($Video['Size'])?></a></td>
                <td><a href="?id=<?=$Video['ID']?>" target="_blank" class="has-text-link"><?=gmdate("Y-m-d H:i:s", $Video['Date']);?></a></td>
                <td>
                    <a href="?id=<?=$Video['ID']?>" target="_blank" class="has-text-link">
                        <?=$Video['Title']?>
                    </a>
                    <?php
                        if ($Video['UploadData']) {
                            $Data = json_decode($Video['UploadData']);
                            if (isset($Data->Transcoding) && $Data->Transcoding)
                                echo "Processing...";
                            else
                                echo "Uploading...";
                        }
                    ?>
                </td>
                <td class="has-text-right">
                    <button id="delete-button" onclick="Edit('<?=$Video['ID']?>', '<?=addslashes($Video['Title']);?>');">
                        <i class="fas fa-edit"></i>
                    </button>
                        

                    <button id="delete-button" onclick="Delete('<?=$Video['ID']?>');">
                        <i class="fas fa-trash-alt"></i>
                    </button>
                </td>
            </tr>
            <?php
        }
        ?>
    </tbody>
</table>
<script>
async function Delete(ID) {
    if (!confirm("Delete Video?"))
        return    
    var DeleteRequest = await fetch("API.php", {
        method: "POST",
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            API: "DeleteVideo",
            ID: ID,
        })
    })
    const Result = await DeleteRequest.json()
    console.log("DeleteVideo", Result)
    if (Result.OK)
        location.reload()
}
async function Edit(ID, Title) {
    $('#modal').addClass("is-active")
    $('#modaltitle').text("Edit title of video " + ID)
    const INPUT = $('<INPUT>').appendTo($('#modalbody').empty())
        .addClass("input")
        .prop('placeholder', 'New Title')
        .val(Title)

    const SaveButton = $('<BUTTON>').appendTo($('#modalfoot').empty())
        .addClass('button is-success')
        .text('Save')
        .click(async()=>{
            var EditVideoTitleRequest = await fetch("API.php", {
                method: "POST",
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    API: "EditVideoTitle",
                    ID: ID,
                    Title: INPUT.val()
                })
            })
            const Result = await EditVideoTitleRequest.json()
            console.log("EditVideoTitle", Result)
            location.reload()
        })

    const CancelButton = $('<BUTTON>').appendTo($('#modalfoot'))
        .addClass('button is-danger')
        .text('Cancel')
        .click(async()=>{
            $('#modal').removeClass("is-active")
        })

}

<?php
if (isset($_GET['uploaded'])) {
?>
$(async()=>{
    $('#upload').show()
    $('#uploadprogress').width("100%")
    $('#uploadprogress').text("Upload Complete!")
})
<?php } ?>
</script>