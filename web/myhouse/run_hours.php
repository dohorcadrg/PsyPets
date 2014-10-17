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
$max_pets = max_active_pets($user, $house);
$house_hours = floor(($now - $house['lasthour']) / (60 * 60));
$can_spend_hours = (count($userpets) <= $max_pets && $house['curbulk'] <= min(max_house_size(), $house['maxbulk']) && $user['no_hours_fool'] == 'no');

if($_POST['action'] == 'Go!' && $hoursToRun > 0 && $house_hours > 0 && $can_spend_hours)
{
    $pets = array();
    foreach ($userpets as &$pet)
        $pets[] = Pet::Load($pet, $user_object);

    $houseChecker = new HouseChecker($user_object, $pets);

    while($house_hours > 0 && $can_spend_hours && $hoursToRun > 0)
    {
        $houseChecker->Step();

        $hoursToRun--;

        if($hoursToRun > 0)
        {
            $house = get_house_byuser($user['idnum']);
            $max_pets = max_active_pets($user, $house);
            $house_hours = floor(($now - $house['lasthour']) / (60 * 60));
            $can_spend_hours = (count($userpets) <= $max_pets && $house['curbulk'] <= min(max_house_size(), $house['maxbulk']) && $user['no_hours_fool'] == 'no');
        }
    }
}

header('Location: /myhouse.php');
exit();
