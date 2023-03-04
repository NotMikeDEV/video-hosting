<?php
$Video = Database("SELECT ID, Filename, Title, SHA256, UploadData FROM Videos WHERE ID = :ID", [':ID'=>$_GET['id']]);
if (!count($Video)) {
    die("Video not found.");
}
$Video = $Video[0];
session_start();
$_SESSION['videos'][$Video['Filename']] = true;
?>
<HTML>
    <HEAD>
        <TITLE><?=$Video['Title'];?> - <?=Setting("Title");?></TITLE>
        <STYLE>
        .logo {
            position: absolute;
            top: 0px;
            left: 0px;
        }
        .center {
            margin: 0;
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
        }
        /* Default size for the video element */
        .video {
            width: 100%; /* Set the width to 100% of the container */
            height: auto; /* Automatically adjust the height to maintain aspect ratio */
        }

        /* Set size for window widths up to 800px */
        @media (max-width: 800px) {
            .video {
                width: 426px;
                height: 240px;
            }
        }

        /* Set size for window widths greater than 800px */
        @media (min-width: 801px) {
            .video {
                width: 854px;
                height: 480px;
            }
        }
        @media (min-width: 1280px) {
            .video {
                width: 1280px;
                height: 720px;
            }
        }
        </STYLE>
    </HEAD>
        <BODY>
        <IMG SRC="logo.png" class="logo" />
        <DIV CLASS="center">
            <H2><?=$Video['Title'];?></H2>
            <VIDEO CONTROLS AUTOPLAY class="video">
                <SOURCE SRC="video.php?id=<?=$Video['ID'];?>" type="video/mp4" />
                An error occurred. It seems your browser does not support playing back this video.
            </VIDEO>
        </DIV>
    <BODY>
</HTML>