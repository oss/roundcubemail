function forward_add() {

  	var email = document.getElementById("email").value;
  	var localcopy_check = document.getElementById("localcopy-check").value;

    if (validateEmail()) {
        rcmail.http_post("plugin.webtoolsforward-post", {
            func: "add",
            value: email,
            check: localcopy_check
        })
    } else {
        $.pnotify({
            pnotify_title: "Invalid",
            pnotify_text: "You must enter a valid email address.",
            pnotify_type: "error",
            pnotify_animation: "slide",
            pnotify_height: "100px"
        })
    }
}

function forward_remove_all() {
    rcmail.http_post("plugin.webtoolsforward-post", {
        func: "delall"
    })
}

function forward_remove(a) {
    rcmail.http_post("plugin.webtoolsforward-post", {
        func: "del",
        value: a
    })
}

function forward_chtype() {
    var a = $("#localcopy-check").attr("checked");
    rcmail.http_post("plugin.webtoolsforward-post", {
        func: "chtype",
        value: a
    })
}

function delall_dialog() {
    $("#dialog-test").dialog("open")
}

function forward_post_callback(c) {
    if (c.func == "add") {
        if (c.alreadyexists == "true") {
            $.pnotify({
                pnotify_title: "Failed",
                pnotify_text: "You're already forwarding to that address!",
                pnotify_type: "error",
                pnotify_animation: "slide",
                pnotify_height: "100px"
            });
            return
        }
        if (c.youremail == "true") {
            $.pnotify({
                pnotify_title: "Failed",
                pnotify_text: "You can't forward to your own address!",
                pnotify_type: "error",
                pnotify_animation: "slide",
                pnotify_height: "100px"
            });
            return
        }
        if (c.no_mxr == "true") {
            $.pnotify({
                pnotify_title: "Failed",
                pnotify_text: "Mail server doesn't seem to exist!",
                pnotify_type: "error",
                pnotify_animation: "slide",
                pnotify_height: "100px"
            });
            return
        }
        var b;
        var a = $("#localcopy-row");
        if (a.prev().attr("class") == "rowstyle0") {
            b = "rowstyle1"
        } else {
            b = "rowstyle0"
        }
        a.before("<tr style='display:none' class=\"" + b + '" id="' + c.id + '"><td><a href="#" onclick="forward_remove(\'' + c.value + '\')"><img src="plugins/webtools/webtools/forward/delete.png" /></a></td><td><p class="emailstyle">' + c.value + "</p></td></tr>");
        $("#" + c.id).fadeIn();
        $("#email").attr("value", "");
        num_forwards++
    } else {
        if (c.func == "del") {
            $("#" + c.id).fadeOut();
            num_forwards--
        } else {
            if (c.func == "chtype") {
                $.pnotify({
                    pnotify_title: "Success",
                    pnotify_notice_icon: "ui-icon ui-icon-check",
                    pnotify_text: "Setting saved!",
                    pnotify_animation: "slide",
                    pnotify_height: "100px"
                })
            } else {
                if (c.func == "delall") {
                    $("#forward-table tr").each(function(d) {
                        if ((this.getAttribute("class") == "rowstyle0") || (this.getAttribute("class") == "rowstyle1")) {
                            $(this).fadeOut()
                        }
                    });
                    $.pnotify({
                        pnotify_title: "Success",
                        pnotify_notice_icon: "ui-icon ui-icon-check",
                        pnotify_text: "Your forward(s) have been removed successfully.",
                        pnotify_animation: "slide",
                        pnotify_height: "100px"
                    });
                    num_forwards = 0
                }
            }
        }
    } if (num_forwards == 0) {
        $("#removeall").hide();
        $("label[for=localcopy-check],#localcopy-check").hide();
        $("#localcopy-check").attr("checked", false)
    } else {
        $("#removeall").show();
        $("label[for=localcopy-check],#localcopy-check").show()
    }
}

function validateEmail() {
    var c = $("#email");
    var b = c.val();
    var d = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.(?:[a-zA-Z]{2}|com|org|net|edu|gov|mil|biz|info|mobi|name|aero|asia|jobs|museum)$/;
    if (d.test(b)) {
        c.removeClass("ui-state-error");
        return true
    } else {
        c.addClass("ui-state-error");
        return false
    }
}
rcmail.addEventListener("plugin.forward-post-callback", forward_post_callback);
$(document).ready(function() {
    $("#email").bind("keyup", function(b) {
        validateEmail();
        var a = b.keyCode || b.which;
        if (a == 13) {
            forward_add()
        }
    });
    $("#email").change(validateEmail);
    $("#dialog-test").dialog({
        modal: true,
        resizable: false,
        autoOpen: false,
        buttons: {
            "Delete All": function() {
                forward_remove_all();
                $(this).dialog("close")
            },
            Cancel: function() {
                $(this).dialog("close")
            }
        }
    })
});
