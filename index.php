<?php header('Content-Type: text/html; charset=utf-8'); ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>Парсер поисковой выдачи</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<link type="text/css" rel="stylesheet" href="style/main.css"/>
<script type="text/javascript" src="exec.php?action=getRegionsJsArrays"></script>
<script type="text/javascript" src="style/main.js"></script>
</head>
<body>
<div class="main">

	<div class="queryBox">
		
		<div class="hdr_1">URL</div>
			<div class="hdr_2">Пример: site.com</div>
			<img src="style/clear.gif" alt="clear" class="clearButton" onclick="clearInp('urlInput');" title="Очистить поле" />
		<input class="inputinf" type="text" id="urlInput" />
		
		<div class="hdr_1" style="margin-top: 20px;">Регион или город</div>
			<div class="hdr_2">Регион или город в котором будет осуществляется поиск</div>
		
		<div id="regionHelplist" style="display: none;">
			<!--<div class="rhm">Москва</div>-->
		</div>

		<img src="style/clear.gif" alt="clear" class="clearButton" onclick="clearInp('regionInput');" title="Очистить поле" />
		<input class="inputinf" type="text" onkeyup="getRegionsHelpList();" id="regionInput"/>


		<div class="hdr_1" style="margin-top: 20px;">Поисковые запросы</div>
			<div class="hdr_2">Запросы можно вводить списком, разделяя нажатем кнопки Enter или запятой</div>
		<img src="style/clear.gif" alt="clear" class="clearButton" onclick="clearInp('queriesInput');" title="Очистить поле" />
		<textarea id="queriesInput" cols="" rows=""></textarea>
		
		<div class="hdr_1" style="margin-top: 20px;">Поисковая выдача в ТОП100</div>
			<div class="hdr_2">Поиск будет осуществляется в обычной поисковой выдачи, или в XML Yandex Api</div>
		 
		<span style="margin-right: 10px;"><label><input type="radio" name="searchMethod" value="serp" checked="checked" onchange="radioMethodSrch('serp');"/> SERP</label></span>
		<span><label><input type="radio" name="searchMethod" value="serpXml" onchange="radioMethodSrch('serpXml');" /> XML SERP</label></span>
		
			<div id="yapiurlbox" style="display: none;">
				<div class="hdr_1" style="margin-top: 20px;">Адрес для совершения XML запросов</div>
				<div class="hdr_2">1. Зарегистрируйте ip-адрес в системе, с которого будут совершаться запросы</div>
				<div class="hdr_2">2. Введите в поле, ниже, ваш личный url-адрес, который вам выдали в <a href="http://xml.yandex.ru/settings.xml">Yandex Api</a></div>
				<img src="style/clear.gif" alt="clear" class="clearButton" onclick="clearInp('yandexApiUrl');" title="Очистить поле" />
				<input class="inputinf" style="width: 500px !important;" type="text" id="yandexApiUrl" />
			</div>
		
		<div class="submit" onclick="sendFormData();">Отправить</div>
		<img src="style/loading.gif" alt="loading" id="loadingImg" style="display: none;"/>
	</div><!--queryBox-->
	
	
	<div class="resultBox" id="resbox" style="display: none;">
		<table class="resboxinf">
		<tr>
			<td class="tb_1 inf_1">№</td>
			<td class="tb_1 inf_2">Позиция</td>
			<td class="tb_1 inf_3">Запрос</td>
		</tr>
		</table>
		
		<table id="tableResult">
			<tr><td></td><td></td></tr>
		</table>
	</div><!--resultBox-->
	
</div>
</body>
</html>