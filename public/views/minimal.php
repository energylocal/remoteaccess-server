<h2>Hello World</h2>
<p>Emoncms is a powerful open-source web-app for processing, logging and visualising energy, temperature and other environmental data.</p>

<style>
tr { cursor:pointer }
</style>

<div class="alert alert-warning">
  <strong>Loading:</strong> Remote feed list, please wait 5 seconds...
</div>

<table id="feeds" class="table table-hover"></table>

<script>
var session = <?php echo json_encode($session); ?>;
var settings = <?php echo json_encode($settings); ?>;

</script>