<?php
    global $session;
    $menu_right[] = array('name'=> "Logout", 'icon'=>'icon-off icon-white', 'path'=>"user/logout", 'session'=>"read", 'order' => 1000);
    if (!$session['read']) $menu_right[] = array('name'=>"Log In", 'icon'=>'icon-home icon-white', 'path'=>"user/login", 'order' => 1000);
