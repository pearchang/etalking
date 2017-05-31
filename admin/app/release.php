<?php
switch (MODE)
{
  case 'appoint':
    $sales = GetParam('sales');
    foreach ($_POST['checked'] as $id)
    {
      $v['sales_id'] = $sales;
      $v['status'] = 60; // 開發中
      $rs->update('member', $id, $v);
      $rs->select('user', $sales);
      $r = $rs->fetch();
      unset ($v);
      $v['member_id'] = $id;
      $v['type'] = 10; // 指派
      $v['content'] = '轉派給' . $r['user_name'];
      $rs->insert('member_history', $v);
    }
    Message('轉派完成', true, MSG_OK);
    exit;
    break;
  case 'develop':
    $id = GetParam('id');
    $v['status'] = 60; // 開發
    $v['sales_id'] = $_SESSION['admin_id'];
    $rs->update('member', $id, $v);
    // history
    unset ($v);
    $v['member_id'] = $id;
    $v['type'] = 60; // 開發
    $v['content'] = $_SESSION['admin_name'] . '開發';
    $rs->insert('member_history', $v);
    Message('已取得開發名單', false, MSG_OK);
    GoLast();
    break;
  case 'delete':
    $TABLE = 'member';
    break;
  case 'popup':
    $TABLE = 'member_history';
    $SEARCH = false;
    $SORT_BY = 'id DESC';
    $TRANSLATE = array (
      'type' => array($var_member_history_type),
    );
    $SPECIAL = "member_id = " . GetParam('id');
    break;
  default:
    $TABLE = 'member_history';
    $FUNC_NAME = '釋出名單';
//$SELECT = array ('status');
    $SEARCH = true;
    $SEARCH_KEYS = array ('m.member_name', 'm.mobile', 'm.email', 'm.source', 'm.track', 'h.content');
//    $SORT_BY = 'a.cdate DESC';
    $TRANSLATE = array (
      'status' => array($var_request_status, $var_request_status_color),
      'gender' => array($var_member_gender, $var_member_gender_color),
    );

    $sql = <<<EOT
SELECT u.id, CONCAT(u.user_name, ' [', g.group_name, ']') AS data
FROM `group` g, `user` u, `user_group` ug
WHERE g.status = 10 AND u.status = 10 AND u.id = ug.user_id AND ug.group_id = g.id AND g.is_sales = 1
ORDER BY group_name, user_name
EOT;
    $VARS['sales_select'] = GenSelectBySQL('sales', $sql, false, true);

// status = 60 開發中
    $DATA_SQL = <<<EOT
SELECT m.id, m.member_name, m.mobile, m.email, m.gender, a.member_id, m.source, a.status, a.content, a.cdate FROM member_history a
LEFT OUTER JOIN member_history b ON a.cdate < b.cdate AND a.member_id = b.member_id LEFT JOIN member m ON a.member_id = m.id
WHERE b.cdate IS NULL AND m.status = 70 {filter} GROUP BY a.member_id ORDER BY a.cdate DESC
EOT;
    $DATA_SQL = <<<EOT
SELECT m.id, m.member_name, m.mobile, m.email, m.gender, m.source, m.track, h.member_id, h.status, h.content, h.cdate FROM member m, member_history h,
 (SELECT member_id, MAX(id) AS id FROM `member_history` GROUP BY member_id) z
 WHERE m.id = z.member_id AND h.id = z.id AND m.status = 70 {filter} ORDER BY h.cdate DESC
EOT;
    break;
}

////////////////////////////////////
	require_once('func.inc.php');
////////////////////////////////////
$VARS['add_button'] = false;

function fn_callback($r)
{
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