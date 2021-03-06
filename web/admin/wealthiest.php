<?php
$IGNORE_MAINTENANCE = true;

require_once 'commons/init.php';

$require_login = 'no';

// confirm the session...
require_once 'commons/dbconnect.php';
require_once 'commons/sessions.php';
require_once 'commons/rpgfunctions.php';
require_once 'commons/encryption.php';
require_once 'commons/formatting.php';

if($admin['clairvoyant'] != 'yes')
{
    header('Location: /n404/');
    exit();
}

$wealthiest = fetch_multiple("SELECT display,money FROM monster_users WHERE is_npc='no' AND disabled='no' ORDER BY money DESC LIMIT 10");

include 'commons/html.php';
?>
<head>
    <title><?= $SETTINGS['site_name'] ?> &gt; Admin Tools &gt; Wealthiest 10</title>
    <?php include "commons/head.php"; ?>
</head>
<body>
    <?php include 'commons/header_2.php'; ?>
    <h4><a href="/admin/tools.php">Administrative Tools</a> &gt; Wealthiest 10</h4>
    <ol>
        <?php foreach($wealthiest as $this_user): ?>
            <li><p><a href="/userprofile.php?user=<?= link_safe($this_user["display"]) ?>"><?= $this_user["display"] ?></a> has <?= $this_user["money"] ?> moneys.</p></li>
        <?php endforeach; ?>
    </ol>
    <?php include 'commons/footer_2.php'; ?>
</body>
</html>
