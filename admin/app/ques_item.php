<?php
$parent = $VARS['parent'] = GetParam('parent');
if (MODE == 'add')
{
	$content = explode("\n", GetParam('content'));
	foreach ($content as $c)
	{
		if (empty($c))
			continue;
		unset($v);
		$v['ques_id'] = $parent;
		$v['tag'] = GetParam('tag');
		$v['text'] = trim($c);
		$rs->insert('ques_item', $v);
	}
	Message('新增完成', false, MSG_OK);
	GoBack();
	exit;
}
$TABLE = 'ques_item';
$FUNC_NAME = '問題';
//$SELECT = array ('status');
$SEARCH = true;
$SEARCH_KEYS = array ('text');
$SORT_BY = 'rank';
$SPECIAL = "ques_id = $parent";

$rs3->select('questionnaire', $parent);
$r3 = $rs3->fetch();
$VARS['parent_text'] = $r3['text'];
$VARS['type_text'] = $var_questionnaire_type[$r3['type']];

////////////////////////////////////
require_once('func.inc.php');
////////////////////////////////////

function fn_callback($r)
{
	global $var_questionnaire_type, $rs3;


	return $r;
}

function fn_new()
{
	global $VARS;

	$VARS['tag'] = 0;
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