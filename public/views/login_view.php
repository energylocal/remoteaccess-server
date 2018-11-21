<?php
$current_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
$base = getFullUrl('');
$next = str_replace($base,"",$current_url);
?>
<script src="js/jquery-1.11.3.min.js"></script>

<form id="login-form" action="/login">
  <div class="mt-5">
    <h2>Please Login</h2>
      <input autofocus id="username" name="username" type="text" placeholder="Username..."><br><br>
      <input id="password" name="password" type="password" placeholder="Password..."><br><br>
      <input id="next" name="next" type="hidden" value="<?php echo $next ?>">
      <button id="login" class="btn">Login</button>
      <div id="alert"></div>
    </div>
</form>

<script>
var base = "<?php echo $base ?>";
var blacklist = 'login,logout,another-disallowed-path'.split(',');
$("#login-form").submit(function(event) {
    var username = $("#username").val();
    var password = $("#password").val();
    var $form = $(this);
    event.preventDefault();
    $.ajax({
        type: 'POST', 
        url: base + 'auth', 
        data: $form.serialize(),
        dataType: 'json', 
        success: function(result){
            if (result.success) {
                var next = base; // default redirect to tld
                if (result.next) next = result.next; // if auth returned next redirect there
                if (blacklist.indexOf(base) > -1) next = base; // if next in blacklist go to tld
                console.log(next);
                location.replace(next); // remove redirect from browser history
            } else {
                $("#alert").html(result.message);
            }
        },
        error: function (xhr, status, error) {
            console.log('login error:', status)
        }
    });
});

</script>
