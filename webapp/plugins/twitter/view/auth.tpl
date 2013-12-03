{include file="_header.tpl"}
{include file="_statusbar.tpl"}

<div id="main" class="container">

    <div class="row">
        <div class="col-md-3">
            
        </div>
        <div class="col-md-9">
            {include file="_usermessage.tpl" enable_bootstrap=1}
        </div>
    </div>

    <div class="row">
        <div class="col-md-3">
            
        </div>
        <div class="col-md-9">

            <div class="panel panel-warning">
                <div class="panel-heading">
                    <h3 class="panel-title">Twitter authentication</h3>
                </div>
                <div class="panel-body">

                  <div class="grid_9 prefix_7 alpha omega prepend_20 append_20 clearfix">
                    <a href="{$site_root_path}account/?p=twitter" class="linkbutton emphasized">Back to Twitter settings</a>
                  </div>

                </div>
            </div>

        </div>
    </div>

</div>




{include file="_footer.tpl"}
