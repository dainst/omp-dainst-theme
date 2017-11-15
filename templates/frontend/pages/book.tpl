{include file="frontend/components/header.tpl" pageTitleTranslated=$publishedMonograph->getLocalizedFullTitle()}

<div class="page page_book">
	{include file="frontend/components/breadcrumbs_catalog.tpl"}
	{* Display book details *}
	{include file="frontend/objects/monograph_full.tpl" monograph=$publishedMonograph}
</div><!-- .page -->

{include file="frontend/components/footer.tpl"}
