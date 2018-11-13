<style>
    /* Q2.{emrys} how do i add css to the container template?? */
    .card .dropdown-toggle:after {transition: all .2s; margin-right:.3rem}
    .card .collapsed .dropdown-toggle:after {transform: rotate(180deg)}

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
        background-color: transparent!important;
    }
    .no-underline {
        text-decoration: none !important
    }

    /* show feed update status as red,yellow,green circle  - mobile only*/
    @media (max-width: 575px) {
        .list-group-item-success:before{
            background-color: #c3e6cb;
        }
        .list-group-item-danger:before{
            background-color: #c3e6cb;
        }
        .list-group-item-warning:before{
            background-color: #ffeeba;
        }
    }


</style>

<div id="feeds-navbar" class="d-flex justify-content-sm-between flex-wrap">
    <div id="page-title" class="d-flex align-items-start flex-nowrap">
        <h2 class="mb-1 mr-2 text-nowrap">Feed List</h2>
        <button id="toggle" class="btn btn-outline-secondary" data-status="disconnected" onclick="on_off(event)">connect</button>
    </div>
    <nav id="feedlist-buttons" class="btn-toolbar d-flex justify-content-end" role="toolbar" aria-label="feed buttons">
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

<p class="d-none d-sm-block">Emoncms is a powerful open-source web-app for processing, logging and visualising energy, temperature and other environmental data.</p>

<div id="loading" class="alert alert-warning">
<strong>Loading:</strong> Remote feed list, please wait 5 seconds...
</div>

<div id="feeds"></div>
<table id="feeds-old" class="table"></table>

<script src="js/jquery-1.11.3.min.js""></script>
<script src="js/bootstrap.bundle.min.js"></script>
<script src="js/mqtt.min.js"></script>
<script src="js/misc.js"></script>

<script>
var options = {
    username: '<?php echo $username; ?>', // load with AJAX would be better
    password: '<?php echo $password; ?>', // load with AJAX would be better
    clientId: 'mqttjs_' + '<?php echo $username; ?>' + '_' + Math.random().toString(16).substr(2, 8), // @todo: output 6 digit random hex number: eg a31bc1
    port: 8083,
    ejectUnauthorized: false,
    host: "wss://mqtt.emoncms.org"
}
// DEV ONLY SETTINGS
// var options = {
//     username: '<?php echo $username; ?>', // load with AJAX would be better
//     password: '<?php echo $password; ?>', // load with AJAX would be better
//     clientId: 'localDev_js',
//     port: 9001,
//     host: "ws://localhost"
// }
document.querySelector('#loading').classList.add('d-none')
var nodes = false;
var pubInterval = null

// auto connect on load:
connect()

function connect() {
    document.querySelector('#loading').classList.remove('d-none')
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
        draw(nodes)
        // disconnect()
    })
}

function draw(nodes) {
    var out = ''
    for (i in nodes) {
        let node = nodes[i]
        out += '<div class="card dropup mb-1" class="node.status">';
        out += '<div class="card-header p-0" id="heading' + node.id +'">';
        out += '<a href="#"';
        out += '    class="d-flex no-gutters text-body justify-content-between py-2 no-underline' + (node.collapsed !== false ? ' collapsed' : '') + '"';
        out += '    data-toggle="collapse" data-target="#collapse_' + (node.id) + '"';
        out += '    aria-controls="collapse_' + (node.id) + '"';
        out += '>';
        out += '  <div class="d-flex col justify-content-between">';
        out += '    <h5 class="col d-flex mb-0 col-md-8 col-xl-6">' + node.tag + ': '
        if (hasSelectedFeeds(node.tag) > 0) {
            out += '      <small class="font-weight-light text-muted"> (' + hasSelectedFeeds(node.tag) + ')</small>';
        }
        out += '    </h5>';
        out += '    <div class="col d-none d-sm-block ml-4 pl-4 pl-md-3 pl-lg-2 ml-xl-5 pl-xl-3 text-muted">' + prettySize(node.size) + '</div>';
        out += '  </div>';
        out += '  <div class="col text-truncate dropdown-toggle d-none d-sm-block col-3 text-right">';
        out += list_format_updated(node.lastupdate);
        out += '  </div>';
        out += '</a>'
        out += '</div>' // end of .card-header
        
        out += '<div id="collapse_' + node.id + '" data-key="' + i + '"';
        out += 'class="collapse ' + (node.collapsed !== false ? '' : ' show') + '" aria-labelledby="heading' + node.id + '">';
        out += '<ul class="list-group list-group-flush">';
        out += getFeedsHtml(node.feeds)
        out += '</ul>'; // end of .list-group
        out += '</div>'; // end of .collapse
        out += "</div>" // end of .card
    }
    document.querySelector('#feeds').innerHTML = out;
    document.querySelector('#loading').classList.add('d-none')
}

