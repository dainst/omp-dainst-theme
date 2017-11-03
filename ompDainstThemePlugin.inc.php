<?php

/**
 * @file plugins/themes/default/ompDainstThemePlugin.inc.php
 *
 * Copyright (c) 2014-2017 Simon Fraser University
 * Copyright (c) 2003-2017 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class DefaultThemePlugin
 * @ingroup plugins_themes_default
 *
 * @brief Default theme
 */

import('lib.pkp.classes.plugins.ThemePlugin');

class ompDainstThemePlugin extends ThemePlugin {

	/*
	 * @copydoc ThemePlugin::isActive()
	 */
	public function isActive() {
		if (defined('SESSION_DISABLE_INIT')) return true;
		return parent::isActive();
	}

	/**
	 * Initialize the theme's styles, scripts and hooks. This is run on the
	 * currently active theme and it's parent themes.
	 *
	 * @return null
	 */
	public function init() {

		// Register theme options
		/*
		$this->addOption('typography', 'radio', array(
			'label' => 'plugins.themes.ompDainstTheme.option.typography.label',
			'description' => 'plugins.themes.ompDainstTheme.option.typography.description',
			'options' => array(
				'notoSans' => 'plugins.themes.ompDainstTheme.option.typography.notoSans',
				'notoSerif' => 'plugins.themes.ompDainstTheme.option.typography.notoSerif',
				'notoSerif_notoSans' => 'plugins.themes.ompDainstTheme.option.typography.notoSerif_notoSans',
				'notoSans_notoSerif' => 'plugins.themes.ompDainstTheme.option.typography.notoSans_notoSerif',
				'lato' => 'plugins.themes.ompDainstTheme.option.typography.lato',
				'lora' => 'plugins.themes.ompDainstTheme.option.typography.lora',
				'lora_openSans' => 'plugins.themes.ompDainstTheme.option.typography.lora_openSans',
			)
		));

		$this->addOption('baseColour', 'colour', array(
			'label' => 'plugins.themes.ompDainstTheme.option.colour.label',
			'description' => 'plugins.themes.ompDainstTheme.option.colour.description',
			'default' => '#1E6292',
		));
		*/

		// cache cleansing
		$templateMgr = TemplateManager::getManager();
		if ($this->getServerType() != "production") {
			$templateMgr->caching = 0;
			$templateMgr->cache_lifetime = 0;
			$templateMgr->clear_all_cache();
			$templateMgr->clear_compiled_tpl();
		}


		// Load primary stylesheet
		$this->addStyle('stylesheet', 'styles/index.less');

		// Store additional LESS variables to process based on options
		$additionalLessVariables = array();


		// dainst functions
		$this->theUrl = Request::getBaseUrl();

		$templateMgr->register_function("idai_head", array($this, "getHead"));
		$templateMgr->register_block("idai_navbar", array($this, "getNavbar"));
		$templateMgr->register_function("idai_footer", array($this, "getFooter"));
		$templateMgr->register_function("pdf_viewer", array($this, "getViewer"));
		$templateMgr->register_function("getOJSFolder", array($this, "getOJSFolder"));
		$templateMgr->register_function("getOJSDomain", array($this, "getOJSDomain"));

		require_once($this->getFilePath() . '/lib/idai-components-php/idai-components.php');

		$this->_idaic = new \idai\components(
			array(
				'return'	=>	true,
				'webpath'	=> 	"{$this->theUrl}/{$this->pluginPath}/lib/idai-components-php/"
			)
		);
		$this->_idaic->settings["scripts"]["jquery"]["include"] = false;
		$this->_idaic->settings["scripts"]["bootstrap"]["include"] = false;



		// continue

		$request = Application::getRequest();

		// Load icon font FontAwesome - http://fontawesome.io/
		if (Config::getVar('general', 'enable_cdn')) {
			$url = 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.css';
		} else {
			$url = $request->getBaseUrl() . '/lib/ui-library/static/fontawesome/fontawesome.css';
		}
		$this->addStyle(
			'fontAwesome',
			$url,
			array('baseUrl' => '')
		);

		// Load jQuery from a CDN or, if CDNs are disabled, from a local copy.
		$min = Config::getVar('general', 'enable_minified') ? '.min' : '';
		$request = Application::getRequest();
		if (Config::getVar('general', 'enable_cdn')) {
			$jquery = '//ajax.googleapis.com/ajax/libs/jquery/' . CDN_JQUERY_VERSION . '/jquery' . $min . '.js';
			$jqueryUI = '//ajax.googleapis.com/ajax/libs/jqueryui/' . CDN_JQUERY_UI_VERSION . '/jquery-ui' . $min . '.js';
		} else {
			// Use OJS's built-in jQuery files
			$jquery = $request->getBaseUrl() . '/lib/pkp/lib/vendor/components/jquery/jquery' . $min . '.js';
			$jqueryUI = $request->getBaseUrl() . '/lib/pkp/lib/vendor/components/jqueryui/jquery-ui' . $min . '.js';
		}
		// Use an empty `baseUrl` argument to prevent the theme from looking for
		// the files within the theme directory
		$this->addScript('jQuery', $jquery, array('baseUrl' => ''));
		$this->addScript('jQueryUI', $jqueryUI, array('baseUrl' => ''));
		$this->addScript('jQueryTagIt', $request->getBaseUrl() . '/lib/pkp/js/lib/jquery/plugins/jquery.tag-it.js', array('baseUrl' => ''));

		// Load Bootsrap's dropdown
		//$this->addScript('popper', 'js/lib/popper/popper.js');
		//$this->addScript('bsUtil', 'js/lib/bootstrap/util.js');
		//$this->addScript('bsDropdown', 'js/lib/bootstrap/dropdown.js');

		// Load custom JavaScript for this theme
		$this->addScript('default', 'js/main.js');

		// Add navigation menu areas for this theme
		$this->addMenuArea(array('primary', 'user'));


		/**
		 * Stand:
		 * - jquery und idai-navbar.js kommen nicht in der richtigen reihenfolge, daher geht imprint-popup nicht
		 * -
		 *
		 *
		 */




	}

