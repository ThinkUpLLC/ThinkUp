/**
 * Grid Search Object for searching json posts data
 */
var TUGridSearch = function() {

    /**
     * @var boolean Enable for console logging
     */
    this.DEBUG = false;

    this.searchString = "";
    
    /**
     * Init grid search
     */
    this.init = function() {
        // register on submit event on our form
        $(document).ready(function() {
            if (tu_grid_search.DEBUG) {
                console.log("Grid Search initialized...");
            }
            $(".grid_search").click(function(event) {
                if (tu_grid_search.DEBUG) {
                    console.debug("search button selected");
                }
                tu_grid_search.load_iframe();
            });
            $('#close_grid_search').click(function() {
                tu_grid_search.close_iframe();
            });
        });
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
            field : "author"
        }, {
            id : "text",
            name : "Text",
            field : "text",
            width : 475
        } ];

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
    	if(item['id'] == -1) { return false; }
        if (tu_grid_search.searchString != "" && item["text"].toLowerCase().indexOf(tu_grid_search.searchString.toLowerCase()) == -1) {
            return false;
        } else {
            return true;
        }
    }
    
    /**
     * 
     */
    this.load_iframe = function() {
        //alert( current_query_string );
        $('#grid_overlay_div').show();
        $('#grid_iframe').show();
        var path = typeof (site_root_path) != 'undefined' ? site_root_path : '';
        $('#grid_iframe').attr('src',
                path + 'assets/html/grid.html?' + current_query_string + '&cb=' + (new Date()).getTime());
        if (tu_grid_search.DEBUG) {
            console.debug("loading grid search iframne %s",  $('#grid_iframe').attr('src') );
        }
    }
    /**
     * 
     */
    this.close_iframe = function() {
        var path = typeof (site_root_path) != 'undefined' ? site_root_path : '';
        $('#grid_iframe').attr('src', path + '/assets/img/loading.gif');
        $('#grid_overlay_div').hide();
        $('#grid_iframe').hide();
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
