<?php
chdir("..");
require_once ("common/init.php");

session_start();
header ("Content-type: image/png");

$rno = rand(1000,99999);
$_SESSION['ckey'] = md5($rno);

$img = rand(1,4);
$img_handle = imageCreateFromPNG($THINKTANK_CFG['source_root_path']."webapp/assets/img/captcha/bg".$img.".PNG");
$color = ImageColorAllocate ($img_handle, 0, 0, 0);
ImageString ($img_handle, 5, 20, 13, $rno, $color);
ImagePng ($img_handle);
ImageDestroy ($img_handle);

?>