	/**
	 * Get the name of the settings file to be installed on new journal
	 * creation.
	 * @return string
	 */
	function getContextSpecificPluginSettingsFile() {
		return $this->getPluginPath() . '/settings.xml';
	}

	/**
	 * Get the name of the settings file to be installed site-wide when
	 * OJS is installed.
	 * @return string
	 */
	function getInstallSitePluginSettingsFile() {
		return $this->getPluginPath() . '/settings.xml';
	}

	/**
	 * Get the display name of this plugin
	 * @return string
	 */
	function getDisplayName() {
		return __('plugins.themes.omp-dainst-theme.name');
	}

	/**
	 * Get the description of this plugin
	 * @return string
	 */
	function getDescription() {
		return __('plugins.themes.omp-dainst-theme.description');
	}


	function getServerType() {

		$testservers = array(
			'test.publications.dainst.org'
		);
		$devservers = array(
			'195.37.232.186'
		);
		if (in_array($_SERVER['SERVER_NAME'], $testservers)) {
			return "test";
		}
		if (in_array($_SERVER['SERVER_NAME'], $devservers)) {
			return "dev";
		}
		return "production";
	}

	function getHead($params, &$smarty) {
		return $this->_idaic->header();
	}



	/**
	 * show the idai-navbar!
	 *
	 * registered as smarty block function
	 *
	 * @param unknown $params
	 * 	subtitle - string: journal name
	 * @return string
	 */
	function getNavbar($params, $content, &$smarty, &$repeat) {
		if ($repeat == true) {
			return;
		}

		$session = $this->getUser($smarty);

		$server = $this->getServerType();

		// construct the navbar via the settings array
		$this->_idaic->settings['logo']['text'] 					= '/ books';
		$this->_idaic->settings['logo']['src'] 						= $this->theUrl . '/' . $this->pluginPath . '/img/logo_publications.png';
		$this->_idaic->settings['logo']['href'] 					= $this->theUrl;
		$this->_idaic->settings['logo']['href2'] 					= $smarty->smartyUrl(array('page' => "index"),$smarty);

		if ($server != "production") {
			$this->_idaic->settings['buttons']["aaa-warning"]		= array(
				"label" => "$server server",
				"class" => "$server-server-warning"
			);
		}



		//$smarty = new TemplateManager();

		$context = $smarty->get_template_vars('currentContext');

		// search
		$this->_idaic->settings['search']['href']					= ($context != null) ?
			$smarty->smartyUrl(array("page" => "catalog", "context" => $context->getPath(), "op" => 'results'), $smarty) :
			$this->theUrl . '/index.php/index/catalog/results?';
		$this->_idaic->settings['search']['name'] 					= "query";
		$this->_idaic->settings['search']["label"]					= strtoupper(AppLocale::translate("common.search"));
		$this->_idaic->settings['search']["method"]					= "post";

		// user
		$this->_idaic->settings["user"]["name"] 					= (Validation::isLoggedIn()) ? $session->userName : '';

		$this->_idaic->settings['buttons']['login']['href'] 		= $smarty->smartyUrl(array("page" => "login", "op" => "signIn"),$smarty);
		$this->_idaic->settings['buttons']['login']['label'] 		= AppLocale::translate("user.login");
		unset($this->_idaic->settings['buttons']['login']['glyphicon']);
		$this->_idaic->settings['buttons']['register']['href'] 		= $smarty->smartyUrl(array("page" => "user", "op" => "register"),$smarty);
		$this->_idaic->settings['buttons']['register']['label'] 	= AppLocale::translate("user.register");

		$this->_idaic->settings['buttons']['usermenu']["glyphicon"]	= 'user';

		// user menu
		$this->_idaic->settings['buttons']['usermenu']["submenu"]["a"] = array(
			"label"	=>	AppLocale::translate("navigation.dashboard") . "<span class='badge pull-right'>" . $session->notifications . "</span>",
			"href"	=>	$smarty->smartyUrl(array("page" => "user", "op" => "submissions"), $smarty)
		);
		$this->_idaic->settings['buttons']['usermenu']["submenu"]["b"] = array(
			"label"	=>	AppLocale::translate("user.profile"),
			"href"	=>	$smarty->smartyUrl(array("page" => "user", "op" => "profile", "context" => $context->getPath()), $smarty)
		);
		$this->_idaic->settings['buttons']['usermenu']["submenu"]["c"] = array(
			"label"	=>	AppLocale::translate("navigation.admin"),
			"href"	=>	$smarty->smartyUrl(array("page" => "admin", "context" => $context->getPath()), $smarty)
		);

		// context menu
		$contextId = ($context) ? $context->getId() : CONTEXT_ID_NONE;
		$navigationMenuDao = DAORegistry::getDAO('NavigationMenuDAO');
		$navigationMenu = $navigationMenuDao->getByArea($contextId, 'primary');
		if (isset($navigationMenu)) {
			import('classes.core.ServicesContainer');
			ServicesContainer::instance()
				->get('navigationMenu')
				->getMenuTree($navigationMenu);
		}

		foreach ($navigationMenu->menuTree as $i => $navigationMenuItemAssignment) {
			$navigationMenuItem = $navigationMenuItemAssignment->navigationMenuItem;
			if (!$navigationMenuItem->getIsDisplayed()) {
				continue;
			}

			$this->_idaic->settings['buttons']['amenu-' . $i] = array(
				"label"	=>	AppLocale::translate($navigationMenuItem->getData('titleLocaleKey')),
				"href"	=>	$navigationMenuItem->getData('url')
			);

			foreach ($navigationMenuItemAssignment->children as $ii => $subItem) {
				//echo "<pre>", print_r($subItem,1), "</pre>";
				$item = $subItem->navigationMenuItem;
				if (!$item->getIsDisplayed()) {
					continue;
				}
				$this->_idaic->settings['buttons']['amenu-' . $i]['submenu']['menu-' . $i . '-' . $subItem->getData('seq')] = array(
					"label"	=>	AppLocale::translate($item->getData('titleLocaleKey')),
					"href"	=>	$item->getData('url')
				);
			}

		}

		// language menu
		foreach ($this->getLocales() as $localeKey => $localeName) {
			$this->_idaic->settings['buttons']['languagemenu']['submenu'][$localeKey] = array(
				"label"	=> $localeName,
				"href"	=> $smarty->smartyUrl(
					array(
						"page" => "user",
						"op" => "setLocale",
						"path" => $localeKey,
						"source" => $_SERVER['REQUEST_URI'],
						"router" => ROUTE_PAGE),
					$smarty
				)
			);
		}

		// remove contact
		unset($this->_idaic->settings['buttons']['zzzzcontact']);



/*
		// admin/editor specicifc
		$isArticle = Request::getRequestedPage() == 'article';

		if ($journal and $isArticle) {
			$journalPath = $journal->getPath();
			$args = Request::getRequestedArgs();
			$articleId = $args[0];
			$galleyId = (count($args) > 1) ? $args[1] : -1;
			$user =& Request::getUser();
			if ($user) {
				$roleDao =& DAORegistry::getDAO('RoleDAO');
				$isEditor = $roleDao->userHasRole($journal->getId(), $user->getId(), ROLE_ID_EDITOR);
				$isManager = $roleDao->userHasRole($journal->getId(), $user->getId(), ROLE_ID_JOURNAL_MANAGER);
			} else {
				$isEditor = false;
				$isManager = false;
			}

			if ($isEditor or $isManager) {
				$this->_idaic->settings['buttons']['edit'] = array(
					'label' => AppLocale::translate("plugins.themes.dainst.edit"),
					'submenu' => array()
				);

				$this->_idaic->settings['buttons']['edit']["submenu"]["editarticle"] = array(
					"label"	=>	AppLocale::translate("plugins.themes.dainst.editarticle"),
					"href"	=>	"{$this->theUrl}/index.php/$journalPath/editor/submission/$articleId"
				);

				$this->_idaic->settings['buttons']['edit']["submenu"]["editarticlemeta"] = array(
					"label"	=>	AppLocale::translate("plugins.themes.dainst.editarticlemeta"),
					"href"	=>	"{$this->theUrl}/index.php/$journalPath/editor/viewMetadata/$articleId"
				);

				if ($galleyId > -1) {
					$this->_idaic->settings['buttons']['edit']["submenu"]["editgalley"] = array(
						"label"	=>	AppLocale::translate("plugins.themes.dainst.editgalley"),
						"href"	=>	"{$this->theUrl}/index.php/$journalPath/editor/editGalley/$articleId/$galleyId"
					);
				}

			}

			die();


		}


		unset($this->_idaic->settings['buttons']['zzzzcontact']);
		//*/
		return $this->_idaic->navbar($content);
	}

