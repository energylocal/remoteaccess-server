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
                var dp = 2;
                var units = '';
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
            // yaxis: { min: 0 },
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
        // message
        var response = JSON.parse(message.toString());
        data = response.result;
        plot();
    })

    function get_feed_data(feedid,start,end,interval,skipmissing,limitinterval) {
        console.log("mqtt: requesting feed data");
        var publish_options = {
            clientId: options.clientId,
            action: "feed/data",
            data: {
                id:feedid,
                start: start,
                end: end,
                interval: interval,
                skipmissing: skipmissing,
                limitinterval: limitinterval
            }
        }
        client.publish("user/"+options.username+"/request", JSON.stringify(publish_options))
    }
