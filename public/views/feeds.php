<style>
    /* Q2.{emrys} how do i add css to the container template?? */
    .card .dropdown-toggle:after {transition: all .2s; margin-right:.3rem}
    .card .collapsed .dropdown-toggle:after {transform: rotate(180deg)}

    .list-group-item {
        color: inherit
    }
    .list-group-item:before {
        content: "";
        width: 13px;
        height: 13px;
        right: .3rem;
        top: 50%;
        margin-top: -.4rem;
        position: absolute;
        border-radius: 50%;
    }
    .list-group-item{
        color:#000;
        background-color: transparent!important;
    }
    .no-underline {
        text-decoration: none !important
    }

    /* show feed update status as red,yellow,green circle  - mobile only*/
    @media (max-width: 575px) {
        .list-group-item-success:before{
            background-color: #28A745;
        }
        .list-group-item-warning:before{
            background-color: #FFC107;
        }
        .list-group-item-danger:before{
            background-color: #DC3545;
        }
    }


</style>

<div id="feeds-navbar" class="d-flex justify-content-sm-between flex-wrap">
    <div id="page-title" class="d-flex align-items-start flex-nowrap">
        <h2 class="mb-1 mr-2 text-nowrap">Feed List</h2>
        <button id="toggle" class="btn btn-outline-secondary" data-status="disconnected" onclick="on_off(event)">connect</button>
    </div>
    <nav id="feedlist-buttons" v-if="Object.values(nodes).length > 0" class="btn-toolbar d-flex justify-sm-content-end" role="toolbar" aria-label="feed buttons">
        <div id="list-buttons" class="btn-group align-items-start mb-1 xxxmr-1" role="group" aria-label="Basic example">
            <button id="collapse-all"
                type="button"
                v-on:click="collapseAllNodes"
                class="btn btn-outline-info"
                title="collapse all devices"
                data-toggle="tooltip">collapse
            </button>
            <button id="select-all"
                type="button"
                v-on:click="selectAllFeeds"
                class="btn btn-outline-info"
                title="select all feeds"
                data-toggle="tooltip"
            >select
            </button>
        </div>
        
        <div id="feed-buttons" class="d-none btn-group align-items-start mb-1" role="group" aria-label="Feed Specific actions">
            <button type="button" class="btn btn-info" disabled title="Edit selected feeds" data-toggle="tooltip">
                <svg viewBox="0 0 8 8" width="16px" height="16px" style="fill:currentColor"><use href="#edit"></use></svg>
            </button>
            <button type="button" class="btn btn-info" disabled title="Delete selected feeds" data-toggle="tooltip">
                <svg viewBox="0 0 8 8" width="16px" height="16px" style="fill:currentColor"><use href="#delete"></use></svg>
            </button>
            <button type="button" class="btn btn-info" disabled title="Download selected feeds" data-toggle="tooltip">
                <svg viewBox="0 0 8 8" width="16px" height="16px" style="fill:currentColor"><use href="#download"></use></svg>
            </button>
            <button type="button" class="btn btn-info" disabled title="View Selected feeds as a graph" data-toggle="tooltip">
                <svg viewBox="0 0 8 8" width="16px" height="16px" style="fill:currentColor"><use href="#view"></use></svg>
            </button>
        </div>
    </nav>
</div>

<p v-if="!connected" class="d-none d-sm-block">
    Emoncms is a powerful open-source web-app for processing, logging and visualising energy, temperature and other environmental data. 
</p>

