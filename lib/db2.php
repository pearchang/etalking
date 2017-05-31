<?php
require_once ('db.php');

class MultiLangResultSet extends ResultSet
{
  var $lang_table, $lang_field;

  function fetch($no_underline = false)
  {
    global $LANG_TABLE, $LANG_FIELD;

    $r = parent::fetch($no_underline);
    $table = empty($this->lang_table) ? $LANG_TABLE : $this->lang_table;
    $field = empty($this->lang_field) ? $LANG_FIELD : $this->lang_field;
    if (empty($talbe))
      return $r;
    $rs = new ResultSet();
    $sql = "SELECT * FROM `$talbe` WHERE `$field` = " . $r['id'] . " AND lang = " . LANGID;
    $rs->query($sql);
    if ($rs->count > 0)
    {
      $r2 = $rs->fetch();
      unset ($r2['id']);
      unset ($r2[$field]);
      unset ($r2['lang']);
      foreach ($r2 as $f => $v)
      {
        $r['_' . $f] = $r[$f];
        if (!empty($v))
          $r[$f] = $v;
      }
    }
  }

  function get_value($table, $field, $id, $f = 'id')
  {
    $sql = "SELECT `$field` FROM `$table` WHERE `$f` = :id";
    $this->query($sql, array('id' => $id));
    if ($this->count == 0)
      return null;
    $r = $this->fetch();
    return $r[$field];
  }
}
?>