<div id="follower_map_{$i->id}"></div>

    <script type="text/javascript">
        // Load the Visualization API and the standard charts
        {literal}
        var callback = {packages: ['map'], "callback" : drawFollowerMapChart{/literal}{$i->id}{literal}};
        {/literal}
        google.load('visualization', '1', callback);
        // Set a callback to run when the Google Visualization API is loaded.
        {literal}
        function drawFollowerMapChart{/literal}{$i->id}{literal}() {
            {/literal}
            var data = new google.visualization.DataTable({$i->related_data.bar_chart});
            {literal}
            var c = window.tu.constants.colors;
            {/literal}
            var view_duration_chart = new google.visualization.Map(document.getElementById('follower_map_{$i->id}'));
            {literal}
            view_duration_chart.draw(data, {showTip: true, mapType:'normal'});
        }
        {/literal}
    </script>
