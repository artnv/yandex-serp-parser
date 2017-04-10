// ссылка на аякс объект
var resultXpObject = xp();

// Блокировщики функций. Функцию можно будет использовать, если она закончила работу
var lock_1 = 0;

// Метод поиска serp/serpXml
var searchMethod = 'serp'; 

// Ajax объект
function xp()
{
	var xmlhttp;
	
	try
	{
		xmlhttp = new XMLHttpRequest();
	}
	catch(e)
	{
		var XmlHttpVersions = new Array("MSXML2.XMLHTTP.6.0","MSXML2.XMLHTTP.5.0","MSXML2.XMLHTTP.4.0","MSXML2.XMLHTTP.3.0","MSXML2.XMLHTTP.2.0","MSXML2.XMLHTTP","Microsoft.XMLHTTP");
		for(var i=0;i<XmlHttpVersions.length && !xmlhttp; i++)
		{
			try
			{
				xmlhttp = new ActiveXObject(XmlHttpVersions[i]);
			}
			catch (e) {
				alert("xp::error: 1\n"+e);
			}
		}
	}
	
	if(!xmlhttp) alert("xp::error: 2");
	else return xmlhttp;
}

// возвращеает объект
function getObj(id) {
	return document.getElementById(id);
}

// Картинка загрузки
function showLoadingimg(arg) {
	var img = getObj("loadingImg");
	if(arg == 1) {
		img.style.display = "block";
	} else {
		img.style.display = "none";
	}
}

// Отправляем данные формы, скрипту
function sendFormData()
{
	if(resultXpObject && lock_1==0)
	{

		//Очищаем таблицу вывода
		getObj("tableResult").innerHTML = "";
	
		urlInput		=	encodeURIComponent(getObj("urlInput").value.toString());
		regionInput		=	encodeURIComponent(getObj("regionInput").value.toString());
		queriesInput	=	encodeURIComponent(getObj("queriesInput").value.toString());
		
		if(searchMethod == 'serp') 		var url = 'action=serp&region='+regionInput+'&url='+urlInput+'&queries='+queriesInput;
		if(searchMethod == 'serpXml') 	{
			yandexApiUrl	=	encodeURIComponent(getObj("yandexApiUrl").value.toString());
			var url = 'action=serpXml&region='+regionInput+'&url='+urlInput+'&queries='+queriesInput+'&yandexApiUrl='+yandexApiUrl;
		}

		resultXpObject.open("POST",'exec.php',true);
		resultXpObject.setRequestHeader("Content-type","application/x-www-form-urlencoded");
		resultXpObject.onreadystatechange = function HRSC() {
		
			if(resultXpObject.readyState == 4) {
			
				// Картинка процесса и блокировщик функции
				lock_1	=	0;
				showLoadingimg(0);
					
				if(resultXpObject.status == 200) {
				
					var response = resultXpObject.responseXML;
					var xmlDoc = response.documentElement;
					var position, status, query, top100, alertCssClass = "", res = "", colorLine = 1;
					
					for(i=0;i<xmlDoc.getElementsByTagName("status").length;i++) {
					
						try { position = xmlDoc.getElementsByTagName("position")[i].firstChild.data } catch(e) {}
						try { status = xmlDoc.getElementsByTagName("status")[i].firstChild.data } catch(e) {}
						try { query = xmlDoc.getElementsByTagName("query")[i].firstChild.data } catch(e) {}
						
						
						alertCssClass = "";
						
						// Если ничего не найдено, то выделяем красным и пишем что нету
						if(status == 'fail') {
							top100 = 'Нет в ТОП100';
							alertCssClass = "alert";
						} else top100 = position;
						
						if((i+1)%2 == 0) colorLine = 2; else colorLine = 1;
						
						res += '<tr><td class="inf_1 bgt'+colorLine+'">'+(i+1)+'</td><td class="inf_2 bgt'+colorLine+' '+alertCssClass+'">'+top100+'</td><td class="inf_3 bgt'+colorLine+'">'+query+'</td></tr>';
					}
					
					getObj("resbox").style.display = "block";
					getObj("tableResult").innerHTML = res;
					
		
					
				} else alert("ERROR-2\n");
			}
			
		}
		
		resultXpObject.send(url);
		
		showLoadingimg(1);
		lock_1	=	1;
	}
}


// Список с доступными регионами
function getRegionsHelpList() {

	//regionsIdArr
	//regionsNamesArr
	
	
	var regBox			= getObj("regionInput").value.toLowerCase();	// Поле, куда юзер вводит регионы
	var helpList		= getObj("regionHelplist"); 					// Всплывающий список с регионами
	var maxViewWords	= 5;											// Максимальное кол-во слов в выадающем списке
	var srchd			= 0;											// Сколько слов уже нашли
	
	
	//Если юзер ввел больше n букв, то запускаем поиск
	if(regBox.length >= 0) {
	
		// Прячем список и очищаем его
		helpList.style.display = "none"; 
		helpList.innerHTML = "";
		
		//При новом вводе обнуляем счетчик
		srchd = 0;
		
		
		// Пробегаемся по всем словам из списка
		for(i=0;i<regionsNamesArr.length;i++) {
			// Если лимит найденых слов превышен, то выходим
			if(srchd < maxViewWords) {
			
				if(regBox[0] && regionsNamesArr[i][0])
				{
					tmpRegion = regionsNamesArr[i].toLowerCase(); // создаем копию региона, только в малом регистре, для сравнения слов/букв
					// Небольшая оптимизация, если слова начинаются на ту же букву, продолжаем работу
					if(regBox[0] == tmpRegion[0]) {
						
						// Обнуляем при новом поиске совпавшие буквы
						letters = 1;
						
						//далее находим слова, у которых первые буквы совпали. 1 - т.к первую буква сравнили в начале
						for(e=1;e<regBox.length;e++) {
							//Если первые буквы совпадают, то инкрементируем переменую
							if(regBox[e] == tmpRegion[e]) {
								letters++;
							}
						}
						
						// Если кол-во первых букв совпало в слове и в поле, то добавляем слово в список
						if(letters == regBox.length) {
						
							helpList.style.display = "block";
							helpList.innerHTML += '<div class="rhm" onclick="putIdregions(this.id)" id="rhm'+i+'">'+regionsNamesArr[i]+'</div>';
							
							//добавляем к найденому слову
							srchd++;
						}
						
					} 
				}
			} else {
				break;
			}
			
		} 
	}
		
}

// При выборе города, вставляется id в поле
function putIdregions(id) {
	if(id) {
		getObj("regionInput").value = getObj(id).innerHTML;
		getObj("regionHelplist").style.display = "none";
	}
}


// Очищает поля
function clearInp(arg) {
	if(arg) {
		getObj(arg).value = "";
	}
}

// функция переключения метода поиска
function radioMethodSrch(arg) {
	if(arg) {	
		switch(arg) {
		case 'serp':
			getObj('yapiurlbox').style.display = "none";
			searchMethod = 'serp';
		break;
		case 'serpXml':
			getObj('yapiurlbox').style.display = "block";
			searchMethod = 'serpXml';
		break;
		}
	}
}