	/**
	 * show the idai-footer
	 *
	 * registered as  smarty function
	 *
	 * @param array $params
	 * @return string
	 */
	function getFooter($params, &$smarty) {
		$this->_idaic->settings["footer_classes"] = array($params["mode"]);

		$this->_idaic->settings["footer_links"]["termsofuse"] = array(
			'label' => AppLocale::translate("plugins.themes.dainst.termsOfUse"),
			'moreinfo' => AppLocale::translate("plugins.themes.dainst.termsOfUseText"), //'Terms of use',
			'id' =>  'idai-footer-termsOfUse' // important
		);

		$this->_idaic->settings["footer_links"]["contact"] = array(
			'text' => AppLocale::translate("plugins.themes.dainst.reportBugsTo"), // report Bugs to
			'label' => 'idai.publications@dainst.de',
			'href' => 'mailto:idai.publications@dainst.de'
		);

		$this->_idaic->settings["footer_links"]["imprint"] = array(
			'label' => AppLocale::translate("plugins.themes.dainst.imprint"), // report Bugs to
			'moreinfo' => AppLocale::translate("plugins.themes.dainst.imprintText"),
			'id' =>  'idai-footer-imprint' // important
		);

		$this->_idaic->settings['version']			= '';

		unset($this->_idaic->settings["footer_links"]['licence']);

		return $this->_idaic->footer() . $this->getPiwik();
	}

