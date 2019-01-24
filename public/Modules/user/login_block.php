<?php
/*
 All Emoncms code is released under the GNU Affero General Public License.
 See COPYRIGHT.txt and LICENSE.txt.

 ---------------------------------------------------------------------
 Emoncms - open source energy visualisation
 Part of the OpenEnergyMonitor project:
 http://openenergymonitor.org
 */

// no direct access
defined('EMONCMS_EXEC') or die('Restricted access');

global $path;

?>
<style>
  .main {
    max-width: 320px;
    margin: 0 auto;
    padding: 10px;
  }
  
</style>
<script type="text/javascript" src="<?php echo $path; ?>Modules/user/user.js?v=2"></script>
<br>

<div class="main">
  <div class="well">
    <img src="<?php echo $path; ?>Theme/basic/logo_login.png" alt="Login" width="256" height="46" />
    
    <div style="color:#666; padding-top:10px; padding-bottom:10px">Remote Access (Beta)</div>
        
    <div class="login-container">
        <div id="login-form">
            <div id="loginblock">
                <div class="form-group register-item" style="display:none">
                    <label><?php echo _('Email'); ?>
                        <input type="text" name="email" tabindex="1"/>
                    </label>
                </div>

                <div class="form-group">
                    <label><?php echo _('Username'); ?>
                        <input type="text" tabindex="2" autocomplete="on" name="username"  />
                    </label>
                </div>

                <div class="form-group">
                    <label><?php echo _('Password'); ?>
                        <input type="password" tabindex="3" autocomplete="on" name="password" />
                    </label>
                </div>

                <div id="loginmessage"></div>

                <div class="form-group login-item">
                    <button id="login" class="btn btn-primary" tabindex="6" type="submit"><?php echo _('Login'); ?></button>
                </div>

            </div>
        </div>
    </div>
  </div>
</div>

<script>
"use strict";
var path = "<?php echo $path; ?>";
$("body").addClass("body-login");

$("#cancel-link").click(function(){
    $(".login-item").show();
    $("#loginmessage").html("");
    return false;
});

$('input').on('keypress', function(e) {
    //login or register when pressing enter
    if (e.which == 13) {
        e.preventDefault();
        login();
    }
});

$('#login').click(function() { login(); });

function login(){
    var username = $("input[name='username']").val();
    var password = $("input[name='password']").val();

    var result = user.login(username,password);

    if (result.success==undefined) {
        $("#loginmessage").html("<div class='alert alert-error'>"+result+"</div>");
        return false;
    
    } else {
        if (result.success) {
            location.replace(result.next);
            return true;
        } else {
            $("#loginmessage").html("<div class='alert alert-error'>"+result.message+"</div>");
            return false;
        }
    }
}

var user = {
  'login':function(username,password)
  {
    var result = {};
    $.ajax({
      type: "POST",
      url: path+"user/auth",
      data: "&username="+encodeURIComponent(username)+"&password="+encodeURIComponent(password),
      dataType: "text",
      async: false,
      success: function(data_in)
      {
         try {
             result = JSON.parse(data_in);
             if (result.success==undefined) result = data_in;
         } catch (e) {
             result = data_in;
         }
      },
      error: function (xhr, ajaxOptions, thrownError) {
         result = xhr.status+" "+thrownError;
      }
    });
    return result;
  }
}

</script>
