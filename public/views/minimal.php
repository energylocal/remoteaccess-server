<h2>Hello World</h2>
<p>Emoncms is a powerful open-source web-app for processing, logging and visualising energy, temperature and other environmental data.</p>

<style>
tr { cursor:pointer }
</style>

<div class="alert alert-warning">
  <strong>Loading:</strong> Remote feed list, please wait 5 seconds...
</div>

<table id="feeds" class="table table-hover"></table>

<script src="js/jquery-1.11.3.min.js"></script>
<script src="js/mqtt.min.js"></script>
<script src="js/misc.js"></script>

<script>
var session = <?php echo json_encode($session); ?>;

var options = {
    username: session.username,
    password: session.password,
    clientId: 'mqttjs_' + session.username + '_' + Math.random().toString(16).substr(2, 8), // @todo: output 6 digit random hex number: eg a31bc1
    port: 8083,
    ejectUnauthorized: false
}

var feeds = [];

console.log("mqtt connect");
var client = mqtt.connect("wss://mqtt.emoncms.org", options)

client.on('connect', function () {
    console.log("mqtt: connected");
    client.subscribe("user/"+options.username+"/response/"+options.clientId, function (err) {
        if (!err) {
            publish();
            setInterval(publish,5000);
        }
    })
})

client.on('message', function (topic, message) {
    // message is Buffer
    console.log("response received");
    var response = JSON.parse(message.toString());
    feeds = response.result;
    draw(feeds);
})

function publish() {
    console.log("mqtt: requesting feed list");
    var publish_options = {
        clientId: options.clientId,
        action: "feed/list"
    }
    client.publish("user/"+options.username+"/request", JSON.stringify(publish_options))
}

function draw(feeds) {
    var out = "";
    for (var z in feeds) {
        var row = "";
        row += "<td>"+feeds[z].id+"</td>";
        row += "<td>"+feeds[z].name+"</td>";
        row += "<td>"+feeds[z].tag+"</td>";
        row += "<td>"+list_format_updated(feeds[z].time)+"</td>";
        row += "<td>"+list_format_value(feeds[z].value)+"</td>";
        out += "<tr row="+z+">"+row+"</tr>";
    }
    $("#feeds").html(out);
    $(".alert").hide();
}

$("#feeds").on("click","tr",function() {
    var z = $(this).attr("row");
    window.location = "graph?id="+feeds[z].id+"&name="+feeds[z].name;
});

</script>
