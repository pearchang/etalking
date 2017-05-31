<?php
$TABLE = 'questionnaire';
$FUNC_NAME = '問題';
//$SELECT = array ('status');
$SEARCH = true;
$SEARCH_KEYS = array ('text');
$SORT_BY = 'rank';
$TRANSLATE = array (
  'type' => array($var_questionnaire_type),
);

$VARS['type_select'] = GenRadio('type', $var_questionnaire_type, true);
////////////////////////////////////
require_once('func.inc.php');
////////////////////////////////////

function fn_callback($r)
{
  global $rs3;

  $sql = "SELECT tag, GROUP_CONCAT(`text` ORDER BY rank) AS content FROM ques_item WHERE ques_id = {$r['id']} GROUP BY tag";
  //echo $sql;
  $rs3->query($sql);
  $r['item'] = AssignResult($rs3);

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