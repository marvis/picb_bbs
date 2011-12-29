<?php
if (!defined('INCLUDED776')) die ('Fatal error.');

/* Avatars addon */

if($action=='vthread') {
include($pathToFiles.'addon_avatar_options.php');
$fName=$pathToFiles.'lang/avatars_'.$lang.'.php';
if(file_exists($fName)) include($fName); else include($pathToFiles.'lang/avatars_eng.php');
if($staticAvatarSize) $avatarDim=' style="width:'.$maxAvatarWidth.'px;height:'.$maxAvatarHeight.'px"';
}

if($action=='userinfo' or $action=='removeuser2' or $action=='allbuddies' or ($GLOBALS['enableProfileUpdate'] and ($action=='prefs' or $action=='editprefs' or $action=='avatarupload1' or $action=='avatarupload2' or $action=='avatardelete' or $action=='avatarchoose1' or $action=='avatarchoose2')) OR (isset($GLOBALS['adminPanel']) and $action=='removeuser2' and $user_id==1) OR ( ($isMod==1 or $user_id==1) and $action=='delAvatarAdmin') ) include($pathToFiles.'addon_avatar.php');

if($action=='vthread' and ($user_id==1 or $isMod==1)) {
$delAvatarJs=<<<out
function confirmDeleteAvatar(user, addstr){
var csrfcookie=getCSRFCookie();
if(csrfcookie!='') csrfcookie='&csrfchk='+csrfcookie;
if(confirm('{$l_deleteAvatar}?')) document.location='{$main_url}/{$indexphp}action=delAvatarAdmin&user='+ user + addstr + csrfcookie;
else return;
}
out;
}

function parseUserInfo_user_custom1($av){
if(!isset($GLOBALS['cols'][0])) {
$GLOBALS['cols'][0]=$GLOBALS['user'];
$addStr='';
}
else{
$addStr="&amp;forum={$GLOBALS['forum']}&amp;topic={$GLOBALS['topic']}&amp;page={$GLOBALS['page']}";
}
if(isset($GLOBALS['avatarDim'])) $avatarDim=$GLOBALS['avatarDim']; else $avatarDim='';

if( ($GLOBALS['user_id']==1 or $GLOBALS['isMod']==1) and $av!='' and $GLOBALS['action']=='vthread') {
$a1="<a href=\"JavaScript:confirmDeleteAvatar({$GLOBALS['cols'][0]},'{$addStr}');\" onmouseover=\"window.status=''; return true;\" onmouseout=\"window.status=''; return true;\">";
$a2='</a>';
$alt=$GLOBALS['l_deleteAvatar'];
}
else { $a1=''; $a2=''; $alt=''; }

if($av!='' and $av!='*') $im="{$a1}<img src=\"{$GLOBALS['main_url']}/img/forum_avatars/{$av}\" alt=\"{$alt}\" title=\"{$alt}\"{$avatarDim} />{$a2}";
elseif($av!='' and $av=='*') $im="{$a1}<img src=\"{$GLOBALS['avatarUrl']}/{$GLOBALS['cols'][0]}.mbb\" alt=\"{$alt}\" title=\"{$alt}\"{$avatarDim} />{$a2}";
else $im='';

return $im;
}

/*--Avatars addon */
?>
