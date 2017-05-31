<?php
$TABLE = 'plan';
$FUNC_NAME = '方案';
$CAN_DELETE = true;
$SELECT = array ('period', 'cat_id');
$SEARCH = true;
$SEARCH_KEYS = array ('plan_name', 'price', 'point');
$SEARCH_DATE = array ('begin', 'end');
$SORT_BY = '`begin` DESC, `end` DESC, period';
$TRANSLATE = array (
  'status' => array($var_general_status, $var_general_status_color),
);

unset ($var_period);
for ($i = 1; $i <= 36; $i++)
  $var_period[$i] = $i;
$RADIO = array('status');
$VARS['status_select'] = GenRadio('status', $var_general_status, false, true);
$VARS['period_select'] = GenSelect('period', $var_period, false, true);
$VARS['cat_id_select'] = GenSelectBySQL('cat_id', "SELECT id, cat_name AS `data` FROM `plan_category` WHERE status = 10 ORDER BY cat_name", true, true);

////////////////////////////////////
require_once('func.inc.php');
////////////////////////////////////

function fn_callback($r)
{
  global $rs3;

  $rs3->select('plan_category', $r['cat_id']);
  $r2 = $rs3->fetch();
  $r['cat_name'] = $r2['cat_name'];

	return $r;
}

function fn_new()
{
  global $VARS;

  $VARS['status'] = DOC_STATUS_SHOW;
  $VARS['price'] = $VARS['point'] = $VARS['gift'] = 0;
  $VARS['begin'] = date('Y-m-d', time());
  $VARS['end'] = date('Y-m-d', mktime(0, 0, 0, date('m') + 1, date('d'), date('Y')));
}

function fn_add($id)
{
  if (GetParam('begin') > GetParam('end'))
  {
    Message('起始日不得大於結束日', true, MSG_WARN);
  }
}

function fn_edit($id)
{
}

function fn_modify($id)
{
  if (GetParam('begin') > GetParam('end'))
  {
    Message('起始日不得大於結束日', true, MSG_WARN);
  }
}
?>