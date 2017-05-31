<?
class ResultSet
{
	var $rs;
	var $count;
	var $fc;
	var $last_id;
	var $last_sql;
  var $last_params;
	var $conn;
	var $no_multilang;
	var $locked = false;

	function ResultSet($c = null)
	{
		global $conn;

		if (empty($c))
			$this->conn = $conn;
		else
			$this->conn = $c;
	}

	function execute($sql, $params = null)
	{
    $this->last_sql = $sql;
    $this->last_params = $params;
    $this->rs = $this->conn->prepare($sql);

    if (is_array($params))
    {
      foreach ($params as $k => $v)
        $pp[":$k"] = $v;
//        $this->rs->bindParam(':' . $k, $v, is_integer($v) ? PDO::PARAM_INT : PDO::PARAM_STR);
    }

    $this->rs->execute($pp);
    $c = substr($sql, 0, 6);
    if ($c == 'INSERT' || $c == 'REPLAC')
      $this->last_id = $this->conn->lastInsertId();
    elseif ($c != 'UPDATE')
      $this->last_id = 0;
	}

  function batch_execute($sql)
  {
    if (mysqli_multi_query($this->conn, $sql))
    {
      while(mysqli_more_results($this->conn))
      {
        mysqli_next_result($this->conn);
        $discard = mysqli_store_result($this->conn);
      }
      return true;
    }
    return false;
  }

	function query($sql, $params = null)
	{
		$this->last_sql = $sql;
    $this->last_params = $params;
    $this->rs = $this->conn->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
//    echo $sql;
//    print_r($params);
//    echo '<br>';
    if (is_array($params))
    {
      foreach ($params as $k => $v)
        $pp[":$k"] = $v;
//      {
//        $this->rs->bindParam(':' . $k, $v, is_integer($v) ? PDO::PARAM_INT : PDO::PARAM_STR);
//      }
    }
//    echo $sql;
//    print_r($params);
    $this->rs->execute($pp);
    $this->count = $this->conn->query("SELECT FOUND_ROWS()")->fetchColumn();
//    echo $this->count . "<br>";
    $this->fc = $this->rs->columnCount();
    $this->last_id = $this->conn->lastInsertId();
	}

	function variables()
	{
		$r = $this->fetch(true);
		foreach ($r as $k => $v)
		{
			global $$k;
			$$k = $v;
		}
	}

	function fetch($no_underline = false)
  {

    $r = $this->rs->fetch(PDO::FETCH_ASSOC);
    if (empty($r))
      return $r;
    if (!$no_underline)
    {
      foreach ($r as $f => $v)
        $r['_' . $f] = $v;
    }
    return $r;
  }

  function fetch_array($no_underline = false)
  {
    while (($r = $this->fetch($no_underline)))
      $rr[] = $r;
    return $rr;
  }

	function row()
	{
		return $this->rs->fetch(PDO::FETCH_NUM);
	}

	function record()
	{
		$r = $this->row();
		return $r[0];
	}

	function record_array()
	{
		while (($r = $this->row()))
			$s[] = $r[0];
		return $s;
	}

	function get_value($table, $field, $id, $f = 'id')
	{
    $sql = "SELECT `$field` FROM `$table` WHERE `$f` = :id";
    $this->query($sql, array('id' => $id));
    if ($this->count == 0)
      return null;
    return $this->record();
	}

  function get_count($table, $condition, $params = null)
  {
    $where = empty($condition) ? 1 : $condition;
    $sql = "SELECT COUNT(*) FROM `$table` WHERE $where";
    $this->query($sql, $params);
    if ($this->count == 0)
      return 0;
    return $this->row();
  }


	function check_exists($table, $field, $data, $id = 0, $ff = 'id')
	{
		if (is_int($id) && $id == 0)
    {
      $sql = "SELECT COUNT(*) FROM `$table` WHERE `$field` = :data";
      $params = array ('data' => $data);
    }
		else
    {
      $sql = "SELECT COUNT(*) FROM `$table` WHERE `$field` = :data AND `$ff` <> :id";
      $params = array ('data' => $data, 'id' => $id);
    }
    $this->query($sql, $params);
    return $this->record() == 1;
	}

