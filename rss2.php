<?php
/*
rss.php: RSS feed for miniBB (RSS 2.0).
This file is part of miniBB. miniBB is free discussion forums/message board software, without any warranty. See COPYING file for more details. Copyright (C) 2004-2007 Paul Puzyrev. www.minibb.com
Latest File Update: 2009-May-28
*/

/* Some options */
$displayForums=array(); /* Define comma-delimited array of IDs of your forum(s). Topic data from these forum(s) will be displayed in the feed. To view forum ID, mouse over on forum's title in forum on the first page, and check out the data link saying "forum=...".IDs of forums to use in display. If you want all forums taken into attention, just leave this array empty. */

$limit=10; /* Number of topics to be displayed */

$sort='topic_id DESC'; /* Order by: 'topic_id' OR 'topic_last_post_id' */

$post_sort=0; /* If 0, first post will be displayed under topic's title (slower); else last (faster). Post time will be taken correspondly, from the first or last post time. */

$maxTxtLength=500; /* Maximum Text length within display. */

$contentFeed=<<<out
<?xml version="1.0" encoding="utf-8" ?>
<rss version="2.0">
<channel>
<title>miniBB news</title>
<link>http://www.minibb.com/forums/</link>
<description>miniBB forums</description>

out;

if(isset($premodDir)) unset($premodDir);
//$premodDir='./shared_files/'; /* If you are using premoderation addon, set/uncomment this option to not display pending messages in the RSS feed. */

/* THAT'S ALL. DO NOT EDIT BELOW IF YOU ARE NEWBIE */

/* Now, we rock the code */
define ('INCLUDED776',1);

include ('./setup_options.php');
include ($pathToFiles.'setup_mysql.php');
include ($pathToFiles.'bb_functions.php');

if(sizeof($displayForums)>0) $xtr=getClForums($displayForums,'where','','forum_id','or','=');

if(isset($premodDir)) {
include($premodDir.'premoder_posts.php');
include($premodDir.'premoder_topics.php');
}

if(!defined('PAGE1_OFFSET')) define('PAGE1_OFFSET', 0);

/* get forum titles */
$forumNames=array();
if($rw=db_simpleSelect(0,$Tf,'forum_id,forum_name')){
do $forumNames[$rw[0]]=$rw[1];
while($rw=db_simpleSelect(1));
}

$topics=array();
$topics2=array();
$topics3=array();
if($res=db_simpleSelect(0,$Tt,'topic_id, topic_title, topic_poster_name, topic_time, topic_last_post_id, forum_id, posts_count','','','',$sort,$limit)){
do{
if(!isset($premodDir) OR (isset($premodDir) and isset($premodTopics) and is_array($premodTopics) and !in_array($res[0], $premodTopics))){

$tid=$res[0];
$topics2[]=$res[4];
$topics3[]=$res[0];
$topics[$tid]['topic_title']=$res[1];
$topics[$tid]['topic_last_post_id']=$res[4];
$topics[$tid]['forum_id']=$res[5];
$topics[$tid]['posts_count']=$res[6]-1;
}

}
while($res=db_simpleSelect(1));
}

$lbDate='';

if($post_sort==1) $xtr=getClForums($topics2,'where','','post_id','or','=');
else $xtr=getClForums($topics3,'where','','topic_id','or','=');

if($res=db_simpleSelect(0,$Tp,'topic_id,post_text,post_time,poster_name,post_id','','','','topic_id ASC, post_id ASC')){
$keep=0;

do{

$tid=$res[0];

if(!isset($premodPosts) OR (isset($premodPosts) and isset($premodPosts) and is_array($premodPosts) and !in_array($res[4], $premodPosts))){

/* Updated 2010-10-05, by Paul */
$keepNext=$tid;

if($keepNext!=$keep){
$ttxt=substr(strip_tags(str_replace('<br />', ' ', $res[1])),0,$maxTxtLength);

$clearTxt=strip_tags(str_replace('<br />', ' ', $res[1]));
if(strlen($clearTxt)>$maxTxtLength){
$mxTxt=explode(' ', $clearTxt);
$putText='';
for($i=0; $i<sizeof($mxTxt)-1; $i++){
if(strlen($putText.$mxTxt[$i].' ')<$maxTxtLength) $putText.=$mxTxt[$i].' ';
else break;
}
$ttxt=$putText;
}

//$ttxt=htmlspecialchars(substr(strip_tags($res[1]),0,$maxTxtLength),ENT_QUOTES);
//if(substr_count(substr($ttxt,-5),'&')>0) $ttxt=substr($ttxt,0,strlen($ttxt)-5);
$topics[$tid]['post_text']=$ttxt;
if(strlen($res[1])>$maxTxtLength) $topics[$tid]['post_text'].='...';
$topics[$tid]['topic_time']=date('D, d M Y H:i:s O', strtotime($res[2]));
$topics[$tid]['topic_poster_name']=$res[3].'@fakemail.com';
}
$keep=$tid;

/* --Updated 2010-10-05, by Paul */

}

}
while($res=db_simpleSelect(1));
}

$st=0;
foreach($topics as $key=>$val){
$topic_id=$key;
foreach($val as $k=>$v) $$k=$v;

if(isset($mod_rewrite) and $mod_rewrite) $link=addTopicURLPage(genTopicURL($main_url, $forum_id, $forumNames[$forum_id], $topic_id, $topic_title), PAGE1_OFFSET+1); else $link="{$main_url}/{$indexphp}action=vthread&amp;forum={$forum_id}&amp;topic={$topic_id}";

if(!isset($topic_time)) continue;

if($st==0) {
$lbDate=$topic_time;
$contentFeed.="<lastBuildDate>{$topic_time}</lastBuildDate>\n";
}

$contentFeed.=<<<out
<item>
<title>{$topic_title}</title>
<link>{$link}</link>
<description>{$post_text}</description>
<comments>{$link}</comments>
<pubDate>{$topic_time}</pubDate>
</item>

out;
$st=1;
}

$contentFeed.='</channel></rss>';

header("Content-type: text/xml");
header("Last-Modified: $lbDate");
//header("Etag: \"$MyETAG\"");

echo $contentFeed;

?>