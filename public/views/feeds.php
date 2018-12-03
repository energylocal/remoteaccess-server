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
    .list-group-item-selected{
        background-color: #b8daff!important;
    }
    .no-underline {
        text-decoration: none !important
    }
    .animate{
        transition: all .44s ease-in-out;
    }
    .col-hidden {
        flex-grow: 0;
        width: 0;
        overflow: hidden;
    }
    .wide {
        flex-grow: 0;
        width: 75%;
    }
    .narrow {
        flex-grow: 1;
        width: 25%;
    }
    .narrow .d-narrow-none {
        display: none;
    }
    .w-100{
        width: 100%!important;
        max-width: 100%!important;
        flex: 0 0 100%!important;
    }
    #graph{
        margin-top: -.5rem;
    }
    #feedslist-section.narrow #feed-list > li {
        cursor: pointer;
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



/*

    .fade-enter-active, .fade-leave-active {
        transition: all .5s; overflow: hidden;
    }
    .fade-enter, .fade-leave-to {
        opacity: 0; 
    }
    .fade-enter{background: height: 1px; width: 1px;}
    .fade-enter-active{background: black}
    .fade-enter-to{background: yellow}
    .fade-leave{background: height: 1px; width: 1px;}
    .fade-leave-active{background: black}
    .fade-leave-to{background: black}

*/







</style>

<div id="feeds-navbar" class="d-flex justify-content-sm-between flex-wrap">
    <div id="page-title" class="d-flex align-items-start flex-nowrap">
        <h2 class="mb-1 mr-2 text-nowrap">Feed List</h2>
        <button id="toggleRefresh" class="btn btn-outline-secondary" data-status="disconnected" onclick="on_off(event)">connect</button>
    </div>
    <div id="dev"><mark>{{ view }}</mark></div>
    <nav id="feedlist-buttons" class="btn-toolbar d-flex justify-sm-content-end" role="toolbar" aria-label="feed buttons">
        <div id="list-buttons" class="btn-group align-items-start mb-1" role="group" aria-label="Basic example">
            <button id="collapse-all"
                type="button"
                v-on:click="toggleCollapseAllNodes"
                class="btn btn-outline-info"
                title="collapse or expand all devices"
                data-toggle="tooltip">collapse
            </button>
            <button id="select-all"
                type="button"
                v-on:click="toggleSelectAllFeeds"
                class="btn btn-outline-info"
                title="select or de-select all feeds"
                data-toggle="tooltip"
            >select
            </button>
        </div>

        <div id="feed-buttons" class="btn-group align-items-start mb-1 ml-1" role="group" aria-label="Feed Specific actions">
            <button type="button" class="btn btn-info" title="View Selected feeds as a graph" 
                v-on:click="graphFeeds"
                :aria-pressed="view === 'graph'"
                :class="{'active': view === 'graph'}"
                :disabled="STORE.getSelectedFeeds().length === 0"
            >
                <svg viewBox="0 0 8 8" width="16px" height="16px" style="fill:currentColor"><use href="#view"></use></svg>
            </button>
            <button type="button" class="btn btn-info" title="@todo: Edit selected feeds"
                v-on:click="editFeeds"
                :aria-pressed="view === 'edit'"
                :class="{'active': view === 'edit'}"
                :disabled="true"
             >
                <svg viewBox="0 0 8 8" width="16px" height="16px" style="fill:currentColor"><use href="#edit"></use></svg>
            </button>
            <button type="button" class="btn btn-info" title="@todo: Delete selected feeds"
                v-on:click="deleteFeeds"
                :aria-pressed="view === 'delete'"
                :class="{'active': view === 'delete'}"
                :disabled="true"
             >
                <svg viewBox="0 0 8 8" width="16px" height="16px" style="fill:currentColor"><use href="#delete"></use></svg>
            </button>
            <button type="button" class="btn btn-info" title="@todo: Download selected feeds"
                v-on:click="downloadFeeds"
                :aria-pressed="view === 'download'"
                :class="{'active': view === 'download'}"
                :disabled="true"
             >
                <svg viewBox="0 0 8 8" width="16px" height="16px" style="fill:currentColor"><use href="#download"></use></svg>
            </button>
        </div>
    </nav>
