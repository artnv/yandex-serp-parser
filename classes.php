<?php
class parser {

	// Загружает страницы с яндекса. Возвращет необработанный СЕРП
	private function getPageContents($query, $regionId, $pageNum) {
	
		if(!empty($query) && is_numeric($regionId) && is_numeric($pageNum)) {
				
			$query	=	urlencode($query);
			
			if($pageNum > 0) {
				$url 	= 'http://yandex.ru/yandsearch?p='.$pageNum.'&text='.$query.'&lr='.$regionId.'&rstr=-'.$regionId;	
			} else {
				$url 	= 'http://yandex.ru/yandsearch?text='.$query.'&lr='.$regionId.'&rstr=-'.$regionId;
			}

			// Потом нужно заменить на $url, тогда поиск будет осуществлятся в Яндексе, а не в сохраненных страницах!
			//return file_get_contents('yandex_saves_pages/'.$pageNum.'.htm');
			return file_get_contents($url);
		}
	}

	// Возвращает результат в XML, Простого СЕРП (НЕ XML api)
	public function getSerp($region, $inputUrl, $queries) {
		if(!empty($region) && !empty($inputUrl) && !empty($queries)) {

			$inputUrl		=	trim(urldecode($inputUrl));							// url введенеый юзером
			$queries		=	htmlspecialchars(strip_tags(urldecode($queries))); 	// Запросы
			$pageMaxNum		=	10; // Сколько обрабатываем страниц из Серп, каждая страница по 10 линков (ТОП-100)
			
			$regObj			=	new regions();
			$regionId		=   $regObj->getIdOfRegion($region); //Получаем id по региону
			
			// Проверка параметров
			if(preg_match("#^\d+$#", $regionId) && preg_match("#^(?:https?:\/\/|www\.)?[-\w\d_\.]+\/?$#is", $inputUrl, $m)) {
			
				// удаляем http и www, и / в текущем url
				$URL = preg_replace("#(?:https?:|\/|www\.)+#is", "", $inputUrl);
				

				// Очищаем запросы от лишнего мусора и разделяем запятыми. Далее создаем массив из запросов
				$newQueries	= explode(",", preg_replace("#[\n\r\b\,]+#is", ",", $queries));
				

				/* ------------------------------------------------------ */
				
				// Позиция линков на странице
				$realPosition = 1;
				
				header('Content-Type: text/xml; charset=utf-8');
				echo '<?xml version="1.0" encoding="UTF-8"?>';
				echo '<root>';
				
				// Очередь запросов
				for($i=0;$i<count($newQueries);$i++) {
					// Обновляем позицию на 1, для новых запросов
					$realPosition = 1;
					
					if($newQueries[$i]) {
						
						echo '<url>'.$URL.'</url>';
						
						// Загружаем и обрабатываем по странице, если находим то что нужно - выходим из цикла
						for($p=0;$p<$pageMaxNum;$p++) {
						
							// Загружаем по странице (Нумерация страниц яндекса с "0") (поисковый запрос, регион, номер страницы)
							$pageContent	=	$this->getPageContents($newQueries[$i], $regionId, $p); 
							
							$inPageUrlsArr = null; // Массив с url из СЕРП
							
							// Получаем url из СЕРП (вид site.com или www.site.com)
							//preg_match_all("#(?<=<span class=\"b-serp-url__item\">)(?:<a class=\"b-serp-url__link\" href=\"(?:https?\:\/\/)([^\"]+)\/\"[^>]*>.*?<\/a>)#is", $pageContent, $inPageUrlsArr);
							
							// Модифицированный регексп (ищет сайты с меткой "опасный")
							//preg_match_all("#(?<=<span class=\"b-serp-url__item\">)(?:<a class=\"b-serp-url__link\" href=\"(?:https?\:\/\/)?([^\"]+|\/infected[^\"]+)\"[^>]*>.*?<\/a>)#is", $pageContent, $inPageUrlsArr);
							preg_match_all("#(?<=<span class=\"b-serp-url__item\">)(?:<a class=\"b-serp-url__link\" href=\"(https?\:\/\/[^\"]+|\/infected[^\"]+)\"[^>]*>.*?<\/a>)#is", $pageContent, $inPageUrlsArr);
							

							// Пока не найден URL, значение всегда false
							$urlFindedSwitcher = false;
							
							//Линки из Серп (10шт), сравниваем с нашим url
							for($e=0;$e<count($inPageUrlsArr[1]);$e++) {

								if(preg_match("#".$URL."#is", $inPageUrlsArr[1][$e])) {
								
									// Обновляем переключатель на true, т.к нашли то что искали
									$urlFindedSwitcher = true;
								
									echo '<position>'.$realPosition.'</position>';
									echo '<query>'.$newQueries[$i].'</query>';
									echo '<status>ok</status>';
									
									$realPosition++;
									
									// Выходим из сравнения
									break;
								}
								
								$realPosition++;
								
							}
							
							// Если уже нашли то что искали, то прекращаем загружать страницы и выходим
							if($urlFindedSwitcher == true) {
								break;
							}
						}
						
						if($urlFindedSwitcher == false) {
							// Сообщаем скрипту что ничего не нашли
							echo '<position></position>';
							echo '<query>'.$newQueries[$i].'</query>';
							echo '<status>fail</status>';
						}

					}
				}
				
				echo '</root>';	
			} else echo 'fail-2';
		} else echo 'fail-1';
	}
	
	
	// Использует Api Яндекса для поиска
	public function getSerpXmlApi($region, $inputUrl, $queries, $yandexApiUrl) {
		if(!empty($region) && !empty($inputUrl) && !empty($queries) && !empty($yandexApiUrl)) {
			
			$inputUrl		=	trim(urldecode($inputUrl));									// url введенеый юзером
			$queries		=	htmlspecialchars(strip_tags(urldecode($queries))); 			// Запросы
			$pageMaxNum		=	10; // Сколько обрабатываем страниц из Серп, каждая страница по 10 линков (ТОП-100)
			
			$regObj			=	new regions();
			$regionId		=   $regObj->getIdOfRegion($region); //Получаем id по региону
			
	
			// Проверка параметров
			if(preg_match("#^\d+$#", $regionId) && preg_match("#^(?:https?:\/\/|www\.)?[-\w\d_\.]+\/?$#is", $inputUrl, $m)) {
			
				// удаляем http и www, и / в текущем url
				$URL = preg_replace("#(?:https?:|\/|www\.)+#is", "", $inputUrl);
				

				// Очищаем запросы от лишнего мусора и разделяем запятыми. Далее создаем массив из запросов
				$newQueries	= explode(",", preg_replace("#[\n\r\b\,]+#is", ",", $queries));
				

				/* ------------------------------------------------------ */
				
				// Позиция линков на странице
				$realPosition = 1;
				
				header('Content-Type: text/xml; charset=utf-8');
				echo '<?xml version="1.0" encoding="UTF-8"?>';
				echo '<root>';
				
				// Очередь запросов
				for($i=0;$i<count($newQueries);$i++) {
					// Обновляем позицию на 1, для новых запросов
					$realPosition = 1;
					
					if($newQueries[$i]) {
						
						echo '<url>'.$URL.'</url>';
						
						// Загружаем и обрабатываем по странице, если находим то что нужно - выходим из цикла
						for($p=0;$p<$pageMaxNum;$p++) {
						
							// Загружаем по странице (Нумерация страниц яндекса с "0") (поисковый запрос, регион, номер страницы)
							$yandexApiUrl	.= '&query='.urlencode($newQueries[$i]).'&lr='.$regionId.'&page='.$p;	// Выданный url, в системе Яндекс API, куда будут посылатся запросы
							$pageXMLContent	=	file_get_contents($yandexApiUrl); // XML страница
							
							/* ----------------------------------------- xml*/
							$xml = simplexml_load_string($pageXMLContent);
							
							$inPageUrlsArr = array(); // Массив с url из СЕРП
							
							for($k=0;$k<10;$k++) {
								$inPageUrlsArr[$k] = $xml->response->results->grouping->group[$k]->doc->domain;
							}
	
							// Пока не найден URL, значение всегда false
							$urlFindedSwitcher = false;
							
							//Линки из Серп (10шт), сравниваем с нашим url
							for($e=0;$e<count($inPageUrlsArr);$e++) {

								if(preg_match("#".$URL."#is", $inPageUrlsArr[$e])) {
								
									// Обновляем переключатель на true, т.к нашли то что искали
									$urlFindedSwitcher = true;
								
									echo '<position>'.$realPosition.'</position>';
									echo '<query>'.$newQueries[$i].'</query>';
									echo '<status>ok</status>';
									
									$realPosition++;
									
									// Выходим из сравнения
									break;
								}
								
								$realPosition++;
								
							}
							
							// Если уже нашли то что искали, то прекращаем загружать страницы и выходим
							if($urlFindedSwitcher == true) {
								break;
							}
						}
						
						if($urlFindedSwitcher == false) {
							// Сообщаем скрипту что ничего не нашли
							echo '<position></position>';
							echo '<query>'.$newQueries[$i].'</query>';
							echo '<status>fail</status>';
						}

					}
				}
				
				echo '</root>';	
			} else echo 'fail-2';	
		} else echo 'fail-1';
	}
}

