 {literal}

    <script type="text/javascript" src="https://www.google.com/jsapi"></script>
    <script type="text/javascript">
     google.load("visualization", "1", {packages:["corechart"]});
      google.setOnLoadCallback(drawChart);
      function drawChart() {
        var data = google.visualization.arrayToDataTable([
          ['Time', 'Hours'],
          ['1', 1],
          ['2', 1],
          ['3', 1],
          ['4', 1],
          ['5', 1],
          ['6', 1],
          ['7', 1],
          ['8', 1],
          ['9', 1],
          ['10', 1],
          ['11', 1],
          ['12', 1],
        ]);

        var options = {
          legend: 'none',
          tooltip: {
            text: 'value',
          },
          chartArea: {
            left: '0',
            right: '0',
            width: 150,
          },
          backgroundColor: {
            fill: 'none',
          },
          width: 125,
          pieSliceBorderColor: '#9dd767',
          pieSliceText: 'label',
          pieStartAngle: 15,

          colors: [
{/literal}
{section name=piecolors loop=13 start=1 }
{if $smarty.section.piecolors.index eq $i->related_data}
            '#5fac1c',
{else}
            '#9dd767',
{/if}
{/section}
{literal}
          ],

        };

        var chartdiv = document.getElementById('insight-text-{/literal}{$i->id}{literal}');
        chartdiv.insertAdjacentHTML('afterbegin', '<span id="piechart-{/literal}{$i->id}{literal}" style="width: 100; height: 80; float: right; position: relative; top: -40px; background: transparent;"></span>')

        var chart = new google.visualization.PieChart(document.getElementById('piechart-{/literal}{$i->id}{literal}'));
        chart.draw(data, options);
      }
{/literal}
    </script>
