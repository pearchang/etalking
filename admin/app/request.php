<?php
if (MODE == 'add')
{
  // TODO: constraint mobile & email
  $_POST['status'] = 60; // 開發中
  $_POST['sales_id'] = $_SESSION['admin_id'];
  $rs->insert('member');
  $id = $rs->last_id;
  unset ($v);
  $v['member_id'] = $id;
  $v['type'] = 60; // 開發
  $v['content'] = '建立開發名單';
  $rs->insert('member_history', $v);
  Message('開發名單建立完成', false, MSG_OK);
  GoBack();
  exit;
}
$TABLE = 'member_history';
$FUNC_NAME = '業務開發';
//$SELECT = array ('status');
$SEARCH = true;
$SEARCH_KEYS = array ('member_name', 'mobile', 'email', 'source', 'track');
$SORT_BY = '`condition` ASC, h.next_time ASC, h.cdate ASC';
$TRANSLATE = array (
  'status' => array($var_request_status, $var_request_status_color),
  'gender' => array($var_member_gender, $var_member_gender_color),
);
$VARS['gender_select'] = GenRadio('gender', $var_member_gender, true);

if ($_SESSION['admin_manager'] || $_SESSION['admin_marketing'])
  GenFilterBySQL('filter', "SELECT u.id AS value, CONCAT(u.first_name, ' ', u.last_name) AS text FROM `user` u, `group` g, user_group ug WHERE g.is_sales = 1 AND ug.group_id = g.id AND ug.user_id = u.id GROUP BY (u.id) ORDER BY u.first_name, u.last_name");

if ($_SESSION['admin_manager'] || $_SESSION['admin_marketing'])
{
  $FILTER = 'm.sales_id';
  $FILTER_NAME = '業務';
}
else
  $where = 'AND m.sales_id = ' . $_SESSION['admin_id'];
  
	if(GetParam('search_begin')){
		
		$s =  date('Y-m-d 00:00:00', strtotime(GetParam('search_begin')) );
		
		$e =  date('Y-m-d 23:59:59', strtotime(GetParam('search_end')) );
		
		$range = " AND h.next_time BETWEEN '$s' AND '$e' ";
		
		$VARS['search_begin'] = GetParam('search_begin');
		$VARS['search_end'] = GetParam('search_end');
		
	}
	
	if(GetParam('filter2')){
		$demo_done = ' AND m.demo_done=1 ';
	}
$DATA_SQL = <<<EOT
SELECT IF( next_time='0000-00-00 00:00:00' AND DATE(h.cdate)= CURDATE() ,1,
			IF( NOW() > DATE_ADD(h.next_time,INTERVAL 1 HOUR) , 2, 
				IF( NOW() < h.next_time AND DATE(h.next_time)= CURDATE() , 3, 
					IF( NOW() < h.next_time AND DATE(h.next_time) != CURDATE() , 4, 5 )
				)
			)
	
	)  AS `condition`,
 m.id, m.member_name, m.mobile, m.email, m.gender, m.source, m.track, h.member_id, h.status, h.content, h.next_time FROM member m, member_history h,
 (SELECT member_id, MAX(id) AS id FROM `member_history` GROUP BY member_id) z
 WHERE m.id = z.member_id AND h.id = z.id AND m.status = 60 $where {filter} $range $demo_done ORDER BY $SORT_BY
EOT;
//echo $DATA_SQL;

////////////////////////////////////
	require_once('func.inc.php');
////////////////////////////////////
$VARS['add_button'] = false;
$VARS['search_date'] = true;
$checked = GetParam('filter2') ? 'checked' : '';
$VARS['custom']= "<input type=checkbox id='search_filter2' name=filter2 value=1 $checked onChange='do_search()' >已demo名單";
function fn_callback($r)
{
  $time = substr($r['next_time'],-8);
  if($time=='11:59:59')
	$r['next_time'] = substr($r['next_time'],0,-8).' 上午';
  elseif($time=='17:59:59')
	$r['next_time'] = substr($r['next_time'],0,-8).' 下午';
  elseif($time=='23:59:59')
	$r['next_time'] = substr($r['next_time'],0,-8).' 晚上';
  elseif($r['next_time']=='0000-00-00 00:00:00')
	$r['next_time'] = '';
  else {
    // $r['next_time'] = substr($r['next_time'],0,-8).' '.((int)substr($r['next_time'],-8,2)).'點';
    $date = new DateTime($r['next_time'], new DateTimeZone('Asia/Taipei'));
    $r['next_time'] = $date->format("Y-m-d H:i");
  }
  return $r;
}

function fn_new()
{
}

function fn_add($id)
{
}

function fn_edit($id)
{
}

function fn_modify($id)
{
}
?>
