<?php
$TABLE = 'banner';
$TYPE = DOC_TYPE_BANNER;
$NAME = '官網banner';
$SELECT = array ('status');
$SEARCH = true;
$SORT_BY = 'cdate DESC';
$TRANSLATE = array (
  'status' => array($var_doc_status, $var_doc_status_color),
);
$SEARCH_KEYS = array ('name');
$SEARCH_DATE = array ('cdate');
$IMG_PATH = '/imgs/banner/';
$IMG_TABLE = 'banner_images';
$IMAGES = array (
  0 => array ('title' => '大圖', 'size' => array (1901, 621, 190, 62), 'required' => true),
  1 => array ('title' => '中圖', 'size' => array (1201, 621, 120, 62), 'required' => true),
  2 => array ('title' => '小圖', 'size' => array (1001, 861, 100, 86), 'required' => true),
);

$VARS['status_select'] = GenSelect('status', $var_doc_status, false, true);

////////////////////////////////////
require_once('func.inc.php');
////////////////////////////////////

function fn_callback($r)
{
  global $rs2;

  $sql = "SELECT thumb FROM banner_images WHERE tag = 0 AND parent = {$r['id']}";
  $rs2->query($sql);
  if ($rs2->count > 0)
    $r['thumb'] = $rs2->record();
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