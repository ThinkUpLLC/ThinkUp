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

    var self = this;

    this.keyup = function(e) {
        if (e.keyCode == 27) { 
            self.close_iframe();
        }
    };

    /**
	 * Init grid search
	 */
    this.init = function() {
        // register on submit event on our form
        $(document).ready(function() {
            if (self.DEBUG) {
                console.log("Grid Search initialized...");
            }
            $('#grid_search_icon').click(function() {
                self.load_iframe();
            });
            $('#close_grid_search').click(function() {
                self.close_iframe();
            });
            if(document.location.search.match(/search=/)) {
                self.load_iframe();
            }
        });
        // self.load_iframe();
    }

    /**
	 * @param Object
	 *            {success: true|false, posts: [a posts array]};
	 */
    this.populate_grid = function(obj) {
        if (self.DEBUG) { console.debug(obj.posts.length + ' posts'); }
        if (self.DEBUG) { console.debug(obj.limit + ' limit'); }
        if((obj.posts.length - obj.limit) == 1) {
            $('#max_rows').html(obj.limit);
            $('#overlimit').show();
        } else {
            $('#overlimit').hide();
        }
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
            width : 550,
            formatter: function(row, cell, value, columnDef, dataContext) {
                var url_match = /(https?:\/\/([-\w\.]+)+(:\d+)?(\/([\w/_\.]*(\?\S+)?)?)?)/g;
                value = value.replace(url_match, '<a href="$1" target="_blank">$1</a> ');
                value = value.replace(/@(\w+)/g, '<a href="http://twitter.com/$1" target="_blank">@$1</a>');
                return value;
            }
        } ];
        // if( parent.GRID_TYPE == 2) {
        columns[3].width = 400;
        // }

        var options = {
            enableCellNavigation : false,
            enableColumnReorder : false
        };

        this.dataView.beginUpdate();
        this.dataView.setItems(obj.posts);
        this.dataView.setFilter(self.myFilter);
        this.dataView.endUpdate();
        $("#grid_search_count").html(self.add_commas(self.dataView.rows.length));
        $('#myGrid').show();
        var grid = new Slick.Grid($("#myGrid"), this.dataView.rows, columns, options);
        
        this.dataView.onRowCountChanged.subscribe(function(args) {
            grid.updateRowCount();
            grid.render();
            $("#grid_search_count").html(self.add_commas(self.dataView.rows.length));
        });
        
        this.dataView.onRowsChanged.subscribe(function(rows) {
            grid.removeRows(rows);
            grid.render();
        });
        
        $("#grid_search_form").submit(function(e) {
            Slick.GlobalEditorLock.cancelCurrentEdit();
            var select_element = $('#grid_search_input');
            this.value = select_element.val();
            self.searchString = this.value;
            self.dataView.refresh();
        });

        $("#grid_export").click(function(e) {
            $('#grid_search_form').hide();
            $('#grid_search_spinner').show();
            $('#grid_export_data').val( $.toJSON(self.dataView.rows) );
            $('#grid_export_form').submit();
            $('#grid_search_spinner').hide();
            $('#grid_search_form').show();
        });

        // if search arg, filter...
        if(match_array = document.location.search.match(/search=(.*?)&/)) {
            search_query = decodeURIComponent(unescape(match_array[1])).replace(/\+/g, ' ');
            if (self.DEBUG) { console.debug("search param defined: %s", search_query); }
            $('#grid_search_input').val(search_query);
            $('#grid_search_input').focus();
            this.value = search_query;
            self.searchString = this.value;
            self.dataView.refresh();
        }
    }

    /**
	 * search filter
	 */
    this.myFilter = function (item) {
        if(item['id'] == -1 || item['text'] == null) { return false; }
        if (self.searchString != "" && 
        item["text"].toLowerCase().indexOf(self.searchString.toLowerCase()) == -1) {
            return false;
        } else {
            return true;
        }
    }

    /**
	 * 
	 */
    this.load_iframe = function(nolimit) {
        nolimit = nolimit ? true : false;
        // close grid search with escape key
        $(document).keyup( this.keyup );

        if(self.loading) { return; };
        // close top 20 words if needed
        if(typeof(tu_word_freq) != 'undefined') { tu_word_freq.close(); };
        self.loading = true;
        if(window.GRID_TYPE && GRID_TYPE==1) {
            window.scroll(0,0);
            $('#screen').css({ opacity: 0.7, "width":$(document).width(),"height":$(document).height()});
        } else {
            $('#post-replies-div').hide();
            $('#all-posts-div').hide();
            $('#word-frequency-div').hide();
            $('#older-posts-div').hide();
        }
        var fade = (typeof(GRID_TYPE) != 'undefined' && GRID_TYPE==1) ? 500 : 1;
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
                    path + 'assets/html/grid.html?' + query_string + '&nolimit=' + nolimit
                    + '&cb=' + (new Date()).getTime());
            if (self.DEBUG) {
                console.debug("loading grid search iframes %s",  $('#grid_iframe').attr('src') );
            }
            self.loading = false;
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
        if(typeof(GRID_TYPE) != 'undefined' && GRID_TYPE == 1) {
            $('#screen').fadeOut(500);
        } else {
            $('#post-replies-div').show();
            $('#word-frequency-div').show();
            $('#all-posts-div').show();
            $('#older-posts-div').show();
        }
        $(document).unbind('keyup', this.keyup);
    }
    
    /**
	 * load xss script with post data callback
	 */
    this.get_data = function() {
        $('#myGrid').hide();
        var url = '../../post/grid.php' + document.location.search;
        if (self.DEBUG) { console.debug('Getting data with url: ' + url); }
        script = document.createElement('script');
        script.setAttribute("src", url);
        script.setAttribute("type", "text/javascript");
        document.getElementsByTagName('head')[0].appendChild(script);
    }
    
    /**
	 * Format numeric string with commas
	 */
    this.add_commas = function(nStr) {
        nStr += '';
        x = nStr.split('.');
        x1 = x[0];
        x2 = x.length > 1 ? '.' + x[1] : '';
        var rgx = /(\d+)(\d{3})/;
        while (rgx.test(x1)) {
            x1 = x1.replace(rgx, '$1' + ',' + '$2');
        }
        return x1 + x2;
    }
}
var tu_grid_search = new TUGridSearch();
tu_grid_search.init();
