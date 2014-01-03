{include file="_header.tpl"}
{include file="_navigation.tpl"}


<div class="container">

<div class="row">

    <div class="col-md-1">
    </div><!--/col-md-1-->
    <div class="col-md-9">
        <iframe width="100%" height="500px" src="run.php{if $log == 'full'}?log=full{/if}" style="border:solid black 1px"></iframe>
    </div>
</div>


<div class="row">
    <div class="col-md-1">&nbsp;</div>
    <div class="col-md-9">
        {include file="_usermessage.tpl"}
</div>


</div>

{include file="_footer.tpl"}