{**
 * templates/frontend/pages/index.tpl
 *
 * Copyright (c) 2014-2017 Simon Fraser University
 * Copyright (c) 2003-2017 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @brief Display the front page of the site
 *
 * @uses $homepageImage array Details about the uploaded homepage image
 * @uses $spotlights array Selected spotlights to promote on the homepage
 * @uses $featuredMonographs array List of featured releases in this press
 * @uses $newReleases array List of new releases in this press
 * @uses $announcements array List of announcements
 * @uses $numAnnouncementsHomepage int Number of announcements to display on the
 *       homepage
 * @uses $additionalHomeContent string HTML blob of arbitrary content added by
 *  an editor/admin.
 *}
{include file="frontend/components/header.tpl"}

<div class="page page_homepage">

    {* Homepage Image *}
    {if $homepageImage}
        <div class="homepage_image">
            <img src="{$publicFilesDir}/{$homepageImage.uploadName|escape:"url"}" alt="{$homepageImage.altText|escape}">
        </div>
    {/if}

    {* Spotlights *}
    {if !empty($spotlights)}
        <h2 class="pkp_screen_reader">
            {translate key="spotlight.spotlights"}
        </h2>
        {include file="frontend/components/spotlights.tpl"}
    {/if}


    {* Featured *}
    {if !empty($featuredMonographs)}
        {include file="frontend/components/monographList.tpl" monographs=$featuredMonographs titleKey="catalog.featured"}
    {/if}

    {* New releases *}
    {if !empty($newReleases)}
        {include file="frontend/components/monographList.tpl" monographs=$newReleases titleKey="catalog.newReleases"}
    {/if}

    {* Announcements *}
    {if $numAnnouncementsHomepage && $announcements|@count}
        <div class="cmp_announcements highlight_first">
            <h2>
                {translate key="announcement.announcements"}
            </h2>
            {foreach name=announcements from=$announcements item=announcement}
            {if $smarty.foreach.announcements.iteration > $numAnnouncementsHomepage}
                {php}break;{/php}
            {/if}
            {if $smarty.foreach.announcements.iteration == 1}
            {include file="frontend/objects/announcement_summary.tpl" heading="h3"}
            <div class="more">
                {else}
                <article class="obj_announcement_summary">
                    <h4>
                        <a href="{url router=$smarty.const.ROUTE_PAGE page="announcement" op="view" path=$announcement->getId()}">
                            {$announcement->getLocalizedTitle()|escape}
                        </a>
                    </h4>
                    <div class="date">
                        {$announcement->getDatePosted()}
                    </div>
                </article>
                {/if}
                {/foreach}
            </div><!-- .more -->
        </div>
    {/if}

    {* display series on index page an additional time because in the sidebar is appareantly not enaough *}
    <div class="cmp_series_list">
        <h2 class="title">{translate key="plugins.themes.dainst.series"}</h2>
        {iterate from=browseSeriesFactory item=browseSeriesItem}
            {assign var="seriesId" value=$browseSeriesItem->getData('id')}
            {idai_series_info series=$browseSeriesItem}
            {if $seriesInfo.count > 0}
                <div class="row">
                    <div class="obj_monograph_summary">
                        <a class="cover" href="{url router=$smarty.const.ROUTE_PAGE page="catalog" op="series" path=$browseSeriesItem->getPath()|escape}">
                            {if $seriesInfo.image !== null}
                                <img src="{$seriesInfo.image}" alt="Image {$browseSeriesItem->getLocalizedTitle()|escape}" />
                            {/if}
                        </a>
                        <div class="title">
                            <a href="{url router=$smarty.const.ROUTE_PAGE page="catalog" op="series" path=$browseSeriesItem->getPath()|escape}">
                                {$browseSeriesItem->getLocalizedTitle()|escape} ({$seriesInfo.count})
                            </a>
                        </div>
                        <div class="author">
                            <b>{$browseSeriesItem->getLocalizedSubtitle()}</b>
                            <p>{$seriesInfo.text|strip_unsafe_html}</p>
                        </div>
                    </div>
                </div>
            {/if}

        {/iterate}
    </div>

    {* Additional Homepage Content *}
    {if $additionalHomeContent}
        <div class="additional_content">
            {$additionalHomeContent}
        </div>
    {/if}

</div>
{include file="frontend/components/footer.tpl"}
