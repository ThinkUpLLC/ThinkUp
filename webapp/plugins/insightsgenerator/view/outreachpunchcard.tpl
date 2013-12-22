{include file=$tpl_path|cat:'_header.tpl'}

{if  !$expand}
<div class="pull-right detail-btn"><button class="btn btn-info btn-mini" data-toggle="collapse" data-target="#chart-{$i->id}"><i class="icon-signal icon-white"></i></button></div>
{/if}

<span class="label label-{if $i->emphasis eq '1'}info{elseif $i->emphasis eq '2'}success{elseif $i->emphasis eq '3'}error{else}info{/if}"><i class="icon-white icon-time"></i> <a href="?u={$i->instance->network_username}&n={$i->instance->network}&d={$i->date|date_format:'%Y-%m-%d'}&s={$i->slug}">{$i->headline}</a></span>

<i class="icon-{$i->instance->network}{if $i->instance->network eq 'google+'} icon-google-plus{/if} icon-muted"></i>
{$i->text|link_usernames_to_twitter}

{if !$expand}
<div class="collapse in" id="chart-{$i->id}">
{/if}

    <div id="outreach_punchcard_{$i->id}">&nbsp;</div>
    <div id="outreach_punchcard_legend_{$i->id}" style="padding: 5px; display: block; float: right; font-family: sans-serif; font-size: 12px;">
      <div style="margin-right: 4px; padding: 2px; color: #0072bc; float: left;">posts</div>
      <div style="margin-right: 4px; padding: 2px; color: #7dd3f0; float: left;">responses</div>
    </div>
    <script type="text/javascript">
        {literal}
        (function(d3) {
          var OutreachPunchcard = function(placeholder_id, graph_size, outreach_data) {
            var vis = d3.select('#'+placeholder_id)
            .append("svg")
            .attr("width", graph_size)
            .attr("height", (graph_size / 3))
            .style("display", "block")
            .style("margin", "0 auto");

            var x = d3.scale.linear().domain([0, 23]).range([(graph_size / 9), (graph_size - 20)]);
            var y = d3.scale.linear().domain([1, 7]).range([20, ((graph_size / 3) - 40)]);

            var xAxis = d3.svg.axis().scale(x).orient("bottom")
            .ticks(24)
            .tickFormat(function (d, i) {
              var m = (d < 12) ? 'a' : 'p';
              return (d % 12 == 0) ? 12+m :  (d % 12)+m;
            });
            var yAxis = d3.svg.axis().scale(y).orient("left")
            .ticks(7)
            .tickFormat(function (d, i) {
              return ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'][d - 1];
            });

            vis.append("g")
            .attr("class", "axis")
            .attr("transform", "translate(0, "+((graph_size / 3) - 20)+")")
            .call(xAxis);

            vis.append("g")
            .attr("class", "axis")
            .attr("transform", "translate(70, 0)")
            .call(yAxis);

            d3.selectAll('.axis path')
            .style("fill", "none")
            .style("stroke", "#eee")
            .style("shape-rendering", "crispEdges");

            d3.selectAll('.axis line')
            .style("fill", "none")
            .style("stroke", "#eee")
            .style("shape-rendering", "crispEdges");

            d3.selectAll('.axis text')
            .style("font-family", "sans-serif")
            .style("font-size", "11px");

            var punches = {posts: [], responses: []};
            var max_val = 0;
            for (var i = 1; i <= 7; i++) {
              for (var j = 0; j < 24; j++) {
                max_val = Math.max(outreach_data.posts[i][j],max_val);
                max_val = Math.max(outreach_data.responses[i][j],max_val);

                punches.posts.push([i, j, outreach_data.posts[i][j]]);
                punches.responses.push([i, j, outreach_data.responses[i][j]]);
              }
            }
            var rad = d3.scale.linear()
            .domain([0, 1, max_val])
            .range([0, (max_val < 6 ? Math.round(12 / max_val) : 2), 12]);

            var post_punch = vis.selectAll('g.post-punch')
            .data(punches.posts)
            .enter()
            .append("g")
            .attr("class", "post-punch");

            post_punch.append("title")
            .text(function(d) { return d[2]+" post"+(d[2] > 1 ? 's' : ''); });

            post_punch.append("circle")
            .attr("cx", function(d) { return x(d[1]); })
            .attr("cy", function(d) { return y(d[0]); })
            .attr("r", function(d) { return Math.round(rad(d[2])); })
            .style("fill", "#0072bc")
            .style("opacity", "1.0");

            var response_punch = vis.selectAll('g.response-punch')
            .data(punches.responses)
            .enter()
            .append("g")
            .attr("class", "response-punch");

            response_punch.append("title")
            .text(function(d) { return d[2]+" response"+(d[2] > 1 ? 's' : ''); });

            response_punch.append("circle")
            .attr("cx", function(d) { return x(d[1]); })
            .attr("cy", function(d) { return y(d[0]); })
            .attr("r", function(d) { return Math.round(rad(d[2])); })
            .style("fill", "#7dd3f0")
            .style("opacity", "0.5");
          };
          {/literal}
          var dataset = {$i->related_data|@json_encode};
          new OutreachPunchcard("outreach_punchcard_{$i->id}", $("#outreach_punchcard_{$i->id}").width(), dataset);
          {literal}
        })(d3);
        {/literal}
    </script>

{if  !$expand}
</div>
{/if}

{include file=$tpl_path|cat:'_footer.tpl'}
