<?php
$wiki = 'Real_Estate';
$require_petload = 'no';

require_once 'commons/init.php';

// confirm the session...
require_once 'commons/dbconnect.php';
require_once 'commons/rpgfunctions.php';
require_once 'commons/sessions.php';
require_once 'commons/grammar.php';
require_once 'commons/formatting.php';
require_once 'commons/houselib.php';
require_once 'commons/userlib.php';

$locid = $user['locid'];
$house = get_house_byuser($user['idnum'], $locid);

$addons = take_apart(',', $house['addons']);
$have_basement = (array_search('Basement', $addons) !== false);
$have_lake = (array_search('Lake', $addons) !== false);

if($_POST['action'] == 'getdeed')
{
    $size = (int)$_POST['size'];

    if($house['maxbulk'] - $size >= 5000)
    {
        if($size == 500 || $size == 1000 || $size == 2000 || $size == 5000 || $size == 10000)
        {
            upgrade_house($house['idnum'], $house['maxbulk'] - $size);
            add_inventory($user['user'], 'u:' . $user['idnum'], 'Deed to ' . ($size / 10) . ' Units', '', 'storage/incoming');
            flag_new_incoming_items($user['user']);
            $message = '<span class="success">Transaction complete.  The deed is in Incoming.</span>';

            $house['maxbulk'] -= $size;

            require_once 'commons/statlib.php';
            record_stat($user['idnum'], 'Acquired a Deed from Real Estate', 1);
        }
        else
            $message = "<span class=\"failure\">There is no such deed available.</span>";
    }
}

// check to see if we're already working on the lake
load_user_projects($user, $userprojects);

$working_on_lake = false;

if(count($userprojects) > 0)
{
    foreach($userprojects as $project)
    {
        if($project['itemid'] == 22)
        {
            $working_on_lake = true;
            break;
        }
    }
}

include 'commons/html.php';
?>
<head>
    <title><?= $SETTINGS['site_name'] ?> &gt; Real Estate &gt; Acquire Deeds</title>
    <?php include 'commons/head.php'; ?>
</head>
<body>
    <?php include 'commons/header_2.php'; ?>
    <h5>Real Estate &gt; Acquire Deeds</h5>
    <ul class="tabbed">
        <?php if($house['maxbulk'] <= 4000): ?><li><a href="realestate.php">Buy Land</a></li><?php endif; ?>
        <li class="activetab"><a href="realestate_deeds.php">Acquire Deeds</a></li>
        <?php if(!$working_on_lake && !$have_lake): ?><li><a href="realestate_lake.php">Build Lake</a></li><?php endif; ?>
    </ul>
    <a href="npcprofile.php?npc=Amanda+Branaman"><img src="//<?= $SETTINGS['static_domain'] ?>/gfx/npcs/real-estate-agent.png" align="right" width="350" height="490" alt="(Amanda, the Real Estate agent)" /></a>
    <?php include 'commons/dialog_open.php'; ?>
    <?php if($message): ?>
        <p><?= $message ?></p>
    <?php endif; ?>

    <?php if($have_basement): ?>
        <p>You currently have a size <?= ($house['maxbulk'] / 10) ?> estate, and a <?= ($house['maxbasement'] / 100) ?>-level basement.</p>
    <?php else: ?>
        <p>You currently have a size <?= ($house['maxbulk'] / 10) ?> estate.</p>
    <?php endif; ?>
    <p>You may get a paper deed for a plot of land you own no smaller than 50 units.  You can use these to buy, sell and trade parts of your estate.</p>
    <p>City ordinances prevent us from reducing an estate size below 500 units.</p>
    <?php if($have_basement): ?>
        <p>Since you have a basement, you should know that a Deed to 1000 Units can be used to add a floor to your basement.</p>
    <?php endif; ?>
    <?php include 'commons/dialog_close.php'; ?>

    <?php if($house['maxbulk'] >= $house['curbulk'] + 10): ?>
        <?php $excessSpace = floor(($house['maxbulk'] - $house['curbulk']) / 10); ?>
        <p>You have <?= $excessSpace ?> extra space in your house right now (space beyond what your items currently take up). Reducing your house size more than this might not be the <em>best</em> idea... but it's up to you.</p>
    <?php else: ?>
        <p class="failure">You already have more stuff in your house than you can fit. Reducing your house size further might not be the <em>best</em> idea right now. But it's up to you.</p>
    <?php endif; ?>

    <?php if($house['maxbulk'] >= 5500): ?>
        <?php $rowclass = begin_row_class(); ?>
        <table>
            <tr class="titlerow">
                <th>Plot Size</th>
                <th></th>
            </tr>
            <form action="realestate_deeds.php" method="post">
                <tr class="<?= $rowclass ?>">
                    <td class="centered">50</td>
                    <td><input type="hidden" name="action" value="getdeed" /><input type="hidden" name="size" value="500" /><input type="submit" value="Get Deed" /></td>
                </tr>
            </form>
            <?php if($house['maxbulk'] >= 6000): ?>
                <?php $rowclass = alt_row_class($rowclass); ?>
                <form action="realestate_deeds.php" method="post">
                    <tr class="<?= $rowclass ?>">
                        <td class="centered">100</td>
                        <td><input type="hidden" name="action" value="getdeed" /><input type="hidden" name="size" value="1000" /><input type="submit" value="Get Deed" /></td>
                    </tr>
                </form>
            <?php endif; ?>

            <?php if($house['maxbulk'] >= 7000): ?>
                <?php $rowclass = alt_row_class($rowclass); ?>
                <form action="realestate_deeds.php" method="post">
                    <tr class="<?= $rowclass ?>">
                        <td class="centered">200</td>
                        <td><input type="hidden" name="action" value="getdeed" /><input type="hidden" name="size" value="2000" /><input type="submit" value="Get Deed" /></td>
                    </tr>
                </form>
            <?php endif; ?>

            <?php if($house['maxbulk'] >= 10000): ?>
                <?php $rowclass = alt_row_class($rowclass); ?>
                <form action="realestate_deeds.php" method="post">
                    <tr class="<?= $rowclass ?>">
                        <td class="centered">500</td>
                        <td><input type="hidden" name="action" value="getdeed" /><input type="hidden" name="size" value="5000" /><input type="submit" value="Get Deed" /></td>
                    </tr>
                </form>
            <?php endif; ?>

            <?php if($house["maxbulk"] >= 15000): ?>
                <?php $rowclass = alt_row_class($rowclass); ?>
                <form action="realestate_deeds.php" method="post">
                    <tr class="<?= $rowclass ?>">
                        <td class="centered">1000</td>
                        <td><input type="hidden" name="action" value="getdeed" /><input type="hidden" name="size" value="10000" /><input type="submit" value="Get Deed" /></td>
                    </tr>
                </form>
            <?php endif; ?>
        </table>
    <?php endif; ?>
    <?php include 'commons/footer_2.php'; ?>
</body>
</html>
