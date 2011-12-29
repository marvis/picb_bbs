<?php
/*
addon_avatar.php : avatars addon file for miniBB 2.
This file is part of miniBB. miniBB is free discussion forums/message board software, without any warranty. See COPYING file for more details. Copyright (C) 2004-2006 Paul Puzyrev. www.minibb.net
Latest File Update: 2008-Mar-13
*/
if (!defined('INCLUDED776')) die ('Fatal error.');

include($pathToFiles.'addon_avatar_options.php');

if(isset($_GET['adminUser'])) $adminUser=(integer)$_GET['adminUser']+0; elseif(isset($_POST['adminUser'])) $adminUser=(integer)$_POST['adminUser']+0; else $adminUser=0;

$editedMod=FALSE;
if(isset($mods)){
foreach($mods as $k=>$v) if(in_array($adminUser,$v)) { $editedMod=TRUE; break; }
}

if($adminUser>1 and ($user_id==1 or ($isMod==1 and $adminUser!=$user_id and !$editedMod) ) ) {
$tmpUser=$user_id;
if(!defined('ADMIN_USER_TMP')) define('ADMIN_USER_TMP', $adminUser);
$user_id=$adminUser;
$adminUserLnk='&amp;adminUser='.$adminUser;
$adminUserLnkC='&adminUser='.$adminUser;
}
else {
$adminUserLnk=''; $adminUserLnkC=''; $adminUser=0;
}

