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

var client = mqtt.connect(options.host, options)

var feedid = <?php echo $feedid; ?>;
var feedname = "<?php echo $feedname; ?>";
var path = "<?php echo $path; ?>";


</script>

