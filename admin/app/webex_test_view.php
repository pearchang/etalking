<?php

$id = GetParam('id');

$sql = "SELECT id,webex_name FROM webex WHERE status=10 AND type=5 ";
$rs->query($sql);
$webex = array();
if($rs->count>0){
	while($row = $rs->fetch()){
		$webex[$row['id']] = $row['webex_name'];
	}
}
$VARS['webex'] = GenSelect('webex_id', $webex, true);

$type = array(1=>'學員',2=>'顧問');
$VARS['type_select'] = GenSelect('type', $type, true);

switch (MODE)

{
  case 'edit':
  
	$sql = "SELECT * FROM webex_test WHERE id=".$_GET['id'];
	$rs->query($sql);
	//$row = $rs->fetch();
	$VARS['data'] = $rs->fetch();
  break;
	
  case 'save':
	$_POST['test_user'] = $_SESSION['admin_id'];
	$r = $rs->update( 'webex_test', $_GET['id'] , $_POST, 'id' );
	json_output(true);
	exit;
  break;
  
  case 'autocomplete_member':
	$kw = "like '%".strtolower(GetParam('term'))."%'";
	$sql = "SELECT id, first_name, last_name, member_name FROM member WHERE LOWER(first_name) $kw OR LOWER(last_name) $kw OR LOWER(member_name) $kw ";
	$rs->query($sql);
	$list = array();
	if($rs->count>0){
		while($row = $rs->fetch()){
			$name = !empty($row['member_name']) ? $row['member_name'] : $row['last_name'].$row['last_name'];
			$list[] = array( 'id' => $row['id'], 'label' => $name );
		}
	}
	json_output($list);
  exit;
  break;
  
  
  
  case 'autocomplete_consultant':
	$kw = "like '%".strtolower(GetParam('term'))."%'";
	$sql = "SELECT id, first_name, last_name, chi_name FROM consultant WHERE LOWER(first_name) $kw OR LOWER(last_name) $kw  OR LOWER(chi_name) $kw";
	$rs->query($sql);
	$list = array();
	if($rs->count>0){
		while($row = $rs->fetch()){
			$name = !empty($row['chi_name']) ? $row['chi_name'] : $row['first_name'].' '.$row['last_name'];
			$list[] = array( 'id' => $row['id'], 'label' => $name );
		}
	}
	json_output($list);
  exit;
  break;
  
  case 'add':
 	$v = array();
	$v['open_time']  = $_POST['date']." ".$_POST['time'].":00";
	$t = strtotime($v['open_time']);
    $v['begin_time'] = date('Y-m-d H:i:00', $t - 3600);
    $v['end_time']   = date('Y-m-d H:i:00', $t + 3600 * 3);	
	$v['status'] = 10;
	$v['date'] = $_POST['date'];
	$v['time'] = $_POST['time'];
	$v['memo'] = MD5( time() );
	
	$wid = check_webex2( $v['open_time'] );
	if (!$wid)
    {
      MessageBox('WebEx教室已用完，無法建立教室',true);	
      exit;
    }
	$v['webex_id'] = $wid;
	$rs->insert('classroom_test', $v);
	$cid = $rs->last_id;
	
 	if( $cid && create_test_meeting_room($cid)){
	
		$_POST['classroom_id'] = $cid;
		$_POST['datetime'] = $_POST['date']." ".$_POST['time'].":00";
		$_POST['status'] = 10;
		$rs->insert('webex_test', $_POST );
		
		if($rs->last_id){
			
			$v['name'] = $_POST['target'];
			
			$email = $_POST['type']==1 ? $rs->get_value("member","email",$_POST['target_id'],'id') : 
											$rs->get_value("consultant","email",$_POST['target_id'],'id');
			if( $_POST['type']==2) $v['memo'].= "?teacher=1"; 
			if(empty($email))
				Message('DEMO預約成功,但電子郵件錯誤', false, MSG_OK);
			else{
				$m = new MailModule();
				$m->template = 'email_webextest';
				$m->vars = $v;
				$m->addAddress( $email, $v['name'] );
				$m->subject = $EMAIL_SUBJECT_DEMO;
				$m->send();
				Message('DEMO預約成功', false, MSG_OK);	
			}			
		}
	}

    echo <<<EOT
<script language="javascript">
parent.window.location.reload();
</script>
EOT;
	exit;
	break;
  

  case 'cancel':

    $sql = "SELECT c.*, w.id AS wid, w.target_id, c.meeting_key FROM classroom_test c, webex_test w WHERE c.id = $id AND c.id = w.classroom_id";

    $rs->query($sql);

    $r = $rs->fetch();

    unset ($v);

    $v['status'] = 20;

    $rs->update('classroom_test', $id, $v);

    $rs->update('webex_test', $r['wid'], $v);
	
	$rs->select('webex', $r['webex_id']);

	$w = $rs->fetch();

	$wx = new WebexAPI;

	$wx->set_auth( $w["account"], $w["password"], WEBEX_SID, WEBEX_PID);

	$wx->set_url(WEBEX_API_URL);

	$result = $wx->dm($r['meeting_key']);
			
	$email = $r['type']==1 ? $rs->get_value("member","email",$r['target_id'],'id') : 
							$rs->get_value("consultant","email",$r['target_id'],'id');
	$m = new MailModule();
	$m->template = 'email_webextest_cancel';
	$m->vars = $r;
	$m->addAddress( $email, $r['name'] );
	$m->subject = $EMAIL_SUBJECT_DEMO;
	$m->send();

	print_r($r); echo $email; exit;
	
    json_output(array ('status' => $result));

    break;
	
	
	

  case 'enter':

    $rs->select('classroom_test', $id);

    $c = $rs->fetch();

    unset ($v);

    $v['status2'] = 10; // enter

    $rs->update('classroom_test', $id, $v);

    $rs->select('webex', $c['webex_id']);

    $w = $rs->fetch();

	
    if(!isset($_GET['ST']))

    {

      $_SESSION['ref'] = $_SERVER['HTTP_REFERER'];



      $bu = urlencode("http://{$_SERVER['HTTP_HOST']}{$_SERVER['REQUEST_URI']}");

      header ("Location: https://etalking.webex.com/etalking/p.php?AT=LO&BU=$bu");

    }

    else

    {

      $data['service'] = "user.GetLoginTicket";

      $data['xml_body'] = "";

      $wx = new WebexAPI;

      $wx->set_auth($w["account"], $w["password"], WEBEX_SID, WEBEX_PID);

      $wx->set_url(WEBEX_API_URL);

      $tk = $wx->getTicket();

      $url = urlencode($_SESSION['ref']); //$_SERVER['HTTP_REFERER']);

      unset ($_SESSION['ref']);

      echo <<<EOT

<form method=POST action="https://etalking.webex.com/etalking/p.php">

  <input type="hidden" name="AT" value="LI">

  <input type="hidden" name="TK" value="$tk">

  <input type="hidden" name="WID" value="{$w['account']}">

  <input type="hidden" name="MU" value="https://etalking.webex.com/etalking/m.php?AT=HM&MK={$c['meeting_key']}&BU=$url">

</form>

<script language="javascript">

  document.forms[0].submit();

</script>

EOT;

    }

    exit;

    break;
	
  case "new":

	include 'webex.inc.php';
	$VARS['menu_name'] = "請選擇預約時段";
	$VARS['url'] = "webex_test/newform";
	$VARS['test'] =1;
  break;

  default:
  
	

	$filter = GetParam('search_begin') ?  "w.`datetime` BETWEEN '".GetParam('search_begin')." 00:00:00' AND '".GetParam('search_end')." 23:59:59' " :  " w.`datetime` >= NOW() ";

	$DATA_SQL = <<<EOT
		SELECT w.*, c.*, w.date as wdate, w.time as wtime, w.type as wtype, w.id as wid FROM classroom_test c, webex_test w 
		WHERE w.classroom_id = c.id AND $filter
EOT;

	$SORT_BY = "`datetime` DESC";
	require_once('func.inc.php');
	$VARS['search_begin'] = GetParam('search_begin');
	$VARS['search_end'] = GetParam('search_end');
    break;

}



