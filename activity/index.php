<?php
$act = $_GET['act'];
$track = $_GET['track'];

$file  = $act . '_' . $track . '.html';

if (file_exists($file))
  require($file);
else
  require('default.html');