<div id="feeds">
    <div v-if="nodes.length == 0" id="loading" class="alert alert-warning">
        <strong>Loading:</strong> Remote feed list, please wait 5 seconds&hellip;
    </div>
    <div v-for="(node, node_id) in nodes" 
        v-bind:class="node.status"
        class="card dropup mb-1"
    >
        <div class="card-header p-0" :id="'heading_' + node.id">
            <a class="d-flex no-gutters text-body justify-content-between py-2 no-underline"
               data-toggle="collapse"
               v-bind:href="'#collapse_' + node.id" 
               v-bind:class="{'collapsed': node.collapsed !== false}" 
               v-bind:aria-controls="'collapse_' + node.id"
            >
                <div class="d-flex col justify-content-between">
                    <h5 class="col d-flex mb-0 col-md-8 col-xl-6">{{node.tag}} :
                        <small v-if="hasSelectedFeeds(node_id) > 0" class="font-weight-light text-muted">
                            ({{hasSelectedFeeds(node_id)}})
                        </small>
                    </h5>
                    <div class="col d-none d-sm-block ml-4 pl-4 ml-md-0 pl-md-1 ml-lg-5 pl-lg-3 ml-xl-5 pl-xl-3 text-muted">
                        {{node.size | prettySize}}
                    </div>
                </div>
                <div class="col text-truncate dropdown-toggle d-none d-sm-block col-3 text-right"
                    v-html="list_format_updated(node.lastupdate)"
                ></div>
            </a>
        </div>
  
        <div class="collapse"
            v-bind:id="'collapse_' + node.id"
            v-bind:data-key="node_id"
            v-bind:class="{'show': !node.collapsed}"
            v-bind:aria-labelledby="'heading_' + node.id"
        >
            <ul class="list-group list-group-flush">
                <li class="list-group-item pl-0" 
                    data-toggle="popover" 
                    data-content="@todo: fill tooltip"
                    v-for="(feed, feed_id) in node.feeds" 
                    v-bind:class="getFeedClass(feed)"
                    v-bind:title="feed.id"
                >
                    <div class="d-flex justify-content-between no-gutters">
                        <div class="col col-8 col-lg-9">
                            <div class="row no-gutters">
                            <div class="pl-3 pull-left">
                                <div class="custom-control custom-checkbox text-center">
                                <input class="custom-control-input select-feed" 
                                    type="checkbox" 
                                    aria-label="select this feed"
                                    v-bind:id="'select-feed-' + feed.id"
                                    v-bind:data-id="feed.id" 
                                    v-bind:checked="feed.selected"
                                    v-on:change="feed.selected = $event.target.checked"
                                >
                                <label v-bind:for="'select-feed-' + feed.id" class="custom-control-label position-absolute"></label>
                                </div>
                            </div>
                            <div class="col text-truncate pl-1 col-md-5 col-xl-4" v-bind:title="feed.name">
                                {{feed.name}}
                            </div>
                            <div class="d-none col d-none d-sm-flex col-5 col-lg-6 col-xl-4">
                                <div class="d-none d-sm-block pull-left" v-bind:title="feed.public ? 'Public': 'Private'">
                                    <svg viewBox="0 0 8 8" width="16px" height="16px" style="fill:currentColor">
                                        <use v-bind:href="feed.public ? '#lock-unlocked': '#lock-locked'"></use>
                                    </svg>
                                </div>
                                <div class="col d-none d-md-block text-truncate col-5 col-md-6" v-bind:title="getEngineName(feed)">
                                    {{getEngineName(feed)}}
                                </div>
                                <div class="col d-none d-sm-block col-6 col-sm-10 ml-lg-1 ml-xl-0">
                                    {{feed.size | prettySize }}
                                </div>
                            </div>
                            </div>
                        </div>
                        <div class="col col-sm-4 col-lg-3">
                            <div class="row no-gutters">
                                <div class="col text-right text-truncate pr-2">
                                    {{list_format_value(feed.value)}} {{feed.unit}}
                                </div>
                            <div class="col col-6 col-md-5 text-right d-none d-sm-block" v-html="list_format_updated(feed.time)"></div>
                            </div>
                        </div>
                    </div>
                </li>
            </ul>
        </div>
    </div>
</div>

