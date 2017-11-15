<!DOCTYPE html>
<html lang="{$currentLocale|replace:"_":"-"}" xml:lang="{$currentLocale|replace:"_":"-"}">
	{if !$pageTitleTranslated}{translate|assign:"pageTitleTranslated" key=$pageTitle}{/if}
	{include file="frontend/components/headerHead.tpl"}
	<body class="pkp_page_{$requestedPage|escape|default:"index"} pkp_op_{$requestedOp|escape|default:"index"}" dir="{$currentLocaleLangDir|escape|default:"ltr"}">
		{idai_navbar}{/idai_navbar}
		<div class="row toprow">
			{assign var="isGalleyView" value="true"}
			<div class="col-md-6 toprow-left">{include file="frontend/components/breadcrumbs_catalog.tpl"}</div>
			<div class="col-md-6 toprow-right"></div>

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