	function insert($table, $var = null)
	{
		if (is_null($var))
			$var = $_POST;
		$sql = "SHOW FIELDS FROM `$table`";
		$rr = $this->conn->query($sql);
    $fields = $rr->fetchAll(PDO::FETCH_ASSOC);
    $rr->closeCursor();
		$rank = false;
		foreach ($fields as $ro)
		{
      $ff = $ro['Field'];
			$n = strtolower($ff);
			$field_array[] = $n;
			if (isset($var[$n]) && $n != 'id')
			{
				$v = $var[$n];
        if ((strstr($n, 'date') || strstr($n, 'time')) && strstr($v, '()'))
          $s[] = "`$ff` = $v";
        else
        {
          $s[] = "`$ff` = :$ff";
          $params[$ff] = $v;
        }
			}
		}
		if (in_array('cdate', $field_array) && !isset($var['cdate']))
			$s[] = "cdate = NOW()";
		if (in_array('creator', $field_array) && !isset($var['creator']) && $_SESSION['admin_id'])
			$s[] = "creator = {$_SESSION['admin_id']}";
		if (is_array($s))
		{
			$s = implode(', ', $s);
			$sql = "INSERT INTO `$table` SET $s";
      $this->execute($sql, is_array($params) ? $params : null);
			if ($this->conn->errorCode() == '00000') // OK
			{
				unset($s);
				$err = false;
				if (in_array('rank', $field_array) && !is_numeric($var['rank']))
					$s[] = 'rank = ' . ($this->last_id * 100);
				if (is_array($s))
				{
					$s = implode(', ', $s);
					$sql = "UPDATE `$table` SET $s WHERE id = " . $this->last_id;
					$this->conn->exec($sql);
				}
			}
			else
				$err = true;
		}
		return $err;
	}

	function update($table, $id, $var = null, $f = 'id')
	{
		if (is_null($var))
			$var = $_POST;
    $sql = "SHOW FIELDS FROM `$table`";
    $rr = $this->conn->query($sql);
    $fields = $rr->fetchAll(PDO::FETCH_ASSOC);
    $rr->closeCursor();
    $err = false;
    foreach ($fields as $ro)
		{
      $ff = $ro['Field'];
			$n = strtolower($ff);
			$field_array[] = $n;
			if (isset($var[$n]) && $n != $f)
			{
        $v = $var[$n];
        if ((strstr($n, 'date') || strstr($n, 'time')) && strstr($v, '()'))
          $s[] = "`$ff` = $v";
        else
        {
          $s[] = "`$ff` = :$ff";
          $params[$ff] = $v;
        }
			}
		}
		if (in_array('mdate', $field_array) && !isset($var['mdate']))
			$s[] = "mdate = NOW()";
		if (in_array('modifier', $field_array) && !isset($var['modifier']) && $_SESSION['admin_id'])
			$s[] = "modifier = {$_SESSION['admin_id']}";
		if (is_array($s))
		{
			$s = implode(', ', $s);
			if (is_array($id))
			{
				foreach ($id as $k => $v)
        {
          $w[] = "`{$f[$k]}` = :id$k";
          $params["id$k"] = $v;
        }
				$w = implode(' AND ', $w);
			}
			else
      {
        $w = "`$f` = :id";
        $params['id'] = $id;
      }
			$sql = "UPDATE `$table` SET $s WHERE $w";
      $this->execute($sql, is_array($params) ? $params : null);
			$err = $this->conn->errorCode() == '00000' ? false : true;
		}
		return $err;
	}