<script src="js/jquery-1.11.3.min.js"></script>
<script src="js/bootstrap.bundle.min.js"></script>
<script src="js/mqtt.min.js"></script>
<script src="js/misc.js"></script>
<script src="js/vue.js"></script>

<script>
    // global state for both vue instances
    var store = {
        debug: true,
        state: {
            nodes: {},
            connected: false
        },
        // edit the store's state with internal functions...
        toggleCollapsed: function(tag, state) {
            if(typeof state === 'undefined') state = true;
            if (this.debug) console.log('toggleCollapsed() triggered with', tag, state);
            if(tag) this.state.nodes[tag].collapsed = state;
        },
        setNodes: function(nodes){
            this.state.nodes = nodes;
        }
    } 

    function debug() {
        for(a in arguments) {
            console.log(JSON.parse(JSON.stringify(arguments[a])))
        }
    }

    function groupFeeds(feeds) {
        var nodes = {}
        for (key in feeds) {
            let feed = feeds[key];
            if(typeof nodes[feed.tag] === 'undefined') {
                nodes[feed.tag] = {
                    tag: feed.tag,
                    id: camelCase(feed.tag)
                }
            }
            // only create the node if it doesn't already exist
            if(typeof nodes[feed.tag].feeds === 'undefined'){
                nodes[feed.tag].feeds = {}
            }
            // add the feed to the parent node
            nodes[feed.tag].feeds[feed.id] = feed;
        }

        // total up the node's feed properties
        prevNodes = store.state.nodes;
        for (n in nodes) {
            let lastupdate = 0;
            let size = 0;
            let node = nodes[n];
            for (f in node.feeds) {
                let feed = node.feeds[f];
                size += parseInt(feed.size);
                lastupdate = parseInt(feed.time) > lastupdate ? parseInt(feed.time) : lastupdate;
                feed.selected = prevNodes[n] && prevNodes[n].feeds[f] ? prevNodes[n].feeds[f].selected : false;
            }
            node.collapsed = prevNodes[n] ? prevNodes[n].collapsed : true;
            node.size = size;
            node.lastupdate = lastupdate;
        }
        return nodes;
    }

    // feed list
    var app = new Vue({
        el: '#feeds',
        data: store.state,
        methods: {
            list_format_updated: function(value) {
                return list_format_updated(value);
            },
            list_format_value: function(value) {
                return list_format_value(value);
            }
        },
        filters: {
            prettySize: function (bytes) {
                bytes = bytes || 0;
                var decimals = 0
                var size = new Number(bytes).toFixed(decimals) + 'B';
                var length = bytes.toString().length
                if(length > 9) {
                    size = new Number(bytes/1000000000).toFixed(decimals) + ' GB';
                } else if (length > 6) {
                    size = new Number(bytes/1000000).toFixed(decimals) + ' MB';
                } else if (length > 3) {
                    size = new Number(bytes/1000).toFixed(decimals) + ' KB';
                }
                return size;
            }
        }
    });







    // feed list buttons
    var app2 = new Vue({
        el: '#feedlist-buttons',
        data: store.state,
        methods: {
            collapseAllNodes: function() {
                for(key in this.nodes) {
                    let node = this.nodes[key]
                    node.collapsed = true
                }
            },
            selectAllFeeds: function() {
                for(n in this.nodes) {
                    let node = this.nodes[n];
                    for(f in node.feeds) {
                        let feed = node.feeds[f];
                        feed.selected = true;                        
                    }
                }
            }
        }
    })





// jquery accordion
$(function(){
    $('#feeds').on('hidden.bs.collapse', '.collapse', function (event) {
        var tag = $(this).data('key');
        if(tag) store.toggleCollapsed(tag, true)
    })
    $('#feeds').on('shown.bs.collapse', '.collapse', function (event) {
        var tag = $(this).data('key');
        if(tag) store.toggleCollapsed(tag, false)
    })
})


</script>
<script>
var session = <?php echo json_encode($session); ?>;
var settings = <?php echo json_encode($settings); ?>;

