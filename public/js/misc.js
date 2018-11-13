function list_format_updated(time) {
    time = time * 1000;
    var servertime = new Date().getTime(); // - table.timeServerLocalOffset;
    var update = new Date(time).getTime();
    
    var secs = (servertime - update) / 1000;
    var mins = secs / 60;
    var hour = secs / 3600;
    var day = hour / 24;
    
    var updated = secs.toFixed(0) + "s";
    if (update == 0 || !$.isNumeric(secs)) updated = "n/a";
    else if (secs < 0) updated = secs.toFixed(0) + "s";
    // update time ahead of server date is signal of slow network
    else if (secs.toFixed(0) == 0) updated = "now";
    else if (day > 7) updated = "inactive";
    else if (day > 2) updated = day.toFixed(1) + " days";
    else if (hour > 2) updated = hour.toFixed(0) + " hrs";
    else if (secs > 180) updated = mins.toFixed(0) + " mins";
    
    secs = Math.abs(secs);
    var color = "rgb(255,0,0)";
    if (secs < 25) color = "rgb(50,200,50)";
    else if (secs < 60) color = "rgb(240,180,20)";
    else if (secs < 3600 * 2) color = "rgb(255,125,20)";
    
    return "<span style='color:" + color + ";'>" + updated + "</span>";
}

function list_format_value(value) {
    if (value == null) return "NULL";
    value = parseFloat(value);
    if (value >= 1000) value = parseFloat(value.toFixed(0));
    else if (value >= 100) value = parseFloat(value.toFixed(1));
    else if (value >= 10) value = parseFloat(value.toFixed(2));
    else if (value <= -1000) value = parseFloat(value.toFixed(0));
    else if (value <= -100) value = parseFloat(value.toFixed(1));
    else if (value < 10) value = parseFloat(value.toFixed(2));
    return value;
}
