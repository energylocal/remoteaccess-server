<?php
    /*
    All Emoncms code is released under the GNU Affero General Public License.
    See COPYRIGHT.txt and LICENSE.txt.

    ---------------------------------------------------------------------
    Emoncms - open source energy visualisation
    Part of the OpenEnergyMonitor project:
    http://openenergymonitor.org
    */

    global $path;
    
    $feedid = 165091;
    $feedname = "emonlibcm_power";
    
    if (isset($_GET['id'])) $feedid = (int) $_GET['id'];
    if (isset($_GET['name'])) $feedname = $_GET['name'];
?>

<script src="js/jquery-1.11.3.min.js"></script>
<script src="js/mqtt.min.js"></script>
<!--[if IE]><script language="javascript" type="text/javascript" src="<?php echo $path;?>lib/flot/excanvas.min.js"></script><![endif]-->
<script language="javascript" type="text/javascript" src="<?php echo $path; ?>lib/flot/jquery.flot.merged.js"></script>
<script language="javascript" type="text/javascript" src="<?php echo $path;?>js/vis.helper.js"></script>

<h2>Graph: <?php echo $feedname; ?></h2>

<div id="graph_bound" style="height:400px; width:100%; position:relative; ">
    <div id="graph"></div>
    <div id="graph-buttons" style="position:absolute; top:18px; right:32px; opacity:0.5;">
        <div class='btn-group'>
            <button class='btn graph-time' type='button' time='1'>D</button>
            <button class='btn graph-time' type='button' time='7'>W</button>
            <button class='btn graph-time' type='button' time='30'>M</button>
            <button class='btn graph-time' type='button' time='365'>Y</button>
        </div>

        <div class='btn-group' id='graph-navbar' style='display: none;'>
            <button class='btn graph-nav' id='zoomin'>+</button>
            <button class='btn graph-nav' id='zoomout'>-</button>
            <button class='btn graph-nav' id='left'><</button>
            <button class='btn graph-nav' id='right'>></button>
        </div>

    </div>
</div>

<script id="source" language="javascript" type="text/javascript">

var session = <?php echo json_encode($session); ?>;
var settings = <?php echo json_encode($settings); ?>;
var options = {
    username: session.username,
    password: session.password,
    clientId: 'mqttjs_' + session.username + '_' + Math.random().toString(16).substr(2, 8),
    port: settings.port,
    host: settings.host
}

console.log("mqtt connect");
var client = mqtt.connect(options.host, options)

var feedid = <?php echo $feedid; ?>;
var feedname = "<?php echo $feedname; ?>";
var path = "<?php echo $path; ?>";

var top_offset = 0;
var placeholder_bound = $('#graph_bound');
var placeholder = $('#graph');

var width = placeholder_bound.width();
var height = width * 0.5;

placeholder.width(width);
placeholder_bound.height(height);
placeholder.height(height-top_offset);

var timeWindow = (3600000*24.0);
view.start = +new Date - timeWindow;
view.end = +new Date;

var data = [];

$("#zoomout").click(function () {view.zoomout(); draw();});
$("#zoomin").click(function () {view.zoomin(); draw();});
$('#right').click(function () {view.panright(); draw();});
$('#left').click(function () {view.panleft(); draw();});
$('.graph-time').click(function () {view.timewindow($(this).attr("time")); draw();});
    
    placeholder.bind("plotselected", function (event, ranges)
    {
        view.start = ranges.xaxis.from;
        view.end = ranges.xaxis.to;
        draw();
    });

    placeholder.bind("plothover", function (event, pos, item)
    {
        if (item) {
            //var datestr = (new Date(item.datapoint[0])).format("ddd, mmm dS, yyyy");
            //$("#stats").html(datestr);
            if (previousPoint != item.datapoint)
            {
                previousPoint = item.datapoint;

                $("#tooltip").remove();
                var itemTime = item.datapoint[0];
                var itemVal = item.datapoint[1];

                // I'd like to eventually add colour hinting to the background of the tooltop.
                // This is why showTooltip has the bgColour parameter.
                tooltip(item.pageX, item.pageY, itemVal.toFixed(dp) + " " + units, "#DDDDDD");
            }
        }
        else
        {
            $("#tooltip").remove();
            previousPoint = null;
        }
    });

    function draw()
    {
        var npoints = 800;
        interval = Math.round(((view.end - view.start)/npoints)/1000);
        get_feed_data(feedid,view.start,view.end,interval,1,1);
    }
    
    function plot()
    {
        var options = {
            canvas: true,
            lines: { fill: true },
            xaxis: { mode: "time", timezone: "browser", min: view.start, max: view.end, minTickSize: [interval, "second"] },
            //yaxis: { min: 0 },
            grid: {hoverable: true, clickable: true},
            selection: { mode: "x" },
            touch: { pan: "x", scale: "x" }
        }

        $.plot(placeholder, [{data:data}], options);
    }

    
    // Graph buttons and navigation efects for mouse and touch
    $("#graph").mouseenter(function(){
        $("#graph-navbar").show();
        $("#graph-buttons").stop().fadeIn();
    });
    $("#graph_bound").mouseleave(function(){
        $("#graph-buttons").stop().fadeOut();
    });
    $("#graph").bind("touchstarted", function (event, pos){
        $("#graph-navbar").hide();
        $("#graph-buttons").stop().fadeOut();
    });
    
    $("#graph").bind("touchended", function (event, ranges){
        $("#graph-buttons").stop().fadeIn();
        view.start = ranges.xaxis.from; 
        view.end = ranges.xaxis.to;
        draw();
    });
    
    $(window).resize(function(){
        var width = placeholder_bound.width();
        var height = width * 0.5;

        placeholder.width(width);
        placeholder_bound.height(height);
        placeholder.height(height-top_offset);

        plot();
    });

    client.on('connect', function () {
        console.log("mqtt: connected");
        client.subscribe("user/"+options.username+"/response/"+options.clientId, function (err) {
            if (!err) {
                draw();
            }
        })
    })

    client.on('message', function (topic, message) {
        // message is Buffer
        console.log("response received");
        data = JSON.parse(message.toString());
        plot();
    })

    function get_feed_data(feedid,start,end,interval,skipmissing,limitinterval) {
        console.log("mqtt: requesting feed data");
        var publish_options = {
            clientId: options.clientId,
            path: "/emoncms/feed/data.json?id="+feedid+"&start="+start+"&end="+end+"&interval="+interval+"&skipmissing="+skipmissing+"&limitinterval="+limitinterval
        }
        client.publish("user/"+options.username+"/request", JSON.stringify(publish_options))
    }


</script>