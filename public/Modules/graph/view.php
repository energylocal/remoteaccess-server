<?php
    /*
    All Emoncms code is released under the GNU Affero General Public License.
    See COPYRIGHT.txt and LICENSE.txt.

    ---------------------------------------------------------------------
    Emoncms - open source energy visualisation
    Part of the OpenEnergyMonitor project:
    http://openenergymonitor.org
    */

    global $path, $embed;
    global $fullwidth;
    $fullwidth = true;
    
    $userid = 0;
    if (isset($_GET['userid'])) $userid = (int) $_GET['userid'];
    
    $feedidsLH = "";
    if (isset($_GET['feedidsLH'])) $feedidsLH = $_GET['feedidsLH'];

    $feedidsRH = "";
    if (isset($_GET['feedidsRH'])) $feedidsRH = $_GET['feedidsRH'];    
?>

<!--[if IE]><script language="javascript" type="text/javascript" src="<?php echo $path;?>Lib/flot/excanvas.min.js"></script><![endif]-->


<script language="javascript" type="text/javascript" src="<?php echo $path;?>Lib/flot/jquery.flot.min.js"></script>
<script language="javascript" type="text/javascript" src="<?php echo $path;?>Lib/flot/jquery.flot.time.min.js"></script>
<script language="javascript" type="text/javascript" src="<?php echo $path;?>Lib/flot/jquery.flot.selection.min.js"></script>
<script language="javascript" type="text/javascript" src="<?php echo $path;?>Lib/flot/jquery.flot.touch.min.js"></script>
<script language="javascript" type="text/javascript" src="<?php echo $path;?>Lib/flot/jquery.flot.togglelegend.min.js"></script>
<!--
<script language="javascript" type="text/javascript" src="<?php echo $path;?>Lib/flot/flot.min.js"></script>
-->
<script language="javascript" type="text/javascript" src="<?php echo $path; ?>Lib/flot/jquery.flot.stack.min.js"></script>
<script language="javascript" type="text/javascript" src="<?php echo $path;?>Modules/graph/vis.helper.js"></script>
<script language="javascript" type="text/javascript" src="<?php echo $path;?>Lib/misc/clipboard.js"></script>
<link href="<?php echo $path; ?>Lib/bootstrap-datetimepicker-0.0.11/css/bootstrap-datetimepicker.min.css" rel="stylesheet">
<script language="javascript" type="text/javascript" src="<?php echo $path; ?>Lib/bootstrap-datetimepicker-0.0.11/js/bootstrap-datetimepicker.min.js"></script>
<link href="<?php echo $path; ?>Modules/graph/graph.css" rel="stylesheet">

