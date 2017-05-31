<?php
$TABLE = 'service';
$FILTER = 'status';
$FILTER_NAME = '狀態';
$FUNC_NAME = '聯絡我們';
$SELECT = array ('status');
$SEARCH = true;
$SEARCH_KEYS = array ('email', 'name', 'phone', 'content');
$SORT_BY = 'cdate DESC';

GenFilter('filter', $var_service_status);

if (MODE == '')
  $DATA_SQL = "SELECT a.* FROM `service` a LEFT OUTER JOIN `service` b ON a.sn = b.sn AND a.type = b.type AND a.cdate < b.cdate WHERE a.type = 1 AND b.cdate IS NULL GROUP BY a.sn ORDER BY a.cdate DESC";
elseif (MODE == 'update')
{
  if (!empty($_POST['content']))
  {
    $rs->insert('service');
    $id = GetParam('id');
    $rs->select('service', $id);
    $r = $rs->fetch();
    $sql = "UPDATE `service` SET status = {$_POST['status']} WHERE sn = '{$r['sn']}' AND status = 10";
    $rs->execute($sql);
    $sql = "SELECT * FROM `service` WHERE sn = '{$r['sn']}' AND type = 1 ORDER BY id DESC";
    $rs->query($sql);
    AssignValue($rs, null, 'cust');
    $sql = "SELECT * FROM `service` WHERE sn = '{$r['sn']}' AND type = 2 ORDER BY id DESC";
    $rs->query($sql);
    AssignValue($rs, null);
    $m = new MailModule();
    $m->template = 'email_service_response';
    $m->vars = $VARS;
    $m->addAddress($VARS['email'], $VARS['name']);
    $m->subject = EMAIL_SERVICE_RESPONSE . ' #' . $r['sn'];
    $m->send();
    unset($m);
    Message('已新增留言!', true, MSG_OK);
  }
  else
  {
    $id = GetParam('id');
    $rs->select('service', $id);
    $r = $rs->fetch();
    $sql = "UPDATE `service` SET `status` = 30 WHERE sn = {$r['sn']}";
    $rs->execute($sql);
    Message('已設定為[結案]!', true, MSG_OK);
  }
}

////////////////////////////////////
	require_once('func.inc.php');
////////////////////////////////////
$VARS['add_button'] = false;

function cbOrder($r)
{
  global $rs3, $var_order_status, $var_payment, $var_paid, $var_shipment;

  $r['status_text'] = $var_order_status[$r['status']];
  $r['paid_text'] = $var_paid[$r['paid']];
  $r['shipment_text'] = $var_shipment[$r['shipment']];
  $r['payment_text'] = $var_payment[$r['payment']];
  return $r;
}

function fn_callback($r)
{
	global $rs3, $var_service_status, $var_service_status_color;

  $r['status_text'] = $var_service_status[$r['status']];
  $r['status_color'] = $var_service_status_color[$r['status']];
	return $r;
}

function cb_edit($r)
{
  $r['class'] = $r['type'] == 1 ? 'client' : 'store';
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
  global $rs3, $VARS;

  unset($VARS['content']);
  $rs3->select('service', $id);
  $r = $rs3->fetch();
  $sql = "SELECT * FROM `service` WHERE sn = '{$r['sn']}' ORDER BY id";
  $rs3->query($sql);
  AssignValues($rs3, null, 'cb_edit');
  //print_r($VARS);
}

function fn_modify($id)
{
}
?>