function getFeedsHtml(feeds) {
    var out = "";
    for (var z in feeds) {
        var row = "";
        var feed = feeds[z];
        var icon_id = feed.public ? "#lock-locked" : '#lock-unlocked';
        row += '<div class="d-flex justify-content-between no-gutters">';
        row += '  <div class="col col-8 col-md-9">';
        row += '    <div class="row no-gutters">';
        row += '      <div class="pl-3 pull-left">';
        row += '        <div class="custom-control custom-checkbox text-center">';
        row += '          <input id="' + 'select-feed-' + feed.id + '" ' + (feed.selected ? ' checked' : '') + ' data-id="' + feed.id + '" class="custom-control-input select-feed" type="checkbox" aria-label="select this item">';
        row += '          <label for="' + 'select-feed-' + feed.id + '" class="custom-control-label position-absolute"></label>';
        row += '        </div>';
        row += '      </div>';
        row += '      <div class="col text-truncate pl-1 col-md-4 col-lg-5 col-xl-4" title="' + feed.name + '">' + feed.name + '</div>';
        row += '      <div class="d-none col d-none d-sm-flex col-5 col-md-7 col-lg-5 col-xl-4">';
        row += '        <div class="d-none d-sm-block pull-left" title="' + (feed.public ? 'Public': 'Private') + '">';
        row += '            <svg viewBox="0 0 8 8" width="16px" height="16px" style="fill:currentColor"><use href="' + icon_id +'"></use></svg>';
        row += '        </div>';
        row += '        <div class="col d-none d-md-block text-truncate col-5 col-md-6" title="' + getEngineName(feed) + '">' + getEngineName(feed) + '</div>';
        row += '        <div class="col d-none d-sm-block col-6 col-sm-10 col-md-4">' + prettySize(feed.size) + '</div>';
        row += '      </div>';
        row += '    </div>';
        row += '  </div>';
        row += '  <div class="col col-sm-4 col-md-3">';
        row += '    <div class="row no-gutters">';
        row += '      <div class="col text-right text-truncate pr-2">' + list_format_value(feed.value) + 
                        (list_format_value(feed.value) !== 'NULL' ? ' ' + feed.unit : '');
        row += '      </div>';
        row += '      <div class="col col-6 col-md-4 text-right d-none d-sm-block">' + list_format_updated(feed.time) + '</div>';
        row += '    </div>';
        row += '  </div>';
        row += '</div>';


        out += '<li class="list-group-item pl-0 ' + getFeedClass(feed) +'" data-toggle="popover" title="' + feed.id + '" data-content="@todo: fill tooltip">'
        out += row
        out += '</li>';
    }
    return out
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
        size = new Number(bytes/1000000000).toFixed(decimals) + ' GB';
    } else if (length > 6) {
        size = new Number(bytes/1000000).toFixed(decimals) + ' MB';
    } else if (length > 3) {
        size = new Number(bytes/1000).toFixed(decimals) + ' KB';
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
            nodes[key].collapsed = !nodes[key].collapsed;
        })

        $(document).on('click', 'input.select-feed', function(event) {
            for (z in nodes) {
                for (x in nodes[z].feeds) {
                    if (nodes[z].feeds[x].id === event.target.dataset.id) {
                        nodes[z].feeds[x].selected = nodes[z].feeds[x].selected !== true;
                    }
                }
            }
            draw(nodes)
        })
    })
    
</script>
    
    
<script>
    var fake = '<tbody><tr><td>21</td><td>solarmodel</td><td>node abc</td><td><span style="color:rgb(255,0,0);">inactive</span></td><td>23</td></tr><tr><td>22</td><td>Power Example</td><td>node abc123</td><td><span style="color:rgb(255,0,0);">inactive</span></td><td>100</td></tr><tr><td>80</td><td>test 1</td><td>node abc123</td><td><span style="color:rgb(255,0,0);">inactive</span></td><td>100</td></tr><tr><td>81</td><td>node:emontx:power1</td><td>Node emontx</td><td><span style="color:rgb(255,0,0);">n/a</span></td><td>NULL</td></tr><tr><td>82</td><td>New Virtual Feed</td><td>Virtual</td><td><span style="color:rgb(255,0,0);">n/a</span></td><td>NULL</td></tr><tr><td>83</td><td>test description</td><td>Node emontx</td><td><span style="color:rgb(255,0,0);">inactive</span></td><td>100</td></tr><tr><td>84</td><td>abc</td><td>Node emontx</td><td><span style="color:rgb(255,0,0);">inactive</span></td><td>0</td></tr><tr><td>85</td><td>Power</td><td>Test</td><td><span style="color:rgb(255,0,0);">n/a</span></td><td>NULL</td></tr><tr><td>86</td><td>use</td><td>1</td><td><span style="color:rgb(255,0,0);">6 hrs</span></td><td>110</td></tr><tr><td>87</td><td>use_kwh</td><td>1</td><td><span style="color:rgb(255,0,0);">n/a</span></td><td>NULL</td></tr><tr><td>88</td><td>use</td><td>emontx</td><td><span style="color:rgb(255,0,0);">inactive</span></td><td>100</td></tr><tr><td>89</td><td>use_kwh</td><td>emontx</td><td><span style="color:rgb(255,0,0);">inactive</span></td><td>0</td></tr></tbody>'
    $("#feeds-old-dev-only").html(fake)
</script>