	/**
	 * the piwik
	 * @return string
	 */
	function getPiwik() {
		if ($this->getServerType() != "production") {
			return '<!-- no piwik since no production -->';
		}
		$journalPath = ($this->_journal) ? $this->_journal->getPath() : '';
		ob_start();
		include($this->getFilePath() . '/piwik.inc.php');
		return ob_get_clean();
	}


	/**
	 * show the pdf reader
	 *
	 * registered as samrty function
	 *
	 * @param array $params
	 * 	file - full url to pdf file
	 * @param red $smarty
	 * @return string
	 */
	function getViewer($params, &$smarty) {
		$viewerSrc = Config::getVar('dainst', 'viewerUrl');
		if ($viewerSrc) {
			$url = "$viewerSrc?file={$params['file']}" . ((Config::getVar('dainst', 'viewerAppendId') or isset($_GET['ann'])) ? "&pubid={$params['article']}" : '');
		} else {
			$url = $params['file'];
		}

		return "<iframe id='dainstPdfViewer' onload='setViewerHeight()' src='$url'></iframe>";

		//$viewerSrc = $this->theUrl . '/plugins/themes/dainst/inc/dbv/viewer.html';
	}



	function getFilePath() {
		return dirname(__FILE__);
	}


	function getUser(&$smarty) {
		if (!defined('SESSION_DISABLE_INIT')) {
			$session =& Request::getSession();
			$loginUrl = Request::url(null, 'login', 'signIn');
			$user = Request::getUser();

			// if the page is not ssl enabled, and force_login_ssl is set, this flag will present a link instead of the form
			$forceSSL = false;
			if (Config::getVar('security', 'force_login_ssl')) {
				if (Request::getProtocol() != 'https') {
					$loginUrl = Request::url(null, 'login');
					$forceSSL = true;
				}
				$loginUrl = String::regexp_replace('/^http:/', 'https:', $loginUrl);
			}

			// Get a count of unread tasks.
			if ($user) {
				$notificationDao = DAORegistry::getDAO('NotificationDAO');
				// Exclude certain tasks, defined in the notifications grid handler
				$notifications = $notificationDao->getNotificationCount(false, $user->getId(), null, NOTIFICATION_LEVEL_TASK);
			}

			return (object) array(
				"userName" 	=>	$session->getSessionVar('username'),
				"loginUrl" 	=>	$loginUrl,
				"forceSSL" 	=>	$forceSSL,
				/*"hasOtherJournals" => $smarty->get_template_vars('hasOtherJournals'),*/
				/*"hasOtherJournals" => $smarty->get_template_vars('hasOtherJournals'),*/
				"signedInAs"	=> $session->getSessionVar('signedInAs'),
				"notifications"	=> $notifications
			);

		}

	}
	function getLocales() {
		if (!defined('SESSION_DISABLE_INIT')) {
			$press = Request::getPress();
			if (isset($press)) {
				$locales = $press->getSupportedLocaleNames();

			} else {
				$site = Request::getSite();
				$locales = $site->getSupportedLocaleNames();
			}
		} else {
			$locales =& AppLocale::getAllLocales();
		}
		return $locales;
	}

}

?>
