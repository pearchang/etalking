<?php

$TABLE = 'product';
$NAME = '產品';
$SELECT = array ('status');
$SEARCH = true;
$SEARCH_KEYS = array ('prod_name');
$IMAGE_WIDTH = 1200;
$IMAGE_HEIGHT = 1200;
$THUMB_WIDTH = 100;
$THUMB_HEIGHT = 100;
$SORT_BY = 'status DESC, cat_id, prod_name';
$IMG_PATH = '/imgs/pro/';
$IMG_TABLE = 'pro_images';
$CHECKBOX = array ('spot');
$IMAGES = array (
  0 => array ('title' => '大圖', 'size' => array (800, 629, 100, 78), 'required' => true),
);
$EDITORS = array (
  array ('type' => EDITOR_TEXT, 'name' => 'brief', 'height' => 10),
  array ('type' => EDITOR_HTML, 'name' => 'content', 'height' => 500),
);
$VARS['status_select'] = GenSelect('status', $var_doc_status, false, true);

////////////////////////////////////
	require_once('func.inc.php');
////////////////////////////////////


function fn_callback($r)
{
	global $rs3, $var_doc_status, $var_doc_status_color, $var_doc_yesno, $var_doc_yesno_color;

  $r['status_text'] = $var_doc_status[$r['status']];
  $r['status_text_color'] = $var_doc_status_color[$r['status']];
  $r['spot_text'] = $var_doc_yesno[$r['spot']];
  $r['spot_text_color'] = $var_doc_yesno_color[$r['spot']];
	return $r;
}

function fn_new()
{
  global $VARS;

  $VARS['status'] = '10';
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