<?php
if($okay_to_be_here !== true)
  exit();

require_once 'commons/petlib.php';

$pet_field = 'special_' . $action_info[2];
$action_description = $action_info[3];
$power_description = $action_info[4];

if(count($userpets) == 0)
  echo '<p>You have no pet to give this to.</p>';
else
{
  $petid = (int)$_POST['petid'];

  if($petid > 0)
    $target_pet = get_pet_byid($petid);
  else
    $target_pet = array();

  if($target_pet['user'] != $user['user'] || $target_pet[$pet_field] == 'yes' || $target_pet['dead'] != 'no' || $target_pet['zombie'] == 'yes' || $target_pet['location'] != 'home')
  {
?>
<p><?= $action_description ?> will give it a fantastic power: <?= $power_description ?>!</p>
 <form action="itemaction.php?idnum=<?= $_GET['idnum'] ?>" method="post">
 <p>
  <select name="petid">
<?php
    foreach($userpets as $this_pet)
    {
      if($this_pet[$pet_field] == 'yes')
        echo '   <option disabled>' . $this_pet['petname'] . ' (already has this power)</option>';
      else if($this_pet['dead'] != 'no')
        echo '   <option disabled>' . $this_pet['petname'] . ' (is dead)</option>';
      else if($this_pet['zombie'] == 'yes')
        echo '   <option disabled>' . $this_pet['petname'] . ' (is a zombie)</option>';
      else
        echo '   <option value="' . $this_pet['idnum'] . '">' . $this_pet['petname'] . '</option>';
    }
?>
  </select> <input type="submit" name="submit" value="Give" />
 </p>
 </form>
<?php
  }
  else
  {
    $command = 'UPDATE monster_pets SET ' . $pet_field . '=\'yes\' WHERE idnum=' . $target_pet['idnum'] . ' LIMIT 1';
    $database->FetchNone($command, 'granting pet ' . $power_description);

    echo '<p><a href="/pet/profile.php?petid=' . $petid . '">' . $target_pet['petname'] . '</a> has gained the power of ' . $power_description . '!</p>';

    delete_inventory_byid($this_inventory['idnum']);

    $AGAIN_WITH_ANOTHER = true;
  }
}
?>
