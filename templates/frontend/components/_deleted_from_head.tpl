<div class="pkp_site_name_wrapper">
	{* Logo or site title. Only use <h1> heading on the homepage.
       Otherwise that should go to the page title. *}
	{if $requestedOp == 'index'}
	<h1 class="pkp_site_name">
		{else}
		<div class="pkp_site_name">
			{/if}
			{if $currentContext && $multipleContexts}
				{url|assign:"homeUrl" journal="index" router=$smarty.const.ROUTE_PAGE}
				{url|assign:"homeUrl" journal="index" router=$smarty.const.ROUTE_PAGE}
				{url|assign:"homeUrl" journal="index" router=$smarty.const.ROUTE_PAGE}
			{else}
				{url|assign:"homeUrl" page="index" router=$smarty.const.ROUTE_PAGE}
			{/if}
			{if $displayPageHeaderLogo && is_array($displayPageHeaderLogo)}
				<a href="{$homeUrl}" class="is_img">
					<img src="{$publicFilesDir}/{$displayPageHeaderLogo.uploadName|escape:"url"}" width="{$displayPageHeaderLogo.width|escape}" height="{$displayPageHeaderLogo.height|escape}" {if $displayPageHeaderLogo.altText != ''}alt="{$displayPageHeaderLogo.altText|escape}"{else}alt="{translate key="common.pageHeaderLogo.altText"}"{/if} />
				</a>
			{elseif $displayPageHeaderTitle && !$displayPageHeaderLogo && is_string($displayPageHeaderTitle)}
				<a href="{$homeUrl}" class="is_text">{$displayPageHeaderTitle}</a>
			{elseif $displayPageHeaderTitle && !$displayPageHeaderLogo && is_array($displayPageHeaderTitle)}
				<a href="{$homeUrl}" class="is_img">
					<img src="{$publicFilesDir}/{$displayPageHeaderTitle.uploadName|escape:"url"}" alt="{$displayPageHeaderTitle.altText|escape}" width="{$displayPageHeaderTitle.width|escape}" height="{$displayPageHeaderTitle.height|escape}" />
				</a>
			{else}
				<a href="{$homeUrl}" class="is_img">
					<img src="{$baseUrl}/templates/images/structure/logo.png" alt="{$applicationName|escape}" title="{$applicationName|escape}" width="180" height="90" />
				</a>
			{/if}
			{if $requestedOp == 'index'}
	</h1>
	{else}
</div>
{/if}
</div>
