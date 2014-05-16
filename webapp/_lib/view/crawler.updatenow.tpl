{include file="_header.tpl"}
{include file="_navigation.tpl"}


<div class="container">
  <div class="stream{if count($insights) eq 1} stream-permalink{/if}">
    <div class="date-group{if $i->date|relative_day eq "today"} today{/if}">

        <iframe width="100%" height="600" src="run.php{if $log == 'full'}?log=full{/if}" style="border:none;"></iframe>

    </div><!-- end date-group -->
  </div><!-- end stream -->
</div><!-- end container -->

{include file="_footer.tpl"}