</div>

<p class="d-none d-sm-block">
    Emoncms is a powerful open-source web-app for processing, logging and visualising energy, temperature and other environmental data.
</p>

<div class="row split">
    <div id="graph-section" class="col-slide col-hidden animate" 
        :class="{'wide': shared.view == 'graph'}"
    >
        <transition name="fade">
        <h2 v-if="selectedFeedNames !== ''">Graph: {{ selectedFeedNames }} </h2>
        </transition>
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
    </div><!-- /#graph -->



    <div id="feedslist-section" class="col animate" :class="{'narrow': view === 'graph'}">
        <div v-if="nodes.length == 0" id="loading" class="alert alert-warning">
            <strong>Loading:</strong> Remote feed list, please wait 5 seconds&hellip;
        </div>
        <div v-for="(node, node_id) in nodes"
            v-bind:class="node.status"
            class="card dropup mb-1"
        >
            <div class="card-header p-0" :id="'heading_' + node.id">
                <a class="d-flex no-gutters text-body justify-content-between py-2 no-underline row"
                data-toggle="collapse"
                v-bind:href="'#collapse_' + node.id"
                v-bind:class="{'collapsed': node.collapsed !== false}"
                v-bind:aria-controls="'collapse_' + node.id"
                >
                    <div class="d-flex col justify-content-between">
                        <h5 class="col d-flex mb-0 col-md-8 col-xl-6" :class="{'w-100': view === 'graph'}">{{node.tag}} :
                            <transition name="fade">
                            <small v-if="STORE.getSelectedFeeds(node_id).length > 0" class="font-weight-light text-muted d-narrow-none">
                                ({{ STORE.getNodeSelectedFeeds(node_id).length }})
                            </small>
                            </transition>
                        </h5>
                        <transition name="slide">
                        <div v-if="view === 'list'" class="col d-none d-sm-block ml-4 pl-4 ml-md-0 pl-md-1 ml-lg-5 pl-lg-3 ml-xl-5 pl-xl-3 text-muted">
                            {{node.size | prettySize}}
                        </div>
                        </transition>
                    </div>
                    <transition name="slide">
                    <div v-if="view === 'list'" class="col text-truncate dropdown-toggle d-none d-sm-block col-3 text-right"
                        v-html="list_format_updated(node.lastupdate)"
                    ></div>
                    </transition>
                </a>
            </div><!-- /.card-header -->

            <div class="collapse"
                v-bind:id="'collapse_' + node.id"
                v-bind:data-key="node_id"
                v-bind:class="{'show': !node.collapsed}"
                v-bind:aria-labelledby="'heading_' + node.id"
            >
                <ul id="feed-list" class="list-group list-group-flush">
                    <li class="list-group-item pl-0"
                        data-toggle="popover"
                        data-content="@todo: fill tooltip"
                        v-for="(feed, feed_id) in node.feeds"
                        v-bind:class="getFeedClass(feed)"
                        v-bind:title="feed.id"
                        v-on:click.stop="itemClicked(feed)"
                    >
                        <div class="d-flex justify-content-between" :class="{'no-gutters': view === 'list'}">
                            <div class="col col-8 col-lg-9" :class="{'col-12': view === 'graph','col-lg-12': view === 'graph'}">
                                <div class="d-flex" :class="{'no-gutters': view === 'list'}">
                                    <div v-if="view === 'list'" class="pl-3 pull-left">
                                        <div class="custom-control custom-checkbox text-center">
                                            <input class="custom-control-input select-feed"
                                                type="checkbox"
                                                aria-label="select this feed"
                                                v-bind:id="'select-feed-' + feed.id"
                                                v-bind:data-id="feed.id"
                                                v-bind:checked="feed.selected"
                                                v-on:change="feed.selected = $event.target.checked"
                                                v-on:click.stop="return true"
                                            >
                                            <label v-bind:for="'select-feed-' + feed.id" class="custom-control-label position-absolute"></label>
                                        </div>
                                    </div>
                                    <div class="col text-truncate pl-1 col-md-5 col-xl-4" 
                                        v-bind:title="feed.name"
                                        v-bind:class="{'pl-3': view !== 'list', 'col-12': view !== 'list','col-md-12': view !== 'list','col-xl-12': view !== 'list'}" 
                                    >
                                        {{feed.name}}
                                    </div>
                                    <div v-if="view === 'list'" class="d-none col d-none d-sm-flex col-5 col-lg-6 col-xl-4">
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
                            <div class="col col-sm-4 col-lg-3" v-if="view === 'list'">
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
            </div><!-- /.collapse -->
        </div><!-- /.card -->
    </div><!-- /#feeds -->

