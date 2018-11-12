{if $editors}
  {assign var="class" value="editors"}
{else}
  {assign var="class" value="authors"}
{/if}

<div class="author-list author-list-{$class}">
{* Show short author lists on multiple lines *}
{if $authors|@count < 5}
  {foreach from=$authors item=author}
    <div class="sub_item">
      <div class="label">
        {if $editors}
          {translate key="submission.editorName" editorName=$author->getFullName()|escape}
        {else}
          {$author->getFullName()|escape}
        {/if}
      </div>
      {if $author->getLocalizedAffiliation()}
        <div class="value">
          {$author->getLocalizedAffiliation()|escape}
        </div>
      {/if}
      {if $author->getOrcid()}
        <span class="orcid">
          <a href="{$author->getOrcid()|escape}" target="_blank">
            {$author->getOrcid()|escape}
          </a>
        </span>
      {/if}
    </div>
  {/foreach}

{* Show long author lists on one line *}
{else}
  {foreach name="authors" from=$authors item=author}
    {* strip removes excess white-space which creates gaps between separators *}
    {strip}
      {if $author->getLocalizedAffiliation()}
        {if $editors}
          {capture assign="authorName"}<span class="label">{translate key="submission.editorName" editorName=$author->getFullName()|escape}</span>{/capture}
        {else}
          {capture assign="authorName"}<span class="label">{$author->getFullName()|escape}</span>{/capture}
        {/if}
        {capture assign="authorAffiliation"}<span class="value">{$author->getLocalizedAffiliation()|escape}</span>{/capture}
        {translate key="submission.authorWithAffiliation" name=$authorName affiliation=$authorAffiliation}
      {else}
        <span class="label">{$author->getFullName()|escape}</span>
      {/if}
      {if !$smarty.foreach.authors.last}
        {translate key="submission.authorListSeparator"}
      {/if}
    {/strip}
  {/foreach}
{/if}
</div>
