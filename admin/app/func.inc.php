<?php
//function cbDocument($r)
//{
////	echo $r['thumb'];
//	$r['picture'] = ShowPicture($r['picture']);
//	$r['thumb'] = ShowPicture($r['thumb']);
////	echo $r['thumb'];
//	$r['file'] = basename($r['file']);
//	if (function_exists('fn_callback'))
//		fn_callback(&$r);
//}

function cbFunc($r)
{
  global $TRANSLATE, $rs3, $CALLBACK;
	// $r['picture'] = ShowPicture($r['picture']);
	// $r['thumb'] = ShowPicture($r['thumb']);
  if (is_array($TRANSLATE))
  {
    foreach ($TRANSLATE as $k => $v)
    {
      $r[$k . '_text'] = $v[0][$r[$k]];
      if (is_array($v[1]))
        $r[$k . '_text_color'] = $v[1][$r[$k]];
    }
  }
  // creator, modifier
  $r['creator_text'] = $rs3->get_value('user', 'user_name', $r['creator']);
  if ($CALLBACK)
    return call_user_func_array($CALLBACK, array($r));
	elseif (function_exists('fn_callback'))
		return call_user_func_array('fn_callback', array($r));
  else
    return $r;
}

if (isset($FUNC_NAME))
  $VARS['FUNC_NAME'] = $FUNC_NAME;
if (isset($FILTER_NAME))
  $VARS['FILTER_NAME'] = $FILTER_NAME;
if (isset($FILTER2_NAME))
  $VARS['FILTER2_NAME'] = $FILTER2_NAME;
$VARS['NAME'] = $NAME;
if (isset($TYPE))
{
  // TODO: type filter?
  $where = ($TABLE == 'document') ? "`type` = $TYPE" : '1';
	$_POST['type'] = $TYPE;
	if (empty($IMG_PATH))
		$IMG_PATH = "/imgs/$TYPE/";
}
else
{
  $TYPE = '0';
	$where = '1';
	if (empty($IMG_PATH))
		$IMG_PATH = "/imgs/";
}
if (isset($SPECIAL))
	$where .= " AND $SPECIAL";

if (empty($SORT_BY))
	$SORT_BY = 'id';
if (!isset($TABLE))
	$TABLE = 'document';
if (!isset($FIELD))
	$FIELD = 'title';
if (isset($SEARCH) && $SEARCH)
{
	$VARS['keyword'] = $keyword = GetParam('keyword', '');
	// if (trim($keyword) == '')
	// 	unset($SEARCH);
}

