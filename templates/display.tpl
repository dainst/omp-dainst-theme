<!DOCTYPE html>
<html lang="{$currentLocale|replace:"_":"-"}" xml:lang="{$currentLocale|replace:"_":"-"}">
	{if !$pageTitleTranslated}{translate|assign:"pageTitleTranslated" key=$pageTitle}{/if}
	{include file="frontend/components/headerHead.tpl"}
	<body class="pkp_page_{$requestedPage|escape|default:"index"} pkp_op_{$requestedOp|escape|default:"index"}" dir="{$currentLocaleLangDir|escape|default:"ltr"}">
		{idai_navbar}{/idai_navbar}
		<div class="row toprow">
			{assign var="isGalleyView" value="true"}
			<div class="col-md-6 toprow-left">{include file="frontend/components/breadcrumbs_catalog.tpl"}</div>
			<div class="col-md-6 toprow-right">
				<a href="#" id="article-meta-toggler">{translate key="plugins.themes.dainst.additionalInfo"}<b class="caret"></b></a>
				<div class="panel panel-default" id="article-meta">
					<div class="panel-heading">{translate key="plugins.themes.dainst.additionalInfo"}</div>
					<div class="panel-body">
						{idai_pubid_plugins}
						{foreach from=$pubIdPlugins item=pubIdPlugin}
							{assign var=pubId value=$publishedMonograph->getStoredPubId($pubIdPlugin->getPubIdType())}
							{if $pubId}
								{assign var="doiUrl" value=$pubIdPlugin->getResolvingURL($currentPress->getId(), $pubId)|escape}
								<div class="item doi">
									{$pubIdPlugin->getDisplayName()}<a href="{$doiUrl}">{$doiUrl}</a>
								</div>
							{/if}
							{assign var=pubId value=$publicationFormat->getStoredPubId($pubIdPlugin->getPubIdType())}
							{if $pubId}
								{assign var="doiUrl" value=$pubIdPlugin->getResolvingURL($currentPress->getId(), $pubId)|escape}
								<div class="item doi">
									{$pubIdPlugin->getDisplayName()}<a href="{$doiUrl}">{$doiUrl}</a>
								</div>
							{/if}
							{assign var=pubId value=$submissionFile->getStoredPubId($pubIdPlugin->getPubIdType())}
							{if $pubId}
								{assign var="doiUrl" value=$pubIdPlugin->getResolvingURL($currentPress->getId(), $pubId)|escape}
								<div class="item doi">
									{$pubIdPlugin->getDisplayName()}<a href="{$doiUrl}">{$doiUrl}</a>
								</div>
							{/if}
						{/foreach}
					</div>
				</div>
			</div>

		</div>
		<div id="main">
			{idai_viewer file=$downloadUrl}
		</div>
		{idai_footer}
		{load_script context="frontend"}
		{call_hook name="Templates::Common::Footer::PageFooter"}
		{idai_footer_scripts}
	</body>
</html>
