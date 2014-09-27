<?phprequire_once 'commons/init.php';$whereat = "home";$wiki = "Moat";$require_petload = 'no';$THIS_ROOM = 'Moat';$url = '/myhouse/addon/moat.php';// confirm the session...require_once 'commons/dbconnect.php';require_once 'commons/sessions.php';require_once 'commons/rpgfunctions.php';require_once 'commons/grammar.php';require_once 'commons/formatting.php';require_once 'commons/messages.php';require_once 'commons/houselib.php';require_once 'commons/dungeonlib.php';require_once 'commons/utility.php';require_once 'commons/questlib.php';require_once 'commons/mazelib.php';if(!addon_exists($house, 'Moat')){  header('Location: /myhouse.php');  exit();}$pirate_talk = array('Yarr!', 'Har-har-har!', 'Grr!', 'Avast!', 'Arr!', 'Aye!');// check out the inventories$inventory = get_houseinventory_byuser_forpets($user['user']);$fish_count = get_quest_value($user['idnum'], 'fish count');if($fish_count === false){  add_quest_value($user['idnum'], 'fish count', 0);  $number_of_fish = 0;}else  $number_of_fish = (int)$fish_count['value'];$pirate_quest = get_quest_value($user['idnum'], 'moat pirates');if($pirate_quest === false)  $pirate_tribute = 0;else  $pirate_tribute = (int)$pirate_quest['value'];$jerky = 0;$pieces_of_eight = 0;$have_fishingpole = false;foreach($inventory as $i){  $details = get_item_byname($i['itemname']);  if(substr($details['itemtype'], 0, 12) == 'tool/fishing')    $have_fishingpole = true;  if($i['itemname'] == 'Jerky')    $jerky++;  else if($i['itemname'] == 'Piece of Eight')    $pieces_of_eight++;}if($_POST['action'] == 'fish' && $pirate_tribute == 0){  $quantity = (int)$_POST['quantity'];  if(!$have_fishingpole)    $descript = '<span class="failure">You can\'t fish without a Fishing Pole.</span>';  else if($jerky < 1 || $quantity < 1)    $descript = '<span class="failure">You can\'t fish without bait.</span>';  else if($jerky < $quantity)    $descript = '<span class="failure">You don\'t have ' . $quantity . ' Jerky.</span>';  else  {    if($quantity > 1)      $descript = '<ol>';    else      $descript = '<p>';      for($x = 0; $x < $quantity; ++$x)    {      if($quantity > 1)        $descript .= '<li>';          $i = rand(1, 100);      if($i <= 2)      {        $itemname = get_random_obstacle();        $item = get_item_byname($itemname);        $pirate_tribute = $item['idnum'];        if($pirate_quest === false)          add_quest_value($user['idnum'], 'moat pirates', $pirate_tribute);        else          update_quest_value($pirate_quest['idnum'], $pirate_tribute);        $jerky_used++;        $descript .= '<span class="failure">Moat Pirates attack, and you\'re forced to abandon your Jerky to the Moat.</span></li>';        break;      }      else if($i <= 50)      {        $get_item = false;        $descript .= '<span class="failure">You feel a tug on the line, but when you pull it in the Jerky is gone.  Sneaky Fish!  Better try again.</span>';      }      else if($i <= 90)      {        $get_item = true;        if(mt_rand(1, 30) == 1)        {          $a = mt_rand(1, 3);          if($a == 1)            $item = 'Showa Koi Plushy';          else if($a == 2)            $item = 'Kohaku Koi Plushy';          else            $item = 'Bekko Koi Plushy';          require_once 'commons/statlib.php';          record_stat($user['idnum'], 'Fished a Koi from a Moat', 1);        }        else          $item = 'Fish';        $descript .= '<span class="success">You feel a tug on the line, and reel in a Fish!  (And put it in Incoming.)</span>';        $fish_caught++;      }      else // 91-100      {        $get_item = true;        $items = array(          'Dirty Linen' => '', 'Talon' => 'a ', 'Empty Fishbowl' => 'an ', 'Old Tire' => 'an ', 'Empty Can' => 'an ',          'Gold Ring' => 'a ', 'Red Crystal Ball' => '', 'Dragon-in-a-Can' => 'a ', 'Springtime Kimono' => 'a ',          'Old Boot' => 'an ',        );        $item = array_rand($items);        $descript .= '<span class="failure">You feel a tug on the line, and reel in ' . $items[$item] . $item . '!  (And put it in Incoming.)  That\'s not a Fish at all!</span>';      }      if($get_item)      {        add_inventory($user['user'], '', $item, 'Fished from ' . $user['display'] . "'s Moat.", 'storage/incoming');        flag_new_incoming_items($user['user']);      }      $jerky_used++;      if($quantity > 1)        $descript .= '</li>';    }    if($quantity > 1)      $descript .= '</ol>';    else      $descript .= '</p>';    delete_inventory_fromhome($user['user'], 'Jerky', $jerky_used);    $jerky -= $jerky_used;    if($fish_caught > 0)    {      $number_of_fish += $fish_caught;      update_quest_value($fish_count['idnum'], $number_of_fish);      if($number_of_fish - $fish_caught < 100 && $number_of_fish >= 100)      {        if($fish_caught == 1)          $descript .= '<p><span class="success">Hm?  What\'s this?!  The Fish has a badge in its mouth!  (You received the Expert Fisher badge!)</span></p>';        else          $descript .= '<p><span class="success">Hm?  What\'s this?!  One of the Fish has a badge in its mouth!  (You received the Expert Fisher badge!)</span></p>';        set_badge($user['idnum'], 'fisher');      }    }  }}else if($pirate_tribute > 0){  if($pieces_of_eight >= 9)  {    $item = get_item_byid($pirate_tribute);    $command = 'SELECT idnum FROM monster_inventory WHERE user=' . quote_smart($user['user']) . ' AND location=\'storage\' AND itemname=' . quote_smart($item['itemname']) . ' LIMIT 1';    $my_item = fetch_single($command, $url);    if($my_item !== false)    {      $pirate_dialog = 'Ho thar!  That there be <em>nine</em> Pieces of Eight!  Never ye mind all this about the ' . $item['itemname'] . '!  We\'ll be takin\' the pieces, and a bloody end to them as shall gainsay us!';      $pirate_tribute = 0;      delete_inventory_fromhome($user['user'], 'Piece of Eight', 9);      add_inventory($user['user'], '', 'Jolly Roger', $pirate_talk[array_rand($pirate_talk)], 'storage/incoming');      flag_new_incoming_items($user['user']);      update_quest_value($pirate_quest['idnum'], 0);    }  }  else if($_GET['action'] == 'paytribute')  {    $item = get_item_byid($pirate_tribute);    $count = delete_inventory_fromstorage($user['user'], $item['itemname'], 1);    if($count == 1)    {      $pirate_dialog = 'Arr!  I guess this\'ll do and belike... but our courses\'ll cross again!  No bones about\'t!';      $pirate_tribute = 0;      add_inventory($user['user'], '', 'Piece of Eight', $pirate_talk[array_rand($pirate_talk)], 'storage/incoming');      update_quest_value($pirate_quest['idnum'], 0);      flag_new_incoming_items($user['user']);      if($pieces_of_eight > 0)      {        if($pieces_of_eight < 9)          $pirate_dialog .= '</p><p><span class="size7"><i>Faith we\'ll return here again, jim lads!  I spied no fewer than ' . $pieces_of_eight . ' Piece' . ($pieces_of_eight > 1 ? 's' : '') . ' of Eight amongst this one\'s estate!</i></span>';        else          $pirate_dialog .= '</p><p><span class="size7"><i>This one be loaded with Pieces of Eight, devil a doubt!  What say ye we take that booty next the opportunity presents itself?</i></span>';      }      require_once 'commons/statlib.php';      record_stat($user['idnum'], 'Gave in to the Moat Pirates\' Demands', 1);    }  }}require 'commons/html.php';?> <head>  <title><?= $SETTINGS['site_name'] ?> &gt; <?= $user["display"] ?>'s House &gt; Moat</title><?php include "commons/head.php"; ?> </head> <body><?php include 'commons/header_2.php'; ?>     <h4><a href="/myhouse.php"><?= $user['display'] ?>'s House</a> &gt; Moat</h4><?phpecho $message;room_display($house);if(strlen($descript) > 0)  echo $descript;if($pirate_tribute > 0){  echo '<img src="/gfx/npcs/moatpirates.png" alt="(Moat Pirates)" width="350" height="175" align="right" />';  include 'commons/dialog_open.php';  $item = get_item_byid($pirate_tribute);?><p><?= $pirate_talk[array_rand($pirate_talk)] ?> This be our moat now, land lubber!</p><p>Of c'urse, we may be convinced t' carry on our plunderin' elsewhere... <strong><em>but not fer less than a single <a href="/encyclopedia2.php?i=<?= $pirate_tribute ?>"><?= $item['itemname'] ?></a>!  <?= $pirate_talk[array_rand($pirate_talk)] ?></strong></em></p><?php  include 'commons/dialog_close.php';  $command = 'SELECT idnum FROM monster_inventory WHERE user=' . quote_smart($user['user']) . ' AND location=\'storage\' AND itemname=' . quote_smart($item['itemname']) . ' LIMIT 1';  $my_item = fetch_single($command, $url);?><p><i>(You can only give the Moat Pirates items from your Storage.)</i></p><?php  if($my_item !== false)    echo '<ul><li><a href="' . $url . '?action=paytribute">Give them what they want</a></li></ul>';}else{  if(strlen($pirate_dialog) > 0)  {    echo '<img src="/gfx/npcs/moatpirates.png" alt="(Moat Pirates)" width="350" height="175" align="right" />';    include 'commons/dialog_open.php';    echo '<p>' . $pirate_talk[array_rand($pirate_talk)] . ' ' . $pirate_dialog . ' ' . $pirate_talk[array_rand($pirate_talk)] . '</p>';    include 'commons/dialog_close.php';  }  if($number_of_fish == 0)    $fish_caught = 'You haven\'t managed to catch a single one yet.';  else if($number_of_fish == 1)    $fish_caught = 'So far you\'ve only managed to catch one.';  else if($number_of_fish < 100)    $fish_caught = 'So far you\'ve caught ' . $number_of_fish . '.';  else    $fish_caught = 'So far you\'ve caught ' . $number_of_fish . '!  How many can there be in there?  Seriously.';?>     <p>Gators patrol the moat.  You feel safer already!</p>     <p>The moat appears to also have been populated with fish.  Somehow.  <?= $fish_caught ?></p><?php  if($have_fishingpole)  {    if($jerky > 0)    {      if(strlen($descript) == 0)        echo "     <p>You can fish the moat using a Fishing Pole.  This will use 1 Jerky (for bait).</p>\n";?>     <p>You have <?= $jerky ?> Jerk<?= $jerky != 1 ? "ies" : "y" ?> at home to use.</p>     <form action="<?= $url ?>" method="post">     <p>Jerky: <input type="hidden" name="action" value="fish" /><input name="quantity" value="1" maxlength="<?= strlen($jerky) ?>" size="3" /> <input type="submit" value="Go Fish" /></p>     </form><?php    }    else    {?>     <p>You could fish the moat using that Fishing Pole you have at home, but alas you have no bait.</p><?php    }  }}?><?php include 'commons/footer_2.php'; ?> </body></html>