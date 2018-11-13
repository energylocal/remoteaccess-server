<?php global $path; ?>

<script src="js/jquery-1.11.3.min.js"></script>

<div id="login-block">
  <br><br>
  <div class="login-box">
    
    <h2>Please Login</h2>
    <p>
      <input id="username" type="text" placeholder="Username..."><br><br>
      <input id="password" type="password" placeholder="Password..."><br><br>
      <button id="login" class="btn">Login</button>
    </p>
    <div id="alert"></div>
  </div>
</div>

<script>
var path = "<?php echo $path; ?>";

$("#login").click(function() {
    var username = $("#username").val();
    var password = $("#password").val();
    
    $.ajax({ type: 'POST', url: path+"auth", data: "username="+username+"&password="+password, dataType: 'json', async: false, success: function(result){
        if (result.success) {
            window.location = path;
        } else {
            $("#alert").html(result.message);
        }
    }});
});

</script>
