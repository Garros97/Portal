RegExp.quote = function(str) {
    return (str+'').replace(/[.?*+^$[\]\\(){}|-]/g, "\\$&");
};

$(function () {
    "use strict";
    //UI widgets
    $('.form-horizontal input[title], .form-horizontal textarea').each(function(){
        $(this).attr({
            'data-toggle': 'popover',
            'data-trigger': 'focus',
            'data-container': 'body',
            'title': 'Hinweis',
            'data-content': $(this).attr('title')
        });
    });
    $('[data-toggle="popover"]').popover({
        html: true
    });
    if (typeof jQuery.fn.datetimepicker === "function") {
        $('[data-provide="datepicker"]').datetimepicker({
            locale: "de",
            format: 'DD.MM.YYYY' //no time
        });
        $('[data-provide="datetimepicker"]').datetimepicker({
            locale: "de",
            sideBySide: true
        });
    }
    if (typeof jQuery.fn.msDropDown === "function") {
        $('[data-provide="dropdown"]').msDropDown();
    }
    if (typeof jQuery.fn.summernote === "function") {
        $('[data-provide="rich-text-editor"]').summernote({
            lang: 'de-DE',
            height: 200,
            'callbacks' : {
                onFocus: function () {
                    $(this).parent().addClass('col-md-10 no-padding animate-transition').removeClass('col-md-6');
                },
                onBlur: function () {
                    $(this).parent().addClass('col-md-6 animate-transition').removeClass('col-md-10 no-padding');
                }
            }
        });
    }

    //Fix the color of progress bars >45%
    $('.progress.progress-text-centered .progress-bar').filter(function() {
        return $(this).width() / $(this).parent().width() > 0.45;
    }).find('span').css({color: 'white'});

    //Fix affix'ed sidebar
    $(window).on('load resize', function () {
        $('#sidebar').width($('#sidebar-container').width());
    });

    //Quick Search
    $(document).on("keypress", function(e) {
        var path = window.location.pathname;
        var pathS = path.split("/");
        if(pathS[2] !== 'admin') return;
        // keys are not handled the same way on all operating systems so we have to check for the original key code
        if(e.altKey && (e.which == 115 || e.originalEvent.code == "KeyS")) {
            e.preventDefault();
            var base = "/" + pathS[1];
            if (pathS[3] === 'search') {
                var s = window.location.search;
                if (s.includes("ref=")) {
                    window.location.href = base + s.split("ref=")[1];
                } else {
                    $('.qs-box input').focus();
                }
                return;
            }
            window.location.href = base + "/admin/search?ref=" + path.replace(base, "");
        }
    });

    function col(s) {
        return "<td>" + s + "</td>";
    }
    function row(...args) {
        var deleteUserPrompt = "Account -u- wirklich l??schen?";
        var deleteRegistrationPrompt = "Wollen Sie diese Registrierung wirklich l??schen? Dadurch wird der Teilnehmende von diesem Projekt abgemeldet.";
        var editActionU = '<a href="' +window.rootRelative+'/admin/users/edit/-id-" class="btn btn-xs btn-default" title="Bearbeiten"><i aria-hidden="true" class="glyphicon glyphicon-pencil"></i></a>';
        var deleteActionU = '<form name="pu_-pid-" style="display:none;" method="post" action="'+window.rootRelative+'/admin/users/delete/-id-"><input type="hidden" name="_method" class="form-control" value="POST" /></form><a href="#" class="btn btn-xs btn-default" title="L??schen" onclick="if (confirm(&quot; Account -u- wirklich l??schen? &quot;)) { document.pu_-pid-.submit(); } event.returnValue = false; return false;"><i aria-hidden="true" class="glyphicon glyphicon-trash"></i></a>';
        var editActionR = editActionU.replace("pencil","eye-open").replace("users", "registrations").replace("Bearbeiten", "Anzeigen");
        var deleteActionR = deleteActionU.replace("trash", "remove").replace("users", "registrations").replace(deleteUserPrompt, deleteRegistrationPrompt).replace("L??schen", "Teilnehmenden abmelden").replace(/pu_/g, "pr_");
        var editActionG = editActionU.replace("users", "groups").replace("Bearbeiten", "Gruppe bearbeiten").replace("pencil", "th-large");
        var actionsU = editActionU.concat(deleteActionU);
        var actionsR = editActionR.concat(deleteActionR);
        var l = args.length - 2;
        var ur = args[l];
        var gid = args[l+1];
        var c = ur ? "u_row" : "r_row";
        var r = '<tr class="-c-">'.replace("-c-", c);

        for (var i = 0; i < l; i++) {
            r = r.concat(col(args[i]));
        }

        var actions = (ur ? actionsU : actionsR).replace(/-pid-/g, ur ? args[0] : (args[1] + (gid == -1 ? "" : gid))).replace(/-id-/g, ur ? args[0] : args[1]);
        if (ur) actions = actions.replace(/-u-/, args[3]);
        if (gid != -1) actions =  (editActionG + actions).replace(/-id-/, gid);
        r = r.concat(col(actions)).concat("</tr>");
        return r;
    }
    function uRow(uid, u) {
        var e = "-";
        return row(uid, e, e, u.username, u.email, u.first_name, u.last_name, e, true, -1);
    }
    function rRow(rid, r, gid) {
        var e = "";
        var gname = (gid == -1) ? "" : r["groups"][gid]["gname"];
        return row("&#8627;", rid, r.pname, e, e, e, e, gname, false, gid);
    }

    function loadresults(s, pid = 0, o = 0, pl = false) {
        $.get(window.rootRelative+"/admin/search/search-all", {q: s, o: o, pid: pid}).done(function(data) {
            var d = JSON.parse(data);
            var res = d["results"];
            var r = "";
            var n = Object.keys(d).length;
            var rC = 0;
            for (var uid in res) {
                if (res.hasOwnProperty(uid)) {
                    r = r.concat(uRow(uid, res[uid]));
                    var regs = res[uid]["registrations"];
                    for (var rid in regs) {
                        if (regs.hasOwnProperty(rid)) {
                            var groups = regs[rid]["groups"];
                            for (var gid in groups) {
                                if (groups.hasOwnProperty(gid)) {
                                    r = r.concat(rRow(rid, res[uid]["registrations"][rid], gid));
                                    rC++;
                                }
                            }
                        }
                    }
                }
            }
            window.resultsTotal += rC;
            if (rC == 0) window.resultsTotal = 0;
            if (o == 0) {
                window.sContainer.html(r);
            } else {
                if (pl) {
                    window.nextPage = r;
                    return;
                }
                window.sContainer.append(r);
            }
        });
    }
    $(document).ready(function() {
        if ($(".scontainer").length !== 0 && $("#project-selector").length !== 0) {
            window.sContainer = $(".scontainer");
            window.pSel = $("#project-selector");
            window.lastQuery = null;
            window.pL = false;
            window.plL = false;
            window.nextPage = null;
            window.resultsTotal = 0;
            window.rootRelative = "/" + window.location.pathname.split('/')[1];

            $('.qs-box input[type="text"]').keyup(function(event) {
                var s = $(this).val();
                if((s.length > 2 || $.isNumeric(s)) && s != window.lastQuery) {
                    window.resultsTotal = 0;
                    loadresults(s, window.pSel.val());
                    window.lastQuery = s;
                }  else if (!s.length) {
                    window.sContainer.empty();
                    window.lastQuery = null;
                    window.resultsTotal = 0;
                    window.nextPage = null;
                }
            });
        }
    });
    $(window).scroll(function () {
        if (typeof window.sContainer === "undefined" || window.sContainer == null || window.resultsTotal == 0) return;
        if ($(window).scrollTop() + $(window).height() >= window.sContainer.offset().top + window.sContainer.height()
            && !window.pL && window.lastQuery != null) {
            // load new elements and append to container
            if (window.nextPage == null) {
                loadresults(window.lastQuery, window.pSel.val(), window.resultsTotal);
            } else {
                window.sContainer.append(window.nextPage);
                window.nextPage = null;
                window.plL = false;
            }
            window.pL = true;
            setTimeout(function(){window.pL = false;}, 200);
        }
        if (window.lastQuery != null && window.nextPage == null && !window.plL) {
            window.plL = true;
            loadresults(window.lastQuery, window.pSel.val(), window.resultsTotal, true);
        }
    });
});