$id = GetParam('id');
$SUB = GetParam('sub');
switch ($MODE)
{
case 'delete':
  $r = true;
  before_log($TABLE, $id);
  if (function_exists('fn_before_delete'))
    $r = call_user_func('fn_before_delete', $id);
  if ($r)
  {

//  if (is_array($IMAGES))
//  {
//    foreach ($IMAGES as $kk => $ii)
//    {
//      $sql = "SELECT `id` FROM `{$IMG_TABLE}` WHERE `parent_type` = {$TYPE} AND `parent` = {$id} AND `tag` = $kk";
//      $rs2->query($sql);
//      if ($rs2->count > 0)
//      {
//        $iid = $rs2->record();
//        DeleteFiles($IMG_TABLE, $iid, array('image', 'thumb', 'origin'));
//        $rs2->delete($IMG_TABLE, $iid);
//      }
//    }
//  }
//  DeleteFiles($TABLE, $id, array('file', 'picture', 'thumb'));
    $rs->delete($TABLE, $id);
    if (function_exists('fn_delete'))
      call_user_func('fn_delete', $id);
    log_action(ACT_DELETE, $TABLE, $id, null);

    Message('刪除成功!', false, MSG_OK);
  }
	GoLast();
	break;
case 'new':
	//$VARS['action_name'] = '新增';
  $VARS['func_name'] = '新增' . $NAME;
	$VARS['back'] = true;
	$VARS['date'] = date('Y-m-d');
  if (function_exists('fn_before_new'))
    call_user_func('fn_before_new');

  if (is_array($EDITORS))
  {
    foreach ($EDITORS as $e)
    {
      $type = empty($e['type']) ? EDITOR_TEXT : $e['type'];
      if (empty($e['height']))
        $e['height'] = $type == EDITOR_HTML ? 300 : 20;
      $ename = empty($e['name']) ? 'content' : $e['name'];
      if ($type == EDITOR_HTML)
        NewEditor($ename, $VARS[$ename], $e['height'], '100%', "editor_$ename", isset($e['required']) ? $e['required'] : false);
      else
        NewTextArea($ename, $VARS[$ename], $e['height'], '100%', "editor_$ename", isset($e['required']) ? $e['required'] : false);
    }
  }
	$VARS['url'] = 'http://';
	if (function_exists('fn_new'))
		call_user_func('fn_new');
	if (is_array($SELECT))
	{
		foreach ($SELECT as $v)
		{
			if (isset($VARS[$v]))
//				$SCRIPTS[] = "\$(\"#$v option[value='{$VARS[$v]}']\").prop('selected', true);";
        $SCRIPTS[] = "\$(\"#$v\").val('{$VARS[$v]}');";
      else
      {
        $var = GetParam($v);
        if (!empty($var))
//          $SCRIPTS[] = "\$(\"#$v option[value='{$var}']\").prop('selected', true);";
          $SCRIPTS[] = "\$(\"#$v\").val('$var');";
      }
		}
	}
  if (is_array($RADIO))
  {
    foreach ($RADIO as $v)
    {
      if (isset($VARS[$v]))
        $SCRIPTS[] = "\$(\"input[name='$v'][value='{$VARS[$v]}']\").prop('checked', true);";
      else
      {
        $var = GetParam($v);
        if (!empty($var))
          $SCRIPTS[] = "\$(\"input[name='$v'][value='{$VARS[$v]}']\").prop('checked', true);";
      }
    }
  }
  if ($FILTER)
    $VARS['filter'] = $filter = GetParam('filter', '-9999');
  if ($FILTER2)
    $VARS['filter2'] = $filter2 = GetParam('filter2', '-9999');
  // images
  if (is_array($IMAGES))
  {
    foreach ($IMAGES as $kk => $ii)
    {
      $ii['i'] = $kk;
      $ii['width'] = $ii['size'][0];
      $ii['height'] = $ii['size'][1];
      $IMAGES[$kk] = $ii;
    }
    $VARS['IMAGES'] = genView('images_new', array ('list' => $IMAGES));
  }
  // images
  if (is_array($FILES))
  {
    foreach ($FILES as $kk => $ii)
    {
      $ii['i'] = $kk;
      $FILES[$kk] = $ii;
    }
    $VARS['FILES'] = genView('files_new', array ('list' => $FILES));
  }

  if (file_exists(APP_PATH . 'langs/func.new.inc.php'))
    include (APP_PATH . 'langs/func.new.inc.php');
  break;
case 'add':
// 	print_r($_POST);
//  print_r($_FILES);
// 	exit;
//	SetBit('hide');
  // 判斷constraints
  if (isset($CONSTRAINT) && is_array($CONSTRAINT))
  {
    foreach ($CONSTRAINT as $field => $v)
    {
      $d = GetParam($field);
      if (empty($d))
        continue;
      unset ($err);
      switch ($v[1]) // type
      {
        case CONSTRAINT_UNIQUE:
          $sql = "SELECT `$field` FROM `$TABLE` WHERE `$field` = '$d'" . ($CAN_DELETE ? ' AND deleted = 0' : '');
          $rs->query($sql);
          if ($rs->count > 0)
            $err[] = $v[0] . "已存在，不能重複";
          break;
      }
    }
    if (isset($err) && is_array($err))
    {
      Message(implode("\n", $err), false, MSG_WARN);
      GoLast();
    }
  }

  $can_add = true;
  if (function_exists('fn_before_add'))
    $can_add = call_user_func('fn_before_add');
  if (!$can_add)
    GoLast();
  before_log($TABLE);
	if (isset($SEO_KEY))
		$_POST['seo_key'] = strtolower(str_replace($special_chars, '-', $_POST[$SEO_KEY]));
	//$_POST['rank'] = time();
	if (isset($TYPE) && $TYPE != '0')
		$_POST['type'] = $TYPE;
  if (is_array($EDITORS))
  {
    unset ($ff);
    foreach ($EDITORS as $e)
      $ff[] = empty($e['name']) ? 'content' : $e['name'];
    $rs->insertWithEditor($TABLE, $ff, $IMG_PATH);
  }
	else
		$rs->insert($TABLE);
	$id = $rs->last_id;
  $rs->select($TABLE, $id);
  $record = $rs->fetch(true);
  log_action(ACT_ADD, $TABLE, $id, $record);

  if (is_array($CHECKBOX))
  {
    foreach ($CHECKBOX as $v)
    { // $v = array ('field' => 'xxx', 'table' => 'xx', 'key' => 'xx', foreign_key => 'xx')
      if (!empty($_POST[$v['field']]))
      {
        $v2 = $_POST[$v['field']]; //explode(',', $_POST[$v['field']]);
        unset ($vv);
        foreach ($v2 as $fid)
        {
          $vv[$v['key']] = $id;
          $vv[$v['foreign_key']] = $fid;
          $rs2->insert($v['table'], $vv);
        }
      }
    }
  }

  if (is_array($IMAGES))
  {
    foreach ($IMAGES as $kk => $ii)
    {
      unset ($v);
      $idiv[0] = $ii['size'][0];
      $idiv[1] = $ii['size'][1];
      $tdiv[0] = $ii['size'][2];
      $tdiv[1] = $ii['size'][3];
      $nimg = 'image' . $kk;
      $ntxt = 'image_text' . $kk;
      $npic = 'image';
      $nthb = 'thumb';
      $norg = 'origin';
      $file = time() . $id;
      $image = MakePicture($nimg, $npic, $IMG_PATH, $file . '_' . $kk, $idiv);
      if (isset($_POST[$ntxt]))
        $v['text'] = $_POST[$ntxt];
      if ($image != '')
      {
        $v[$npic] = $image;
        if ($THUMB_COPY)
          $v[$nthb] = $image;
        else
          $v[$nthb] = MakePicture($nimg, $nthb, $IMG_PATH, $file . '_' . $kk . 's', $tdiv);
//          $v[$nthb] = MakeThumb($IMG_PATH, $image, $nthb, 's', $tdiv);
        if ($SAVE_ORIGIN)
          $v[$norg] = MakePicture($nimg, $norg, $IMG_PATH, $file . '_' . $kk . 'o' . $$kk, array(10000, 10000));
        if ($kk == 0)
          $rs->update($TABLE, $id, $v);
      }
      if (is_array($v))
      {
        $v['parent_type'] = $TYPE;
        $v['parent'] = $id;
        $v['tag'] = $kk;
        $v['rank'] = $id * 10;
        $rs->insert($IMG_TABLE, $v);
      }
    }
  }


  if (is_array($FILES))
  {
    foreach ($FILES as $kk => $ii)
    {
      if (empty($_FILES['file' . $kk]['name']))
        continue;
      unset ($v);
      $v['file'] = MoveUpload('file' . $kk, $FILE_PATH, $id . '_' . $kk);
      $v['file_name'] = $_FILES['file' . $kk]['name'];
      $v['parent_type'] = $TYPE;
      $v['parent'] = $id;
      $v['tag'] = $kk;
      $v['rank'] = $id * 10;
      $rs->insert($FILE_TABLE, $v);
    }
  }

  if (file_exists(APP_PATH . 'langs/func.add.inc.php'))
    include (APP_PATH . 'langs/func.add.inc.php');
	if (function_exists('fn_add'))
		call_user_func('fn_add', $id);
    Message('新增成功!', false, MSG_OK);
  if (GetParam('special') == 'popup')
    json_output(['status' => true]);
	GoBack();
	break;
case 'edit':
	//$VARS['action_name'] = '編輯';
  $VARS['func_name'] = '編輯' . $NAME;
	$VARS['back'] = true;
	if ($SUB == 'del')
	{
		DeleteFiles($TABLE, $id);
    // TODO: multi-lang
		GoLast();
	}
	$rs->select($TABLE, $id);
	$r = $rs->fetch();
  $r = cbFunc($r);
	foreach ($r as $k => $v)
		$VARS[$k] = str_replace('"', '&quot;', $v);
//	if (is_array($CHECKBOX))
//		GetBit($CHECKBOX);

  if (is_array($CHECKBOX))
  {
    foreach ($CHECKBOX as $v)
    { // $v = array ('field' => 'xxx', 'table' => 'xx', 'key' => 'xx', foreign_key => 'xx')
      $sql = "SELECT `{$v['foreign_key']}` FROM `{$v['table']}` WHERE `{$v['key']}` = $id";
      $rs->query($sql);
      while (($r = $rs->fetch()))
        $SCRIPTS[] = "\$(\"#{$v['field']}__{$r[$v['foreign_key']]}\").prop('checked', true);";
    }
  }

  if (is_array($RADIO))
  {
    foreach ($RADIO as $v)
    {
      if (isset($VARS[$v]))
        $SCRIPTS[] = "\$(\"input[name='$v'][value='{$VARS[$v]}']\").prop('checked', true);";
    }
  }

	if (is_array($SELECT))
	{
		foreach ($SELECT as $v)
		{
			if (isset($VARS[$v]))
//        $SCRIPTS[] = "\$(\"#$v option[value='{$VARS[$v]}']\").prop('selected', true);";
        $SCRIPTS[] = "\$(\"#$v\").val('{$VARS[$v]}');";
		}
	}
	//	$VARS['file'] = basename($VARS['file']);
  if (is_array($EDITORS))
  {
    foreach ($EDITORS as $e)
    {
      $type = empty($e['type']) ? EDITOR_TEXT : $e['type'];
      if (empty($e['height']))
        $e['height'] = $type == EDITOR_HTML ? 300 : 20;
      $ename = empty($e['name']) ? 'content' : $e['name'];
      if ($type == EDITOR_HTML)
        NewEditor($ename, $VARS[$ename], $e['height'], '100%', "editor_$ename", isset($e['required']) ? $e['required'] : false);
      else
        NewTextArea($ename, $VARS[$ename], $e['height'], '100%', "editor_$ename", isset($e['required']) ? $e['required'] : false);
    }
  }
  if (is_array($IMAGES))
  {
    $sql = "SELECT * FROM `{$IMG_TABLE}` WHERE `parent_type` = {$TYPE} AND `parent` = $id";
    $rs2->query($sql);
//    $rs2->select($IMG_TABLE, $id, '*', 'parent');
    $rr = $rs2->fetch_array();
    unset ($imgs);
    if (is_array($rr))
      foreach ($rr as $r)
        $imgs[$r['tag']] = $r;
    foreach ($IMAGES as $kk => $ii)
    {
      $ii['i'] = $kk;
      $ii['width'] = $ii['size'][0];
      $ii['height'] = $ii['size'][1];
      if (is_array($imgs[$kk]))
        $ii = array_merge($ii, $imgs[$kk]);
      $IMAGES[$kk] = $ii;
    }
    $VARS['IMAGES'] = genView('images_edit', array ('list' => $IMAGES));
  }
  if (is_array($FILES))
  {
    $sql = "SELECT * FROM `{$FILE_TABLE}` WHERE `parent_type` = {$TYPE} AND `parent` = $id";
    $rs2->query($sql);
//    $rs2->select($FILE_TABLE, $id, '*', 'parent');
    $rr = $rs2->fetch_array();
    unset ($files);
    if (is_array($rr))
      foreach ($rr as $r)
        $files[$r['tag']] = $r;
    foreach ($FILES as $kk => $ii)
    {
      $ii['i'] = $kk;
      if (is_array($files[$kk]))
        $ii = array_merge($ii, $files[$kk]);
      $FILES[$kk] = $ii;
    }
    $VARS['FILES'] = genView('files_edit', array ('list' => $FILES));
  }
  if (file_exists(APP_PATH . 'langs/func.edit.inc.php'))
    include (APP_PATH . 'langs/func.edit.inc.php');
	if (function_exists('fn_edit'))
		call_user_func('fn_edit', $id);
	break;
case 'modify':
case 'modify2':
	//	$title = $rs->get_value($TABLE, $FIELD, $id);
//	actionLog('編輯', $title, $TABLE, $id, $DOC_TYPE);
// 	print_r($_POST);
// 	exit;

  // 判斷constraints
  if (isset($CONSTRAINT) && is_array($CONSTRAINT))
  {
    foreach ($CONSTRAINT as $field => $v)
    {
      $d = GetParam($field);
      if (empty($d))
        continue;
      unset ($err);
      switch ($v[1]) // type
      {
        case CONSTRAINT_UNIQUE:
          $sql = "SELECT `$field` FROM `$TABLE` WHERE `$field` = '$d' AND `id` <> $id" . ($CAN_DELETE ? ' AND deleted = 0' : '');
          $rs->query($sql);
          if ($rs->count > 0)
            $err[] = $v[0] . "已存在，不能重複";
          break;
      }
    }
    if (isset($err) && is_array($err))
    {
      Message(implode("\n", $err), false, MSG_WARN);
      GoLast();
    }
  }

  before_log($TABLE, $id);
  $rs->select($TABLE, $id);
  $data = $rs->fetch();
  if (function_exists('fn_before_modify'))
    call_user_func('fn_before_modify', $id);
	if (isset($SEO_KEY))
		$_POST['seo_key'] = strtolower(str_replace($special_chars, '-', $_POST[$SEO_KEY]));
//	$doc = MoveUpload('doc', '/files/doc/', $id);
//	if ($doc != '')
//	{
//		$_POST['file'] = $doc;
//		$_POST['filename'] = $_FILES['doc']['name'];
//	}

  if (is_array($EDITORS))
  {
    unset ($ff);
    foreach ($EDITORS as $e)
      $ff[] = empty($e['name']) ? 'content' : $e['name'];
    $rs->updateWithEditor($TABLE, $id, $ff, $IMG_PATH);
  }
  else
    $rs->update($TABLE, $id);
  $rs->select($TABLE, $id);
  $record = $rs->fetch(true);
  log_action(ACT_EDIT, $TABLE, $id, $record);

  if (is_array($CHECKBOX))
  {
    foreach ($CHECKBOX as $v)
    { // $v = array ('field' => 'xxx', 'table' => 'xx', 'key' => 'xx', foreign_key => 'xx')
      if (empty($_POST[$v['field']]))
        $v2 = array ();
      else
      {
        $v2 = $_POST[$v['field']];
        //print_r($v2);
        unset ($vv);
        foreach ($v2 as $fid)
        {
          $vv[$v['key']] = $id;
          $vv[$v['foreign_key']] = $fid;
          $vv['permission'] = PERM_WRITE;
          $rs2->insert($v['table'], $vv);
        }
      }
      $sql = "SELECT `{$v['foreign_key']}` FROM `{$v['table']}` WHERE `{$v['key']}` = $id";
      $rs->query($sql);
      while (($r = $rs->fetch()))
      {
        if (!in_array($r[$v['foreign_key']], $v2))
        {
          $sql = "DELETE FROM `{$v['table']}` WHERE `{$v['key']}` = $id AND `{$v['foreign_key']}` = {$r[$v['foreign_key']]}";
          $rs2->execute($sql);
        }
      }
    }
  }

//  if (is_array($RADIO))
//    GetBit($RADIO);

  if (is_array($IMAGES))
  {
    foreach ($IMAGES as $kk => $ii)
    {
      unset ($v);
      $sql = "SELECT `id` FROM `{$IMG_TABLE}` WHERE `parent_type` = {$TYPE} AND `parent` = {$id} AND `tag` = $kk";
      $rs2->query($sql);
      $iid = $rs2->count > 0 ? $rs2->record() : 0;
      if (isset($_POST['delete' . $kk]) && $_POST['delete' . $kk])
      {
        if ($iid)
        {
          DeleteFiles($IMG_TABLE, $iid, array('image', 'thumb', 'origin'));
          $rs2->delete($IMG_TABLE, $iid);
          if ($kk == 0)
          {
            $v['image'] = $v['thumb'] = '';
            $rs->update($TABLE, $id, $v);
          }
        }
      }
      $idiv[0] = $ii['size'][0];
      $idiv[1] = $ii['size'][1];
      $tdiv[0] = $ii['size'][2];
      $tdiv[1] = $ii['size'][3];
      $nimg = 'image' . $kk;
      $ntxt = 'image_text' . $kk;
      $npic = 'image';
      $nthb = 'thumb';
      $norg = 'origin';
      $file = time() . $id;
      $image = MakePicture($nimg, $npic, $IMG_PATH, $file . '_' . $kk, $idiv);
      if (isset($_POST[$ntxt]))
        $v['text'] = $_POST[$ntxt];
      if ($image != '')
      {
        if ($iid)
          DeleteFiles($IMG_TABLE, $iid, array('image', 'thumb', 'origin'));
        $v[$npic] = $image;
        if ($THUMB_COPY)
          $v[$nthb] = $image;
        else
          $v[$nthb] = MakePicture($nimg, $nthb, $IMG_PATH, $file . '_' . $kk . 's', $tdiv);
//          $v[$nthb] = MakeThumb($IMG_PATH, $image, $nthb, 's', $tdiv);
        if ($SAVE_ORIGIN)
          $v[$norg] = MakePicture($nimg, $norg, $IMG_PATH, $file . '_' . $kk . 'o' . $$kk, array(10000, 10000));
//        print_r($v);
        if ($kk == 0)
          $rs->update($TABLE, $id, $v);
      }
      if (is_array($v))
      {
        $v['parent_type'] = $TYPE;
        $v['parent'] = $id;
        $v['tag'] = $kk;
        $v['rank'] = $id * 10;
        $sql = "SELECT `id` FROM `{$IMG_TABLE}` WHERE `parent_type` = {$TYPE} AND `parent` = {$id} AND `tag` = $kk";
        $rs2->query($sql);
        if ($rs2->count == 0)
          $rs->insert($IMG_TABLE, $v);
        else
          $rs->update($IMG_TABLE, $iid, $v);
      }
    }
  }
  if (is_array($FILES))
  {
    foreach ($FILES as $kk => $ii)
    {
      if (empty($_FILES['file' . $kk]['name']))
        continue;
      unset ($v);
      $v['file'] = MoveUpload('file' . $kk, $FILE_PATH, $id . '_' . $kk);
      $v['file_name'] = $_FILES['file' . $kk]['name'];
      $v['parent_type'] = $TYPE;
      $v['parent'] = $id;
      $v['tag'] = $kk;
      $v['rank'] = $id * 10;
      $sql = "SELECT id FROM $FILE_TABLE WHERE `parent` = $id AND `tag` = $kk";
      $rs2->query($sql);
      if ($rs2->count == 0)
        $rs->insert($FILE_TABLE, $v);
      else
      {
        // TODO unlink
        $fid = $rs2->record();
        $rs->update($FILE_TABLE, $fid, $v);
      }
    }
  }
  if (file_exists(APP_PATH . 'langs/func.modify.inc.php'))
    include (APP_PATH . 'langs/func.modify.inc.php');
// 	echo htmlspecialchars(print_r($record, true));
// 	exit;
	if (function_exists('fn_modify'))
		call_user_func('fn_modify', $id);
	Message('編輯完成!', false, MSG_OK);
	if ($MODE != 'modify2')
		GoBack();
	else
		GoLast();
	break;
/*
case 'update':
	$sql = "SELECT id FROM $TABLE WHERE $where";
	$rs->query($sql);
	if ($rs->count == 0)
	{
		$v['type'] = $TYPE;
		$rs->insert($TABLE, $v);
		$id = $rs->last_id;
	}
	else
		$id = $rs->record();
	$image = MakePicture('image', 'picture', $IMG_PATH, $id, $idiv);
	if ($image != '')
	{
		$_POST['picture'] = $image;
		if ($THUMB_COPY)
			$_POST['thumb'] = $image;
		else
			$_POST['thumb'] = MakeThumb($IMG_PATH, $image, 'thumb', 's', $tdiv);
		if ($SAVE_ORIGIN)
			$v['origin'] = MakePicture('image', 'origin', $IMG_PATH, $id . 'o', array(2000, 1500));
	}
	$doc = MoveUpload('doc', '/files/doc/', $id);
	if ($doc != '')
	{
		$_POST['file'] = $doc;
		$_POST['filename'] = $_FILES['doc']['name'];
	}
	if (is_array($CHECKBOX))
		SetBit($CHECKBOX);
  if (is_array($EDITORS))
  {
    unset ($ff);
    foreach ($EDITORS as $e)
      $ff[] = empty($e['name']) ? 'content' : $e['name'];
    $rs->updateWithEditor($TABLE, $id, $ff, $IMG_PATH);
  }
  else
    $rs->update($TABLE, $id);
	Message('更新完成!', false, MSG_OK);
	GoLast();
	break;
*/
case 'view':
	$rs->select($TABLE, $id);
	AssignValue($rs, 'cbFunc');
	if (function_exists('fn_view'))
		call_user_func('fn_view', $id);
	if (is_array($SELECT))
	{
		foreach ($SELECT as $v)
		{
			if (isset($VARS[$v]))
        $SCRIPTS[] = "\$(\"#$v option[value='{$VARS[$v]}']\").prop('selected', true);";
		}
	}
  if (is_array($EDITORS))
  {
    foreach ($EDITORS as $e)
    {
      $type = empty($e['type']) ? EDITOR_TEXT : $e['type'];
      if (empty($e['height']))
        $e['height'] = $type == EDITOR_HTML ? 300 : 20;
      $ename = empty($e['name']) ? 'content' : $e['name'];
      if ($type == EDITOR_HTML)
        NewEditor($ename, '', $e['height'], '100%', "editor_$ename", isset($e['required']) ? $e['required'] : false);
      else
        NewTextArea($ename, '', $e['height'], '100%', "editor_$ename", isset($e['required']) ? $e['required'] : false);
    }
  }
	break;
case 'move':
	if ($FILTER)
	{
		$VARS['filter'] = $filter = GetParam('filter', '-9999');
		if ($filter != '-9999')
			$where .= " AND $FILTER = $filter";
	}
  if ($FILTER2)
  {
    $VARS['filter2'] = $filter2 = GetParam('filter2', '-9999');
    $VARS['filter2_field'] = $FILTER;
    if ($filter2 != '-9999')
      $where .= " AND $FILTER2 = $filter2";
  }
	//	$title = $rs->get_value($TABLE, $FIELD, $id);
//	actionLog('移動', $title, $TABLE, $id, $DOC_TYPE);
	RankExchange($TABLE, GetParam('up'), GetParam('rank'), $id, $where);
	GoLast();
	break;
case 'export':
default:
  unset ($params);
  unset ($filters);
	if ($FILTER)
	{
		$VARS['filter'] = $filter = GetParam('filter', '-9999');
    $VARS['filter_field'] = $FILTER;
		if ($FILTER != '-' && $filter != '-9999')
      $filters[] = "$FILTER = $filter";
	}
	if ($FILTER2)
	{
		$VARS['filter2'] = $filter2 = GetParam('filter2', '-9999');
    $VARS['filter2_field'] = $FILTER;
		if ($FILTER2 != '-' && $filter2 != '-9999')
			$filters[] = "$FILTER2 = $filter2";
	}
//	if (isset($TYPE))
//		$where .= " AND `type` = $TYPE";
	if (isset($SEARCH) && $SEARCH)
	{
    $VARS['search'] = true;
    if (!empty($keyword))
    {
  		unset($s);
  		foreach ($SEARCH_KEYS as $v)
      {
        if (!strstr($v, '.'))
          $v = "`$v`";
        $s[] = "$v LIKE '%$keyword%'";
      }
  		$filters[] = '(' . implode(' OR ', $s) . ')';
    }
	}
  $VARS['search_date'] = isset($SEARCH_DATE) && is_array($SEARCH_DATE);
  if ($VARS['search_date'] && GetParam('search_begin') != '' && GetParam('search_end') != '')
  {
    $VARS['search_date'] = true;
    $VARS['search_begin'] = $b = GetParam('search_begin');
    $VARS['search_end'] = $e = GetParam('search_end');
    unset($s);
    foreach ($SEARCH_DATE as $f)
      $s[] = " ($f BETWEEN '$b' AND '$e') ";
    $filters[] = ' (' . implode(' OR ', $s) . ')';
  }

  if (is_array($filters))
    $filters = 'AND ' . implode(' AND ', $filters);
  else
    $filters = '';
  if ($CAN_DELETE)
    $filters .= ' AND deleted = 0';

  if (!isset($DATA_SQL))
	  $sql = "SELECT * FROM `$TABLE` WHERE $where $filters ORDER BY $SORT_BY";
  else
  {
    if (!strstr($DATA_SQL, 'WHERE'))
      $filters = 'WHERE ' . $filters;
    elseif (strstr($DATA_SQL, '{filter}'))
      $sql = str_replace('{filter}', $filters, $DATA_SQL);
    else
      $sql = "$DATA_SQL $filters";
  }
	/////////////////////////
// 	if (isset($SEO_KEY))
// 	{
// 		unset ($rr);
// 		$rs->query($sql);
// 		while (($r = $rs->fetch()))
// 		{
// 			$rr['seo_key'] = strtolower(str_replace($special_chars, '-', $r[$SEO_KEY]));
// 			$rs->update($TABLE, $r['id'], $rr);
// 		}
// 	}
	////////////////////////

  if (is_array($RADIO))
  {
    foreach ($RADIO as $v)
    {
      if (isset($VARS[$v]))
        $SCRIPTS[] = "\$(\"input[name='$v'][value='{$VARS[$v]}']\").prop('checked', true);";
      else
      {
        $var = GetParam($v);
        if (!empty($var))
          $SCRIPTS[] = "\$(\"input[name='$v'][value='{$VARS[$v]}']\").prop('checked', true);";
      }
    }
  }

  if (function_exists('before_index'))
    $sql = call_user_func('before_index', $sql, $filters);
  //echo $sql;

  if (MODE == 'export')
  {
    unset ($r, $rr);
    $rs->query($sql);
    $export_callback = isset($EXPORT_CALLBACK) ? $EXPORT_CALLBACK : 'fn_export';
    if (function_exists($export_callback))
      $data = AssignResult($rs, $export_callback);
    else
      $data = AssignResult($rs);
    header("Content-Disposition: attachment; filename={$EXPORT_FILENAME}");
    echo pack("CCC",0xef,0xbb,0xbf); // utf-8
    foreach ($EXPORT_FIELDS as $k => $v)
      $r[$k] = '"=""' . str_replace('"', '\"', $v) . '"""';
    $rr[] = implode(',', $r);
    foreach ($data as $d)
    {
      foreach ($EXPORT_FIELDS as $k => $v)
        $r[$k] = '"=""' . str_replace('"', '\"', $d[$k]) . '"""';
      $rr[] = implode(',', $r);
    }
    echo implode("\n", $rr) . "\n";
    exit;
  }
  else
  {
    if ($UPDATE_ONLY)
    {
      $rs->query($sql);
      AssignValue($rs);
      if ($HTML_EDITOR)
        NewEditor($EDITOR_NAME, $VARS[$EDITOR_NAME], $EDITOR_HEIGHT);
      else
        NewTextArea($EDITOR_NAME, $VARS[$EDITOR_NAME], $EDITOR_HEIGHT);
    }
    else
      PageControl($sql, null, null, null, 'cbFunc');
  }
//  echo $sql;
//	if (isset($TYPE))
//	{
//		$MODE_LIST = array('new', 'add', 'edit', 'modify', 'update', 'delete', 'view');
//		foreach ($MODE_LIST as $v)
//		{
//			$n = strtoupper($v) . '_URL';
//			$VARS[$n] .= '&type=' . $TYPE;
//		}
//	}
	$VARS['type'] = $TYPE;
  if (!isset($VARS['add_button']))
	  $VARS['add_button'] = true;
	break;
}
?>