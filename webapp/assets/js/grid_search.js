/**
 * Grid Search Object for searching json posts data
 */
var TUGridSearch = function() {

    /**
	 * @var boolean Enable for console logging
	 */
    this.DEBUG = false;

    this.searchString = "";
    
    this.loading = false;

    this.keyup = function(e) {
        if (e.keyCode == 27) { 
            tu_grid_search.close_iframe();
        }
    };

    /**
	 * Init grid search
	 */
    this.init = function() {
        // register on submit event on our form
        $(document).ready(function() {
            if (tu_grid_search.DEBUG) {
                console.log("Grid Search initialized...");
            }
            $('#grid_search_icon').click(function() {
                tu_grid_search.load_iframe();
            });
            $('#close_grid_search').click(function() {
                tu_grid_search.close_iframe();
            });
        });
        // tu_grid_search.load_iframe();
    }

    /**
	 * @param Object
	 *            {success: true|false, posts: [a posts array]};
	 */
    this.populate_grid = function(obj) {
        if (tu_grid_search.DEBUG) { console.debug(obj.posts.length + ' posts'); }
        $('#grid_search_icon').show();
        $('#grid_search_spinner').hide();

        var grid;
        this.dataView = new Slick.Data.DataView();
        var columns = [ {
            id : "cnt",
            name : "#",
            field : "id",
            width: 30
        }, {
            id : "author",
            name : "Author",
            field : "author",
            formatter: function(row, cell, value, columnDef, dataContext) {
        if (dataContext['network'] == 'twitter') {
        return '<a href="http://twitter.com/' + value + '" target="_blank">' + value  + '</a>';
        } else {
        return value;
        }
                }
        }, {
            id : "date",
            name : "Date",
            field : "date",
            width: 125,
            formatter: function(row, cell, value, columnDef, dataContext) { 
                var path = typeof (site_root_path) != 'undefined' ? site_root_path : '';
                output = '<a href="' + path + '../../post/?t=' + 
                dataContext['post_id_str'].substr(0, (dataContext['post_id_str'].length - 4) ) +
                '&n='+ dataContext['network'] +'" target="_blank">#</a>&nbsp; ';
                if (dataContext['network'] == 'twitter') {
                 output = output + '<a href="http://twitter.com/' + dataContext['author'] + '/status/' + 
                 dataContext['post_id_str'].substr(0, (dataContext['post_id_str'].length - 4) ) + 
                 '" target="_blank">' + value + '</a>';
                } else {
                output = output + value;
                }
                return output;
            }
        }, {
            id : "text",
            name : "Text",
            field : "text",
            width : 615,
            formatter: function(row, cell, value, columnDef, dataContext) {
                var url_match = /(https?:\/\/([-\w\.]+)+(:\d+)?(\/([\w/_\.]*(\?\S+)?)?)?)/g;
                value = value.replace(url_match, '<a href="$1" target="_blank">$1</a> ');
                value = value.replace(/@(\w+)/g, '');
                return value;
            }
        } ];
        if( parent.GRID_TYPE == 2) {
            columns[3].width = 455;
        }

        var options = {
            enableCellNavigation : false,
            enableColumnReorder : true
        };

        this.dataView.beginUpdate();
        this.dataView.setItems(obj.posts);
        this.dataView.setFilter(tu_grid_search.myFilter);
        this.dataView.endUpdate();
        $("#grid_search_count").html(tu_grid_search.dataView.rows.length);
        $('#myGrid').show();
        var grid = new Slick.Grid($("#myGrid"), this.dataView.rows, columns, options);
        
        this.dataView.onRowCountChanged.subscribe(function(args) {
            grid.updateRowCount();
            grid.render();
            $("#grid_search_count").html(tu_grid_search.dataView.rows.length);
        });
        
        this.dataView.onRowsChanged.subscribe(function(rows) {
            grid.removeRows(rows);
            grid.render();
        });
        
        $("#grid_search_form").submit(function(e) {
            Slick.GlobalEditorLock.cancelCurrentEdit();
            var select_element = $('#grid_search_input');
            this.value = select_element.val();
            tu_grid_search.searchString = this.value;
            tu_grid_search.dataView.refresh();
        });

        $("#grid_export").click(function(e) {
            $('#grid_search_form').hide();
            $('#grid_search_spinner').show();
            $('#grid_export_data').val( $.toJSON(tu_grid_search.dataView.rows) );
            $('#grid_export_form').submit();
            $('#grid_search_spinner').hide();
            $('#grid_search_form').show();
        });

    }

    /**
	 * search filter
	 */
    this.myFilter = function (item) {
        if(item['id'] == -1 || item['text'] == null) { return false; }
        if (tu_grid_search.searchString != "" && 
        item["text"].toLowerCase().indexOf(tu_grid_search.searchString.toLowerCase()) == -1) {
            return false;
        } else {
            return true;
        }
    }

    /**
	 * 
	 */
    this.load_iframe = function() {

        // close grid search with escape key
        $(document).keyup( this.keyup );

        if(tu_grid_search.loading) { return; };
        // close top 20 words if needed
        if(typeof(tu_word_freq) != 'undefined') { tu_word_freq.close(); };
        tu_grid_search.loading = true;
        if(GRID_TYPE==1) {
            window.scroll(0,0);
            $('#screen').css({ opacity: 0.7, "width":$(document).width(),"height":$(document).height()});
        } else {
            $('#post-replies-div').hide();
            $('#word-frequency-div').hide();
        }
        var fade = (GRID_TYPE==1) ? 500 : 1;
        $('#screen').fadeIn(fade, function() {
            $('#grid_overlay_div').show();
            $('#grid_iframe').show();
            var path = typeof (site_root_path) != 'undefined' ? site_root_path : '';
            query_string = 'not=true';
            if(document.location.search) {
                query_string = document.location.search;
                query_string = query_string.replace(/^\?/, '').replace(/^v/, 'd');
            }
            if(typeof(post_username) != 'undefined') { query_string+= '&u=' + escape(post_username); } 
            $('#grid_iframe').attr('src',
                    path + 'assets/html/grid.html?' + query_string + '&cb=' + (new Date()).getTime());
            if (tu_grid_search.DEBUG) {
                console.debug("loading grid search iframe %s",  $('#grid_iframe').attr('src') );
            }
            tu_grid_search.loading = false;
        });
    }
    /**
	 * 
	 */
    this.close_iframe = function() {
        var path = typeof (site_root_path) != 'undefined' ? site_root_path : '';
        $('#grid_iframe').attr('src', path + '/assets/img/ui-bg_glass_65_ffffff_1x400.png');
        $('#grid_overlay_div').hide();
        $('#grid_iframe').hide();
        if(GRID_TYPE==1) {
            $('#screen').fadeOut(500);
        } else {
            $('#post-replies-div').show();
            $('#word-frequency-div').show();
        }
        $(document).unbind('keyup', this.keyup);
    }
    
    /**
	 * load xss script ewith post data callback
	 */
    this.get_data = function() {
        $('#myGrid').hide();
        var url = '../../post/grid.php' + document.location.search;
        if (tu_grid_search.DEBUG) { console.debug('Getting data with url: ' + url); }
        script = document.createElement('script');
        script.setAttribute("src", url);
        script.setAttribute("type", "text/javascript");
        document.getElementsByTagName('head')[0].appendChild(script);
    }
}

var tu_grid_search = new TUGridSearch();
tu_grid_search.init();