////////////////////////////////////

	

////////////////////////////////////
	$VARS['search_date'] = true;
	$VARS['add_button'] = false;
	$VARS['date'] = GetParam('date');
	$VARS['time'] = GetParam('time');
	
function fn_callback($r)
{
  global $rs3;

	$r['creator'] = $rs3->get_value("user","user_name",$r['creator'],'id');
	$r['test_user'] = $rs3->get_value("user","user_name",$r['test_user'],'id');
			
	if($r['wtype']==1){
			$people = $rs3->get_value("member","account",$r['target_id'],'id');
			$people = "<a href='member/edit?x&id=".$r['target_id']."'>".$people."</a>";
	}else{
			$rs3->query("SELECT first_name,last_name FROM  consultant WHERE id=".$r['target_id']);
			//$rs3->query("SELECT account FROM  consultant WHERE id=".$r['target_id']);
			$people = $rs3->fetch();
			$people = $people['first_name'].' '.$people['last_name'];
			//$people = $people['account'];
			$people = "<a href='consultant/edit?x&id=".$r['target_id']."'>".$people."</a>";
	}
	$r['name']= $people;
	if(is_numeric($r['pass']))
		$r['pass']= $r['pass']==1 ? "<font color=green>Yes</font>" : "<font color=red>No</font>";

  return $r;
}

function create_test_meeting_room($classroom_id)

{

  global $rs3;



  $rs3->select('classroom_test', $classroom_id);

  $r = $rs3->fetch();


  if ($r['webex_id'] == 0)

    return false;

  if ($r['status'] != 10 || !empty($r['meeting_key']))

    return true;

  $t = strtotime($r['open_time']) + 3600 * 3; // due time

//  echo $t . time();

  if ($t < time())

  	return true;

  // check type

  $du = 45;



//    $t -= 3600; // 提早一小時

    $du = 105;

    $title = 'TEST';



  // get webex

  $rs3->select('webex', $r['webex_id']);

  $w = $rs3->fetch();

  $pw = makePassword();



  $wx = new WebexAPI;

  $wx->set_auth( $w["account"], $w["password"], WEBEX_SID, WEBEX_PID);

  $wx->set_url(WEBEX_API_URL);

  $k = $wx->sm($w["account"], $pw, $r['open_time'] . ' ' . $title, date('m/d/Y H:i:s', strtotime($r['begin_time'])), $du);

//  echo $k;

  if (!is_numeric($k))

    return false;

  $v['meeting_key'] = $k;

  $v['meeting_pw'] = $pw;

  $rs3->update('classroom_test', $classroom_id, $v);

  return $k;

}

function check_webex2( $opentime )

{

  $webex_type = 5;

  $rs2 = new ResultSet();

  $rs3 = new ResultSet();

  $sql = "SELECT * FROM webex WHERE status = 10 AND `type` = 5";

  $rs2->query($sql);

  $wid = 0;

  while (($r2 = $rs2->fetch()))

  {

    // 確認該時段沒有被佔

    $sql = "SELECT webex_id FROM classroom_test WHERE webex_id = {$r2['id']} AND open_time='$opentime' AND status = 10";

    //echo $sql . '<br>';

    $rs3->query($sql);

    if ($rs3->count == 0) {

      $wid = $r2['id'];

      break;

    }

  }

  return $wid ? $wid : false;

}


?>