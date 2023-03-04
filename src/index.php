<?php
require_once("database.php");
if (isset($_GET['id'])) {
    require("view.php");
    die();
}
require_once("init.php");
require_once("login.php");
ob_start();

$page = isset($_GET['p'])?$_GET['p']:'';
?>
<html>
    <head>
        <title>Video Manager</title>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bulma/0.8.2/css/bulma.min.css" />
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.3/jquery.min.js"></script>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.2/css/all.min.css" />
    </head>
    <body>
        <nav class="navbar is-dark" role="navigation" aria-label="main navigation">
			<div class="navbar-brand">
				<a class="has-text-success is-family-monospace title is-1" href="?">
					<span style="width: 10px;"></span>
					Video Manager
				</a>
			</div>
			<div id="navbarBasic" class="navbar-menu is-active">
				<div class="navbar-start">
					<a class="navbar-item<?php if ($page=='') echo ' is-active'?>" href="?">
						Videos
					</a>
                    <?php if ($_SESSION['user']['Admin']) { ?>

					<a class="navbar-item<?php if ($page=='users') echo ' is-active'?>" href="?p=users">
						Users
					</a>
					<a class="navbar-item<?php if ($page=='settings') echo ' is-active'?>" href="?p=settings">
						Settings
					</a>
                    <?php } ?>
				</div>
				<div class="navbar-end">
                    <a class="navbar-item" href="?logout">
						Log Out
					</a>
				</div>
			</div>
		</nav>
        <?php
        switch ($page) {
            case '':
                include("src/video.php");
            break;
            case 'users':
                include("src/users.php");
            break;
            case 'settings':
                include("src/settings.php");
            break;
            default:
            echo "<h1 class='title has-text-centered'>404 Not Found</h1>";
        }
        ?>
    </body>
</html>