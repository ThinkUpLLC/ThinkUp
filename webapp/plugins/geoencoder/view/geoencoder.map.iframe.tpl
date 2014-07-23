<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>{if $controller_title}{$controller_title} | {/if}{$app_title}</title>

  <script type="text/javascript">var site_root_path = '{$site_root_path}';</script>
  {foreach from=$header_scripts item=script}
    <script type="text/javascript" src="{$site_root_path}{$script}"></script>
  {/foreach}

<script type="text/javascript">{$posts_data}</script>
<script type="text/javascript">
    var geo = "{$post->geo}";
    var latlng = geo.split(',');
</script>
{if $gmaps_api}
<script src="http://maps.google.com/maps?file=api&amp;v=2&amp;sensor=false&amp;key={$gmaps_api}" type="text/javascript">
</script>
<script type="text/javascript" src="{$site_root_path}plugins/geoencoder/assets/js/markerclusterer_packed.js"></script>
{/if}
<link rel="stylesheet" type="text/css" href="{$site_root_path}plugins/geoencoder/assets/css/maps.css" />
</head>

<body {if $error_msg}>
        {include file="_usermessage.tpl"}
      {else}
       onload="initializeMap()" onunload="GUnload()">
    <div id="wrap">
      <div id="mappanel">
        <div id="map"></div>
      </div>
      <div id="userpanel">
        <table class="table table-striped table-bordered table-condensed">
            <tbody>
                <tr>
                    <td id="markerlist0"></td>
                    <td id="markerlist1"></td>
                    <td id="markerlist2"></td>
                </tr>
            </tbody>
        </table>

      </div>
      {/if}
     </div>
</body>
</html>