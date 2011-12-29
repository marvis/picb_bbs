<?php

/* Avatar add-on settings */

$avatarDir=$pathToFiles.'shared_files'; //general path to your directory, where "avatars" directory will be CREATED and used. On Linux/FreeBSD/Unix systems, it must have 0777 privileges. Many miniBB plugins will use this directory, so if you already have one, it is better to keep one directory for all shared files. No slash at the end!

$avatarUrl="{$main_url}/shared_files/avatars"; //general WWW path to avatars images. No slash at the end.

$maxFileSize=5120; //maximum avatar file size in Bytes (5120 = 5 Kbytes). If you want to DISABLE uploads, set this to 0.

$maxAvatarWidth=50; //maximum picture width in pixels. Set amount of maximum width and height ONLY if you have GD library installed, ELSE set them to 0 (any pic width and height will be allowed, just because it is impossible to determine w/h then).
$maxAvatarHeight=50; //maximum picture height in pixels. 

$staticAvatarSize=TRUE;// set to TRUE if you would like to allow to upload static size avatars ONLY (the size defined under $maxAvatarWidth and $maxAvatarHeight). This is the most suitable way if you're using threads layout model where username row is placed on top of a message text. Setting this to FALSE allows to upload various size avatars which fits under dimensions specified in $maxAvatarWidth and $maxAvatarHeight.

$availableTypes=array('gif', 'jpeg', 'jpg', 'png'); //list of available picture types. Notice that some GD versions does not support GIFs. Do not put 'bmp' or 'tif' here, please.

$avatarDbField='user_custom1'; // name of the field in user's table. By default, we will use the first from custom miniBB fields.

$chooseTableCells=5; //amount of cells in 'ready' avatars table. If you don't want to use ready avatars, set this to 0!

?>