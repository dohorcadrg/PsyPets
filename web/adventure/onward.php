<?php
require_once 'commons/init.php';

$require_petload = 'no';

// confirm the session...
require_once 'commons/dbconnect.php';
require_once 'commons/rpgfunctions.php';
require_once 'commons/sessions.php';
require_once 'commons/formatting.php';
require_once 'commons/adventurelib.php';

$adventure = get_adventure($user['idnum']);

if($adventure !== false && $adventure['progress'] >= $adventure['difficulty'])
{
  if($adventure['prize'] == '')
  {
		delete_adventure($user['idnum']);
		create_adventure($user['idnum'], $adventure['level']);
  }
}

header('Location: /adventure/');
?>