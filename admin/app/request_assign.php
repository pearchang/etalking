<?php
switch (MODE)
{
  case 'appoint':
    $sales = GetParam('sales');
    unset ($error);
//    foreach ($_POST['checked'] as $id)
//    {
//      $rs->select('request', $id);
//      $r = $rs->fetch();
//      if ($r['status'] != 10)
//      {
//        $error[] = $r['guest_name'] . '(' . $r['tel'] . ')';
//        continue;
//      }
//
//      $sql = "SELECT * FROM member WHERE mobile = '{$r['tel']}'";
//      $rs->query($sql);
//      if ($rs->count > 0)
//        $error[] = $r['guest_name'] . '(' . $r['tel'] . ')';
//    }
////    print_r($error);
////    exit;
//    if (is_array($error) && count($error) > 0)
//    {
//      Message("下列名單已指派過，請更新頁面後重新指派：" . implode(", ", $error), true, MSG_ERROR);
//      exit;
//    }

    foreach ($_POST['checked'] as $id)
    {
      $rs->select('request', $id);
      $r = $rs->fetch();
      if ($r['status'] != 10)
        continue;
      unset ($v);
      $sql = "SELECT * FROM member WHERE mobile = '{$r['tel']}'";
      $rs->query($sql);
      if ($rs->count > 0)
        $error[] = $r['guest_name'] . '(' . $r['tel'] . ')';
      else
      {
        // member
        $v['member_name'] = $r['guest_name'];
        $v['mobile'] = $r['tel'];
        $v['email'] = $r['email'];
        $v['gender'] = $r['gender'];
        $v['sales_id'] = $sales;
        $v['grade'] = 0;
        $v['source'] = $r['source'];
        $v['track'] = $r['track'];
        $v['request_date'] = $r['cdate'];
        $v['status'] = 60; // 開發中
        $rs->insert('member', $v);
        $mid = $rs->last_id;
        // history
        unset ($v);
        $v['member_id'] = $mid;
        $v['type'] = 10; // 指派
//        $v['content'] = '聯絡時段: ' . $var_contact_time[$r['contact_time']];
        $v['content'] = '留單時間: ' . $r['cdate'];
        $rs->insert('member_history', $v);
        $sql = "UPDATE request SET status = 20, deleted = 1, mdate = NOW() WHERE id = $id";
        $rs->execute($sql);
      }
    }
    if (is_array($error) && count($error) > 0)
      Message("下列名單已指派過：" . implode(", ", $error), true, MSG_ERROR);
    else
      Message('指派完成', true, MSG_OK);
    exit;
    break;
  case 'update':
    require_once (DOC_ROOT . 'lib/PHPExcel/IOFactory.php');
    $reader = PHPExcel_IOFactory::load($_FILES['file']['tmp_name']);
    $sheet = $reader->getActiveSheet();
    $cnt = $success = $fail = 0;
    $n = 1;
    unset ($records);
    for ($i = 2; $i < 10000; $i++)
    {
      unset ($v, $vv, $vvv);
      $vv['guest_name'] = $name = addslashes(trim($sheet->getCell("A$i")->getValue()));
      if (empty($name))
        break;
      $vv['source'] = trim($sheet->getCell("D$i")->getValue());
      $vv['email'] = $email = trim($sheet->getCell("C$i")->getValue());
      $tel = trim($sheet->getCell("B$i")->getValue());
      $tt = '';
      for ($k = 0; $k < strlen($tel); $k++)
      {
        $c = substr($tel, $k, 1);
        if (is_numeric($c))
          $tt .= $c;
      }
      $vv['tel'] = $tt;
      // check exists
//      $sql = "SELECT * FROM `request` WHERE `tel` = '$tt' OR email = '$email'";
      $sql = "SELECT * FROM `request` WHERE `tel` = '$tt'";
      $rs2->query($sql);
      if ($rs2->count > 0)
      { // exist
        $vv['exists'] = true;
        $vv['color'] = '#FFCCCC';
        $fail++;
      }
      else
      {
        $vv['status'] = 10;
        $rs2->insert('request', $vv);
        $vv['exists'] = false;
        $success++;
      }
      $records[] = $vv;
    }
    $VARS['list'] = $records;
    $VARS['fail'] = $fail;
    $VARS['success'] = $success;
    $VARS['total'] = $fail + $success;
    $VARS['add_button'] = false;
    //print_r($VARS);
    break;
  default:
    $TABLE = 'request';
    $FUNC_NAME = '需求指派';
    $CAN_DELETE = true;
    $SELECT = array ('status');
    $SEARCH = true;
    $SEARCH_KEYS = array ('guest_name', 'tel', 'email', 'source', 'track');
    $SORT_BY = 'id';
    $TRANSLATE = array (
      'status' => array($var_member_status, $var_member_status_color),
      'gender' => array($var_member_gender, $var_member_gender_color),
      'contact_time' => array($var_contact_time, $var_contact_time_color),
    );
    $SPECIAL = "status = 10";
    $CONSTRAINT = [
      'tel' => ['電話', CONSTRAINT_UNIQUE],
      //'email' => ['Email', CONSTRAINT_UNIQUE],
    ];

    $VARS['custom'] = <<<EOT
<a class="btn-flat primary" href="/admin/request_assign/import"><i class="icon-plus"></i> 匯入名單</a>&nbsp;&nbsp;&nbsp;
EOT;

    $VARS['gender_select'] = GenRadio('gender', $var_member_gender, true);

    $sql = <<<EOT
SELECT u.id, CONCAT(u.first_name, ' ', u.user_name, ' [', g.group_name, ']') AS data
FROM `group` g, `user` u, `user_group` ug
WHERE g.status = 10 AND u.status = 10 AND u.id = ug.user_id AND ug.group_id = g.id AND g.is_sales = 1
ORDER BY group_name, user_name
EOT;
    $VARS['sales_select'] = GenSelectBySQL('sales', $sql, false, true);

////////////////////////////////////
    require_once('func.inc.php');
////////////////////////////////////
//$VARS['add_button'] = false;
    break;
}

function fn_callback($r)
{
  return $r;
}

function fn_new()
{
}

function fn_before_add()
{
  global $rs2, $rs3;

  $tel = GetParam('tel');
  $sql = "SELECT * FROM `member` WHERE `mobile` = '$tel'";
  $rs2->query($sql);
  $sql = "SELECT * FROM `request` WHERE `tel` = '$tel'";
  $rs3->query($sql);
  if ($rs2->count > 0 || $rs3->count > 0)
  {
    Message('電話已存在於系統內(開發名單或會員)');
    return false;
  }
  return true;
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