</div><!-- /.row -->

<script src="js/jquery-1.11.3.min.js"></script>
<script src="js/bootstrap.bundle.min.js"></script>
<script src="js/misc.js"></script>
<script src="js/vue.js"></script>
<script src="js/mqtt.min.js"></script>
<!--[if IE]><script language="javascript" type="text/javascript" src="lib/flot/excanvas.min.js"></script><![endif]-->
<script language="javascript" type="text/javascript" src="lib/flot/jquery.flot.merged.js"></script>
<script language="javascript" type="text/javascript" src="js/vis.helper.js"></script>

<script>
// session variables
var SESSION = <?php echo json_encode($session); ?>;
// application settings
var SETTINGS = <?php echo json_encode($settings); ?>;

// GLOBAL APP STATE for all vue instances
var STORE = {
    debug: true,
    home: 'list',
    state: {
        nodes: {},
        selectedFeeds: [],
        graphSelectedFeeds: [],
        editSelectedFeeds: [],
        deleteSelectedFeeds: [],
        connected: false,
        view: 'list'
    },
    // edit the shared store's state with internal functions...
    toggleCollapsed: function(tag, state) {
        if (typeof state === 'undefined') state = true;
        if (this.debug) console.log('toggleCollapsed() triggered with', tag, state);
        if (this.state.nodes[tag]) this.state.nodes[tag].collapsed = state;
    },
    toggleFeedSelected: function(tag, id, state) {
        if (typeof state === 'undefined') state = false;
        if (this.debug) console.log('toggleFeedSelected() triggered with', tag, id, state);
        if (this.state.nodes[tag] && this.state.nodes[tag].feeds[id]) this.state.nodes[tag].feeds[id].selected = state;
    },
    setNodes: function(nodes){
        // if (this.debug) console.log('setNodes() triggered with', nodes);
        this.state.nodes = nodes;
    },
    // toggle back to default when view already set
    toggleView: function(newView) {
        if (typeof newView === 'undefined') return false;

        newView = this.state.view === newView ? this.home : newView;
        if (this.debug) console.log('MODE::: toggleView() set to ', newView);
        this.state.view = newView;
    },
    // aggrigate functions based on store properties
    totalFeeds: function() {
        var totalFeeds = 0;
        for(n in this.state.nodes) {
            totalFeeds += Object.values(this.state.nodes[n].feeds).length;
        }
        return totalFeeds;
    },
    // return array of selected feeds for a given tag
    getNodeSelectedFeeds: function(tag) {
        var selected = [];
        let node = this.state.nodes[tag];
        for(f in node.feeds) {
            let feed = node.feeds[f];
            if(feed.selected === true) selected.push(feed);
        }
        return selected;
    },
    // return array of all selected feeds
    getSelectedFeeds: function() {
        var selected = [];
        for(n in this.state.nodes) {
            let nodeSelectedFeeds = this.getNodeSelectedFeeds(n);
            for(s in nodeSelectedFeeds){
                selected.push(nodeSelectedFeeds[s]);
            }
        }
        return selected;
    },
    setSelectedFeeds: function() {
        if (this.debug) console.log('setSelectedFeeds() triggered');
        var selectedFeeds = 
        this.state.selectedFeeds = this.getSelectedFeeds();
    }
} // end of STORE

// Debug
var DEBUG = STORE.debug || false;

// return new object with each feed tag as individual object with "feeds" property
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
            nodes[feed.tag].feeds = {};
        }
        // add the feed to the parent node
        nodes[feed.tag].feeds[feed.id] = feed;
    }

    // total up the node's feed properties
    prevNodes = STORE.state.nodes;
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

// VARIABLES, FUNCTIONS AND INIT
//----------------------------------------------------------------------------------------

