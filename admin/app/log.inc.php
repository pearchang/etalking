<?php
$table_define = [
  'consultant' => 1,
  'group' => 2,
  'user' => 3,
  'enterprise' => 4,
];

$log_define = [
  'system' => [],  // 系統設定
  'group' => [ // 組別管理
    'group' => [
      'name' => '組別',
      'key' => 'id',
      'field' => 'group_name',
    ],
  ],
  'user' => [ // 帳號管理
    'user' => [
      'name' => '帳號',
      'key' => 'id',
      'field' => 'user_name',
    ],
  ],
  'consultant' => [ // 顧問管理
    'consultant' => [
      'name' => '顧問',
      'key' => 'id',
      'field' => 'first_name,last_name',
    ],
  ],
  'enterprise' => [ // 企業帳號管理
    'enterprise' => [
      'name' => '企業帳號',
      'key' => 'id',
      'field' => 'ent_name',
    ],
  ],
  'member' => [ // 學員管理
    'member' => [
      'name' => '學員管理',
      'key' => 'id',
      'field' => 'member_name',
    ],
  ],
];

define ('ACT_READ', '讀取');
define ('ACT_ADD', '新增');
define ('ACT_EDIT', '編輯');
define ('ACT_DELETE', '刪除');

$orig_data = '';

function before_log($table, $id = 0)
{ // orig data
  global $orig_data;

  if ($id == 0)
  {
    $orig_data = '';
    return;
  }
  $rs = new ResultSet();
  $rs->select($table, $id);
  $orig_data = $rs->fetch(true);
}

function log_action($act, $table, $id, $data, $user = '', $title = '')
{
  global $log_define, $table_define, $orig_data, $VARS;

  $v['user_id'] = empty($_SESSION['admin_id']) ? 0 : $_SESSION['admin_id'];
  $v['user_name'] = empty($user) ? $_SESSION['admin_name'] : $user;
  if (!isset($log_define[FUNC]))
  {
    $v['table_id'] = 0;
    $v['table_name'] = $table;
    $v['data_id'] = $id;
    $v['data_title'] = $title;
  }
  else
  {
    if (isset($log_define[FUNC][$table]))
    {
      $tbl = $log_define[FUNC][$table];
      $v['table_id'] = $table_define[$table];
      $v['table_name'] = $tbl['name'];
      $v['data_id'] = $id;
      if (strstr($tbl['field'], ','))
        $fld = explode(',', $tbl['field']);
      else
        $fld = array($tbl['field']);
      foreach ($fld as $f)
        $tt[] = is_null($data) ? $orig_data[$f] : $data[$f];
      $v['data_title'] = implode(' ', $tt);
    }
    else
    {
      $v['table_id'] = 0;
      $v['table_name'] = $table;
      $v['data_id'] = $id;
      $v['data_title'] = $title;
    }
  }
  $v['action'] = $act;
  $v['act_name'] = $VARS['menu_name'];

  $rs = new ResultSet();
  $rs->insert('action_log', $v);
  $aid = $rs->last_id;
  unset ($v);
  $v['new_data'] = is_null($data) ? '' : serialize($data);
  $v['old_data'] = serialize($orig_data);
  $v['log_id'] = $aid;
  $rs->insert('action_log_data', $v);
}
?>