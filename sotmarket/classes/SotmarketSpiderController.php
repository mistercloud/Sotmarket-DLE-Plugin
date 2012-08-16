<?php
    /**
     * Класс "Контроллер поисковых систем"
     *
     * @package     SC_CMS
     * @subpackage  CORE
     * @copyright   Copyright (c) 2009
     * @version     0.1 изменения от 23.12.2009
     * @author      Ковылин Владимир ( k-v-n@inbox.ru )
     */
    
    class SotmarketSpiderController
    {
		/**
		 * Число записей в файле sitemap.xml
		 */

		const SITEMAP_URLS_PER_FILE = 2500;

		/**
		 * Массив блокируемых IP адресов
		 */

		private $mIPs;

		/**
		 * Массив блокируемых User Agent
		 */

		private $mUserAgents;

		/**
		 * Константный экземпляр класса
		 */

		private static $mInstance;

		/**
		 * Ссылка на класс "Подключние к БД"
		 */

		private $pDataBase;

		/**
		 * Константный экземпляр класса
		 *
		 * @params	SotmarketConfig		$db		указатель конфигурацию проложения
		 * @return 	SotmarketSpiderController
		 */

		public static function instance(SotmarketConfig &$config, SotmarketDB &$db = null)
		{
			if( !( SotmarketSpiderController::$mInstance instanceof SotmarketSpiderController ) )
			{
				SotmarketSpiderController::$mInstance = new SotmarketSpiderController($config, $db);
			}

			return SotmarketSpiderController::$mInstance;
		}

		/**
		 * Конструктор
		 *
		 * @params	SotmarketConfig		$config		указатель конфигурацию проложения
		 */

		protected function __construct(SotmarketConfig &$config, SotmarketDB &$db = null)
		{
			$this->mConfig		= $config;
			$this->pDataBase	= $db;

			/**
			 * Читаем файл с IP адресами
			 */

			$FileIPs = $this->mConfig->config['paths']['rootLocal'] . "/" . $this->mConfig->config['paths']['data'] . "/ips_ban.txt";

			if( !is_file($FileIPs) )
			{
				throw new Exception("Не найден файл " . $FileIPs);
			}
			else
			{
				$this->mIPs = file($FileIPs);
			}

			/**
			 * Читаем файл с User Agent
			 */

			$FileUA = $this->mConfig->config['paths']['rootLocal'] . "/" . $this->mConfig->config['paths']['data'] . "/spiders_ban.txt";

			if( !is_file($FileUA) )
			{
				throw new Exception("Не найден файл " . $FileUA);
			}
			else
			{
				$this->mUserAgents = file($FileUA);
			}
		}

		/**
		 * Выполняет проверку на поисковую систему
		 *
		 * @params	string		$user_ip		указатель на подключение к БД
		 * @params	string		$user_agent		указатель конфигурацию проложения
		 * @return	bool
		 */

		public function isSpider($user_ip, $user_agent)
		{
			/**
			 * Выполняет проверку по IP
			 */

			if($user_ip)
			{
				for( $i=0; $i<count($this->mIPs); $i++ )
				{
					if( $this->mIPs[$i] == $user_ip )
					{
						return true;
					}
				}
			}

			/**
			 * Выполняет проверку на User Agent
			 */

			if($user_agent)
			{
				for( $i=0; $i<count($this->mUserAgents); $i++ )
				{
					if( substr_count(strtolower($user_agent), $this->mUserAgents[$i]) )
					{
						return true;
					}
				}
			}

			return false;
		}

		/**
		 * Генерирует SiteMap
		 *
		 * Выполянется генерация для каталога товаров
		 */

		public function generateSiteMap()
		{
			/** 
			 * Расположение файлов sitemap.xml
			 */

			$SitemapFolder = $this->mConfig->config['paths']["rootLocal"] . 
							 ( isset($this->mConfig->config['sitemap']) && isset( $this->mConfig->config['sitemap']["location_path"] ) && $this->mConfig->config['sitemap']["location_path"] ? "/" . $this->mConfig->config['sitemap']["location_path"] : "" ) . 
							 "/";

			$FilePermissions = isset($this->mConfig->config['application']['permissions']) ? $this->mConfig->config['application']['permissions'] : SotmarketConfig::DEFAULT_PERMISSIONS;

			if( !is_dir($SitemapFolder) )
			{
				mkdir($SitemapFolder, $FilePermissions, true );
				@chmod($SitemapFolder, $FilePermissions);
			}

			$SitemapURL = $this->mConfig->config['paths']["rootUrl"] . 
							 ( isset($this->mConfig->config['sitemap']) && isset( $this->mConfig->config['sitemap']["location_url"] ) && $this->mConfig->config['sitemap']["location_path"] ? "/" . $this->mConfig->config['sitemap']["location_path"] : "" ) . 
							 "/";

			/** 
			 * Счетчик файлов sitemap
			 */

			$filesCounter = 0;

			/**
			 * Генератор URL'ов
			 */

			$Paths			= new SotmarketPaths($this->mConfig->config['paths']["rootLocal"], $this->mConfig->config['paths']["rootUrl"], $this->mConfig->config['paths']["data"], $this->mConfig->config['paths']["wwwdata"], $_SERVER);
			$URIProcessor	= new SotmarketUriProcessor($Paths->rootUrl(), $Paths->requestUri(), $this->mConfig->config['uris']);

			/**
			 * Общее число товаров
			 */

			$ItemsPerQuery = isset( $this->mConfig->config['sitemap'] ) && isset( $this->mConfig->config['sitemap']['items_per_file'] ) && (int)$this->mConfig->config['sitemap']['items_per_file'] ? (int)$this->mConfig->config['sitemap']['items_per_file'] : self::SITEMAP_URLS_PER_FILE;
			$productsCount = SotmarketProduct::instance($this->pDataBase)->getProductsCount();

			if( $productsCount )
			{
				/**
				 * Продукты
				 */

				for( $i=0; $i<ceil($productsCount/$ItemsPerQuery); $i++ )
				{
					$Products	= SotmarketProduct::instance($this->pDataBase)->getProductURLs($ItemsPerQuery, ($i*$ItemsPerQuery));

					for( $j=0; $j<count($Products); $j++ )
					{
						$Products[$j]['url'] = $URIProcessor->createUri('product', array("uri" => $Products[$j]["url"]));
					}

					$Template = $this->getNewTemplate($Paths);
					$Template->assign("PRODUCTS",			$Products);
					$Template->assign("GENERATION_DATE",	date("Y-m-d"));
					$Template->assign("IS_FIRST_FILE",		!$filesCounter);

					file_put_contents($SitemapFolder . "sitemap_" . $filesCounter . ".xml", iconv('cp1251', 'utf-8', $Template->fetch('sitemap.tpl')));
					@chmod($SitemapFolder . "sitemap_" . $filesCounter . ".xml", $FilePermissions);
					$filesCounter++;
				}

				/**
				 * Index Sitemap
				 */

				$Files = array();

				for( $i=0; $i<$filesCounter; $i++ )
				{
					$Files[] = array( 
							'url' => str_replace("//", "/", $SitemapURL . "sitemap_" . $i . ".xml")
					);
				}

				$Template = $this->getNewTemplate($Paths);
				$Template->assign("FILES",				$Files);
				$Template->assign("GENERATION_DATE",	date("Y-m-d"));

				file_put_contents($SitemapFolder . "sitemap_index.xml", iconv('cp1251', 'utf-8', $Template->fetch('sitemap_index.tpl')));
				@chmod($SitemapFolder . "sitemap_index.xml", $FilePermissions);
			}

			return true;
		}

		/**
		 * Генерирует Robots.txt
		 *
		 * Выполянется генерация для каталога товаров, категории и производители
		 */

		public function generateRobots()
		{
			/** 
			 * Расположение файлов sitemap.xml
			 */

			$SitemapFolder = $this->mConfig->config['paths']["rootLocal"] . 
							 ( isset($this->mConfig->config['sitemap']) && isset( $this->mConfig->config['sitemap']["location_path"] ) && $this->mConfig->config['sitemap']["location_path"] ? "/" . $this->mConfig->config['sitemap']["location_path"] : "" ) . 
							 "/";

			$SitemapURL = $this->mConfig->config['paths']["rootUrl"] . 
							 ( isset($this->mConfig->config['sitemap']) && isset( $this->mConfig->config['sitemap']["location_url"] ) && $this->mConfig->config['sitemap']["location_path"] ? "/" . $this->mConfig->config['sitemap']["location_path"] : "" ) . 
							 "/";
			/**
			 * Пути в системе
			 */

			$Paths				= new SotmarketPaths($this->mConfig->config['paths']["rootLocal"], $this->mConfig->config['paths']["rootUrl"], $this->mConfig->config['paths']["data"], $this->mConfig->config['paths']["wwwdata"], $_SERVER);
			$URIProcessor		= new SotmarketUriProcessor($Paths->rootUrl(), $Paths->requestUri(), $this->mConfig->config['uris']);

			$FilePermissions	= isset($this->mConfig->config['application']['permissions']) ? $this->mConfig->config['application']['permissions'] : SotmarketConfig::DEFAULT_PERMISSIONS;

			/**
			 * Отключенные категории
			 */

			$DisabledCategories = SotmarketCategory::instance($this->pDataBase)->getDisabledCategories();

			for( $i=0; $i<count($DisabledCategories); $i++ )
			{
				$DisabledCategories[$i]['url'] = str_replace("//", "/", str_replace($Paths->rootUrl(), "/", $URIProcessor->createUri('productlist', array("category" => $DisabledCategories[$i]['url']))));
			}

			/**
			 * Отключенные производители в категориях
			 */

			$DisabledManufacturers = SotmarketCategory::instance($this->pDataBase)->getDisabledManufacturers();

			for( $i=0; $i<count($DisabledManufacturers); $i++ )
			{
				$DisabledManufacturers[$i]['url'] = str_replace("//", "/", str_replace($Paths->rootUrl(), "/", $URIProcessor->createUri('productlist', array("category" => $DisabledManufacturers[$i]['category_url'], 'manufacturer' => $DisabledManufacturers[$i]['manufacturer_url']))));
			}
			
			/**
			 * Формируем robots.txt
			 */

			$Template = $this->getNewTemplate($Paths);
			$Template->assign("DISABLED_CATEGORIES", array_merge($DisabledCategories, $DisabledManufacturers));

			/**
			 * Ссылка на SitemapIndex
			 */

			if( is_file($SitemapFolder . "sitemap_index.xml") )
			{
				$Template->assign("SITEMAP_INDEX",  $SitemapURL . "sitemap_index.xml");
			}

			$FilePath = $Paths->rootLocal() . "/robots.txt";

			file_put_contents($FilePath, $Template->fetch('robots.tpl'));
			@chmod($FilePath, $FilePermissions);

			return true;
		}

		/**
		 * Создает экземпляр класса Smarty
		 *
		 * @param	SotmarketPaths		$Paths
		 * @return	Smarty
		 */

		protected function getNewTemplate(SotmarketPaths &$Paths)
		{
			$Template			= new Smarty();
			$Template->caching	= false;

			$rootLocal	= $Paths->rootLocal();
			$data		= $rootLocal . $Paths->data() . "smarty/";

			$Template->template_dir	= $rootLocal . "_templates";
			$Template->compile_dir	= $data . "templates_c";
			$Template->config_dir	= $rootLocal . "_smartyconfig";
			$Template->cache_dir	= $data . "cache";

			return $Template;
		}
	}
?>