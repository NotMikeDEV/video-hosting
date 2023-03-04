<?php
if (!isset($_SESSION['user']['Admin']) || !$_SESSION['user']['Admin'])
    die("Access Denied");

$Settings = Database("SELECT * FROM Settings ORDER BY Name DESC");
?>
<div class="modal" id="modal">
  <div class="modal-background"></div>
    <div class="modal-card">
        <header class="modal-card-head">
        <p class="modal-card-title" id="modaltitle">Add User</p>
        </header>
        <section class="modal-card-body">
            <div class="field">
                <input type="text" id="value" class="input" style="width:600px" placeholder="Value" />
            </div>
        </section>
        <footer class="modal-card-foot">
            <button id="savebutton" class="button is-success" style="width:295px">Save Setting</button>
            <button id="cancelbutton" class="button is-danger" style="width:295px" onclick="$('#modal').removeClass('is-active')">Cancel</button>
        </footer>
    </div>
</div>
<h1 class="title">
    Manage Settings:
</h1>

<table class="table">
    <thead>
        <tr>
            <td width="200">Name</td>
            <td width="400">Value</td>
            <td width="170">Manage</td>
        </tr>
    </thead>
    <tbody>
        <?php
        foreach ($Settings as $Setting) {
            ?>
            <tr>
                <td><?=$Setting['Name']?></td>
                <td><?=$Setting['Value']?></td>
                <td class="has-text-right">
                    <button onclick="Edit('<?=addslashes($Setting['Name'])?>', '<?=addslashes($Setting['Value'])?>');">
                        <i class="fas fa-edit"></i>
                    </button>
                </td>
            </tr>
            <?php
        }
        ?>
    </tbody>
</table>
<script>
async function Edit(Name, Value) {
    $('#modal').addClass("is-active")
    $('#modaltitle').text("Edit \"" + Name + "\"")
    $('#value').val(Value)
    $('#savebutton').click(async()=>{
        var SetRequest = await fetch("API.php", {
            method: "POST",
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                API: "Set",
                Name: Name,
                Value: $('#value').val()
            })
        })
        const Result = await SetRequest.json()
        console.log("Set", Result)
        location.reload()
    })
}
</script>
