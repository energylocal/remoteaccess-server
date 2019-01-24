<?php
$current_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
$base = getFullUrl('');
$next = str_replace($base,"",$current_url);
?>

<form id="login-form" action="/login">
    <div class="mt-5">
        <h2 class="mb-3">Please Login</h2>
        <div class="form-group row">
            <label class="col-sm-3 col-md-2 col-xl-1" for="username">Username</label>
            <div class="col-sm-6 col-md-4">
                <input autofocus type="text" name="username" class="form-control" id="username" aria-describedby="userHelp" placeholder="Enter your EmonCMS username">
            </div>
            <small id="userHelp" class="form-text text-muted"></small>
        </div>
        <div class="form-group row">
            <label class="col-sm-3 col-md-2 col-xl-1" for="password">Password</label>
            <div class="col-sm-6 col-md-4">
                <input type="password" name="password" class="form-control" id="password" placeholder="Password...">
            </div>
        </div>
        <input id="next" name="next" type="hidden" value="<?php echo $next ?>">
        <button id="login" class="btn">Login</button>
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
        url: base + 'user/auth', 
        data: $form.serialize(),
        dataType: 'json', 
        success: function(result){
            if (result.success) {
                var next = base; // default redirect to tld
                if (result.next) next = result.next; // if auth returned next redirect there
                if (blacklist.indexOf(base) > -1) next = base; // if next in blacklist go to tld
                location.replace(next); // remove redirect from browser history
                
                //location.replace("/");
            } else {
                $("#userHelp").html(result.message);
            }
        },
        error: function (xhr, status, error) {
            console.log('login error:', error);
            var result = JSON.parse(xhr.responseText);
            $("#userHelp").html(result.message);
        }
    });
});

</script>