// list of api endpoints
var ENDPOINTS = {
    feedlist: 'feed/list',
    graph: 'feed/data',
    saveFeed: '/emoncms/feed/set.json',
    deleteFeed: '/emoncms/feed/delete.json',
}

// pause or resume the data download by disconnecting and connecting to broker
function on_off(event) {
    event.preventDefault()
    btn = event.target
    if (btn.dataset.status == 'connected') {
        if (DEBUG) console.log('mqtt: publish interval interrupted. #', MQTT.getInterval());
        MQTT.pause();
    } else {
        MQTT.loop(feedlistPublishOptions);
    }
}

// return feed engine name based on feed engine id
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
    if(typeof str != 'undefined') return str.toLowerCase().replace(' ','_')
}

// return list of css classes for list-group-item based on feed values
function getFeedClass(feed) {
    var lastUpdated = new Date(feed.time * 1000);
    var now = new Date().getTime();
    var elapsed = (now - lastUpdated) / 1000;
    var missedIntervals = parseInt(elapsed / feed.interval);
    var css_classes = [];
    css_classes.push('list-group-item-success');
    if (missedIntervals > 8) {
        css_classes.push('list-group-item-danger');
    } else if (missedIntervals > 2) {
        css_classes.push('list-group-item-warning');
    }

    if(feed.selected) {
        css_classes.push('list-group-item-selected');
    }
    return css_classes.join(' ');
}


// mqtt client instance
//----------------------------------------------------------------------------------------
var MQTT = (function(session, settings) {
    mqttClient = null;
    // mqtt broker connection settings
    var brokerOptions = {
        username: session.username,
        password: session.password,
        clientId: 'mqttjs_' + session.username + '_' + Math.random().toString(16).substr(2, 8),
        port: settings.port,
        host: settings.host
    }
    // notify broker of disconnection
    brokerOptions.will = {
        topic: 'user/' + brokerOptions.username + '/response/' + brokerOptions.clientId,
        payload: 'DISCONNECTED CLIENT ' + brokerOptions.clientId + '--------',
        qos: 0,
        retain: false
    };
    // send publish() to mqtt broker at set interval
    var publishInterval = null

    window.addEventListener('beforeunload', function (event) {
        disconnectFromBroker();
    }, false);

    // connect to mqtt broker
    // add callback function to run when subscribed topic messages arrive
    function connectToBroker(options) {
        if (DEBUG) console.log('mqtt: connect() called with ', options);
        mqttClient = mqtt.connect(brokerOptions.host, brokerOptions);

        mqttClient.on('connect', function () {
            if (DEBUG) console.log('mqtt: on connect event called. connected.');
            mqttClient.subscribe("user/" + brokerOptions.username+"/response/" + brokerOptions.clientId, function (err) {
                if (!err && typeof options != 'undefined') {
                    publishToBrokerAtInterval(options);
                }
            })
        })

        /**
        * React when stream data is pushed to the client from the broker
        * @arg String topic
        * @arg Buffer message
        */
        mqttClient.on('message', function(topic, message) {
            var response = JSON.parse(message.toString()); // decode stream
            if (DEBUG) console.log('mqtt: received message from ', topic, '. original request: ', response.request.action);
            var result = response.result;
            switch(response.request.action) {
                case 'feed/list':
                    if (DEBUG) console.log('STORE: setNodes()');
                    var nodes = groupFeeds(result);
                    STORE.setNodes(nodes);
                break;
                case 'feed/data':
                    if (DEBUG) console.log('GRAPH: plot()');
                    GRAPH.plot(response);
                break;
                default:
                    if (DEBUG) console.log('mqtt: cannot respond to unrecognized action ', response.request.action);
            }
        });
    }

    // publish request to mqtt broker
    function publishToBroker(options) {
        if (DEBUG) console.log('mqtt: publishing to request: ', options.action);
        mqttClient.publish("user/" + brokerOptions.username + "/request", JSON.stringify(options))
    }

    // disconnect from mqtt broker
    function disconnectFromBroker() {
        if (DEBUG) console.log('mqtt: disconnect() called.');
        mqttClient.end()
        // stop the looping of publishing to topic
        clearInterval(publishInterval);
    }


    // start a setInterval at 5s
    function publishToBrokerAtInterval(options) {
        if (DEBUG) console.log('mqtt: publish interval started with', options);

        var btn = document.querySelector('#toggleRefresh');
        if (btn) {
            btn.innerText = 'pause updates';
            btn.dataset.status = 'connected'; 
        }

        publishToBroker(options);
        setPublishInterval(setInterval(function(){
            publishToBroker(options)
        }, 5000));
    }

    function setPublishInterval(interval) {
        publishInterval = interval;
    }
    function getPublishInterval(interval) {
        return publishInterval;
    }
    function interruptPublishInterval() {
        var interval = getPublishInterval();
        clearInterval(interval);
        setPublishInterval(null);
        var btn = document.getElementById('toggleRefresh');
        btn.innerText = 'start updates';
        btn.dataset.status = 'disconnected';
    }
    // expose these functions and variables to the global variable mqtt
    return {
        disconnect: disconnectFromBroker,
        publish: publishToBroker,
        connect: connectToBroker,
        client:  mqttClient,
        getInterval: getPublishInterval,
        setInterval: setPublishInterval,
        loop: publishToBrokerAtInterval,
        pause: interruptPublishInterval,
        options: brokerOptions
    }
})(SESSION, SETTINGS);
// end of mqtt "module"
//----------------------------------------------------------------------------------------


