<div id="word-frequency-div" style="display: none; margin-right: 100px; border: solid gray 1px; padding: 10px;">
    <div style="float: left; border: solid black 0px; width: 630px;">
        <div id="word-frequency-spinner" style="text-align: center;">
            <img src="{$site_root_path}assets/img/loading.gif" width="31" height="31" />
            <br />Processing word frequency... 
        </div>
        <div id="word-frequency-list" style="display: none;">
            Top 20 Word list:
            <div class="word-frequency-div" id="word-frequency-words">
            </div>
        </div>
    </div>
    
    <div style="float: right; width: 27px;">
        <a href="#" onclick="return false;" id="word-frequency-close">
        <img src="{$site_root_path}assets/img/close-icon.gif" width="27" height="26" /></a>
    </div>
    
    <div id="word-frequency-posts-div" style="display: none; position: absolute; background-color: #fff; z-index: 1000; 
    border: solid black 1px; padding: 10px;">
    <div style="float: left; margin: 0px 10px 0px 0px; width: 500px;" id="word-frequency-posts">

    </div>
      <div style="float: right; width: 27px;">
        <img id="word-frequency-posts-close" src="{$site_root_path}assets/img/close-icon.gif" width="27" height="26" 
        style="cursor: pointer;"/></a>
      </div>
    <div style="clear: both;"></div>
    </div>
    
    <div style="clear: both;"></div>
    
</div>