{include file="_header.tpl" enable_bootstrap="true"}
{include file="_statusbar.tpl" enable_bootstrap="true"}


<div class="container">

<div class="row">

    <div class="span3">
          <div class="sidebar-nav">
            <ul class="nav nav-list">
              <li class="">
                Update ThinkUp Data
              </li>
            </ul>
          </div><!--/.well -->
    </div><!--/span3-->
    <div class="span9">
        <iframe width="100%" height="500px" src="run.php{if $log == 'full'}?log=full{/if}" style="border:solid black 1px"></iframe>
    </div>
</div>


<div class="row">
    <div class="span3">&nbsp;</div>
    <div class="span9">
        {include file="_usermessage.tpl" enable_bootstrap="true"}
</div>


</div>

{include file="_footer.tpl" enable_bootstrap="true"}