<?php
set_time_limit(30);
include_once "classes.php";
/*	-------------------------------------------------------	*/

if(!empty($_POST['action'])) {
	$action = $_POST['action'];
} elseif(!empty($_GET['action'])) {
	$action = $_GET['action'];
} else $action = null;


switch($action) {
default:
	header('Location: index.php');
break;

// Посылаем формой id-региона, список url, получаем xml ответ с результатом.
case 'serp':
if(!empty($_POST['region']) && !empty($_POST['url']) && !empty($_POST['queries'])) {
	$p	=	new parser();
	$p->getSerp($_POST['region'], $_POST['url'], $_POST['queries']);
}
break;

case 'serpXml':
	if(!empty($_POST['region']) && !empty($_POST['url']) && !empty($_POST['queries']) && !empty($_POST['yandexApiUrl'])) {
		$p	=	new parser();
		$p->getSerpXmlApi($_POST['region'], $_POST['url'], $_POST['queries'], $_POST['yandexApiUrl']);
	}
break;

// Массив с регионами в js, для функции автодополнения
case 'getRegionsJsArrays':
	$r	=	new regions();
	$r->getRegionsJsArray();
break;
}









?>