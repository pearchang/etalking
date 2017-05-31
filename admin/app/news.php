<?php
$TABLE = 'document';
$TYPE = DOC_TYPE_NEWS;
$NAME = '最新消息';
$SELECT = array ('status');
$SEARCH = true;
$SEARCH_KEYS = array ('data');
$SORT_BY = 'date DESC';
$TRANSLATE = array (
  'status' => array($var_doc_status, $var_doc_status_color),
);
$EDITORS = array (
  array ('type' => EDITOR_HTML, 'name' => 'content', 'height' => 300),
  array ('type' => EDITOR_TEXT, 'name' => 'content2', 'height' => 5),
);
$IMG_PATH = '/imgs/news/';
$IMG_TABLE = 'doc_images';
$IMAGES = array (
  0 => array ('title' => '主圖', 'size' => array (800, 504, 100, 63), 'required' => false),
);

$VARS['status_select'] = GenSelect('status', $var_doc_status, false, true);

////////////////////////////////////
require_once('func.inc.php');
////////////////////////////////////

function fn_callback($r)
{
  //print_r($r);
  return $r;
}

function fn_new()
{
  global $VARS;

  $VARS['status'] = DOC_STATUS_SHOW;
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