<?php
require_once 'commons/init.php';

// confirm the session...
require_once 'commons/dbconnect.php';
require_once 'commons/sessions.php';
require_once 'commons/rpgfunctions.php';
require_once 'commons/grammar.php';
require_once 'commons/formatting.php';
require_once 'commons/inventory.php';

require_once 'commons/houselib.php';
require_once 'commons/questlib.php';

$hoursToRun = (int)$_POST['hours'];

$houseChecker = new HouseChecker($user_object);
$house = $houseChecker->House();
$house->CapHours();
$house->RecalculateInventorySize();

if($hoursToRun > $house->Hours())
    $hoursToRun = $house->Hours();
if($hoursToRun > 12)
    $hoursToRun = 12;

if($_POST['action'] == 'Go!' && $hoursToRun > 0 && $houseChecker->CanRun())
{
    while($hoursToRun > 0 && $houseChecker->CanRun())
    {
        $houseChecker->Run();

        $hoursToRun--;
    }
}

header('Location: /myhouse.php');
exit();
