<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/commons/init.php';

$require_login = 'no';
$invisible = 'yes';

require_once 'commons/dbconnect.php';
require_once 'commons/sessions.php';
require_once 'commons/rpgfunctions.php';
require_once 'commons/encryption.php';
require_once 'commons/formatting.php';
require_once 'commons/userlib.php';
require_once 'commons/messages.php';
require_once 'commons/loginlib.php';

$PAGE['force_ad'] = true;

$command = 'SELECT COUNT(idnum) AS c FROM monster_users WHERE lastactivity>=' . ($now - (5 * 60));
$data = $database->FetchSingle($command, 'adminstats.php');

$active_count = $data['c'];

include 'commons/html.php';
?>
 <head>
  <title><?= $SETTINGS['site_name'] ?> &gt; Home</title>
<?php include 'commons/head.php'; ?>
  <link rel="alternate" href="/rss_news.xml" type="application/rss+xml" title="PsyPets Latest News" />
  <link rel="signup" href="/signup.php" title="Sign Up" />
  <link rel="encyclopedia" href="/encyclopedia.php" title="Item Encyclopedia" />
  <link rel="pet-encyclopedia" href="/petencyclopedia.php" title="Pet Encyclopedia" />
  <link rel="stylesheet" href="//<?= $SETTINGS['static_domain'] ?>/styles/pp_markup.css" />
  <link rel="browser-game-info" href="<?= $SETTINGS['protocol'] ?>://www.psypets.net/bghxml.xml" />
 </head>
 <body>
<?php
include 'commons/header_2.php';

if(strlen($_GET['msg']) > 0)
  $error_message .= form_message(explode(',', $_GET['msg']));

if($error_message) echo '<p class="failure">' . $error_message . '</p>';

$general_post = $database->FetchSingle('
  SELECT *
  FROM psypets_news
  WHERE `category` IN (\'routine\', \'important\', \'severe\', \'broadcast\', \'comic\', \'event\')
  ORDER BY idnum DESC
  LIMIT 1
');

$sender = get_user_byid($general_post['author']);

$category = $general_post['category'];

// hack "terrible downtime" messages for all of PsyPets here:
?>
<?php
// -----------------------------------------------------------------------------

$npc = floor($now / (24 * 60 * 60)) % 3 + 1;

if($now_month == 1 && $now_day == 18 && $now_year == 2012)
  include 'commons/index/nocopapipa.php';
else
  include 'commons/index/' . $npc . '.php';
?>
    <h4 style="clear:both;">Latest News <a href="/rss_news.xml"><img src="<?= $SETTINGS['protocol'] ?>://saffron.psypets.net/gfx/famfamfam/feed.png" width="16" height="16" alt="RSS Feed" class="inlineimage" /></a></h4>
    <div id="latestnews" class="cityhallpost category_<?= $category ?>">
     <h5><?= format_text($general_post['subject']) ?></h5>
		 <div style="max-height:500px; overflow:auto; padding:0;">
      <p><?= format_text($general_post['message']) ?></p>
		 </div>
     <p class="dim nomargin" style="border-top:1px solid rgba(0, 0, 0, 0.5);"><i>&mdash; posted by <?= $sender['display'] ?> on <?= local_time($general_post['date'], 0, 'no') ?> UTC</i></p>
    </div>
<?php include 'commons/footer_2.php'; ?>
 </body>
</html>
