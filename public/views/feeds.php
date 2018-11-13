<style>
    /* Q2.{emrys} how do i add css to the container template?? */
    .card .dropdown-toggle:after {transition: all .2s; margin-right:.3rem}
    .card .collapsed .dropdown-toggle:after {transform: rotate(180deg)}

    .list-group-item {
        color: inherit
    }
    .list-group-item:before {
        content: "";
        width: .6rem;
        height: .6rem;
        right: .3rem;
        top: 50%;
        margin-top: -.3rem;
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
        .list-group-item-danger:before{
            background-color: #FFC107;
        }
        .list-group-item-warning:before{
            background-color: #DC3545;
        }
    }


</style>

<div id="feeds-navbar" class="d-flex justify-content-sm-between flex-wrap">
    <div id="page-title" class="d-flex align-items-start flex-nowrap">
        <h2 class="mb-1 mr-2 text-nowrap">Feed List</h2>
        <button id="toggle" class="btn btn-outline-secondary" data-status="disconnected" onclick="on_off(event)">connect</button>
    </div>
    <nav id="feedlist-buttons" class="btn-toolbar d-flex justify-sm-content-end" role="toolbar" aria-label="feed buttons">
        <div id="list-buttons" class="btn-group align-items-start mb-1" role="group" aria-label="Basic example">
            <button id="collapse-all"
                type="button"
                onclick="collapseAll"
                class="btn btn-outline-primary"
                title=""
                data-toggle="tooltip">collapse
            </button>
            <button id="select-all"
                type="button"
                onclick="selectAll"
                class="btn btn-outline-primary"
                title="$t('message.selectall_help')"
                data-toggle="tooltip"
            >select
            </button>
        </div>
        
        <div id="feed-buttons" class="btn-group align-items-start ml-1 mb-1" role="group" aria-label="Feed Specific actions">
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

<p v-if="!connected" class="d-none d-sm-block">Emoncms is a powerful open-source web-app for processing, logging and visualising energy, temperature and other environmental data.</p>


