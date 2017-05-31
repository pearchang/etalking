<?php
$VARS['material_id'] = $id = $_POST['parent'] = GetParam('mid');
$_POST['type'] = 20;

$rs->select('material', $id);
AssignValue($rs, null, 'mt');

$TABLE = 'material';
$FUNC_NAME = '教材';
$CAN_DELETE = true;
//$SELECT = array ('status');
$SEARCH = false;
$SORT_BY = 'rank';
$TRANSLATE = array (
  'status' => array($var_general_status, $var_general_status_color),
);
$SPECIAL = 'parent = ' . $id;

$RADIO = array('status');
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

$VARS['status_select'] = GenRadio('status', $var_general_status, true, true);

////////////////////////////////////
require_once('func.inc.php');
////////////////////////////////////

function fn_callback($r)
{
  return $r;
}

function update_child_interest($cid)
{
  global $rs2, $rs3;

  $sql = "SELECT parent FROM material WHERE id = $cid";
  $rs2->query($sql);
  $pid = $rs2->record();
  $sql = "DELETE FROM material_interest WHERE material_id IN $cid";
  $rs2->execute($sql);
  $sql = "SELECT interest_id FROM material_interest WHERE material_id = $pid";
  $rs2->query($sql);
  $int = $rs2->record_array();
  foreach ($int as $ii)
  {
    $v['material_id'] = $cid;
    $v['interest_id'] = $ii;
    $rs3->insert('material_interest', $v);
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

  update_child_interest($id);
}

function fn_edit($id)
{
}

function fn_modify($id)
{
  update_child_interest($id);
}
?>