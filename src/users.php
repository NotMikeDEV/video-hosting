<?php
if (!isset($_SESSION['user']['Admin']) || !$_SESSION['user']['Admin'])
    die("Access Denied");
$Users = Database("SELECT * FROM Users ORDER BY Username DESC");
?>
<div class="modal" id="addmodal">
  <div class="modal-background"></div>
    <div class="modal-card">
        <header class="modal-card-head">
        <p class="modal-card-title">Add User</p>
        </header>
        <section class="modal-card-body" id="addmodalbody">
            <div class="field">
                <input type="text" id="addusername" class="input" style="width:600px" placeholder="Username" />
            </div>
            <div class="field">
                <input type="password" id="addpassword" class="input" style="width:600px" placeholder="Password" />
            </div>
            <div class="field">
                <input type="password" id="addpassword2" class="input" style="width:600px" placeholder="Confirm Password" />
            </div>
            <div class="field">
                <label class="checkbox">
                    <input type="checkbox" id="addadmin">
                    Admin
                </label>
            </div>
        </section>
        <footer class="modal-card-foot" id="addmodalfoot">
            <button id="addbutton" class="button is-success" style="width:295px">Add User</button>
            <button id="addcancelbutton" class="button is-danger" style="width:295px">Cancel</button>
        </footer>
    </div>
</div>
<script>
$('#adduser').submit(()=>{
    event.preventDefault();
    return false
})
$('#addcancelbutton').click(()=>{
    $('#addmodal').removeClass('is-active')
})
$('#addbutton').click(async()=>{
    if (!$('#addusername').val())
        return alert("Username can not be empty!")
    if (!$('#addpassword').val())
        return alert("Password can not be empty!")
        if ($('#addpassword').val().length < 6)
        return alert("Password must be longer!")
    if ($('#addpassword').val() != $('#addpassword2').val())
        return alert("Passwords do not match!")
    var AddRequest = await fetch("API.php", {
        method: "POST",
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            API: "AddUser",
            Username: $('#addusername').val().toLowerCase(),
            Password: $('#addpassword').val(),
            Admin: $('#addadmin').is(":checked")
        })
    })
    const Result = await AddRequest.json()
    console.log("AddUser", Result)
    if (Result.Error)
        return alert(Result.Error)
    location.reload()
})
</script>


<h1 class="title">
    Manage Users:
    <button class="button is-success" onclick="$('#addmodal').addClass('is-active')">
        Add User
    </button>
</h1>

<table class="table">
    <thead>
        <tr>
            <td width="200">Username</td>
            <td width="100">Type</td>
            <td width="170">Manage</td>
        </tr>
    </thead>
    <tbody>
        <?php
        foreach ($Users as $User) {
            ?>
            <tr>
                <td><?=$User['Username']?></td>
                <td><?=$User['Admin']?'Admin':'User'?></td>
                <td class="has-text-right">
                    <button onclick="Password('<?=$User['Username']?>');">
                        <i class="fas fa-key"></i>
                    </button>
                    <button onclick="Delete('<?=$User['Username']?>');">
                        <i class="fas fa-trash-alt"></i>
                    </button>
                </td>
            </tr>
            <?php
        }
        ?>
    </tbody>
</table>
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
<script>
async function Delete(Username) {
    if (!confirm("Delete " + Username + "?"))
        return    
    var DeleteRequest = await fetch("API.php", {
        method: "POST",
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            API: "DeleteUser",
            Username: Username,
        })
    })
    const Result = await DeleteRequest.json()
    console.log("DeleteUser", Result)
    if (Result.OK)
        location.reload()
    else if (Result.Error) {
        alert(Result.Error)
        location.reload()
    }

}
async function Password(Username) {
    $('#modal').addClass("is-active")
    $('#modaltitle').text("Set password for " + Username)
    const INPUT = $('<INPUT>').appendTo($('#modalbody').empty())
        .addClass("input")
        .prop('type', 'password')
        .prop('placeholder', 'New Passwod')
    const INPUT2 = $('<INPUT>').appendTo($('#modalbody'))
        .addClass("input")
        .prop('type', 'password')
        .prop('placeholder', 'Confirm Passwod')

    const SaveButton = $('<BUTTON>').appendTo($('#modalfoot').empty())
        .addClass('button is-success')
        .text('Set Password')
        .click(async()=>{
            if (INPUT.val() != INPUT2.val())
                return alert("Passwords do not match!")
            var SetUserPasswordRequest = await fetch("API.php", {
                method: "POST",
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    API: "SetUserPassword",
                    Username: Username,
                    Password: INPUT.val()
                })
            })
            const Result = await SetUserPasswordRequest.json()
            console.log("SetUserPassword", Result)
            location.reload()
        })

    const CancelButton = $('<BUTTON>').appendTo($('#modalfoot'))
        .addClass('button is-danger')
        .text('Cancel')
        .click(async()=>{
            $('#modal').removeClass("is-active")
        })

}
</script>

