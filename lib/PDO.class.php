<?php
class Database extends PDO{

	
	//回傳全部
	function fetch_all( $table, $where =false , $order = 'priority ASC' , $colums = '*' ,$cursor = false ,$limit = false ){
		
		$stm = $this->query( $this->gen_select( $table, $where , $order , $colums ,$cursor ,$limit ));
		if(!$stm){
			if(DEBUG) die( debug_print_backtrace(0,1));
			else return false;
		}
		return $stm->fetchAll(PDO::FETCH_ASSOC);
	}
	
	//回傳全部 第一欄當做key 第二欄當做value
	function fetch_assoc( $table, $key , $value , $where = false , $order = 'priority ASC' ){
		
		$sql = $this->gen_select( $table, $where , $order , "`$key`,`$value`" );
		$stm = $this->query($sql);
		if(!$stm){
			if(DEBUG) die( debug_print_backtrace(0,1));
			else return false;
		}
		$array = array();
		while($row = $stm->fetch(PDO::FETCH_NUM))
			$array[$row[0]]=$row[1];
		return $array;
	}
	
	//回傳全部 group後 第一欄當做key 第二欄當做value
	function fetch_group_assoc($table, $key, $value, $group, $order = false ){
		
		$sql = "SELECT {$key},{$value} FROM {$table} GROUP BY {$group} ORDER BY {$order}";
		$stm = $this->query($sql);
		if(!$stm){
			if(DEBUG) die( debug_print_backtrace(0,1));
			else return false;
		}
		$array = array();
		while($row = $stm->fetch(PDO::FETCH_NUM))
			$array[$row[0]]=$row[1];
		return $array;		
	}

	//回傳全部 某欄當做key 值為陣列
	function fetch_assoc_key( $key, $table, $colum = '*' , $where = false , $order = false ){
		
		$sql = $this->gen_select( $table, $where , $order , $colum );
		$stm  = $this->query($sql);
		if(!$stm){
			if(DEBUG) die( debug_print_backtrace(0,1));
			else return false;
		}
		$array = array();
		while($row = $stm->fetch(PDO::FETCH_ASSOC))
			$array[$row[$key]]=$row;
		return $array;
	}
	
	//取回一列
	function fetch_one($table, $where, $priority = false ){
		$stm = $this->query($this->gen_select($table, $where, $priority ));
		if(!$stm){
			if(DEBUG) die( debug_print_backtrace(0,1));
			else return false;
		}
		return $stm->fetch(PDO::FETCH_ASSOC);
	}
	
	//取回一個數值
	function fetch_count( $table, $where =false){
		
		$stm =  $this->query($this->gen_select( $table, $where, false, 'COUNT(*)'));
		if(!$stm){
			if(DEBUG) die( debug_print_backtrace(0,1));
			else return false;
		}
		$row = $stm->fetch(PDO::FETCH_NUM);
		return $row[0];
	}	

	//取回最大值
	function fetch_max( $table, $where =false){
		
		$stm =  $this->query($this->gen_select( $table, $where, false, 'MAX(priority)'));
		if(!$stm){
			if(DEBUG) die( debug_print_backtrace(0,1));
			else return false;
		}
		$row = $stm->fetch(PDO::FETCH_NUM);
		return $row[0];
	}
	
	function schema($table){
		$data = array();
		$stm = $this->query( "SHOW COLUMNS FROM `$table`" ); //初始值
		if(!$stm){
			if(DEBUG) die( debug_print_backtrace(0,1));
			else return false;
		}
		while ($row = $stm->fetch(PDO::FETCH_ASSOC)){
			$data[$row['Field']]= $row['Default'];
		}
		return $data;
	}
	
	//重新排序
	function pop_priority( $table, $id ){
		
		if(isset($_POST['form']['category_id']))
			$this->update( $table , 'priority=priority+1', 'id!='.$id.' AND priority>0 AND category_id='.$_POST['form']['category_id'] );
		else
			$this->update( $table , 'priority=priority+1', 'id!='.$id.' AND priority>0 ' );
		
		$this->update( $table , 'priority=1', 'id='.$id );
	}
	
	//刪除
	function delete( $table , $where ){

		$sql = "DELETE FROM `{$table}` WHERE ".( is_array($where) ? $this->gen_sql_key_pair($where) : $where );
		$stm = $this->query($sql);
		if(!$stm){
			if(DEBUG) die( debug_print_backtrace(0,1));
			else return false;
		}
		return $stm->rowCount() > 0 ? 1 : 0;
	}
	
	//更新
	function update( $table , $values , $where ){

		$sql = "UPDATE `{$table}` SET ";
		if(is_array($values)){
			$array = Array();
			foreach($values as $key => $value){				
				$value = $this->quote($value);
				array_push( $array, "`{$key}`= {$value} " );
			}
			$sql.= implode(',',$array);
		}else
			$sql.=$values;
		$sql.= " WHERE ".( is_array($where) ? $this->gen_sql_key_pair($where) : $where );
		$stm = $this->query($sql);
		if(!$stm){
			if(DEBUG) die( debug_print_backtrace(0,1));
			else return false;
		}
		$num = $stm->rowCount();
		return $num > 0 ? $num : false;
	}
	
	//新增
	function insert( $table , $values ){
		
		$sql = $this->gen_sql_insert( $table , $values );
		$this->query($sql);
		$id = $this->lastInsertId();
		return $id ? $id : false;
		
	}
	
	//執行
	function query($sql){
		
		if(SQLLOG){
			file_put_contents( DOCUMENT_ROOT."upload/log.txt",$sql."\r\n",FILE_APPEND);
		}
		return parent::query($sql);
	}
	
	//以下為 sql statement 產生器
	function gen_select( $table, $where =false , $order = 'priority ASC' , $colums = '*' ,$cursor = false ,$limit = false ){
		
		$sql = is_array($colums) ?  "SELECT ".implode(',',$colums)." FROM {$table} " : "SELECT {$colums} FROM `{$table}` ";
		
		if( $where ){
				$sql.=' WHERE '.(is_array($where) ? $this->gen_sql_key_pair($where) : $where);
		}		
		if( $order ){
			$sql.=' ORDER BY '.(is_array($order) ? $this->gen_sql_order($order) : $order);
		}
		if( is_numeric($cursor) && is_numeric($limit) ){
			$sql.=" LIMIT {$cursor},{$limit} ";
		}
		return $sql;
	}
	
	function gen_sql_insert( $table, $values ){
		
		if(!is_array($values)) fatal_error('Array only');
		$keys = array();
		$vals = array();
		foreach($values as $key => $value ){
			array_push( $keys , "`$key`" );
			array_push( $vals , $this->quote($value) );
		}
		$keys = implode(',',$keys);
		$vals = implode(',',$vals);
		
		$sql = "INSERT INTO `{$table}` ({$keys}) VALUES ({$vals})";
		return $sql;
	}
	
	function gen_sql_order($array){
		
		$result = array();
		foreach($array as $key=>$value){
			array_push( $result , "`{$key}` {$value}");
		}
		return implode(',',$result);
	}
	
	function gen_sql_key_pair($array, $condition = 'AND'){
		
		$result = array();
		foreach($array as $key=>$value){
			array_push( $result , "`{$key}`=\"{$value}\"");
		}
		return implode(" {$condition} ",$result);
	}
	
	function gen_sql_in_array( $colum, $array ){
		
		$result = array();
		foreach($array as $value){
			array_push( $result , "'{$value}'");
		}		
		return $colum.' in ('.implode(',',$result).')';
	}
}
?>