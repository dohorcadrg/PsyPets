<?php$require_login = 'no';$invisible = 'yes';require_once 'commons/dbconnect.php';require_once 'commons/sessions.php';require_once 'commons/rpgfunctions.php';require_once 'commons/encryption.php';require_once 'commons/formatting.php';require_once 'commons/globals.php';require_once 'commons/houselib.php';require_once 'commons/petlib.php';require_once 'commons/timezones.php';if($NO_LOGIN){  header('Location: /');  exit();}$petgfx = get_global('petgfx');$avatargfx = get_global('avatargfx');$thispic = $petgfx[array_rand($petgfx)];$thisavatar = $avatargfx[array_rand($avatargfx)];if(strlen($_POST['submit']) == 0){  $_POST['picture'] = $thispic;  $_POST['avatar'] = $thisavatar;  // 'cause that's where I live  $_POST['timezone'] = -5;}else if($_POST['submit'] == 'Signup'){  $errored = false;  if(!in_array($_POST['picture'], $petgfx))  {    $_POST['picture'] = $thispic;    $picture_message = '<span class="failure">You forgot to select your pet\'s appearance.</span>';    $errored = true;  }  if(!in_array($_POST['avatar'], $avatargfx))  {    $_POST['avatar'] = $thisavatar;    $avatar_message = '<span class="failure">You forgot to select an avatar.</span>';    $errored = true;  }  $_POST['username'] = trim($_POST['username']);  if(strlen($_POST['username']) > 16 || strlen($_POST['username']) < 3)  {    $user_message = "Your login name must be between 3 and 16 characters.";    $errored = true;  }  else if(preg_match("/[^a-zA-Z0-9_]/", $_POST["username"]))  {    $user_message = "Please only use alphanumeric characters (or underscore; no spaces).";    $errored = true;  }  else  {    $matches = get_user_byuser($_POST['username']);    if($matches !== false)    {      $user_message = 'This login name has already been taken.';      $errored = true;    }  }    if(strlen($_POST['pass1']) < 6)  {    $pass_message = 'Your password must be at least 6 characters, but it can be <em>any length you want</em>.  So go on: get crazy with it.';    $_POST['pass1'] = '';    $_POST['pass2'] = '';    $errored = true;  }  else if($_POST['pass1'] != $_POST['pass2'])  {    $pass_message = 'Your passwords do not match.';    $_POST['pass1'] = '';    $_POST['pass2'] = '';    $errored = true;  }  $_POST['email'] = trim($_POST['email']);  $_POST['email2'] = trim($_POST['email2']);  if(strlen($_POST["email"]) == 0)  {    $email_message = "You must have an e-mail address to confirm your account.";    $_POST["email"] = "";    $errored = true;  }  else if($_POST["email"] != $_POST["email2"])  {    $email_message = "Your e-mail addresses did not match.";    $_POST['email'] = '';    $_POST['email2'] = '';    $errored = true;  }  else if(strpos($_POST['email'], '@psypets.net'))  {    $email_message = 'That e-mail address is already in use.';    $errored = true;  }  else  {    $command = 'SELECT email FROM monster_users ' .               'WHERE email=' . quote_smart($_POST["email"]) . " OR newemail=" . quote_smart($_POST["email"]) . " LIMIT 1";    $matches = $database->FetchSingle($command, 'signup.php');    if($matches !== false)    {      $email_message = 'That e-mail address is already in use.';      $errored = true;    }  }  $_POST['display'] = trim($_POST['display']);  if(strlen($_POST['display']) < 2 || strlen($_POST['display']) > 24)  {    $display_message = 'Your resident name must be between 2 and 24 characters.';    $errored = true;  }  else if(preg_match("/[^a-zA-Z0-9Ç-¦_ .!?~'-]/", $_POST["display"]))  {    $display_message = "Please only use alphanumeric characters (or some punctuation)";    $errored = true;  }  else if(preg_match("/[^a-zA-Z]/", $_POST['display']{0}))  {    $display_message = "Your resident name must start with a letter.";    $errored = true;  }  else  {    $matches = get_user_bydisplay($_POST['display']);    if($matches !== false)    {      $display_message = "That resident name is already in use.  Sorry :(";      $errored = true;    }  }  if(strlen($_POST['petname']) > 32 || strlen($_POST['petname']) < 2)  {    $petname_message = 'Your pet\'s name must be between 2 and 32 characters.';    $errored = true;  }  else if(preg_match('/[\0\b\n\r\t]/', $_POST['petname']))  {    $petname_message = 'The following characters may not be used: new line, backspace, tab, NULL.';    $errored = true;  }  if($_POST['gender'] != 'male' && $_POST['gender'] != 'female')  {    $gender_message = 'You need to choose either a male or female pet.';    $errored = true;  }  if(is_numeric($_POST['timezone']))  {    if($_POST['timezone'] < -12 || $_POST['timezone'] > 13)    {      $timezone_message = "Pick a time-zone that <i>makes sense</i>, please.";      $errored = true;    }  }  else  {    $timezone_message = 'Pick a time-zone that <em>makes sense</em>, please.';    $errored = true;  }  $year = (int)$_POST['dob_year'];  $month = (int)$_POST['dob_month'];  $day = (int)$_POST['dob_day'];  if($year < 1900 || $day == 0 || checkdate($month, $day, $year) === false)  {    $birthday_error = 'This is not a valid date.';    $errored = true;  }  else if(mktime(0, 0, 0, $month, $day, $year) > $now)  {    $birthday_error = 'You can\'t have been born in the future... &gt;_&gt;';    $errored = true;  }  else if(($now - mktime(0, 0, 0, $month, $day, $year)) / (60 * 60 * 24 * 365) < 13)  {    $birthday_error = 'Children under the age of 13 may not sign up.  Didn\'t you read the <a href="termsofservice.php">Terms of Service</a>?';    $errored = true;  }  else    $birthdate = $year . '-' . ($month < 10 ? "0$month" : $month) . '-' . ($day < 10 ? "0$day" : $day);  if($_POST['personality'] < 1 || $_POST['personality'] > 4)  {    $errored = true;    $survey_errors[] = 'You forgot to describe your pet\'s personality!';  }  if($_POST['physical'] < 1 || $_POST['physical'] > 3)  {    $errored = true;    $survey_errors[] = 'You forgot to describe your pet\'s physical abilities!';  }  if($_POST['mental'] < 1 || $_POST['mental'] > 3)  {    $errored = true;    $survey_errors[] = 'You forgot to describe your pet\'s mental abilities!';  }  if(count($_POST['skill']) < 2)    $survey_errors[] = 'You forgot to pick two skills and/or knowledges for your pet!';  else if(count($_POST['skill']) > 2)    $survey_errors[] = 'You may only pick two skills and/or knowledges...';  else if($_POST['skill'][0] < 1 || $_POST['skill'][0] > 7 || $_POST['skill'][1] < 1 || $_POST['skill'][1] > 7 || $_POST['skill'][0] == $_POST['skill'][1])  {    $errored = true;    $survey_errors[] = 'You forgot to pick two skills and/or knowledges for your pet!';  }  if(!$errored)  {    $str = 1;    $dex = 1;    $sta = 1;    $int = 1;    $per = 1;    $wit = 1;    $open = 1;    $extraverted = 1;    $agreeable = 1;    $conscientious = 1;    $brawling = 0;    $athletics = 0;    $stealth = 0;    $survival = 0;    $gathering = 0;    $fishing = 0;    $mining = 0;    $crafting = 0;    $painting = 0;    $carpentry = 0;    $jeweling = 0;    $sculpting = 0;    $electrical_engineering = 0;    $mechanical_engineering = 0;    $chemistry = 0;    $smithing = 0;    $tailoring = 0;    $binding = 0;    $piloting = 0;    if($_POST['personality'] == 1)      $open++;    else if($_POST['personality'] == 2)      $extraverted++;    else if($_POST['personality'] == 3)      $agreeable++;    else if($_POST['personality'] == 4)      $conscientious++;    if($_POST['physical'] == 1)      $str++;    else if($_POST['physical'] == 2)      $dex++;    else if($_POST['physical'] == 3)      $sta++;    if($_POST['mental'] == 1)      $int++;    else if($_POST['mental'] == 2)      $per++;    else if($_POST['mental'] == 3)      $wit++;    foreach($_POST['skill'] as $skill)    {      if($skill == 1)        $brawling++;      else if($skill == 2)        $athletics++;      else if($skill == 3)        $stealth++;      else if($skill == 4)        $survival++;      else if($skill == 5)        $gathering++;      else if($skill == 6)        $fishing++;      else if($skill == 7)        $mining++;/*      else if($skill == 9)        $crafting++;      else if($skill == 10)        $painting++;      else if($skill == 11)        $carpentry++;      else if($skill == 12)        $jeweling++;      else if($skill == 13)        $sculpting++;      else if($skill == 14)        $electrical_engineering++;      else if($skill == 15)        $mechanical_engineering++;      else if($skill == 16)        $chemistry++;      else if($skill == 17)        $smithing++;      else if($skill == 18)        $tailoring++;      else if($skill == 19)        $binding++;      else if($skill == 20)        $piloting++;*/    }    $activekey = rand(100000, 999999);    $now = time();    $command = 'INSERT INTO `monster_users` ' .               '(`user`, `pass`, `display`, `readtos`, `email`, `birthday`, `graphic`, `disabled`, `activated`, `signupdate`, `activateid`, `sessionid`, `lastactivity`, `logintime`, `license`, `openstore`, `money`, `savings`, `newevent`, `timezone`) ' .               'VALUES ' .               '(' . quote_smart($_POST['username']) . ', ' . quote_smart(md5($_POST['pass1'])) . ', ' . quote_smart($_POST['display']) . ', \'yes\'' .               ', ' . quote_smart($_POST['email']) . ', ' . quote_smart($birthdate) . ', ' . quote_smart($_POST['avatar']) . ", 'no', 'no', '$now'" .               ", '$activekey', '0', '0', '0', 'no', 'no', '0', '0', 'no'" .               ', ' . quote_smart($_POST['timezone']) . ')';    $database->FetchNone($command, 'adding user account');    $idnum = $database->InsertID();    $database->FetchNone('INSERT INTO psypets_badges (userid) VALUES (' . $idnum . ')');    $bloodtype = random_blood_type();    $stats = array(      $extraverted, $open, $agreeable, $conscientious,      $str, $dex, $sta, $per, $int, $wit,      $brawling, $athletics, $stealth, $survival,      $gathering, $fishing, $mining, $crafting,      $painting, $carpentry, $jeweling, $sculpting,      $electrical_engineering, $mechanical_engineering, $chemistry,      $smithing, $tailoring, $binding, $piloting    );    require_once 'commons/flavorlib.php';    list($likes_flavor, $dislikes_flavor) = array_rand($FLAVORS, 2);    $favorite_color = $COLORS[array_rand($COLORS)];    $database->FetchNone('      INSERT INTO `monster_pets`      (        `user`, `petname`, `birthday`,        `gender`, `prolific`, `bloodtype`, `graphic`,        `energy`, `food`, `safety`, `love`, `esteem`,        `last_check`,        `extraverted`, `open`, `agreeable`, `conscientious`,        `str`, `dex`, `sta`, `per`, `int`, `wit`,        `bra`, `athletics`, `stealth`, `sur`,        `gathering`, `fishing`, `mining`, `cra`,        `painting`, `carpentry`, `jeweling`, `sculpting`,        `eng`, `mechanics`, `chemistry`,        `smi`, `tai`, `binding`, `pil`,        `likes_flavor`, `dislikes_flavor`, `likes_color`      )      VALUES      (        ' . quote_smart($_POST['username']) . ', ' . quote_smart($_POST['petname']) . ', ' . $now . ',        ' . quote_smart($_POST['gender']) . ', \'no\', ' . quote_smart($bloodtype) . ', ' . quote_smart($_POST['picture']) . ',        12, 15, 15, 18, 18,        ' . $now . ',        ' . implode(', ', $stats) . ',        ' . $likes_flavor . ', ' . $dislikes_flavor . ', ' . quote_smart($favorite_color) . '      )    ');    add_house($idnum, 1, 50);    $message = '      <html><body style="font-family: Arial; font-size: 15px;">      <p>You have registered for PsyPets with the login name ' . $_POST['username'] . ', however your account still needs to be activated!</p>      <p>Your activation key is "' . $activekey . '" (without the quotes).</p>      <p>To activate your account, visit <a href="http://www.psypets.net/activate.php">http://www.psypets.net/activate.php</a> and type in your login name and activation key, or use this link to do it automatically: <a href="http://www.psypets.net/activate.php?user=' . $_POST['username'] . '&amp;activate=' . $activekey . '">http://www.psypets.net/activate.php?user=' . $_POST['username'] . '&amp;activate=' . $activekey . '</a></p>      <p>Once your account has been activated you will no longer need the activation key.</p>      <p><center>&diams; &diams; &diams; &diams; &diams;</center></p>      <p>PsyPets has an in-game mailing system.  You have been sent an introductory mail in-game which answers many of the most commonly asked questions, such as "How do I make money?", and explains how some of the basic game mechanics work.</p>      <p>Please read this mail!</p>      <p>Your in-game mail is found in your Mailbox, a link for which will be on the left of the screen once you have logged in.  There will also be an envelope icon in the upper-left, notifying you of unread mail, which can be clicked to take you to your Mailbox.</p>      <p><center>&diams; &diams; &diams; &diams; &diams;</center></p>      <p>Parents of young children may be interested in Content Control, as the public discussion boards are not always appropriate for all age groups!  After logging in, visit the "My Account" page (the link for which will be in the top-left area of the page).  From there, look for the "Content Control" section.</p>      </body></html>    ';    mail($_POST['email'], 'PsyPets account activation', $message, "MIME-Version: 1.0\nContent-type: text/html; charset=utf-8\nFrom: " . $SETTINGS['site_mailer']);    $age = ($now - mktime(0, 0, 0, $month, $day, $year)) / (60 * 60 * 24 * 365);    if($age < 14)      header('Location: ./signupsuccess_minor.php');    else      header('Location: ./signupsuccess.php');    exit();  }}if($_GET['notos'] == '1'){  $signupform_style = '';  $termsofservice_style = 'display: none;';}else{  $signupform_style = 'display: none;';  $termsofservice_style = '';}include 'commons/html.php';?> <head>  <title>PsyPets &gt; Sign Up</title><?php include 'commons/head.php'; ?>  <script type="text/javascript">   function readtos()   {     $('#termsofservice').hide();     $('#signupform').show();     window.scrollTo(0, 0);   }      $(function() {     $('#username').blur(function(e) {       username = $.trim($('#username').val());       $('#username').val(username);     });   });  </script> </head> <body><?php include 'commons/header_2.php'; ?>    <div id="termsofservice" style="<?= $termsofservice_style ?>">    <?php include 'commons/tos.php'; ?>    <p>To play PsyPets, you must read and agree to these Terms of Service.</p>    <p>If you are not willing to read or agree to these Terms of Service, do not sign up for an account.</p>    <p><input type="button" value="Agreed!" onclick="readtos()" /></p>    </div>    <div id="signupform" style="<?= $signupform_style ?>">    <h4>Sign Up</h4>    <p>After sign-up you will receive an e-mail with instructions on how to activate your account.  (Parents of young children: be sure to read this e-mail for information on enabling Content Control.)  Be sure your spam filters will not block mail from <b><?= $SETTINGS["site_mailer"] ?></b>!  If they do you will not be able to activate your account.</p>    <p>For information about how your e-mail address and other information you submit about yourself may be used, please read the <a href="/meta/privacy.php">Privacy Policy</a>.</p><?phpif($errored)  echo '<ul><li><p class="failure">There were some errors!  Check below for more details.</p></li></ul>';?>  <table>   <form action="signup.php?notos=1" method="post" name="signup" id="signup" novalidate><?php if($general_message) { ?>   <tr>    <td colspan=2><span class="failure"><?= $general_message ?></span></td>   </tr><?php } ?>   <tr class="titlerow">    <td colspan=2 align="center"><h4>Login Information</td>    <td>&nbsp;</td>   </tr>   <tr>    <td bgcolor="#f0f0f0">Login&nbsp;name:</td>    <td><input name="username" maxlength="16" id="username" value="<?= $_POST["username"] ?>" /></td><?phpif($user_message)  echo "    <td><span class=\"failure\">" . $user_message . "</span></td>\n";else  echo "    <td>You will use this name to log in to PsyPets.  It is hidden from other players.</td>\n";?>   </tr>   <tr>    <td bgcolor="#f0f0f0">&nbsp;</td>    <td colspan="2">&nbsp;</td>   </tr>   <tr>    <td bgcolor="#f0f0f0">Password:</td>    <td><input name="pass1" type="password" value="<?= $_POST["pass1"] ?>" /></td><?phpif($pass_message)  echo "    <td><span class=\"failure\">" . $pass_message . "</span></td>\n";else  echo "    <td>&nbsp;</td>\n";?>   </tr>   <tr>    <td bgcolor="#f0f0f0"><i>Confirm:</i></td>    <td><input name="pass2" type="password" value="<?= $_POST["pass2"] ?>" /></td>    <td>Enter your password again.</td>   </tr>   <tr>    <td bgcolor="#f0f0f0">&nbsp;</td>    <td colspan="2">&nbsp;</td>   </tr>   <tr>    <td bgcolor="#f0f0f0">E-mail:</td>    <td><input name="email" type="email" maxlength="48" value="<?= $_POST["email"] ?>" /></td><?phpif($email_message)  echo "    <td><span class=\"failure\">" . $email_message . "</span></td>\n";else  echo "    <td>Used to confirm your account and e-mail lost passwords.</td>\n";?>   </tr>   <tr>    <td bgcolor="#f0f0f0"><i>Confirm:</i></td>    <td><input name="email2" type="email" maxlength="48" value="<?= $_POST["email2"] ?>" /></td>    <td>Enter your e-mail address again.</td>   </tr>   <tr>    <td bgcolor="#f0f0f0">&nbsp;</td>    <td colspan="2">&nbsp;</td>   </tr>   <tr>    <td bgcolor="#f0f0f0">Birthday:</td>    <td>     <select name="dob_month">      <option value="1"<?= $month == 1 ? " selected" : "" ?>>Jan</option>      <option value="2"<?= $month == 2 ? " selected" : "" ?>>Feb</option>      <option value="3"<?= $month == 3 ? " selected" : "" ?>>Mar</option>      <option value="4"<?= $month == 4 ? " selected" : "" ?>>Apr</option>      <option value="5"<?= $month == 5 ? " selected" : "" ?>>May</option>      <option value="6"<?= $month == 6 ? " selected" : "" ?>>Jun</option>      <option value="7"<?= $month == 7 ? " selected" : "" ?>>Jul</option>      <option value="8"<?= $month == 8 ? " selected" : "" ?>>Aug</option>      <option value="9"<?= $month == 9 ? " selected" : "" ?>>Sep</option>      <option value="10"<?= $month == 10 ? " selected" : "" ?>>Oct</option>      <option value="11"<?= $month == 11 ? " selected" : "" ?>>Nov</option>      <option value="12"<?= $month == 12 ? " selected" : "" ?>>Dec</option>     </select>&nbsp;<input maxlength=2 size=2 name="dob_day" value="<?= $day ?>" />,&nbsp;<input maxlength=4 size=4 name="dob_year" value="<?= $year ?>" />    </td><?phpif($birthday_error)  echo "    <td><span class=\"failure\">" . $birthday_error . "</span></td>\n";else  echo "    <td>&nbsp;</td>\n";?>   </tr>   <tr>    <td><p>&nbsp;</p></td>    <td colspan="2">&nbsp;</td>   </tr>   <tr class="titlerow">    <td colspan="2" align="center"><h4>Resident Information</td>    <td>&nbsp;</td>   </tr>   <tr>    <td bgcolor="#f0f0f0">Resident&nbsp;name:</td>    <td><input name="display" maxlength="24" value="<?= $_POST["display"] ?>" /></td><?phpif($display_message)  echo '    <td><span class="failure">' . $display_message . '</span></td>';else  echo '    <td>This is the name other people will see in-game.  You can use spaces, hyphens, and most other characters.</td>';?>   </tr>   <tr>    <td bgcolor="#f0f0f0" valign="top">Avatar:</td>    <td>     <iframe src="pickavatar.php?sel=<?= $_POST['avatar'] ?>" width="250" height="384" style="border: 1px solid black;"></iframe>     <input type="hidden" name="avatar" id="avatar" value="<?= $_POST['avatar'] ?>" />    </td>    <td valign="top"><?php if($avatar_message) echo $avatar_message; else echo 'In-game messages and forum posts you make will be accompanied by this graphic.'; ?></td>   </tr>   <tr>    <td bgcolor="#f0f0f0">Time&nbsp;Zone:</td>    <td>     <select name="timezone"><?php foreach($timezones as $value=>$name) {?>      <option value="<?= $value ?>"<?= $_POST['timezone'] == $value ? ' selected' : '' ?>><?= $name ?></option><?php }?>     </select>    </td><?phpif($timezone_message)  echo '    <td><span class="failure">' . $timezone_message . '</span></td>';else  echo '    <td>For displaying local times.</td>';?>   </tr>   <tr>    <td>&nbsp;</td>    <td colspan="2">&nbsp;</td>   </tr>   <tr class="titlerow">    <td colspan="2" align="center"><h4>Pet Information</td>    <td>&nbsp;</td>   </tr>   <tr>    <td bgcolor="#f0f0f0"><p>Name:</p></td>    <td><input name="petname" value="<?= $_POST['petname'] ?>"></td><?phpif($petname_message)  echo "    <td><span class=\"failure\">" . $petname_message . "</span></td>\n";else  echo "    <td>Your pet's name can use spaces, hyphens, and many other characters, even those from other alphabets (Japanese, Russian, etc).</td>\n";?>   </tr>   <tr>    <td bgcolor="#f0f0f0">Gender:</td>    <td>     <p>      <input name="gender" type="radio" value="male" checked />Male&nbsp;&nbsp;<input name="gender" type="radio" value="female" <?= ($_POST["gender"]  == "female" ? "checked" : "") ?> />Female     </p>    </td><?phpif($gender_message)  echo '    <td><span class="failure">' . $gender_message . '</span></td>';else  echo '    <td>&nbsp;</td>';?>   </tr>   <tr>    <td bgcolor="#f0f0f0">&nbsp;</td>    <td colspan="2">&nbsp;</td>   </tr>   <tr>    <td valign="top" bgcolor="#f0f0f0">Appearance:</td>    <td valign="top">     <iframe src="pickpet.php?sel=<?= $_POST['picture'] ?>" width="250" height="384" style="border: 1px solid black;"></iframe>     <input type="hidden" name="picture" id="picture" value="<?= $_POST['picture'] ?>">    </td>    <td valign="top">The pet graphic does not affect its abilities in any way. (ex: a small pet graphic does not mean you will have a small pet, wings do not mean the pet will fly, etc.)</td>   </tr>   <tr>    <td>&nbsp;</td>    <td colspan="2">&nbsp;</td>   </tr>   <tr class="titlerow">    <td colspan="2" align="center"><h4>Pet Survey</td>    <td>&nbsp;</td>   </tr>   <tr>    <td bgcolor="#f0f0f0">&nbsp;</td>    <td colspan="2"><?phpif(count($survey_errors) > 0)  echo '<ul><li class="failure">' . implode('</li><li class="failure">', $survey_errors) . '</li></ul>';$SIGN_UP = true;require 'commons/petsurvey.php';?>    </td>   </tr>   <tr>    <td>&nbsp;</td>    <td colspan="2">&nbsp;</td>   </tr>   <tr>    <td colspan="3" align="center"><input type="submit" name="submit" value="Signup" style="width:100px;"></td>   </tr>   </form>  </table>    </div><?php include 'commons/footer_2.php'; ?> </body></html>