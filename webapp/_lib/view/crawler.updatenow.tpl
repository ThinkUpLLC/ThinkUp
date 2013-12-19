{include file="_header.tpl"}
{include file="_statusbar.tpl"}


<div class="container">

<div class="row">

    <div class="col-md-3">
    <div class="embossed-block">
        <ul>
          <li>
             Updating...
          </li>
        </ul>
    </div>
    </div><!--/col-md-3-->
    <div class="col-md-9">
        <iframe width="100%" height="500px" src="run.php{if $log == 'full'}?log=full{/if}" style="border:solid black 1px"></iframe>
    </div>
</div>


<div class="row">
    <div class="col-md-3">&nbsp;</div>
    <div class="col-md-9">
        {include file="_usermessage.tpl"}
</div>


</div>

{include file="_footer.tpl"}