	function replace($table, $var = null, $id = 'id')
	{
		if (is_null($var))
			$var = $_POST;
    $sql = "SHOW FIELDS FROM `$table`";
    $rr = $this->conn->query($sql);
    $fields = $rr->fetchAll(PDO::FETCH_ASSOC);
    $rr->closeCursor();
    $err = false;
    foreach ($fields as $ro)
		{
      $ff = $ro['Field'];
      $n = strtolower($ff);
			$field_array[] = $n;
			if (isset($var[$n])) // && $n != 'id')
			{
        $v = $var[$n];
        if ((strstr($n, 'date') || strstr($n, 'time')) && strstr($v, '()'))
          $s[] = "`$ff` = $v";
        else
        {
          $s[] = "`$ff` = :$ff";
          $params[$ff] = $v;
        }
			}
		}
		if (in_array('mdate', $field_array) && !isset($var['mdate']))
			$s[] = "mdate = NOW()";
		if (in_array('modifier', $field_array) && !isset($var['modifier']) && $_SESSION['admin_id'])
			$s[] = "modifier = {$_SESSION['admin_id']}";
		if (is_array($s))
		{
			$s = implode(', ', $s);
			$sql = "REPLACE INTO `$table` SET $s";
      $this->execute($sql, is_array($params) ? $params : null);
      $err = $this->conn->errorCode() == '00000' ? false : true;
		}
		return $err;
	}

	function real_delete($table, $id, $f = 'id')
	{
		$sql = "DELETE FROM `$table` WHERE `$f` = :id";
    return $this->execute($sql, array('id' => $id));
	}

	function delete($table, $id, $f = 'id')
	{
		$v['deleted'] = 1;
		$v['status'] = 0;
		$this->update($table, $id, $v, $f);
		// return $this->real_delete($table, $id, $f);
	}

	function select($table, $id = 0, $field = '*', $f = 'id')
	{
		if (strstr($field, ','))
		{
			$r = explode(',', $field);
			foreach ($r as $k => $v)
				$r[$k] = '`' . trim($v) . '`';
			$field = implode(',', $r);
		}
		elseif ($field != '*')
			$field = "`$field`";
    if (is_int($id) && $id == 0)
      $this->query("SELECT $field FROM `$table`");
    else
    {
      $sql = "SELECT $field FROM `$table` WHERE `$f` = :id";
      $this->query($sql, array('id' => $id));
    }
	}

	function insertWithEditor($table, $field, $path, $var = null, $hash = 0, $prefix = 0)
	{
		if (is_null($var))
			$var = $_POST;
		if (is_array($field))
		{
			foreach ($field as $v)
			{
        $data[$v] = $var[$v];
        unset($var[$v]);
			}
		}
		else
		{
			$data = $var[$field];
			unset($var[$field]);
		}
		$this->insert($table, $var);
		if ($hash != 0)
		{
			$i = $this->last_id % $hash;
			$path .= "$i/";
		}
		if (is_array($field))
		{
			foreach ($field as $ff)
				$dat[$ff] = MoveImages($path, $data[$ff], $prefix ? $prefix : $this->last_id);
		}
		elseif (!empty($field))
			$dat[$field] = MoveImages($path, $data, $prefix ? $prefix : $this->last_id);
		$this->update($table, $this->last_id, $dat);
		return $var;
	}

	function updateWithEditor($table, $id, $field, $path, $var = null, $hash = 0, $prefix = 0)
	{
		if (is_null($var))
			$var = $_POST;
		if ($hash != 0)
		{
			$i = $id % $hash;
			$path .= "$i/";
		}
		if (is_array($field))
		{
			foreach ($field as $ff)
				$var[$ff] = MoveImages($path, $var[$ff], $prefix ? $prefix : $id);
		}
		elseif (!empty($field))
			$var[$field] = MoveImages($path, $var[$field], $prefix ? $prefix : $id);
//		print_r($var);
		$this->update($table, $id, $var);
		return $var;
// 		echo htmlspecialchars($this->last_sql);
// 		exit;
	}

  function escape($str)
  {
    return $this->conn->quote($str);
  }

  function free()
  {
    $this->rs->closeCursor();
  }

	function lock($tables)
	{
		$this->execute("LOCK TABLES $tables");
		$this->locked = true;
	}

	function unlock()
	{
		if (!$this->locked)
			return;
		$this->execute("UNLOCK TABLES");
		$this->locked = false;
	}
}

$dsn = "mysql:dbname={$DBNAME};host={$DBHOST};charset=UTF8";
try {
  $conn = new PDO($dsn, $DBUSER, $DBPASS);
} catch (PDOException $e) {
  echo '無法連接資料庫: ' . $e->getMessage();
  exit;
}
?>