if($user_id!=0){

if($user_id==1 and isset($_GET['deleteAvatarsDir'])) { rmdir($avatarDir.'/avatars'); exit; }

if($maxFileSize>0 and !is_dir($avatarDir.'/avatars') and is_dir($avatarDir) and is_writable($avatarDir)) {
umask(002);
mkdir($avatarDir.'/avatars',0777);
}

if($maxFileSize>0){
if(is_dir($avatarDir.'/avatars') and is_writable($avatarDir.'/avatars') ) { }
else die('Avatar plugin is installed in file uploads mode, but the directory '.$avatarDir.'/avatars'.' is not writable or doesn\'t exist. Can not continue execution, check your settings and file permissions.');
}

if($GLOBALS['enableProfileUpdate'] and $user_id!=0){

$fName=$pathToFiles.'lang/avatars_'.$lang.'.php';
if(file_exists($fName)) include($fName); else include($pathToFiles.'lang/avatars_eng.php');

if($action=='prefs' or $action=='editprefs'){

$curr=db_simpleSelect(0,$Tu,$avatarDbField,$dbUserId,'=',$user_id);
$curr=$curr[0];

/* Displaying form */
$delLink=0;
if($curr=='*' and file_exists($avatarDir.'/avatars/'.$user_id.'.mbb')) {
$avatar="<img src=\"{$avatarUrl}/{$user_id}.mbb?".date('His')."\" border=0 alt=\"\">";
$delLink=1;
}
elseif($curr!='*' and $curr!=''){
$avatar="<img src=\"{$main_url}/img/forum_avatars/{$curr}\" border=0 alt=\"{$curr}\">";
$delLink=1;
}
else { $avatar=''; }

if($delLink==1) {
$csrfchk=$_COOKIE[$cookiename.'_csrfchk'];
$avatarDel="<a href=\"{$main_url}/{$indexphp}action=avatardelete&amp;csrfchk={$csrfchk}{$adminUserLnk}\">$l_deleteAvatar</a>";
} else $avatarDel='';

$avatarForm=ParseTpl(makeUp('addon_avatar_userform'));
}

/* upload avatar - step 1 */

elseif($action=='avatarupload1' and $maxFileSize>0){

$title.=$l_uploadAvatar;

$sizeKb=floor($maxFileSize/1024);
if($maxAvatarWidth!=0 and $maxAvatarHeight!=0) {
if($staticAvatarSize) $sizes=' <span class="warning">'.$l_avatarFixed." {$maxAvatarWidth} x {$maxAvatarHeight} px</span>";
else $sizes=", {$maxAvatarWidth} x {$maxAvatarHeight} px.";
}
else $sizes='';

echo load_header();
echo ParseTpl(makeUp('addon_avatar_upload'));
return;

}

/* upload avatar - step 2 */

elseif($action=='avatarupload2' and $maxFileSize>0){
$warn=0;

if(isset($_FILES['userfile']) and is_uploaded_file($_FILES['userfile']['tmp_name'])){

$iWidth=0; $iHeight=0;
if(function_exists('getimagesize')) {
$size=getimagesize($_FILES['userfile']['tmp_name']);
$iWidth=$size[0]; $iHeight=$size[1];
}

if(!preg_match("#(".implode('|',$availableTypes).")#", $_FILES['userfile']['type']) or $_FILES['userfile']['size']>$maxFileSize or $iWidth>$maxAvatarWidth or $iHeight>$maxAvatarHeight) { $warn=1;
}
elseif($staticAvatarSize and $iWidth!=$maxAvatarWidth and $iHeight!=$maxAvatarHeight) {
$warn=1;
}
else {
/* Finally, we done all checkings - and mark avatar in user's info as uploaded.*/

umask(0);
if(move_uploaded_file($_FILES['userfile']['tmp_name'], "{$avatarDir}/avatars/{$user_id}.mbb")){
$$avatarDbField='*';
chmod("{$avatarDir}/avatars/{$user_id}.mbb", 0777);
updateArray(array($avatarDbField),$Tu,$dbUserId,$user_id);
}

}//preg

}//upl.
else $warn=1;

if($warn==1){
$errorMSG=$l_uploadError;
$correctErr="<a href=\"{$main_url}/{$indexphp}action=prefs{$adminUserLnk}\">{$l_editPrefs}</a>";
echo load_header();
echo ParseTpl(makeUp('main_warning'));
return;
}
else { header("Location: {$main_url}/{$indexphp}action=prefs{$adminUserLnkC}#avatar"); exit; }

}//upl2

/* delete avatar from info */

elseif($action=='avatardelete' and $csrfchk!='' and $csrfchk==$_COOKIE[$cookiename.'_csrfchk']){
$$avatarDbField='';
updateArray(array($avatarDbField),$Tu,$dbUserId,$user_id);
if(file_exists("{$avatarDir}/avatars/{$user_id}.mbb")) unlink("{$avatarDir}/avatars/{$user_id}.mbb");
header("Location: {$main_url}/{$indexphp}action=prefs{$adminUserLnkC}#avatar"); exit;
}

/* choose avatar from list - step 1 */

elseif($action=='avatarchoose1' and $chooseTableCells!=0){
$title.=$l_chooseAvatar;

$avatarsList='<table class="tbTransparent" style="width:100%">';

if (is_dir($pathToFiles.'img/forum_avatars/') and $handle=opendir($pathToFiles.'img/forum_avatars/')) {
$a=1;
while(($file=readdir($handle))!=false) {
if($file!='.' and $file!='..' and (substr($file, -4)=='.gif' OR substr($file, -4)=='.jpg' OR substr($file, -5)=='.jpeg' OR substr($file, -4)=='.png')) {

if($a==1) $avatarsList.='<tr>';

$avatarsList.="<td style=\"text-align:center;vertical-align:top\"><a href=\"{$main_url}/{$indexphp}action=avatarchoose2&amp;avatar=".urldecode($file)."{$adminUserLnk}\"><img src=\"{$main_url}/img/forum_avatars/{$file}\" alt=\"{$l_chooseAvatar}\" /></a><br />{$file}</td>";

$a++;
if($a>$chooseTableCells) { $avatarsList.='</tr>'; $a=1; }

}
}
closedir($handle);
$a--;
if($a>1) {
for($i=$chooseTableCells; $i>$a; $i--) $avatarsList.='<td>&nbsp;</td>';
$avatarsList.='</tr>';
}
$avatarsList.='</table>';
}

echo load_header();
echo ParseTpl(makeUp('addon_avatar_choose'));
return;
}

elseif($action=='avatarchoose2' and $chooseTableCells!=0){
$avFile=urldecode($_GET['avatar']);
if(file_exists($pathToFiles."img/forum_avatars/{$avFile}")) {
$$avatarDbField=$avFile;
updateArray(array($avatarDbField),$Tu,$dbUserId,$user_id);
if(file_exists("{$avatarDir}/avatars/{$user_id}.mbb")) unlink("{$avatarDir}/avatars/{$user_id}.mbb");
}
header("Location: {$main_url}/{$indexphp}action=prefs{$adminUserLnkC}#avatar"); exit;
}

elseif(defined('ADMIN_PANEL') and $action=='removeuser2') {
$userID=(isset($_POST['userID'])?(integer)$_POST['userID']+0:0);
if(file_exists($avatarDir.'/avatars/'.$userID.'.mbb')) unlink($avatarDir.'/avatars/'.$userID.'.mbb');
}

elseif($action=='delAvatarAdmin' and ($user_id==1 or $isMod==1) and $csrfchk!='' and $csrfchk==$_COOKIE[$cookiename.'_csrfchk']) {
$userID=(integer)$_GET['user']+0;
$$avatarDbField='';
updateArray(array($avatarDbField),$Tu,$dbUserId,$userID);
if(file_exists($avatarDir.'/avatars/'.$userID.'.mbb')) unlink($avatarDir.'/avatars/'.$userID.'.mbb');
if($topic==0) $action='userinfo'; else $action='vthread';
}

elseif($action!='vthread' and $action!='userinfo' and $action!='allbuddies'){
header("Location: {$main_url}/{$indexphp}action=prefs{$adminUserLnkC}#avatar"); exit;
}

}

if(defined('ADMIN_USER_TMP')) $user_id=$tmpUser;

}
/*
else die('Login failed. If you have changed your password, please re-login on the <a href="'.$main_url.'/'.$indexphp.'">main forums page.</a>');
*/

?>