<?php
ini_set('display_errors', 0);

$x = $_GET['x'];
if (empty($x))
  $x = '';
$name = "captcha$x";
$width = 100;
$height = 28;
$xx = $width * $height;
$mi = intval($xx * 0.17);
$ma = intval($xx * 0.25);
$w_1 = $width - 1;
$h_1 = $height - 1;

header("Content-type: image/png");
session_start();
srand();
$string = $_SESSION[$name] = sprintf('%05d', rand(0, 999999));
$im = imagecreatetruecolor($width, $height);
$background_color = imagecolorallocate($im, 235, 235, 235);
$border_color = imagecolorallocate($im, 192, 192, 192);
imagefill($im, 0, 0, $background_color);
$r = rand($mi, $ma); // 17% ~ 22%
for ($i = 0; $i < $r; $i++)
{
  $x = rand(0, $w_1);
  $y = rand(0, $h_1);
  $c = (rand(128, 250) << 16) + (rand(128, 250) << 8) + rand(128, 250);
  imagesetpixel($im, $x, $y, $c);
}
imageline($im, 0, 0, $w_1, 0, $border_color);
imageline($im, $w_1, 0, $w_1, $h_1, $border_color);
imageline($im, $w_1, $h_1, 0, $h_1, $border_color);
imageline($im, 0, $h_1, 0, 0, $border_color);
//imagettftext($im, 16, 0, 5, 22, $text_color, 'verdana.ttf', $string);
//imagestring($im, 16, $px, 7, $string, $text_color);
for($i = 0; $i < strlen($string); $i++)
{
  $r1 = rand(32, 120);
  $r2 = rand(32, 120);
  $r3 = rand(32, 120);
//  $text_color = imagecolorallocate($im, $r1, $r2, $r3);
  $text_color = ($r1 << 16) + ($r2 << 8) + $r3;
  imagettftext($im, 16, 0, 4 + $i * 15 + (rand(0, 4) - 2), 22 + rand(0, 6) - 3, $text_color, './verdana.ttf',substr($string, $i, 1));
}

$zz = intval($xx * 0.07);
for ($i = 0; $i < $zz; $i++) // 7%
{
  $x = rand(0, $w_1);
  $y = rand(0, $h_1);
  $c = (rand(64, 192) << 16) + (rand(64, 192) << 8) + rand(64, 192);
  imagesetpixel($im, $x, $y, $c);
}

imagepng($im);
imagedestroy($im);
?>