// Graph related code
//----------------------------------------------------------------------------------------
var GRAPH = (function (session, settings, endpoints, mqtt){
    var brokerOptions = mqtt.options;

    function get_feed_data(feedid, start, end, interval, skipmissing, limitinterval) {
        console.log("mqtt: requesting feed data");
        var publish_options = {
            clientId: brokerOptions.clientId,
            action: endpoints.graph,
            data: {
                id: feedid,
                start: start,
                end: end,
                interval: interval,
                skipmissing: skipmissing,
                limitinterval: limitinterval
            }
        }
        mqtt.publish(publish_options);
    }
    function plot(response) {
        if (typeof response === 'undefined') return false;

        var placeholder = document.getElementById('graph');
        var options = {
            canvas: true,
            lines: { fill: true },
            xaxis: {
                mode: "time",
                timezone: "browser",
                min: response.request.data.start,
                max: response.request.data.end,
                minTickSize: [response.request.data.interval, "second"]
            },
            //yaxis: { min: 0 },
            grid: { hoverable: true, clickable: true },
            selection: { mode: "x" },
            touch: { pan: "x", scale: "x" }
        }

        $.plot(placeholder, [{data:response.result}], options);
    }
    function draw(){
        console.log('graph draw');
    }

    // public functions
    return {
        getData: get_feed_data,
        plot: plot,
        draw: draw
    }
})(SESSION, SETTINGS, ENDPOINTS, MQTT);


// auto connect on load:
var feedlistPublishOptions = {
    clientId: MQTT.options.clientId,
    action: ENDPOINTS.feedlist
}

// INIT
MQTT.connect(feedlistPublishOptions);

</script>

