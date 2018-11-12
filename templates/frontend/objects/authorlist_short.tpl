{foreach name="authors" from=$authors item=author}
  {* strip removes excess white-space which creates gaps between separators *}
  {strip}
    {if $author->getIsVolumeEditor()}
      <strong>{translate key="submission.editorName" editorName=$author->getFullName()|escape}</strong>
    {else}
      {$author->getFullName()|escape}
    {/if}

    {if !$smarty.foreach.authors.last and ($smarty.foreach.authors.index > 3)}
      {translate key="plugins.themes.dainst.etAl"}
      {break}
    {/if}

    {if !$smarty.foreach.authors.last}
      {translate key="submission.authorListSeparator"}
    {/if}
  {/strip}
{/foreach}
