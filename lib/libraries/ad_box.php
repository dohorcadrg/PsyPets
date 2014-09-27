<?php
function get_ad_text()
{
  global $PAGE, $SETTINGS;

  list($now_day, $now_month, $now_year) = explode(' ', date('j n Y'));

  $i = mt_rand(1, 100);

  global $_GET;
  if($i <= 5) // player ads (5% frequency)
  {
    switch(mt_rand(1, 3))
    {
      case 1:
        $PAGE['checkad'] = true;
        $PAGE['adname'] = 'iamsooooimportant';
        $PAGE['adlink'] = '<a href="http://www.facebook.com/pages/PsyPets/10560487070">PsyPets Facebook page</a>';

        $files = array('fb.png');
        $day_since_1970 = floor($now / (24 * 60 * 60));
        return '<a href="http://www.facebook.com/pages/PsyPets/10560487070"><img src="' . $SETTINGS['protocol'] . '://saffron.psypets.net/gfx/ads/' . $files[$day_since_1970 % count($files)] . '" width="234" height="60" alt="PsyPets Facebook page" id="iamsooooimportant" /></a>';

      case 2:
        $PAGE['checkad'] = true;
        $PAGE['adname'] = 'iamsooooimportant';
        $PAGE['adlink'] = '<a href="http://www.zazzle.com/telkoth*">PsyPets merchandise at zazzle.com</a>';

        $files = array('zazzlestore.png');
        $day_since_1970 = floor($now / (24 * 60 * 60));

        return '<a href="http://www.zazzle.com/telkoth*"><img src="' . $SETTINGS['protocol'] . '://saffron.psypets.net/gfx/ads/' . $files[$day_since_1970 % count($files)] . '" width="234" height="60" alt="PsyPets merchandise at zazzle.com" id="iamsooooimportant" /></a>';

      case 3:
        $PAGE['checkad'] = true;
        $PAGE['adname'] = 'iamsooooimportant';
        $PAGE['adlink'] = '<a href="http://twitter.com/#!/psypetsideas">PsyPets ideas @ twitter</a>';

        $files = array('twitter.png');
        $day_since_1970 = floor($now / (24 * 60 * 60));

        return '<a href="http://twitter.com/#!/psypetsideas"><img src="http://saffron.psypets.net/gfx/ads/' . $files[$day_since_1970 % count($files)] . '" width="234" height="60" alt="PsyPets ideas @ twitter" id="iamsooooimportant" /></a>';
    }
  }
  else // Monthly ad (20% frequency)
  {
    $monthly_files = array(
      1 => array(
        array('%M_all.png', 'af_getrare2.php'),
        array('%M_all_2.png', 'af_getrare2.php'),
        array('%M_all_3.png', 'af_getrare2.php'),
      ),
      2 => array(
        array('%M_all.png', 'af_getrare2.php'),
        array('%M_all_2.png', 'af_getrare2.php'),
        array('%M_all_3.jpg', 'af_getrare2.php'),
        array('%M_note.png', 'af_getrare2.php'),
      ),
      3 => array(
        array('%M_all.png', 'af_getrare2.php'),
        array('%M_all_2.png', 'af_getrare2.php'),
        array('%M_all_3.png', 'af_getrare2.php'),
      ),
      4 => array(
        array('%M_all.png', 'af_getrare2.php'),
        array('%M_all_2.png', 'af_getrare2.php'),
      ),
      5 => array(
        array('%M_all.png', 'af_getrare2.php'),
        array('%M_all_2.png', 'af_getrare2.php'),
        array('%M_all_3.png', 'af_getrare2.php'),
      ),
      6 => array(
        array('%M_all.png', 'af_getrare2.php'),
        array('%M_all_2.png', 'af_getrare2.php'),
        array('%M_all_3.png', 'af_getrare2.php'),
      ),
      7 => array(
        array('%M_all.png', 'af_getrare2.php'),
        array('%M_all_2.png', 'af_getrare2.php'),
        array('%M_all_3.png', 'af_getrare2.php'),
      ),
      8 => array(
        array('%M_all.png', 'af_getrare2.php'),
        array('%M_all_2.png', 'af_getrare2.php'),
      ),
      9 => array(
        array('%M_all.png', 'af_getrare2.php'),
        array('%M_all_2.png', 'af_getrare2.php'),
      ),
      10 => array(
        array('%M_all.png', 'af_getrare2.php'),
        array('%M_all_2.png', 'af_getrare2.php'),
      ),
      11 => array(
        array('%M_all.png', 'af_getrare2.php'),
        array('%M_all_2.png', 'af_getrare2.php'),
        array('%M_all_3.png', 'af_getrare2.php'),
      ),
      12 => array(
        array('%M_all.png', 'af_getrare2.php'),
        array('%M_all_2_2.png', 'af_getrare2.php'),
      ),
    );

    $i = mt_rand(0, 4);

    if($i == 0)
    {
      $url = 'autofavor.php';
      $PAGE['adlink'] = '<a href="/autofavor.php">PsyPets Favors</a>';
      $img = 'generic3.png';
    }
    else if($i == 1 && $now_month == 10)
    {
      $url = 'af_combinationstation3.php?costume=yes';
      $PAGE['adlink'] = '<a href="/af_combinationstation2.php?costume=yes">make a custom costume</a>';
      $img = 'halloween.png';
    }
    else
    {
      $this_month = $monthly_files[$now_month];

      if(($now_month == 12 && $now_day >= 12) || $now_month == 1)
        $this_month[] = array('leydenjar.png', 'specialoffer_smith.php');

      $ad = $this_month[array_rand($this_month)];

      $img = str_replace('%M', strtolower(date('F')), $ad[0]);
      $url = $ad[1];

      $PAGE['adlink'] = '<a href="/' . $url . '">this month\\\'s items</a>';

    }

    $PAGE['checkad'] = true;
    $PAGE['adname'] = 'iamsooooimportant';

    return '<a href="/' . $url . '"><img src="' . $SETTINGS['protocol'] . '://saffron.psypets.net/gfx/ads/' . $img . '" width="234" height="60" id="iamsooooimportant" /></a>';
  }
}
?>