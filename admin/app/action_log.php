<?php
$TABLE = 'action_log';
$FUNC_NAME = '操作紀錄';
$CAN_DELETE = false;
//$SELECT = array ('status');
$SEARCH = true;
$SEARCH_KEYS = array ('ent_name');
$SEARCH_DATE = array ('cdate');
$SORT_BY = '`id` DESC';
$VARS['add_button'] = false;

////////////////////////////////////
require_once('func.inc.php');
////////////////////////////////////

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