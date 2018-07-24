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

	private function registerSmartyFunction($name, $function) {
		$templateMgr = TemplateManager::getManager();
		if (!isset($templateMgr->registered_plugins['function'][$name])) {
			$templateMgr->register_function($name, $function);
		}
	}

	private function registerSmartyBlock($name, $function) {
		$templateMgr = TemplateManager::getManager();
		if (!isset($templateMgr->registered_plugins['block'][$name])) {
			$templateMgr->register_block($name, $function);
		}
	}

	/**
	 * Initialize the theme's styles, scripts and hooks. This is run on the
	 * currently active theme and it's parent themes.
	 *
	 * @return null
	 */
	public function init() {

		if ($this->getEnabled()) {
			HookRegistry::register('CatalogBookHandler::view', array($this, 'viewerCallback'), HOOK_SEQUENCE_LATE);
		}


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
		//$additionalLessVariables = array();

		// dainst functions
		$this->theUrl = Request::getBaseUrl();

		$this->registerSmartyFunction("idai_header", array($this, "getHeader"));
		$this->registerSmartyBlock("idai_navbar", array($this, "getNavbar"));
		$this->registerSmartyFunction("idai_footer", array($this, "getFooter"));
		$this->registerSmartyFunction("idai_footer_scripts", array($this, "getFooterScripts"));
		$this->registerSmartyFunction("idai_viewer", array($this, "getViewer"));
		$this->registerSmartyFunction("idai_modal", array($this, "getModal"));
		$this->registerSmartyFunction("idai_series", array($this, "getSeriesOfBook"));
		$this->registerSmartyFunction("idai_pubid_plugins", array($this, "getPubidPlugins"));

		require_once($this->getFilePath() . '/lib/idai-components-php/idai-components.php');

		$this->_idaic = new \idai\components(
			array(
				'return'	=>	true,
				'webpath'	=> 	"{$this->theUrl}/{$this->pluginPath}/lib/idai-components-php/"
			)
		);
		$this->_idaic->settings["scripts"]["jquery"]["include"] = false;
		$this->_idaic->settings["scripts"]["bootstrap"]["include"] = false;
		$this->_idaic->settings["scripts"]["navbar"]["include"] = "footer";
		$this->_idaic->settings["styles"]["idai-components.min"]["include"] = true;

		$request = Application::getRequest();

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


		// Load custom JavaScript for this theme
		$this->addScript('default', 'js/main.js');

		// idai cookie notice bullshit
		$this->addScript('idai-cookie-notice', 'lib/idai-cookie-notice/idai-cookie-notice.js');

		// Add navigation menu areas for this theme
		$this->addMenuArea(array('primary', 'user'));

		// add style hacks
		$this->addStyle('dainst', "styles/dainst.css");

		// show series on main page
        //$seriesCountDao = new SeriesCountDAO();
        $press = $request->getPress();
        $seriesDao = DAORegistry::getDAO('SeriesDAO');
        $series = $seriesDao->getByPressId($press->getId());
        //$seriesCount = $seriesCountDao->getSeriesCount();
        $templateMgr->assign('browseSeriesFactory', $series);
        //$templateMgr->assign('seriesCount', $seriesCount);
		$this->registerSmartyFunction('idai_series_info', array($this, "getSeriesInfo"));

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

	function getHeader($params) {
		return $this->_idaic->header();
	}


	function getModal($params, $smarty) {

		$context = $smarty->get_template_vars('currentContext');

		$button_ok  = "<button class='btn btn-primary' id='modal-dialog-ok'>" . AppLocale::translate("common.ok") . "</button>";
		$button_esc = "<button class='btn btn-default' id='modal-dialog-esc'>" . AppLocale::translate("common.cancel") . "</button>";
		$buttons_ok_esc = "<div class='row'><div class='btn-group pull-right'>$button_ok $button_esc</div></div>";

		$modalContent = array();
		if (Request::getRequestedOp() == 'register') {
			$modalContent['title'] = AppLocale::translate("plugins.themes.dainst.termsOfUse");
			$modalContent['body'] = $this->getPageContent('terms', $context->getId());
			$modalContent['footer'] = $buttons_ok_esc;
			$modalContent['class'] = "escapeable terms";
		} else {
			return "";
		}

		return "<div id='modal' class='{$modalContent['class']}'>
				<div class='dialog'>
					<div class='header'>{$modalContent['title']}</div>
					<div class='body'>{$modalContent['body']}</div>
					<div class='footer'>{$modalContent['footer']}</div>
				</div>
			</div>";

	}


	function getPageContent($page, $contextId) {
		$staticPagesDao = DAORegistry::getDAO('StaticPagesDAO');
		$staticPage = $staticPagesDao->getByPath($contextId, $page);
		if ($staticPage) {
			return $staticPage->getLocalizedContent();
		}
		return "x";
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
	function getNavbar($params, $content, $smarty, $repeat) {
		if ($repeat == true) {
			return;
		}

		$session = $this->getUser($smarty);

		$server = $this->getServerType();

		// construct the navbar via the settings array
		$this->_idaic->settings['logo']['text'] 					= '/ books';
		$this->_idaic->settings['logo']['src'] 						= $this->theUrl . '/' . $this->pluginPath . '/img/logo_publications.png';
		$this->_idaic->settings['logo']['href'] 					= "/books";
		$this->_idaic->settings['logo']['href2'] 					= $this->theUrl; //$smarty->smartyUrl(array('page' => "index"),$smarty);

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

		$this->_idaic->settings['buttons']['login']['href'] 		= $smarty->smartyUrl(array("page" => "login", "op" => "signIn"), $smarty);
		$this->_idaic->settings['buttons']['login']['label'] 		= AppLocale::translate("user.login");
		unset($this->_idaic->settings['buttons']['login']['glyphicon']);

		$this->_idaic->settings['buttons']['register']['href'] 		= $smarty->smartyUrl(array("page" => "user", "op" => "register"), $smarty);
		$this->_idaic->settings['buttons']['register']['label'] 	= AppLocale::translate("user.register");

		$this->_idaic->settings['buttons']['usermenu']["glyphicon"]	= 'user';

		// user menu
		$this->_idaic->settings['buttons']['usermenu']["submenu"] = array();
		$this->_idaic->settings['buttons']['usermenu']["submenu"]["a"] = array(
			"label"	=>	AppLocale::translate("user.logOut"),
			"href"	=>	$smarty->smartyUrl(array("page" => "login", "op" => "signOut"), $smarty)
		);
		$this->_idaic->settings['buttons']['usermenu']["submenu"]["b"] = array(
			"label"	=>	AppLocale::translate("navigation.dashboard") . "<span class='badge pull-right'>" . $session->notifications . "</span>",
			"href"	=>	$smarty->smartyUrl(array("page" => "submissions", "context" => $context->getPath()), $smarty)
		);
		$this->_idaic->settings['buttons']['usermenu']["submenu"]["c"] = array(
			"label"	=>	AppLocale::translate("user.profile"),
			"href"	=>	$smarty->smartyUrl(array("page" => "user", "op" => "profile", "context" => $context->getPath()), $smarty)
		);
		$this->_idaic->settings['buttons']['usermenu']["submenu"]["d"] = array(
			"label"	=>	AppLocale::translate("navigation.admin"),
			"href"	=>	$smarty->smartyUrl(array("page" => "admin", "context" => $context->getPath()), $smarty)
		);

		// context menu
		$contextId = ($context) ? $context->getId() : CONTEXT_ID_NONE;
		$navigationMenuDao = DAORegistry::getDAO('NavigationMenuDAO');



		$navigationMenus = $navigationMenuDao->getByArea($contextId, 'primary')->toArray();
		if (isset($navigationMenus[0])) {
			$navigationMenu = $navigationMenus[0];
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
	function getFooter($params, $smarty) {

		$context = $smarty->get_template_vars('currentContext');

		$this->_idaic->settings["footer_classes"] = array($params["mode"]);

		$this->_idaic->settings["footer_links"]["termsofuse"] = array(
			'label' => AppLocale::translate("plugins.themes.dainst.termsOfUse"),
			'href' => $smarty->smartyUrl(array("page" => "terms", "context" => $context->getPath()), $smarty)
		);

		$this->_idaic->settings["footer_links"]["contact"] = array(
			'text' => AppLocale::translate("plugins.themes.dainst.reportBugsTo"), // report Bugs to
			'label' => 'idai.publications@dainst.de',
			'href' => 'mailto:idai.publications@dainst.de'
		);

		$this->_idaic->settings["footer_links"]["imprint"] = array(
			'label' => AppLocale::translate("plugins.themes.dainst.imprint"),
			'href' => $smarty->smartyUrl(array("page" => "imprint", "context" => $context->getPath()), $smarty)
		);

		$this->_idaic->settings['version']			= '';

		unset($this->_idaic->settings["footer_links"]['licence']);

		return $this->_idaic->footer();
	}

	function getFooterScripts($params, $smarty) {
		return $this->getPiwik() . $this->_idaic->getScripts("footer");
	}

	/**
	 * the piwik
	 * @return string
	 */
	function getPiwik() {
		if ($this->getServerType() != "production") {
			return '<!-- no piwik since no production -->';
		}
		$journalPath = "books";
		ob_start();
		include($this->getFilePath() . '/piwik.inc.php');
		return ob_get_clean();
	}


	function viewerCallback($hookName, $args) {
		$publishedMonograph =& $args[1];
		$publicationFormat =& $args[2];
		$submissionFile =& $args[3];

		if ($submissionFile->getFileType() == 'application/pdf') {

			$templateMgr = TemplateManager::getManager($this->getRequest());

			$this->_idaic->settings["styles"]["small_footer"] = array(
				"include" => true,
				"src"	=> $this->theUrl . '/' . $this->pluginPath . '/styles/small_footer.css'
			);

			$templateMgr->display($this->getTemplateResource('display.tpl'));
			return true;
		}

		return false;
	}

	/**
	 * smarty function: idai_series
	 *
	 * setes the $parent and $type smarty vars in book page wo display series in breadcrumptrail
	 *
	 * @param $params
	 * @param $smarty
	 */
	function getSeriesOfBook($params, $smarty) {
		// are we on a book page?
		if (isset($params['monograph']) and (get_class($params['monograph']) == 'PublishedMonograph')) {
			$publishedMonograph = $params['monograph'];
			$seriesId = $publishedMonograph->getSeriesId();
			if ($seriesId) { // then add a series if possible
				$seriesDao = DAORegistry::getDAO("SeriesDAO");
				$series = $seriesDao->getById($seriesId);
				$smarty->assign('parent', $series);
				$smarty->assign('type', "series");
			}

			// title with author
			$smarty->assign("currentTitle", $publishedMonograph->getShortAuthorString() . ': ' . $publishedMonograph->getLocalizedTitle());

			// link in galley view
			if ($smarty->get_template_vars('isGalleyView') == "true") {
				$smarty->assign("currentTitleUrl", $smarty->smartyUrl(array("page" => "catalog", "op" => "book", "path" => $publishedMonograph->getId()),$smarty));
			}
		}
	}


	function getPubidPlugins($params, $smarty) {
		$pubIdPlugins = PluginRegistry::loadCategory('pubIds', true);
		$smarty->assign('pubIdPlugins', $pubIdPlugins);
	}

	/**
	 * show the pdf reader
	 *
	 * registered as smarty function idai_viewer
	 *
	 * @param array $params
	 * 	file - full url to pdf file
	 * @param red $smarty
	 * @return string
	 */
	function getViewer($params, $smarty) {
		$viewerSrc = Config::getVar('dainst', 'viewerUrl');
		if ($viewerSrc) {
			$url = "$viewerSrc?";
			$url .= Config::getVar('dainst', 'restrictAnnotationTypes') ? 'annotation_types=' . Config::getVar('dainst', 'restrictAnnotationTypes') . '&' : '';
			$url .= "file={$params['file']}";
			$url .= (Config::getVar('dainst', 'viewerAppendId') or isset($_GET['ann'])) ? "&pubid={$params['article']}" : '';
		} else {
			$url = $params['file'];
		}

		return "<iframe id='dainstPdfViewer' src='$url'></iframe>";

		//$viewerSrc = $this->theUrl . '/plugins/themes/dainst/inc/dbv/viewer.html';
	}



	function getFilePath() {
		return dirname(__FILE__);
	}


	function getUser($smarty) {
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


    function getSeriesInfo($params, $smarty) {
        $series = $params['series'];
        $info = array();
        $request = Application::getRequest();
        $press = $request->getPress();
        $publishedMonographDao = DAORegistry::getDAO('PublishedMonographDAO');
        $monographs = $publishedMonographDao->getBySeriesId($series->getId(), $press->getId());
        //echo "<pre>", print_r($monographs, 1), "</pre>";

        // count
        $info['count'] = $monographs->getCount();
        //echo "<pre>", $series->getLocalizedTitle(), ": ", count($mar) , "|vs|", $monographs->getCount(), "</pre>";

        // image
        $info['image'] = null;
        if ($series->getImage()) {
            $info['image'] = $smarty->smartyUrl(array(
                    "page" => "catalog",
                    "op" => "thumbnail",
                    "type" => "series",
                    "id" => $series->getId(),
                    "router" => ROUTE_PAGE
                ), $smarty);
        } else if ($monographs->getCount() > 0) {
            while ($monograph = $monographs->next()) {
                if ($monograph->getCoverImage()) {
                    $info['image'] = $smarty->smartyUrl(array(
                        "component" => "submission.CoverHandler",
                        "op" => "thumbnail",
                        "type" => "submission",
                        "submissionId" => $monograph->getId(),
                        router => ROUTE_COMPONENT
                    ), $smarty);
                    break;
                }
            }
        }

        // text (first paragraph only)
        $text = $series->getLocalizedData('description');
        preg_match_all("#<p>(.*)<\/p>#", $text, $matches);
        if (isset($matches[0]) and isset($matches[0][0])) {
            $text = $matches[0][0];
        }
        if (strlen($text) > 300) {
            $text = substr($text, 0, 299) . ' [...]';
        }
        //echo "<pre>", print_r($matches,1), "</pre>";
        $info['text'] = $text;



        $info['title'] = $series->getLocalizedTitle();

        $smarty->assign('seriesInfo', $info);
    }


}

?>