// Класс для работы с регионами
class regions {

	// Список с регионами
	private $regionsPatch = "regionsDB.txt";	

	
	// Выводит два массива, в js, индексы и регионы, для функции автодополнения
	public function getRegionsJsArray() {
		
		// Если существует файл, то продолжаем
		if(file_exists($this->regionsPatch)) {
		
			$regionsList = file_get_contents($this->regionsPatch);
			// Разделяем id от региона
			$regionsArray = explode("@",$regionsList);
			
			header('Content-Type: text/plain; charset=utf-8');
			
			$r_id 	= "";
			$r_n  	= "";
			$z 		= ",";
			
			// Создаем 2 массива с id и регионами
			for($i=1;$i<(count($regionsArray)-1);$i++) {
			
				if($i%2 == 0) {
					if((count($regionsArray)-2)== $i) $z = "";
					$r_n	.= '"'.$regionsArray[$i].'"'.$z;
				} else {
					if((count($regionsArray)-3) == $i) $z = "";
					$r_id	.= $regionsArray[$i].$z;
				}

			}
			
			//echo "var regionsIdArr = new Array(".$r_id.");\n";
			echo "var regionsNamesArr = new Array(".$r_n.");\n";
			
			//Проверка кол-ва элементов
			//echo "alert(regionsIdArr.length+'--'+regionsNamesArr.length)";
		} else {
		
			//echo "var regionsIdArr = new Array(1);\n";
			echo "var regionsNamesArr = new Array('Файла regionsDB.txt не существует!');\n";
		
		}
		
	}
	
	// Получаем id региона, по названию
	public function getIdOfRegion($region) {

		if(!empty($region)) {

			if(file_exists($this->regionsPatch)) {

				$regionsList = file_get_contents($this->regionsPatch);
				
				if(preg_match("#(\d+)@".trim($region)."@#siu", $regionsList, $res)) {
					return $res[1];
				} else {
					// Если такого региона не существует, то возвращаем id-России, поиск будет осуществлятся тут
					return 225;
				}
				
			} else echo 'Файла не существует';

		}

	}

}

?>