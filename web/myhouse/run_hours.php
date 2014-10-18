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

$max_pets = $user_object->MaxActivePets();
$house_hours = floor(($now - $house['lasthour']) / (60 * 60));
$can_spend_hours = (count($userpets) <= $max_pets && $house['curbulk'] <= min(max_house_size(), $house['maxbulk']) && $user['no_hours_fool'] == 'no');

if($hoursToRun > $house_hours)
    $hoursToRun = $house_hours;
if($hoursToRun > 12)
    $hoursToRun = 12;

if($_POST['action'] == 'Go!' && $hoursToRun > 0 && $can_spend_hours)
{
    if($house_hours > 72)
    {
        fetch_none('
            UPDATE monster_houses
            SET lasthour=' . ($now - (72 * 60 * 60)) . '
            WHERE idnum=' . $house['idnum'] . '
            LIMIT 1
        ');
    }

    while($house_hours > 0 && $can_spend_hours && $hoursToRun > 0)
    {
        fetch_none('
            UPDATE monster_houses
            SET lasthour=lasthour+3600
            WHERE idnum=' . $house['idnum'] . '
            LIMIT 1
        ');

        $house['lasthour'] += 3600;

        $houseChecker->Step();

        $hoursToRun--;

        $house = get_house_byuser($user['idnum']);

        if($hoursToRun > 0)
        {
            $house_hours = floor(($now - $house['lasthour']) / (60 * 60));
            $can_spend_hours = (count($userpets) <= $max_pets && $house['curbulk'] <= min(max_house_size(), $house['maxbulk']) && $user['no_hours_fool'] == 'no');
        }
    }
}

header('Location: /myhouse.php');
exit();
