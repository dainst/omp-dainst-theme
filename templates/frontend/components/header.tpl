{* DAINST THEME HEADER *}
{**
 * lib/pkp/templates/frontend/components/header.tpl
 *
 * Copyright (c) 2014-2017 Simon Fraser University
 * Copyright (c) 2003-2017 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @brief Common frontend site header.
 *
 * @uses $isFullWidth bool Should this page be displayed without sidebars? This
 *       represents a page-level override, and doesn't indicate whether or not
 *       sidebars have been configured for thesite.
 *}
{strip}
	{* Determine whether a logo or title string is being displayed *}
	{assign var="showingLogo" value=true}
	{if $displayPageHeaderTitle && !$displayPageHeaderLogo && is_string($displayPageHeaderTitle)}
		{assign var="showingLogo" value=false}
	{/if}
{/strip}
<!DOCTYPE html>
<html lang="{$currentLocale|replace:"_":"-"}" xml:lang="{$currentLocale|replace:"_":"-"}">
{if !$pageTitleTranslated}{capture assign="pageTitleTranslated"}{translate key=$pageTitle}{/capture}{/if}
{include file="frontend/components/headerHead.tpl"}
<body class="pkp_page_{$requestedPage|escape|default:"index"} pkp_op_{$requestedOp|escape|default:"index"}{if $showingLogo} has_site_logo{/if}" dir="{$currentLocaleLangDir|escape|default:"ltr"}">

	{idai_modal}


	<div class="pkp_structure_page">

		{* Header *}
		{idai_navbar}{/idai_navbar}


		<div class="pkp_site_name_wrapper">
			{* Logo or site title. Only use <h1> heading on the homepage.
               Otherwise that should go to the page title. *}
			{if $requestedOp == 'index'}<h1 class="pkp_site_name">{else}<div class="pkp_site_name">{/if}

				{capture assign="homeUrl"}
					{url "homeUrl" press="index" router=$smarty.const.ROUTE_PAGE}
				{/capture}
				{if $displayPageHeaderLogo && is_array($displayPageHeaderLogo)}
					<a href="{$homeUrl}" class="is_img">
						<span class="overlay_text">{$displayPageHeaderTitle}</span>
						<img src="{$publicFilesDir}/{$displayPageHeaderLogo.uploadName|escape:"url"}" width="{$displayPageHeaderLogo.width|escape}" height="{$displayPageHeaderLogo.height|escape}" {if $displayPageHeaderLogo.altText != ''}alt="{$displayPageHeaderLogo.altText|escape}"{else}alt="{translate key="common.pageHeaderLogo.altText"}"{/if} />
					</a>
				{elseif $displayPageHeaderTitle && !$displayPageHeaderLogo && is_string($displayPageHeaderTitle)}
					<a href="{$homeUrl}" class="is_text"><span>{$displayPageHeaderTitle}</span></a>
				{elseif $displayPageHeaderTitle && !$displayPageHeaderLogo && is_array($displayPageHeaderTitle)}
					<a href="{$homeUrl}" class="is_img">
						<span class="overlay_text">{$displayPageHeaderTitle}</span>
						<img src="{$publicFilesDir}/{$displayPageHeaderTitle.uploadName|escape:"url"}" alt="{$displayPageHeaderTitle.altText|escape}" width="{$displayPageHeaderTitle.width|escape}" height="{$displayPageHeaderTitle.height|escape}" />

					</a>
				{else}
					<a href="{$homeUrl}" class="is_img">
						<span class="overlay_text">{$displayPageHeaderTitle}</span>
						<img src="{$baseUrl}/templates/images/structure/logo.png" alt="{$applicationName|escape}" title="{$applicationName|escape}" width="180" height="90" />
					</a>
				{/if}

				<div class="dai-logo">
					<img src="{$baseUrl}/plugins/themes/omp-dainst-theme/img/dailogo232.png">
				</div>

			{if $requestedOp == 'index'}</h1>{else}</div>{/if}

		</div>

		{* Wrapper for page content and sidebars *}
		{if $isFullWidth}
			{assign var=hasSidebar value=0}
		{/if}
		<div class="pkp_structure_content{if $hasSidebar} has_sidebar{/if}">
<div id="pkp_content_main" class="pkp_structure_main" role="main">
