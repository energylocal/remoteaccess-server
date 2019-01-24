<h2>Hello World</h2>
<p>Emoncms is a powerful open-source web-app for processing, logging and visualising energy, temperature and other environmental data.</p>

<style>
tr { cursor:pointer }
</style>

<div id="app">
  <div class="alert alert-warning" v-if="feeds.length==0">
    <strong>Loading:</strong> Remote feed list, please wait 5 seconds...
  </div>

  <table id="feeds" v-else class="table table-hover">
    <tr v-for="(feed,index) in feeds" v-on:click="graph(index)">
      <td>{{ feed.id }}</td>
      <td>{{ feed.name }}</td>
      <td>{{ feed.tag }}</td>
      <td v-html="list_format_updated(feed.time)"></td>
      <td v-html="list_format_value(feed.value)"></td>
    </tr>
  </table>
</div>

<script>
$(document).ready(function() {

var session = <?php echo json_encode($session); ?>;

var options = {
    username: session.username,
    password: session.password,
    clientId: 'mqttjs_' + session.username + '_' + Math.random().toString(16).substr(2, 8), // @todo: output 6 digit random hex number: eg a31bc1
    port: 8083,
    ejectUnauthorized: false
}

var feeds = [];

var app = new Vue({
  el: '#app',
  data: {
    feeds: []
  },
  methods: {
      list_format_updated:function(value) {
          return list_format_updated(value);
      },
      list_format_value:function(value) {
          return list_format_value(value);
      },
      graph:function(z) {
          window.location = "graph?id="+feeds[z].id+"&name="+feeds[z].name;
      }
  }
});

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
    app.feeds = feeds;
})

function publish() {
    console.log("mqtt: requesting feed list");
    var publish_options = {
        clientId: options.clientId,
        action: "feed/list"
    }
    client.publish("user/"+options.username+"/request", JSON.stringify(publish_options))
}

});
</script>
