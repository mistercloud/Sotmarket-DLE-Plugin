<?php
    /**
     * ����� "���������� ��������� ������"
     *
     * @package     SC_CMS
     * @subpackage  CORE
     * @copyright   Copyright (c) 2009
     * @version     0.1 ��������� �� 23.12.2009
     * @author      ������� �������� ( k-v-n@inbox.ru )
     */
    
    class SotmarketSpiderController
    {
		/**
		 * ����� ������� � ����� sitemap.xml
		 */

		const SITEMAP_URLS_PER_FILE = 2500;

		/**
		 * ������ ����������� IP �������
		 */

		private $mIPs;

		/**
		 * ������ ����������� User Agent
		 */

		private $mUserAgents;

		/**
		 * ����������� ��������� ������
		 */

		private static $mInstance;

		/**
		 * ������ �� ����� "���������� � ��"
		 */

		private $pDataBase;

		/**
		 * ����������� ��������� ������
		 *
		 * @params	SotmarketConfig		$db		��������� ������������ ����������
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
		 * �����������
		 *
		 * @params	SotmarketConfig		$config		��������� ������������ ����������
		 */

		protected function __construct(SotmarketConfig &$config, SotmarketDB &$db = null)
		{
			$this->mConfig		= $config;
			$this->pDataBase	= $db;

			/**
			 * ������ ���� � IP ��������
			 */

			$FileIPs = $this->mConfig->config['paths']['rootLocal'] . "/" . $this->mConfig->config['paths']['data'] . "/ips_ban.txt";

			if( !is_file($FileIPs) )
			{
				throw new Exception("�� ������ ���� " . $FileIPs);
			}
			else
			{
				$this->mIPs = file($FileIPs);
			}

			/**
			 * ������ ���� � User Agent
			 */

			$FileUA = $this->mConfig->config['paths']['rootLocal'] . "/" . $this->mConfig->config['paths']['data'] . "/spiders_ban.txt";

			if( !is_file($FileUA) )
			{
				throw new Exception("�� ������ ���� " . $FileUA);
			}
			else
			{
				$this->mUserAgents = file($FileUA);
			}
		}

		/**
		 * ��������� �������� �� ��������� �������
		 *
		 * @params	string		$user_ip		��������� �� ����������� � ��
		 * @params	string		$user_agent		��������� ������������ ����������
		 * @return	bool
		 */

		public function isSpider($user_ip, $user_agent)
		{
			/**
			 * ��������� �������� �� IP
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
			 * ��������� �������� �� User Agent
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
		 * ���������� SiteMap
		 *
		 * ����������� ��������� ��� �������� �������
		 */

		public function generateSiteMap()
		{
			/** 
			 * ������������ ������ sitemap.xml
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
			 * ������� ������ sitemap
			 */

			$filesCounter = 0;

			/**
			 * ��������� URL'��
			 */

			$Paths			= new SotmarketPaths($this->mConfig->config['paths']["rootLocal"], $this->mConfig->config['paths']["rootUrl"], $this->mConfig->config['paths']["data"], $this->mConfig->config['paths']["wwwdata"], $_SERVER);
			$URIProcessor	= new SotmarketUriProcessor($Paths->rootUrl(), $Paths->requestUri(), $this->mConfig->config['uris']);

			/**
			 * ����� ����� �������
			 */

			$ItemsPerQuery = isset( $this->mConfig->config['sitemap'] ) && isset( $this->mConfig->config['sitemap']['items_per_file'] ) && (int)$this->mConfig->config['sitemap']['items_per_file'] ? (int)$this->mConfig->config['sitemap']['items_per_file'] : self::SITEMAP_URLS_PER_FILE;
			$productsCount = SotmarketProduct::instance($this->pDataBase)->getProductsCount();

			if( $productsCount )
			{
				/**
				 * ��������
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
		 * ���������� Robots.txt
		 *
		 * ����������� ��������� ��� �������� �������, ��������� � �������������
		 */

		public function generateRobots()
		{
			/** 
			 * ������������ ������ sitemap.xml
			 */

			$SitemapFolder = $this->mConfig->config['paths']["rootLocal"] . 
							 ( isset($this->mConfig->config['sitemap']) && isset( $this->mConfig->config['sitemap']["location_path"] ) && $this->mConfig->config['sitemap']["location_path"] ? "/" . $this->mConfig->config['sitemap']["location_path"] : "" ) . 
							 "/";

			$SitemapURL = $this->mConfig->config['paths']["rootUrl"] . 
							 ( isset($this->mConfig->config['sitemap']) && isset( $this->mConfig->config['sitemap']["location_url"] ) && $this->mConfig->config['sitemap']["location_path"] ? "/" . $this->mConfig->config['sitemap']["location_path"] : "" ) . 
							 "/";
			/**
			 * ���� � �������
			 */

			$Paths				= new SotmarketPaths($this->mConfig->config['paths']["rootLocal"], $this->mConfig->config['paths']["rootUrl"], $this->mConfig->config['paths']["data"], $this->mConfig->config['paths']["wwwdata"], $_SERVER);
			$URIProcessor		= new SotmarketUriProcessor($Paths->rootUrl(), $Paths->requestUri(), $this->mConfig->config['uris']);

			$FilePermissions	= isset($this->mConfig->config['application']['permissions']) ? $this->mConfig->config['application']['permissions'] : SotmarketConfig::DEFAULT_PERMISSIONS;

			/**
			 * ����������� ���������
			 */

			$DisabledCategories = SotmarketCategory::instance($this->pDataBase)->getDisabledCategories();

			for( $i=0; $i<count($DisabledCategories); $i++ )
			{
				$DisabledCategories[$i]['url'] = str_replace("//", "/", str_replace($Paths->rootUrl(), "/", $URIProcessor->createUri('productlist', array("category" => $DisabledCategories[$i]['url']))));
			}

			/**
			 * ����������� ������������� � ����������
			 */

			$DisabledManufacturers = SotmarketCategory::instance($this->pDataBase)->getDisabledManufacturers();

			for( $i=0; $i<count($DisabledManufacturers); $i++ )
			{
				$DisabledManufacturers[$i]['url'] = str_replace("//", "/", str_replace($Paths->rootUrl(), "/", $URIProcessor->createUri('productlist', array("category" => $DisabledManufacturers[$i]['category_url'], 'manufacturer' => $DisabledManufacturers[$i]['manufacturer_url']))));
			}
			
			/**
			 * ��������� robots.txt
			 */

			$Template = $this->getNewTemplate($Paths);
			$Template->assign("DISABLED_CATEGORIES", array_merge($DisabledCategories, $DisabledManufacturers));

			/**
			 * ������ �� SitemapIndex
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
		 * ������� ��������� ������ Smarty
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