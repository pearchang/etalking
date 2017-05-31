<?php
$TABLE = 'material';
$FUNC_NAME = '教材';
$CAN_DELETE = true;
//$SELECT = array ('status');
$SEARCH = true;
$SEARCH_KEYS = array ('sn', 'title', 'eng_title');
$SORT_BY = 'eng_title';
$TRANSLATE = array (
  'status' => array($var_general_status, $var_general_status_color),
  'type' => array($var_material_type),
);
$FILTER = '-';
$FILTER_NAME = '等級';
$SPECIAL = "parent = 0";

$RADIO = array('status');
$SELECT = array('type');
$CHECKBOX = array (
  array ('field' => 'interest', 'table' => 'material_interest', 'key' => 'material_id', 'foreign_key' => 'interest_id'),
  array ('field' => 'level', 'table' => 'material_level', 'key' => 'material_id', 'foreign_key' => 'level_id'),
);
$EDITORS = array (
  array ('type' => EDITOR_TEXT, 'name' => 'brief', 'height' => 5),
);
$FILE_PATH = '/files/material/';
$FILE_TABLE = 'material_files';
$FILES = array (
  0 => array ('title' => 'PDF檔', 'required' => true, 'accept' => '.pdf'),
  1 => array ('title' => 'PPT檔', 'required' => true, 'accept' => '.ppt, .pptx'),
  2 => array ('title' => 'UCF檔', 'required' => true, 'accept' => '.ucf'),
);

$VARS['type_select'] = GenRadio('type', $var_material_type, true, true);
$VARS['status_select'] = GenRadio('status', $var_general_status, true, true);
$VARS['level_select'] = GenCheckboxBySQL('level', "SELECT id, level_name AS `data` FROM `level` WHERE status = 10 ORDER BY `begin`", true);
$VARS['interest_select'] = GenCheckboxBySQL('interest', "SELECT id, title AS `data` FROM `interest` WHERE status = 10 ORDER BY eng_title", true);
GenFilterBySQL('filter', "SELECT id AS value, level_name AS text FROM level WHERE status = 10");

$sql = "SELECT id FROM `level` WHERE status = 10 AND level_name = 'A0'";
$rs->query($sql);
$VARS['level_a0'] = $rs->record();
//$sql = "SELECT * FROM material ORDER BY id";
//$rs->query($sql);
//unset ($v);
//while (($r = $rs->fetch()))
//{
//  if (strlen($r['sn'] < 6))
//  {
//    $v['sn'] = getSerialNumber2(SN_MATERIAL);
//    $rs2->update('material', $r['id'], $v);
//  }
//}

$VARS['material_id'] = GetParam('id');

////////////////////////////////////
require_once('func.inc.php');
////////////////////////////////////

function before_index($sql, $filters)
{
  global $filter;

  if ($filter != -9999)
    $sql = "SELECT m.* FROM material m, material_level l WHERE m.id = l.material_id AND l.level_id = $filter $filters";
  return $sql;
}

function fn_callback($r)
{
  global $rs3;

  $sql = "SELECT level_name FROM material_level m, level l WHERE m.material_id = {$r['id']} AND m.level_id = l.id";
  $rs3->query($sql);
  while (($rr = $rs3->fetch()))
    $g[] = "<span class='label label-blue'>{$rr['level_name']}</span>&nbsp;";
  $r['level_name'] = is_array($g) ? implode($g) : '';

  return $r;

  return $r;
}

function update_child_interest($pid)
{
  global $rs2, $rs3;

  $sql = "SELECT GROUP_CONCAT(id) FROM material WHERE parent = $pid";
  $rs2->query($sql);
  $child = $rs2->record();
  if (!empty($child))
  {
    $sql = "DELETE FROM material_interest WHERE material_id IN ($child)";
    $rs2->execute($sql);
    $sql = "SELECT interest_id FROM material_interest WHERE material_id = $pid";
    $rs2->query($sql);
    $int = $rs2->record_array();
    $sql = "SELECT id FROM material WHERE parent = $pid";
    $rs2->query($sql);
    unset ($v);
    while (($r = $rs2->fetch()))
    {
      $mid = $r['id'];
      foreach ($int as $ii)
      {
        $v['material_id'] = $mid;
        $v['interest_id'] = $ii;
        $rs3->insert('material_interest', $v);
      }
    }
  }
}

function fn_new()
{
  global $VARS;

  $VARS['status'] = DOC_STATUS_SHOW;
}

function fn_add($id)
{
  global $rs2;

  $v['sn'] = getSerialNumber2(SN_MATERIAL);
  $rs2->update('material', $id, $v);
}

function fn_edit($id)
{
  global $rs2;

  $rs2->select('material', $id);
  AssignValue($rs2, null, 'mt');

  update_child_interest($id);
}

function fn_modify($id)
{
}
?>