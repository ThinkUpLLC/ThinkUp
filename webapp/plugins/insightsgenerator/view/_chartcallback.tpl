{*

Add a callback to google.visualization that tells image-generation services like Slimer/Phantom to wait for the chart to load.

*}
{literal}
if (typeof window.callPhantom !== "undefined" && window.callPhantom !== null) {
  google.visualization.events.addListener(view_duration_chart_{/literal}{$i->id}{literal}, 'ready', window.callPhantom)
}
{/literal}