<div id="wrapper">
    <div id="sidebar-wrapper">
            <div style="padding-left:10px;">
                <div id="sidebar-close" style="float:right; cursor:pointer; padding:10px;"><i class="icon-remove"></i></div>
                <h3>Feeds</h3>
                
            </div>
            <div style="overflow-x: hidden; background-color:#f3f3f3; width:100%">
                <table class="table" id="feeds">
                </table>
            </div>
            
            <div id="mygraphs" style="padding:10px;">
                <h4>My Graphs</h4>
                
                <select id="graph-select" style="width:215px">
                </select>
                
                <br><br>
                <b>Graph Name:</b><br>
                <input id="graph-name" type="text" style="width:200px" />
                <div id="selected-graph-id" style="font-size:10px">Selected graph id: <span id="graph-id">None selected</span></div>
                <button id="graph-delete" class="btn" style="display:none">Delete</button>
                <button id="graph-save" class="btn">Save</button>
            </div>
    </div>

    <div id="page-content-wrapper" style="max-width:1280px">
        
        <h3>Data viewer</h3> 

        <div id="error" style="display:none"></div>

        <div id="navigation" style="padding-bottom:5px;">
            <button class="btn" id="sidebar-open"><i class="icon-list"></i></button>
            <button class='btn graph_time' type='button' time='1'>D</button>
            <button class='btn graph_time' type='button' time='7'>W</button>
            <button class='btn graph_time' type='button' time='30'>M</button>
            <button class='btn graph_time' type='button' time='365'>Y</button>
            <button id='graph_zoomin' class='btn'>+</button>
            <button id='graph_zoomout' class='btn'>-</button>
            <button id='graph_left' class='btn'><</button>
            <button id='graph_right' class='btn'>></button>
            
            <div class="input-prepend input-append" style="float:right; margin-right:22px">
            <span class="add-on">Show</span>
            <span class="add-on">missing data: <input type="checkbox" id="showmissing" style="margin-top:1px" /></span>
            <span class="add-on">legend: <input type="checkbox" id="showlegend" style="margin-top:1px" /></span>
            <span class="add-on">feed tag: <input type="checkbox" id="showtag" style="margin-top:1px" /></span>
            </div>
            
            <div style="clear:both"></div>
        </div>

        <div id="histogram-controls" style="padding-bottom:5px; display:none;">
            <div class="input-prepend input-append">
                <span class="add-on" style="width:75px"><b>Histogram</b></span>
                <span class="add-on" style="width:75px">Type</span>
                <select id="histogram-type" style="width:150px">
                    <option value="timeatvalue" >Time at value</option>
                    <option value="kwhatpower" >kWh at Power</option>
                </select>
                <span class="add-on" style="width:75px">Resolution</span>
                <input id="histogram-resolution" type="text" style="width:60px"/>
            </div>
            
            <button id="histogram-back" class="btn" style="float:right">Back to main view</button>
        </div>

        <div id="placeholder_bound" style="width:100%; height:400px;">
            <div id="placeholder"></div>
        </div>

        <div id="info" style="padding-top:20px; display:none">
            
            <div class="input-prepend input-append" style="padding-right:5px">
                <span class="add-on" style="width:50px">Start</span>
                <span id="datetimepicker1">
                  <input id="request-start" data-format="dd/MM/yyyy hh:mm:ss" type="text" style="width:140px" />
                  <span class="add-on"><i data-time-icon="icon-time" data-date-icon="icon-calendar"></i></span>
                </span>
            </div>
            
            <div class="input-prepend input-append" style="padding-right:5px">
                <span class="add-on" style="width:50px">End</span>
                <span id="datetimepicker2">
                  <input id="request-end" data-format="dd/MM/yyyy hh:mm:ss" type="text" style="width:140px" />
                  <span class="add-on"><i data-time-icon="icon-time" data-date-icon="icon-calendar"></i></span>
                </span>
            </div>
            
            <div class="input-prepend input-append" style="padding-right:5px">
                <span class="add-on" style="width:50px">Type</span>
                <select id="request-type" style="width:120px">
                    <option value="interval">Fixed Interval</option>
                    <option>Daily</option>
                    <option>Weekly</option>
                    <option>Monthly</option>
                    <option>Annual</option>
                </select>
                
            </div>
            <div class="input-prepend input-append" style="padding-right:5px">
                
                <span class="fixed-interval-options">
                    <input id="request-interval" type="text" style="width:60px" />
                    <span class="add-on">Fix <input id="request-fixinterval" type="checkbox" style="margin-top:1px" /></span>
                    <span class="add-on">Limit to data interval <input id="request-limitinterval" type="checkbox" style="margin-top:1px" /></span>
                </span>
            </div>
            
            <div class="input-prepend input-append">
                <span class="add-on" style="width:50px">Y-axis:</span>
                <span class="add-on" style="width:30px">min</span>
                <input id="yaxis-min" type="text" style="width:50px" value="auto"/>

                <span class="add-on" style="width:30px">max</span>
                <input id="yaxis-max" type="text" style="width:50px" value="auto"/>
                
                <button id="reload" class="btn">Reload</button>
            </div>
            
            <div id="window-info" style=""></div><br>
            
            <div class="feed-options hide">
                <div class="feed-options-header">
                    <div class="feed-options-title">Feeds in view</div>
                    <div class="feed-options-show-options hide">Show options</div>
                    <div class="feed-options-show-stats">Show statistics</div>
                </div>

                
                <table id="feed-options-table" class="table">
                    <tr><th></th><th>Feed</th><th>Type</th><th>Color</th><th>Fill</th><th>Stack</th><th style='text-align:center'>Scale</th><th style='text-align:center'>Delta</th><th style='text-align:center'>Average</th><th>DP</th><th style="width:120px"></th></tr>
                    <tbody id="feed-controls"></tbody>
                </table>
                
                <table id="feed-stats-table" class="table hide">
                    <tr><th>Feed</th><th>Quality</th><th>Min</th><th>Max</th><th>Diff</th><th>Mean</th><th>Stdev</th><th>Wh</th></tr>
                    <tbody id="feed-stats"></tbody>
                </table>
            </div>
            <br>
            
            <div class="input-prepend input-append">
                <button class="btn" id="showcsv" >Show CSV Output</button>
                <span class="add-on csvoptions">Time format:</span>
                <select id="csvtimeformat" class="csvoptions">
                    <option value="unix">Unix timestamp</option>
                    <option value="seconds">Seconds since start</option>
                    <option value="datestr">Date-time string</option>
                </select>
                <span class="add-on csvoptions">Null values:</span>
                <select id="csvnullvalues" class="csvoptions">
                    <option value="show">Show</option>
                    <option value="lastvalue">Replace with last value</option>
                    <option value="remove">Remove whole line</option>
                </select>
                <span class="add-on csvoptions">Headers:</span>
                <select id="csvheaders" class="csvoptions">
                    <option value="showNameTag">Show name and tag</option>
                    <option value="showName">Show name</option>
                    <option value="hide">Hide</option>
                </select>
            </div>

            <div class="input-append"><!-- just to match the styling of the other items -->
                <button onclick="copyToClipboardCustomMsg(document.getElementById('csv'), 'copy-csv-feedback','Copied')" class="csvoptions btn hidden" id="copy-csv" type="button">Copy <i class="icon-share-alt"></i></button>
            </div>
            <span id="copy-csv-feedback" class="csvoptions"></span>
            
            <textarea id="csv" style="width:98%; height:500px; display:none; margin-top:10px"></textarea>
        </div>
    </div>
