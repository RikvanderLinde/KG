<?php

session_start();

require_once('functions/functions.php');


class TED_TEDSystemDev_Overview {
// class TED_TEDSystem_Overview {
  public static function showOverview(){
  $version = 'tedsystemdev';
  // $version = 'tedsystem';


    $db = XenForo_Application::get('db');
    $visitor = XenForo_Visitor::getInstance();

    $modtools_permissions = array('Technician'=>3,
                                  'Clanleader'=>85,
                                  'Officer'=>86,
                                  'Supervisor'=>144,
                                  'Modleader'=>87,
                                  'Moderator'=>4);

    $debugger = False;
    $modtools = False;
    if (in_array($visitor->display_style_group_id, array(3))) $debugger = True;
    if (in_array($visitor->display_style_group_id, $modtools_permissions)) $modtools = True;
    if (in_array($visitor->user_group_id, $modtools_permissions)) $modtools = True;

    if (isset($_REQUEST['modtools'])) {
      if ($_REQUEST['modtools']==0) $_SESSION['modtools'] = 0;
      if ($_REQUEST['modtools']==1) $_SESSION['modtools'] = 1;
    }

    if(isset($_SESSION['modtools']) && $_SESSION['modtools']==0) {
      $modtools = False;
      $debugger = True;
    }


    // Make trials > month inactive guest again.

    // if ($debugger) {

    //   $outdated = $db->fetchAll("SELECT user_id,username,user_group_id
    //                             FROM xf_user
    //                             WHERE user_group_id = '19'
    //                             AND  `last_activity` < '1483038216'");

    //   foreach ($outdated as $outdated_user) {
    //     $sql = "UPDATE  `konvictg_xenweb`.`xf_user` SET  `user_group_id` =  '2', `display_style_group_id` =  '2' WHERE  `xf_user`.`user_id` = ".$outdated_user['user_id'].";";
    //     $db->query($sql);
    //   }

    // }






    $date_error = '';

    if (isset($_POST['form'])) {
      $form = $_POST['form'];

      switch ($form) {
        case 'comment':
          $sql = "INSERT INTO konvictg_xenweb.TEDS_comments (user_id,comment,comment_user) VALUES ('".$_POST['userid']."','".str_replace("'", "''", strip_html($_POST['comment']))."','".$visitor->user_id."');";
          $db->query($sql);

          $url = '/pages/'.$version.'/?user_id='.$_POST['userid'];
          header("Location: ".$url); /* Redirect browser */
          exit();
          break;
        
        case 'game':
          $sql = "UPDATE `konvictg_xenweb`.`TEDS` SET `game` = '".clean_SQL($_POST['game'])."' WHERE `TEDS`.`user_id` = ".$_POST['userid'].";";
          $db->query($sql);
          break;
        
        case 'date':
          $date = $_POST['date'];
          $date = str_replace('/', '-', $date);
          if (validateDate($date, 'd-m-Y') || 
              validateDate($date, 'm-d-Y') || 
              validateDate($date, 'Y-m-d')) {
            $sql = "UPDATE `konvictg_xenweb`.`TEDS` SET `end_date` = '".clean_SQL($date)."' WHERE `TEDS`.`user_id` = ".$_POST['userid'].";";
            $db->query($sql);
          } else {
            $date_error = '<br><span class="error">Invalid date</span>';
          }          
          break;
        
        case 'tsuid':
          $uid = $_POST['uid'];
          $sql = "UPDATE `konvictg_xenweb`.`TEDS` SET `ts_uid` = '".clean_SQL($uid)."' WHERE `TEDS`.`user_id` = ".$_POST['userid'].";";
          $db->query($sql);
          break;
        
        case 'app_link':
          $app_link = $_POST['app_link'];
          $sql = "UPDATE `konvictg_xenweb`.`TEDS` SET `app_link` = '".clean_SQL($app_link)."' WHERE `TEDS`.`user_id` = ".$_POST['userid'].";";
          $db->query($sql);
          break;
        
        default:
          # code...
          break;
      }
    }

    $s = '';

    $s.= '<div class="TED_Overview">';

    if ($modtools) $s.= '<div class="information">
      New version : v1.1.2<br>
      <br>
      New Features:<br>
      - Members can only view their own comments ^TB<br>
      - Members can not longer see score ^Ladi<br>
      - Debug mode ^Ladi
      </div>';

    if (!isset($_SESSION['modtools']) && ($modtools || $debugger)) {
      $s.= '<a class="button debug" href="/pages/'.$version.'/?modtools=0">DEBUG:Disable Moderator Tools</a>';
    } elseif (isset($_SESSION['modtools']) && ($modtools || $debugger)) {
      if ($_SESSION['modtools']==0) $s.= '<a class="button debug" href="/pages/'.$version.'/?modtools=1">Enable Moderator Tools</a>';
      if ($_SESSION['modtools']==1) $s.= '<a class="button debug" href="/pages/'.$version.'/?modtools=0">Disable Moderator Tools</a>';
    }


    if (!isset($_REQUEST['user_id'])) {

      /* ----------------------------------------------------------------- */
      /* -------------------------User overview--------------------------- */
      /* ----------------------------------------------------------------- */

      $users = $db->fetchAll("SELECT user_id,username
                              FROM xf_user
                              WHERE user_group_id = '19'");

      if (isset($_REQUEST['game']) && $_REQUEST['game']) {        
        $tedrecords = $db->fetchAll("SELECT user_id,score,game,end_date
                                     FROM TEDS
                                     WHERE game = '".clean_SQL($_REQUEST['game'])."'");
      } else {
        $tedrecords = $db->fetchAll("SELECT user_id,score,game,end_date
                                     FROM TEDS");
      }
      

      $teds = array();

      $balance = array();
      $game = array();
      $end_date = array();
      foreach ($tedrecords as $tedrecord) {
        $balance[$tedrecord['user_id']] = $tedrecord['score'];
        $game[$tedrecord['user_id']] = $tedrecord['game'];
        $end_date[$tedrecord['user_id']] = new DateTime($tedrecord['end_date']);
      }


      foreach($users as $user) {
        if (!isset($balance[$user['user_id']]) && !isset($_REQUEST['game'])) {
          make_new_ted($user['user_id']);
        } else {
          if (isset($_REQUEST['game']) && $_REQUEST['game']) {
            if (!isset($game[$user['user_id']])) continue;
          }

          $tedinfo = array();
          $tedinfo['user_id'] = $user['user_id'];
          $tedinfo['username'] = strip_html($user['username']);
          if (isset($game[$user['user_id']])) $tedinfo['game'] = $game[$user['user_id']];
          if (isset($balance[$user['user_id']])) $tedinfo['balance'] = $balance[$user['user_id']];
          if (isset($end_date[$user['user_id']])) $tedinfo['end_date'] = $end_date[$user['user_id']]->format('d-m-Y');
          $teds[$user['user_id']] = $tedinfo;
        }
      }


      /* ----------------------------------------------------------------------------------------------------------------------------*/

      $s.= '<p>Hello '.strip_html($visitor->username).'!</p>';
      $s.= '<p>There are currently '.count($teds).' trials active.</p>';


      $s.= '<div class="filter"><form action="/pages/'.$version.'/" method="GET">';
      $s.= 'Game : <select name="game">
                    <option value=""'.(isset($_REQUEST['game'])&&$_REQUEST['game']==''?' selected':'').'>Show everyone</option>
                    <option value="Community Member"'.(isset($_REQUEST['game'])&&$_REQUEST['game']=='Community Member'?' selected':'').'>Community Member</option>
                    <option value="CS:GO"'.(isset($_REQUEST['game'])&&$_REQUEST['game']=='CS:GO'?' selected':'').'>CS:GO</option>
                    <option value="Diablo 3"'.(isset($_REQUEST['game'])&&$_REQUEST['game']=='Diablo 3'?' selected':'').'>Diablo 3</option>
                    <option value="FFXIV"'.(isset($_REQUEST['game'])&&$_REQUEST['game']=='FFXIV'?' selected':'').'>FFXIV</option>
                    <option value="Heroes of the Storm"'.(isset($_REQUEST['game'])&&$_REQUEST['game']=='Heroes of the Storm'?' selected':'').'>Heroes of the Storm</option>
                    <option value="League of Legends"'.(isset($_REQUEST['game'])&&$_REQUEST['game']=='League of Legends'?' selected':'').'>League of Legends</option>
                    <option value="Minecraft"'.(isset($_REQUEST['game'])&&$_REQUEST['game']=='Minecraft'?' selected':'').'>Minecraft</option>
                    <option value="NewZ"'.(isset($_REQUEST['game'])&&$_REQUEST['game']=='NewZ'?' selected':'').'>NewZ</option>
                    <option value="Overwatch"'.(isset($_REQUEST['game'])&&$_REQUEST['game']=='Overwatch'?' selected':'').'>Overwatch</option>
                    <option value="Rocket League"'.(isset($_REQUEST['game'])&&$_REQUEST['game']=='Rocket League'?' selected':'').'>Rocket League</option>
                    <option value="Smite"'.(isset($_REQUEST['game'])&&$_REQUEST['game']=='Smite'?' selected':'').'>Smite</option>
                    <option value="Division"'.(isset($_REQUEST['game'])&&$_REQUEST['game']=='Division'?' selected':'').'>Division</option>
                    <option value="World of Tanks"'.(isset($_REQUEST['game'])&&$_REQUEST['game']=='World of Tanks'?' selected':'').'>World of Tanks</option>
                    <option value="World of Warcraft"'.(isset($_REQUEST['game'])&&$_REQUEST['game']=='World of Warcraft'?' selected':'').'>World of Warcraft</option>
                  </select>
              <input type="submit" value="Filter" class="button button-left" /> 
            </form>';
      $s.= '</div>';


      $s.= '<table class="overview">';
      $s.= '<tr>';
      $s.= '<th>Username</th>';
      $s.= '<th>Game</th>';
      if ($modtools) $s.= '<th>Balance</th>';
      $s.= '<th>End date</th>';
      $s.= '<tr>';

      function sorter($a,$b) {
        $c = new DateTime($a['end_date']);
        $d = new DateTime($b['end_date']);

        if ($c>$d) return 1;
        elseif ($c<$d) return -1;
        elseif ($c==$d) return 0;
        else return 0;
      }

      usort($teds, "sorter");

      $now = new DateTime();
      foreach($teds as $ted) {
        $end_date = new DateTime($ted['end_date']);
        $class = '';
        if ($end_date < $now) $class = ' class="end_date_passed"';
        $s.= '<tr'.$class.'>';
        $s.= '<td><a href="/pages/'.$version.'/?user_id='.$ted['user_id'].'">'.strip_html($ted['username']).'</a></td>';
        if (isset($ted['game'])) $s.= '<td align="center">'.$ted['game'].'</td>';
        else $s.= '<td align="center"></td>';

        if ($modtools) {
          if (isset($ted['balance'])) $s.= '<td align="center">'.$ted['balance'].'</td>';
          else $s.= '<td align="center">0</td>';
        }

        if (isset($ted['end_date'])) $s.= '<td align="center">'.$ted['end_date'].'</td>';
        else $s.= '<td align="center"></td>';
        $s.= '</tr>';
      }

      $s.= '</table>';

    } else {

      /* ----------------------------------------------------------------- */
      /* -------------------------User details---------------------------- */
      /* ----------------------------------------------------------------- */

      $user_id = $_REQUEST['user_id'];
      if (!$user_id || !is_numeric($user_id)) {
        $s.= 'User not found!';
      } elseif($user_id == (string)$visitor->user_id) {
        $s.= '<center>';
        $s.= '<div style="font-size:200%; margin-bottom:30px;">You are not allowed to watch your own TED page!</div>';
        $s.= '<img src="https://derpicdn.net/img/view/2013/10/26/456715__safe_solo_rainbow+dash_screencap_animated_looking+at+you_frown_angry_glare_mad.gif">';
        $s.=' </center>';
      } else {

        $user = get_user($user_id);

        $ted = get_ted($user_id);

        if ($user[0]['display_style_group_id'] !== 19 && !$modtools) {
          $s.= '<center>';
          $s.= '<div style="font-size:200%; margin-bottom:30px;">This member is no longer a trial!</div>';
          $s.= '<div style="font-size:200%; margin-bottom:30px;">You have no permission to view this page anymore.</div>';
          $s.= '<img src="https://derpicdn.net/img/view/2013/10/26/456715__safe_solo_rainbow+dash_screencap_animated_looking+at+you_frown_angry_glare_mad.gif">';
          $s.=' </center>';
        } else {

          $comments = get_ted_comments($user_id);

          if(!$ted) {            
            make_new_ted($user_id);
            $ted = get_ted($user_id);
          }



          $score = $ted[0]['score'];
          $voters = array();
          $voters = explode(',', $ted[0]['voters']);
          if (isset($_REQUEST['vote']) && ($_REQUEST['vote']=='up'||$_REQUEST['vote']=='down')) {
            $vote = $_REQUEST['vote'];
            $user_id = $_REQUEST['user_id'];


            foreach($voters as $i=>$voter) {
              if (!$voter) unset($voters[$i]);
            }

            if (!in_array($vote.'~'.$visitor->user_id, $voters)) { // Already voted this answer?
              $found = False;
              if ($voters) {
                $userid = (string)$visitor->user_id;
                foreach($voters as $i=>$voter) {
                  if (strpos($voter, $userid)) {
                    $voters[$i] = $vote.'~'.$visitor->user_id;
                    $found = True;
                    
                    if ($vote=='up') {
                      $score++;
                    }
                    if ($vote=='down') {
                      $score--;
                    }
                  }
                }
              }

              if(!$found) {
                $voters[] = $vote.'~'.$visitor->user_id;
              }

              $users_voted = array();

              if ($voters) $users_voted = implode(',', $voters);

              if ($vote=='up') {
                $score++;
              }
              if ($vote=='down') {
                $score--;
              }

              $db->query("UPDATE `TEDS` SET `score`=".$score.", `voters`='".$users_voted."' WHERE `TEDS`.`user_id`=".$user_id.";");

              $url = '/pages/'.$version.'/?user_id='.$user[0]['user_id'];
              header("Location: ".$url); /* Redirect browser */
              exit();
            }
          } elseif (isset($_REQUEST['modaction']) && $modtools) {
            switch ($_REQUEST['modaction']) {
              case 'extend':
                extend($ted);
                $url = '/pages/'.$version.'/?user_id='.$user[0]['user_id'];
                header("Location: ".$url); /* Redirect browser */
                exit();
                break;
              case 'pass':
                pass($ted, $user);
                $url = '/pages/'.$version.'/?user_id='.$user[0]['user_id'];
                header("Location: ".$url); /* Redirect browser */
                exit();
                break;
              case 'fail':
                fail($ted, $user);
                $url = '/pages/'.$version.'/?user_id='.$user[0]['user_id'];
                header("Location: ".$url); /* Redirect browser */
                exit();
                break;
              case 'restart':
                restart($ted, $user);
                $url = '/pages/'.$version.'/?user_id='.$user[0]['user_id'];
                header("Location: ".$url); /* Redirect browser */
                exit();
                break;
            }
          }

          if ($user[0]['display_style_group_id'] == 19) {
            $up = '<div class="votebutton"><a href="/pages/'.$version.'/?user_id='.$user[0]['user_id'].'&vote=up" class="plus">+</a></div>';
            $down = '<div class="votebutton"><a href="/pages/'.$version.'/?user_id='.$user[0]['user_id'].'&vote=down" class="minus">-</a></div>';
          } else {
            $up = '';
            $down = '';
          }

          if ($voters) {
            $userid = (string)$visitor->user_id;
            foreach($voters as $i=>$voter) {
              if (strpos($voter, $userid)) {
                $vote = explode('~', $voter);

                if ($vote[0]=='up') $up = '';
                if ($vote[0]=='down') $down = '';
              }
            }
          }



          $s.= '<a class="button" href="/pages/'.$version.'/">Go back to overview</a>';
          if ($modtools && ($ted[0]['status'] == 0 || $ted[0]['status'] == 4 || $ted[0]['status'] == 1) && $user[0]['display_style_group_id'] == 19) {
            $s.= '<a class="button extend modbutton" href="/pages/'.$version.'/?user_id='.$user[0]['user_id'].'&modaction=extend">EXTEND</a>';
            $s.= '<a class="button fail modbutton" href="/pages/'.$version.'/?user_id='.$user[0]['user_id'].'&modaction=fail">FAIL</a>';
            $s.= '<a class="button pass modbutton" href="/pages/'.$version.'/?user_id='.$user[0]['user_id'].'&modaction=pass">PASS</a>';
          } elseif ($modtools && ($ted[0]['status'] == 3)) {
            $s.= '<a class="button pass modbutton" href="/pages/'.$version.'/?user_id='.$user[0]['user_id'].'&modaction=restart">RESTART TRIAL</a>';
          }

          switch ($ted[0]['status']) {
            case '1':
              $status = ' <span class="extended">Extended</span>';
              break;
            case '2':
              $status = ' <span class="passed">Passed</span>';
              break;
            case '3':
              $status = ' <span class="failed">Failed</span>';
              break;
            case '4':
              $status = ' <span class="extended">Restarted trial</span>';
              break;
          }


          $s.= '<header class="username"><a href="https://www.konvictgaming.com/members/'.$user[0]['user_id'].'"><span class="style'.$user[0]['display_style_group_id'].'">'.strip_html($user[0]['username']).'</span></a>'.$status.'</header>';

          $s.= '<table class="details">';
          $s.= '<tr>';
          $s.= '<td>Score</td>';
          $s.= '<td>:</td>';
          $s.= '<td>'.($modtools?$score:'').'</td>';
          $s.= '<td>'.$up.$down.'</td>';
          //Vote popup
          $s.= '<div class="popup" style="visibility: hidden">
	                <span class="popuptext" id="minusVotePopup">
                        <form action="/pages/'.$version.'/?user_id='.$user[0]['user_id'].'" method="POST">
                        <input type="hidden" name="_xfToken" value="'.$visitor->csrf_token_page.'" />
                        <input type="hidden" name="form" value="comment" />
                        <input type="hidden" name="userid" value="'.$user[0]['user_id'].'" />
                        <textarea rows="4" name="comment"></textarea>
                        <input type="submit" value="Submit" class="button">
                        </form>
                    </span>
                </div>';
          $s.= '</tr>';

          if ($modtools && $voters) {
            $s.= '<tr>';
            $s.= '<td colspan="4">';
            $voters_list = '';
            foreach($voters as $i=>$voter) {
              if (!$voter) continue;
              $vote = explode('~', $voter);

              $vote_user = get_user($vote[1]);


              if ($vote_user && $vote[0] == 'up') $voters_list .= '<b class="username"><a href="https://www.konvictgaming.com/members/' . $vote_user[0]['user_id'] . '" class="style' . $vote_user[0]['display_style_group_id'] . '">' . strip_html($vote_user[0]['username']) . '</a> (+)</b>';
              if ($vote_user && $vote[0] == 'down') {
                voteShow();


              //$voters_list .= '<b class="username"><a href="https://www.konvictgaming.com/members/' . $vote_user[0]['user_id'] . '" class="style' . $vote_user[0]['display_style_group_id'] . '">' . strip_html($vote_user[0]['username']) . '</a> (-)</b>';
              }
              $voters_list.= '<br/>';

            }
            $s.= $voters_list;
            $s.= '</td>';
            $s.= '</tr>';
          }

          $s.= '<tr>';
          $s.= '<td>Game</td>';
          $s.= '<td>:</td>';
          if ($modtools) {
            $game_form = '<form action="/pages/'.$version.'/?user_id='.$user[0]['user_id'].'" method="POST" class="tedform">
                            <input type="hidden" name="_xfToken" value="'.$visitor->csrf_token_page.'" />
                            <input type="hidden" name="form" value="game" />
                            <input type="hidden" name="userid" value="'.$user[0]['user_id'].'" />
                            <select name="game">
                              <option value=""'.($ted[0]['game']==''?' selected':'').'>No game assigned</option>
                              <option value="Community Member"'.($ted[0]['game']=='Community Member'?' selected':'').'>Community Member</option>
                              <option value="CS:GO"'.($ted[0]['game']=='CS:GO'?' selected':'').'>CS:GO</option>
                              <option value="Diablo 3"'.($ted[0]['game']=='Diablo 3'?' selected':'').'>Diablo 3</option>
                              <option value="FFXIV"'.($ted[0]['game']=='FFXIV'?' selected':'').'>FFXIV</option>
                              <option value="Heroes of the Storm"'.($ted[0]['game']=='Heroes of the Storm'?' selected':'').'>Heroes of the Storm</option>
                              <option value="League of Legends"'.($ted[0]['game']=='League of Legends'?' selected':'').'>League of Legends</option>
                              <option value="Minecraft"'.($ted[0]['game']=='Minecraft'?' selected':'').'>Minecraft</option>
                              <option value="NewZ"'.($ted[0]['game']=='NewZ'?' selected':'').'>NewZ</option>
                              <option value="Overwatch"'.($ted[0]['game']=='Overwatch'?' selected':'').'>Overwatch</option>
                              <option value="Rocket League"'.($ted[0]['game']=='Rocket League'?' selected':'').'>Rocket League</option>
                              <option value="Smite"'.($ted[0]['game']=='Smite'?' selected':'').'>Smite</option>
                              <option value="Division"'.($ted[0]['game']=='Division'?' selected':'').'>Division</option>
                              <option value="World of Tanks"'.($ted[0]['game']=='World of Tanks'?' selected':'').'>World of Tanks</option>
                              <option value="World of Warcraft"'.($ted[0]['game']=='World of Warcraft'?' selected':'').'>World of Warcraft</option>
                            </select>
                            <input type="submit" value="Change" class="button button-left">
                          </form>';

            $s.= '<td colspan="2">'.$game_form.'</td>';
          } else {
            $s.= '<td colspan="2">'.$ted[0]['game'].'</td>';
          }
          $s.= '</tr>';
          if (!$ted[0]['game'] && $modtools) $s.= '<tr><td colspan="4"><span class="error">Please select a game.</span></td></tr>';
          $s.= '<tr>';
          $s.= '<td>End date</td>';
          $s.= '<td>:</td>';

          if ($modtools) {
            $enddate = new DateTime($ted[0]['end_date']);
            $date_form = '<form action="/pages/'.$version.'/?user_id='.$user[0]['user_id'].'" method="POST" class="tedform">
                            <input type="hidden" name="_xfToken" value="'.$visitor->csrf_token_page.'" />
                            <input type="hidden" name="form" value="date" />
                            <input type="hidden" name="userid" value="'.$user[0]['user_id'].'" />
                            <input type="textbox" name="date" value="'.$enddate->format('d-m-Y').'" />
                            <input type="submit" value="Change" class="button button-left">
                          </form>';

            $s.= '<td colspan="2">'.$date_form.$date_error.'</td>';
          } else {
            $enddate = new DateTime($ted[0]['end_date']);
            $s.= '<td colspan="2">'.$enddate->format('d-m-Y').'</td>';
          }
          $s.= '</tr>';

          if ($modtools) {
            $s.= '<tr>';
            $s.= '<td>Teamspeak UID</td>';
            $s.= '<td>:</td>';
            $ts_uid = $ted[0]['ts_uid'];
            $ts_uid_form = '<form action="/pages/'.$version.'/?user_id='.$user[0]['user_id'].'" method="POST" class="tedform">
                            <input type="hidden" name="_xfToken" value="'.$visitor->csrf_token_page.'" />
                            <input type="hidden" name="form" value="tsuid" />
                            <input type="hidden" name="userid" value="'.$user[0]['user_id'].'" />
                            <input type="textbox" name="uid" class="tsuid" value="'.$ts_uid.'" />
                            <input type="submit" value="Set" class="button button-left">
                          </form>';

            $s.= '<td colspan="2">'.$ts_uid_form.'</td>';
            $s.= '</tr>';
            if (!$ted[0]['ts_uid']) $s.= '<tr><td colspan="4"><span class="error">Please provide a Teamspeak Unique ID.</span></td></tr>';

            $s.= '<tr>';
            $s.= '<td>Application</td>';
            $s.= '<td>:</td>';
            $app_link = $ted[0]['app_link'];
            $link = '';
            if ($app_link) $link = '<a href="'.$app_link.'" target="_blank" class="button button-left">Go to Application</a><br/>';
            $app_link_form = '<form action="/pages/'.$version.'/?user_id='.$user[0]['user_id'].'" method="POST" class="tedform">
                            <input type="hidden" name="_xfToken" value="'.$visitor->csrf_token_page.'" />
                            <input type="hidden" name="form" value="app_link" />
                            <input type="hidden" name="userid" value="'.$user[0]['user_id'].'" />
                            <input type="textbox" name="app_link" class="app_link" value="'.$app_link.'" />
                            <input type="submit" value="Set" class="button button-left">
                          </form>';

            $s.= '<td colspan="2">'.$app_link_form.$link.'</td>';
            $s.= '</tr>';
            if (!$ted[0]['app_link']) $s.= '<tr><td colspan="4"><span class="error">Please provide a link to the application.</span></td></tr>';
          }

          $s.= '</table>';

          $s.= '<header>Comments</header>';
          $s.= '<table class="comments">';
          foreach($comments as $comment) {
            $comment_user = get_user($comment['comment_user']);

            if (!$comment_user) continue;
            if ($comment_user[0]['user_id'] !== $visitor->user_id && !$modtools) continue;//Only see your own comments, or you are staff

            $s.= '<tr>';
            $s.= '<td class="username">';
            $s.= '<a href="/members/'.strtolower(strip_html($comment_user[0]['username'])).'.'.$comment_user[0]['user_id'].'" class="style'.$comment_user[0]['display_style_group_id'].'">'.strip_html($comment_user[0]['username']).'';
            $s.= '</td>';
            $s.= '<td>'.$comment['comment'].'</td>';
            $s.= '</tr>';
          }
          $s.= '<tr>';
          $s.= '<td colspan="2">';
          $s.= '<form action="/pages/'.$version.'/?user_id='.$user[0]['user_id'].'" method="POST">
                  <input type="hidden" name="_xfToken" value="'.$visitor->csrf_token_page.'" />
                  <input type="hidden" name="form" value="comment" />
                  <input type="hidden" name="userid" value="'.$user[0]['user_id'].'" />
                  <textarea rows="4" name="comment"></textarea>
                  <input type="submit" value="Submit" class="button">
                </form>';
          $s.= '</td>';
          $s.= '</tr>';
          $s.= '</table>';
        }
      }
    }

    $s.= '</div>';
    $s.= '<script>
            function voteShow()
            {
            var popup = document.getElementById("minusVotePopup");
            popup.css("visibility","visible");
            }
          </script>';
    echo($s);
  }
}