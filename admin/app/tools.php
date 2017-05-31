<?php
switch (MODE)
{
  case 'get_plan':
    $o = [];
    $id = GetParam('id');
    $sql = "SELECT * FROM `plan` WHERE status = 10 AND `begin` <= '$today' AND `end` >= '$today' AND cat_id = $id ORDER BY period, plan_name";
    $rs->query($sql);
    while (($r = $rs->fetch()))
      $o[] = <<<EOT
  <option value="{$r['id']}" data-name="{$r['plan_name']}" data-period="{$r['period']}" data-price="{$r['price']}"
  data-point="{$r['point']}" data-gift="{$r['gift']}">{$r['plan_name']} / {$r['period']}個月 / \${$r['price']} / {$r['point']}點 / 贈{$r['gift']}點</option>
EOT;
    json_output(array('status' => true, 'data' => implode($o)));
    break;
}
?>