var options = {
    username: session.username,
    password: session.password,
    clientId: 'mqttjs_' + session.username + '_' + Math.random().toString(16).substr(2, 8),
    port: settings.port,
    host: settings.host
}

options.will = {
    topic: 'user/' + options.username + '/response/' + options.clientId,
    payload: 'DISCONNECTED CLIENT ' + options.clientId + '--------',
    qos: 0,
    retain: false
};

var pubInterval = null

// auto connect on load:
connect()

function connect() {
    console.log("mqtt connect");
    client = mqtt.connect(options.host, options);
    
    var btn = document.querySelector('#toggle')
    btn.innerText = 'pause updates'
    btn.dataset.status = 'connected'

    client.on('connect', function () {
        console.log("mqtt: connected");
        client.subscribe("user/"+options.username+"/response/"+options.clientId, function (err) {
            if (!err) {
                publish();
                pubInterval = setInterval(publish,5000);
            }
        })
    })
    /** 
    * @arg String topic 
    * @arg Buffer message
    */
    client.on('message', function(topic, message) {
        var feeds = JSON.parse(message.toString());
        var nodes = groupFeeds(feeds);
        store.setNodes(nodes);
    })
}

function publish() {
    console.count("mqtt: requesting feed list");
    var publish_options = {
        clientId: options.clientId,
        path: "/emoncms/feed/list.json"
    }
    client.publish("user/"+options.username+"/request", JSON.stringify(publish_options))
}
function disconnect() {
    console.log('client disconnected')
    client.end()
    clearInterval(pubInterval)
    var btn = document.getElementById('toggle')
    btn.innerText = 'start updates'
    btn.dataset.status = 'disconnected'
}
function on_off(event) {
    //emrys
    event.preventDefault()
    btn = event.target
    if (btn.dataset.status == 'connected') {
        disconnect()
    } else {
        connect()
    }
}
function getEngineName(feed) {
    var engines = {
        0: 'MYSQL',
        2: 'PHPTIMESERIES',
        5: 'PHPFINA',
        6: 'PHPFIWA',
        7: 'VIRTUALFEED',   // Virtual feed, on demand post processing
        8: 'MYSQLMEMORY',   // Mysql with MEMORY tables on RAM. All data is lost on shutdown 
        9: 'REDISBUFFER',   // (internal use only) Redis Read/Write buffer, for low write mode
        10: 'CASSANDRA'    // Cassandra
    }
    return engines[feed.engine]
}

function camelCase(str) {
    // @todo: return string suitable to be used as an object property name
    return str.toLowerCase().replace(' ','_')
}

function hasSelectedFeeds(tag) {
    var count = 0
    let node = store.state.nodes[tag]
    for (f in node.feeds) {
        let feed = node.feeds[f];
        if (feed.selected) {
            count ++;
        }
    }
    return count;
}

function prettySize(bytes) {
    var decimals = 0
    var size = new Number(bytes).toFixed(decimals) + 'B';
    var length = bytes.toString().length
    if(length > 9) {
        size = new Number(bytes/(1024*1024*1024)).toFixed(decimals) + ' GB';
    } else if (length > 6) {
        size = new Number(bytes/(1024*1024)).toFixed(decimals) + ' MB';
    } else if (length > 3) {
        size = new Number(bytes/(1024)).toFixed(decimals) + ' KB';
    }
    return size;
}

function getFeedClass(feed) {
    var lastUpdated = new Date(feed.time * 1000);
    var now = new Date().getTime();
    var elapsed = (now - lastUpdated) / 1000;
    var missedIntervals = parseInt(elapsed / feed.interval);
    var css_class = 'list-group-item-success';
    if (missedIntervals > 8) {
        var css_class = 'list-group-item-danger';
    } else if (missedIntervals > 2) {
        var css_class = 'list-group-item-warning';
    }
    return css_class;
}

</script>