<script>
//----------------------------vue js instances -------------------

    // FEED LIST
    var app = new Vue({
        el: '#feedslist-section',
        data: STORE.state,
        methods: {
            list_format_updated: function(value) {
                return list_format_updated(value);
            },
            list_format_value: function(value) {
                return list_format_value(value);
            },
            itemClicked: function(feed) {
                if(true || this.view === 'graph') {
                    feed.selected = feed.selected === true ? false : true;
                }
            }
        },
        filters: {
            prettySize: function (bytes) {
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
        },
        computed: {
            selectedFeeds: function() {
                return STORE.getSelectedFeeds();
            }
        },
        watch: {
            selectedFeeds: function(){
                // STORE.setSelectedFeeds();
            }
        }
    }); // end of feed list vuejs
    
    // FEED LIST BUTTONS
    var app2 = new Vue({
        el: '#feedlist-buttons',
        data: STORE.state,
        methods: {
            // if any nodes collapsed, expand all; else collapse all.itemClicked
            toggleCollapseAllNodes: function() {
                var totalCollapsed = 0;
                // count collapsed nodes
                for(n in this.nodes) {
                    let node = this.nodes[n];
                    if(node.collapsed === true) totalCollapsed++;
                }
                var state = totalCollapsed > 0;
                // change node state
                for(tag in this.nodes) {
                    let node = this.nodes[tag];
                    STORE.toggleCollapsed(tag, !state);
                }
            },
            // if all feeds selected, select none; else select all.
            toggleSelectAllFeeds: function() {
                var totalSelected = STORE.getSelectedFeeds().length;
                var totalFeeds = STORE.totalFeeds();
                var state = totalSelected < totalFeeds;
                // change feed state
                for(n in this.nodes) {
                    let node = this.nodes[n];
                    for(f in node.feeds) {
                        let feed = node.feeds[f];
                        STORE.toggleFeedSelected(feed.tag, feed.id, state)
                    }
                }
            },
            editFeeds: function(event) {
                if (STORE.debug) console.log('edit() triggered with', event);
                event.preventDefault();
                STORE.toggleView('edit');
                // @todo
            },
            deleteFeeds: function(event) {
                if (STORE.debug) console.log('delete() triggered with', event);
                event.preventDefault();
                STORE.toggleView('delete');
                // @todo
            },
            downloadFeeds: function(event) {
                if (STORE.debug) console.log('download() triggered with', event);
                event.preventDefault();
                STORE.toggleView('download');
                // @todo
            },
            graphFeeds: function(event) {
                if (STORE.debug) console.log('view() graphFeeds() triggered with', event.type);
                event.preventDefault();
                STORE.setSelectedFeeds();
                STORE.toggleView('graph');
            }
        }
    }); // end of #feedlist buttons vuejs

    var app3 = new Vue({
        el: '#graph-section',
        data: {
            shared: STORE.state,
            private: {
                placeholder: document.getElementById('graph'),
                placeholder_bound: document.getElementById('graph_bound')
            }
        },
        computed: {
            selectedFeeds: {
                get: function() {
                    return STORE.getSelectedFeeds() || false;
                },
                set: function() {
                    STORE.setSelectedFeeds();
                }
            },
            selectedFeedNames: function(){
                names = [];
                for(i in this.selectedFeeds) {
                    var feed = this.selectedFeeds[i];
                    names.push(feed.name);
                }
                return names.join(', ');
            }
        },
        watch: {
            selectedFeeds: function(newVal, oldVal) {
                if (this.debug) console.log(view,'::selectedFeeds changed', newVal, oldVal);

                if(newVal.length > 0) {
                    if (this.view === 'graph') {
                        var feed = newVal[0];
                        var npoints = 800;
                        var timeWindow = 3600000 * 24; // one hour x 24 = one day
                        var feedid = feed.id;
                        var start = new Date() - timeWindow;
                        var end = new Date().getTime();
                        var interval = Math.round(((end - start)/npoints)/1000);
                        var skipmissing = 1;
                        var limitinterval = 1;

                        GRAPH.draw(); // set out the graph
                        // request the data. received data will be plotted
                        GRAPH.getData(feedid,start,end,interval,skipmissing,limitinterval);
                    }
                }
            }
        },
        mounted() {
            var vm = this;
            window.addEventListener('resize', function(){
                var width = vm.private.placeholder_bound.offsetWidth;
                var height = width * 0.5;
                var top_offset = 0;
                vm.private.placeholder.width = width;
                vm.private.placeholder_bound.height = height;
                vm.private.placeholder.height = height - top_offset;
                vm.plot();
            });
        },
        methods: {
            plot: function(){
                GRAPH.plot()
            }
        }
    }); // end of #graph vuejs

    // --------------------------------debug remove for production
    new Vue({
        el: "#dev",
        data: STORE.state
    })

</script>

<script>
    // JQUERY & BOOTSTRAP

    // jquery accordion
    $(function(){
        $('#feedslist-section').on('hidden.bs.collapse', '.collapse', function (event) {
            var tag = $(this).data('key');
            if(tag) STORE.toggleCollapsed(tag, true)
        })
        $('#feedslist-section').on('shown.bs.collapse', '.collapse', function (event) {
            var tag = $(this).data('key');
            if(tag) STORE.toggleCollapsed(tag, false)
        })
    })

</script>