</div>

<script language="javascript" type="text/javascript" src="<?php echo $path;?>Modules/graph/graph.js?v=1"></script>
<script language="javascript" type="text/javascript" src="<?php echo $path;?>Lib/moment.min.js"></script>

<script>
    var path = "<?php echo $path; ?>";
    var session = <?php echo $session; ?>;
    var userid = <?php echo $userid; ?>;
    var feedidsLH = "<?php echo $feedidsLH; ?>";
    var feedidsRH = "<?php echo $feedidsRH; ?>";
    
    // Load user feeds
    if (session) {
        $.ajax({                                      
            url: path+"feed/list.json", async: false, dataType: "json",
            success: function(data_in) { feeds = data_in; }
        });
    // Load public feeds for a particular user
    } else if (userid) {
        $.ajax({                                      
            url: path+"feed/list.json?userid="+userid, async: false, dataType: "json",
            success: function(data_in) { feeds = data_in; }
        });
    }

    // stops a part upgrade error - this change requires emoncms/emoncms repo to also be updated 
    // keep button hidden if new version of clipboard.js is not available
    if (typeof copyToClipboardCustomMsg === 'function') {
        document.getElementById('copy-csv').classList.remove('hidden');
    } else {
        copyToClipboardCustomMsg = function () {}
    }

    // Assign active feedid from URL
    var urlparts = window.location.pathname.split("graph/");
    if (urlparts.length==2) {
        var feedids = urlparts[1].split(",");
		    for (var z in feedids) {
		        var feedid = parseInt(feedids[z]);
		         
		        if (feedid) {
		            var f = getfeed(feedid);
                if (f==false) f = getfeedpublic(feedid);
                if (f!=false) feedlist.push({id:feedid, name:f.name, tag:f.tag, yaxis:1, fill:0, scale: 1.0, delta:false, dp:1, plottype:'lines'});
			      }		
		    }
    }
    
    // Left hand feed ids property
    if (feedidsLH!="") {
        var feedids = feedidsLH.split(",");
		    for (var z in feedids) {
		        var feedid = parseInt(feedids[z]);
		         
		        if (feedid) {
		            var f = getfeed(feedid);
                if (f==false) f = getfeedpublic(feedid);
                if (f!=false) feedlist.push({id:feedid, name:f.name, tag:f.tag, yaxis:1, fill:0, scale: 1.0, delta:false, dp:1, plottype:'lines'});
			      }		
		    }
    }

    // Right hand feed ids property
    if (feedidsRH!="") {
        var feedids = feedidsRH.split(",");
		    for (var z in feedids) {
		        var feedid = parseInt(feedids[z]);
		         
		        if (feedid) {
		            var f = getfeed(feedid);
                if (f==false) f = getfeedpublic(feedid);
                if (f!=false) feedlist.push({id:feedid, name:f.name, tag:f.tag, yaxis:2, fill:0, scale: 1.0, delta:false, dp:1, plottype:'lines'});
			      }		
		    }
    }   
    
    sidebar_resize();
    graph_init_editor();
    
    load_feed_selector(); 
    if (!session) $("#mygraphs").hide();
    graph_resize();
    
    var timeWindow = 3600000*24.0*7;
    var now = Math.round(+new Date * 0.001)*1000;
    view.start = now - timeWindow;
    view.end = now;
    view.calc_interval();
    
    graph_reloaddraw();
    
</script>

