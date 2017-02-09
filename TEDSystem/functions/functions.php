<?php



/* Developed by Ladineko */
function dump($data) {
  print("<div class='dump'>");
  if(is_array($data)) { //If the given variable is an array, print using the print_r function.
    print "<pre>-----------------------\n";
    print_r($data);
    print "-----------------------</pre>";
  } elseif (is_object($data)) {
    print "<pre>==========================\n";
    var_dump($data);
    print "===========================</pre>";
  } else {
    print "=========&gt; ";
    var_dump($data);
    print " &lt;=========\r\n";
  }
  print("</div>");
} 

function strip_html($data) {
  $data = str_replace('<', '&lt;', $data);
  $data = str_replace('>', '&gt;', $data);
  return $data;
}

function validateDate($date, $format = 'Y-m-d H:i:s')
{
    $d = DateTime::createFromFormat($format, $date);
    return $d && $d->format($format) == $date;
}

function make_new_ted($user_id)
{
  if (!$user_id) return;
  if (!is_integer($user_id)) return;
  $db = XenForo_Application::get('db');
  $user =$db->fetchAll("SELECT *
                        FROM xf_user
                        WHERE user_id = '".$user_id."'");
  if(!$user) return;

  $datetime = new datetime();
  $datetime->modify('+14 days');
  $db->query("INSERT INTO konvictg_xenweb.TEDS (user_id,game,score,voters,end_date) VALUES ('".$user_id."',NULL,'0','', '".$datetime->format("Y-m-d")."');");
}

function get_ted($user_id)
{
  $db = XenForo_Application::get('db');
  return $db->fetchAll("SELECT *
                        FROM TEDS
                        WHERE user_id = '".$user_id."'");
}

function get_ted_comments($user_id)
{
  $db = XenForo_Application::get('db');
  return $db->fetchAll("SELECT *
                        FROM TEDS_comments
                        WHERE user_id = '".$user_id."'");
}

function get_user($user_id)
{
  $db = XenForo_Application::get('db');
  return $db->fetchAll("SELECT *
                        FROM xf_user
                        WHERE user_id = '".$user_id."'");
}






function extend($ted)
{
  $db = XenForo_Application::get('db');
  $enddate = new DateTime($ted[0]['end_date']);
  $enddate->modify('+14 days');
  $sql = "UPDATE `konvictg_xenweb`.`TEDS` SET `end_date` = '".$enddate->format('Y-m-d')."', `status` = '1' WHERE `TEDS`.`user_id` = ".$ted[0]['user_id'].";";
  $db->query($sql);
}

function pass($ted, $user)
{
  $db = XenForo_Application::get('db');

  $sql = "UPDATE `konvictg_xenweb`.`TEDS` SET `status` = '2' WHERE `TEDS`.`user_id` = ".$ted[0]['user_id'].";";
  $db->query($sql);

  $sql = "UPDATE `konvictg_xenweb`.`xf_user` SET `user_group_id` = '18', `display_style_group_id` = '18', `permission_combination_id` = '7'  WHERE `xf_user`.`user_id` =".$ted[0]['user_id'].";";
  $db->query($sql);
}

function fail($ted, $user)
{
  $db = XenForo_Application::get('db');

  $sql = "UPDATE `konvictg_xenweb`.`TEDS` SET `status` = '3' WHERE `TEDS`.`user_id` = ".$ted[0]['user_id'].";";
  $db->query($sql);

  $sql = "UPDATE `konvictg_xenweb`.`xf_user` SET `user_group_id` = '2', `display_style_group_id` = '2', `permission_combination_id` = '2'  WHERE `xf_user`.`user_id` =".$ted[0]['user_id'].";";
  $db->query($sql);
}

function restart($ted)
{
  $db = XenForo_Application::get('db');
  $enddate = new DateTime();
  $enddate->modify('+14 days');
  $sql = "UPDATE `konvictg_xenweb`.`TEDS` SET `end_date` = '".$enddate->format('Y-m-d')."', `status` = '4', `score` = '0', `voters` = '' WHERE `TEDS`.`user_id` = ".$ted[0]['user_id'].";";
  $db->query($sql);

  $sql = "UPDATE `konvictg_xenweb`.`xf_user` SET `user_group_id` = '19', `display_style_group_id` = '19', `permission_combination_id` = '16' WHERE `xf_user`.`user_id` =".$ted[0]['user_id'].";";
  $db->query($sql);
}