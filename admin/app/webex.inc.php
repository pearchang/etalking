<?php
    for ($i = BEGIN_TIME; $i <= END_TIME+0.5; $i+=0.5){
		if(ceil($i)==$i){
			$h = $i;
			$hour_begin = $h.":00";
			$hour_end = $h.":29";
		}else{
			$h = floor($i);
			$hour_begin = $h.":30";
			$hour_end = $h.":59";
		}
		$hour[] = array ('hour_begin' => $hour_begin, 'hour_end' => $hour_end);
	}
    $VARS['hour_list'] = $hour;
    $now = time();
    $VARS['w'] = $wk = GetParam('w', 0);
    $VARS['w1'] = $wk - 1;
    $VARS['w2'] = $wk + 1;
    $begin = $wk * (DATE_RANGE / 2);
    $end = $begin + (DATE_RANGE / 2);
    for ($i = $begin; $i < $end; $i++) // date
    {
      $t = $now + $i * 86400;
      $d = date('Y-m-d', $t);
      $w['date'] = date('m-d', $t);
      $w['name'] = $var_weekday_short[date('w', $t)];
      unset ($hour);
      for ($k = BEGIN_TIME; $k <= END_TIME+0.5; $k+=0.5)
      {
		$t = ceil($k)==$k ? $k.":00" : floor($k).":30" ;
		$h = array();
        $h['date'] = $d;
        $h['hour'] = $t;
		
        $cnt = $rs->get_count("webex_test","status=10 AND `datetime`='".$h['date'].' '.$h['hour'].":00'");
		$h['already'] =(int)$cnt[0];
		$h['avaliable'] = (int)$rs->get_value("webextestconfig","qyt", "{$d} {$t}:00",'datetime');
        $h['expired'] = date('YmdHi') > date('YmdHi',strtotime("{$d} {$t}:00")) ? true : false;
		
		$hour[] = $h;
		
      }
      $w['hour_list'] = $hour;
      $week[] = $w;
    }
    $VARS['date_list'] = $week;
?>