<div id="feeds">
    <div v-if="nodes.length == 0" id="loading" class="alert alert-warning">
        <strong>Loading:</strong> Remote feed list, please wait 5 seconds...
    </div>
    <div v-for="(node, index) in nodes" class="card dropup mb-1" :class="node.status">
        <div class="card-header p-0" :id="'heading' + node.id">
            <a href="#" class="d-flex no-gutters text-body justify-content-between py-2 no-underline'"
                :class="{'collapsed': node.collapsed !== false}"
                data-toggle="collapse" 
                :data-target="'#collapse_' + node.id"
                :aria-controls="'collapse_' + node.id">
                <div class="d-flex col justify-content-between">
                    <h5 class="col d-flex mb-0 col-md-8 col-xl-6">{{node.tag}}:
                        <small v-if="hasSelectedFeeds(node.tag) > 0" class="font-weight-light text-muted">({{hasSelectedFeeds(node.tag)}})</small>
                    </h5>
                    <div class="col d-none d-sm-block ml-4 pl-4 pl-md-3 pl-lg-2 ml-xl-5 pl-xl-3 text-muted">{{node.size | prettySize}}</div>
                </div>
                <div class="col text-truncate dropdown-toggle d-none d-sm-block col-3 text-right" v-html="list_format_updated(node.lastupdate)"></div>
            </a>
        </div>
            
        <div :id="'collapse_' + node.id"
            class="collapse"
            :data-key="index"
            :class="{'show': node.collapsed !== false}"
            :aria-labelledby="'heading' + node.id">

            <ul class="list-group list-group-flush">
                <li v-for="feed in node.feeds" class="list-group-item pl-0" :class="getFeedClass(feed)" data-toggle="popover" :title="feed.id" data-content="@todo: fill tooltip">
                    <div class="d-flex justify-content-between no-gutters">
                        <div class="col col-8 col-md-9">
                            <div class="row no-gutters">
                            <div class="pl-3 pull-left">
                                <div class="custom-control custom-checkbox text-center">
                                <input :id="'select-feed-' + feed.id"
                                    :selected="feed.selected"
                                    :data-id="feed.id" 
                                    class="custom-control-input select-feed" 
                                    type="checkbox" 
                                    aria-label="select this item">
                                <label :for="'select-feed-' + feed.id" class="custom-control-label position-absolute"></label>
                                </div>
                            </div>
                            <div class="col text-truncate pl-1 col-md-4 col-lg-5 col-xl-4" :title="feed.name">{{feed.name}}</div>
                            <div class="d-none col d-none d-sm-flex col-5 col-md-7 col-lg-5 col-xl-4">
                                <div class="d-none d-sm-block pull-left" :title="{'Public': feed.public, 'Private': !feed.public}">
                                    <svg viewBox="0 0 8 8" width="16px" height="16px" style="fill:currentColor"><use :href="{'#lock-locked' : feed.public, '#lock-unlocked': !feed.public}"></use></svg>
                                </div>
                                <div class="col d-none d-md-block text-truncate col-5 col-md-6" :title="getEngineName(feed)">{{getEngineName(feed)}}</div>
                                <div class="col d-none d-sm-block col-6 col-sm-10 col-md-4">{{feed.size | prettySize(feed.size) }}</div>
                            </div>
                            </div>
                        </div>
                        <div class="col col-sm-4 col-md-3">
                            <div class="row no-gutters">
                            <div class="col text-right text-truncate pr-2">
                                {{list_format_value(feed.value)}} {{feed.unit}}
                            </div>
                            <div class="col col-6 col-md-4 text-right d-none d-sm-block" v-html="list_format_updated(feed.time)"></div>
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
    var list = new Vue({
        el: '#feeds',
        data: {
            nodes: [],
            connected: false
        },
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

</script>
<script>
var options = {
    username: '<?php echo $session['username']; ?>', // load with AJAX would be better
    password: '<?php echo $password; ?>', // load with AJAX would be better
    clientId: 'mqttjs_' + '<?php echo $username; ?>' + '_' + Math.random().toString(16).substr(2, 8), // @todo: output 6 digit random hex number: eg a31bc1
    port: 8083,
    ejectUnauthorized: false,
    host: "wss://mqtt.emoncms.org"
}
// //DEV ONLY SETTINGS
// var options = {
//     username: '<?php echo $username; ?>', // load with AJAX would be better
//     password: '<?php echo $password; ?>', // load with AJAX would be better
//     clientId: 'mqttjs_' + Math.random().toString(16).substr(2, 8),
//     port: 9001,
//     host: "ws://localhost"
// }

options.will = {
    topic: 'user/' + options.username + '/response/' + options.clientId,
    payload: 'DISCONNECTED CLIENT ' + options.clientId + '--------',
    qos: 0,
    retain: false
};

var nodes = false;
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
    client.on('message', function (topic, message) {
        // message is Buffer
        var feeds = JSON.parse(message.toString());
        // console.log(feeds);
        nodes = processData(feeds);
        console.log("response received");
        // console.log('processed feeds into nodes', nodes)
        list.nodes = nodes
        // disconnect()
    })
}

function publish() {
    console.log("mqtt: requesting feed list");
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
function isSelected(feed_id) {
    // return true if checked
    checkbox = document.querySelector('[data-id = "' + feed_id + '"]');
    if (checkbox) {
        return checkbox.checked
    } else {
        return false
    }
}
function camelCase(str) {
    // @todo: return string suitable to be used as an object property name
    return str.toLowerCase().replace(' ','_')
}
function isCollapsed(tag) {
    // return true if tag (group) is collapsed
    for (z in nodes) {
        let node = nodes[z];
        if(node.tag===tag) {
            return node.collapsed;
        }
    }
    return true; // defaults to closed
}
function hasSelectedFeeds(tag) {
    // @todo: return number of selected feeds in node
    var count = 0
    for (z in nodes) {
        if(nodes[z].tag===tag) {
            for (x in nodes[z].feeds) {
                if (nodes[z].feeds[x].selected) {
                    count ++;
                }
            }
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
    var elapsed = (now - lastUpdated / 1000);
    var steps = elapsed / feed.interval;
    var css_class = 'list-group-item-success';
    if (steps > 2) {
        var css_class = 'list-group-item-warning';
    } else if (steps > 8) {
        var css_class = 'list-group-item-success';
    }
    return css_class;
}

function processData(data) {
    var nodes = {}
    for (let key in data) {
        let feed = data[key]
        // create array of nodes with array of feeds as a property of each node
        feed.selected = isSelected(feed.id)
        feed.public = feed.public === '1'
        if (!nodes[feed.tag]) {
            nodes[feed.tag] = {
                tag: feed.tag,
                id: camelCase(feed.tag),
                collapsed: isCollapsed(feed.tag),
                size: 0,
                lastupdate: 0,
                feeds: [],
                status: 'warning'
            }
        }
        
        nodes[feed.tag].size += parseInt(feed.size)
        nodes[feed.tag].lastupdate = parseInt(feed.time) > nodes[feed.tag].lastupdate ? parseInt(feed.time) : nodes[feed.tag].lastupdate
        
        nodes[feed.tag].feeds.push(feed)
        // @todo: set node.status to [success,warning,danger] dependant on feed interval and feed last_update time
        // console.log((new Date().valueOf() / 1000) - nodes[feed.tag].lastupdate)
        
        if ((new Date().valueOf() / 1000) - nodes[feed.tag].lastupdate < 400) {
            nodes[feed.tag].status = 'success'
        }
        if ((new Date().valueOf() / 1000) - nodes[feed.tag].lastupdate > 10000) {
            nodes[feed.tag].status = 'danger'
        }
    }
    nodes = Object.values(nodes)
    return nodes
}

function selectAll() {
    // todo: mark checked all checkboxes with class select-feed
}
function collapseAll() {
    // todo: mark all accordions as collapse
}

// JQUERY TO CALL BOOTSTRAP JAVASCRIPT 
$(function () {
    
    $('[data-toggle="popover"]').popover()
    // store accordion state as property of the nodes array item
    $(document).on('show.bs.collapse hide.bs.collapse', function(event) {
        var key = $(event.target).data('key');
        var value = nodes[key].collapsed || true
        list.nodes[key].collapsed = !value;
    })

    $(document).on('click', 'input.select-feed', function(event) {
        for (z in nodes) {
            for (x in nodes[z].feeds) {
                if (nodes[z].feeds[x].id === event.target.dataset.id) {
                    list.nodes[z].feeds[x].selected = nodes[z].feeds[x].selected !== true;
                }
            }
        }
    })
})
    
</script>
