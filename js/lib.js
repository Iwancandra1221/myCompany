var win = null;
var win_target = '';
jQuery.fn.SearchBox = function(param){
    var target = $(this).attr('target');
    if(target != undefined && target != '')
    {
        var _this = $(this);
        //caller = $(this);
        var title = $(this).attr('targettitle');
        if(title == undefined || title == '')
            title = 'Search';
        var btn = $('<button class="btnLink" type="button"><i class="fa fa-search"></i></button>');
        var w = 800;
        var h = 400;
        var left = (screen.width/2)-(w/2);
        var top = (screen.height/2)-(h/2);

        btn.click(function(){
            $(".loading").show();
            if(win != null && win.location != null && win_target == target)
            {
                win.focus();
                $(".loading").hide();
                return;
            }
            win_target = target;
            var caller = $(this).parent().find('input[type=text]');
            win = window.open(target,title,"toolbar=no, scrollbars=yes, resizable=yes, top=" + top +", left=" + left +", width="+ w + ", height="+h);
            $(win.window).load(function(){
                $(win.document).contents().find('#tblData').on('click','tbody tr',function(){
                    var tds = $(this).find('td');
                    var id = tds[0];
                    $(caller).val($(id).html());
                    win.close();
                    if(param != undefined)
                    {
                        if(param.after != undefined)
                        {
                            param.after(_this);
                        }
                    }
                });
            });
            win.focus();
            $(".loading").hide();
        });
        $(this).after(btn);
        if(param != undefined)
        {
            if(param.onchange != undefined)
            {
                $(this).keypress(param.onchange);
            }
        }
    }
}

jQuery.fn.MultiSearchBox = function(param){
    var target = $(this).attr('target');
    if(target != undefined && target != '')
    {
        var _this = $(this);
        //caller = $(this);
        var title = $(this).attr('targettitle');
        if(title == undefined || title == '')
            title = 'Search';
        var w = 800;
        var h = 400;
        var left = (screen.width/2)-(w/2);
        var top = (screen.height/2)-(h/2);

        $(this).click(function(){
            $(".loading").show();
            if(win != null && win.location != null && win_target == target)
            {
                win.focus();
                $(".loading").hide();
                return;
            }
            win_target = target;
            win = window.open(target,title,"toolbar=no, scrollbars=yes, resizable=yes, top=" + top +", left=" + left +", width="+ w + ", height="+h);
            $(win.window).load(function(){
                if(param.actionBtn != undefined)
                {
                    var datas = new Array();
                    $(win.document).contents().find(param.actionBtn).click(function(){
                        $(win.document).contents().find("#tblData tbody input[type=checkbox]:checked").each(function(){
                            datas.push($(this).val());
                            
                        });
                        if(param.after != undefined)
                        {
                            param.after(_this,datas);
                        }
                        win.close();
                    });
                }
            });
            win.focus();
            $(".loading").hide();
        });
    }
}

jQuery.fn.PopUpForm = function(param){
    this.click(function(){
        if(param.target != undefined)
        {
            $(param.target).fadeIn();
            // $(param.target).css("left",(($(window).width()-$(param.target).width())/2));
            // $(param.target).css("top",(($(window).height()-$(param.target).height())/2));
            if(param.after != undefined)
            {
                $(".popup").fadeIn(param.after($(this)));
                $(".popupForm").fadeIn(param.after($(this)));
            }
            else
                $(".popupForm").fadeIn();
        }
    });
};

jQuery.displayInfo = function(message, okfunc){
    $("#infoMsg").html(message);
    $("#infoPanel").fadeIn(function(){
        $("#infoContent").css("left",(($(window).width()-$("#infoContent").outerWidth(true))/2));
        $("#infoContent").css("top",(($(window).height()-$("#infoContent").outerHeight(true))/2)-50);
        $("#infoContent").show();
        $("#btnOkinfo").focus();
        $("#btnOkinfo").unbind( "click" );
        $("#btnOkinfo").click(function(){
            if(okfunc != undefined)
                $("#infoPanel").fadeOut(okfunc);
            else
                $("#infoPanel").fadeOut();
        });
    });
};


jQuery.displayError = function(message, okfunc){
    $("#errorMsg").html(message);
    $("#errorPanel").fadeIn(function(){
        $("#errorContent").css("left",(($(window).width()-$("#errorContent").outerWidth(true))/2));
        $("#errorContent").css("top",(($(window).height()-$("#errorContent").outerHeight(true))/2)-50);
        $("#errorContent").show();
        $("#btnOkError").unbind( "click" );
        $("#btnOkError").focus();
        
        $("#btnOkError").click(function(){
            $("#errorContent").hide();
            if(okfunc != undefined)
                $("#errorPanel").fadeOut(okfunc);
            else
                $("#errorPanel").fadeOut();
        });        
    });
};

jQuery.displayConfirm = function(message,okfunc,nofunc){
    $("#confirmMsg").html(message);
    $("#confirmPanel").fadeIn(function(){
        $("#confirmContent").css("left",(($(window).width()-$("#confirmContent").outerWidth(true))/2));
        $("#confirmContent").css("top",(($(window).height()-$("#confirmContent").outerHeight(true))/2)-50);
        $("#confirmContent").show();
        $("#btnYesConfirm").focus();
        $("#btnYesConfirm").unbind( "click" );
        $("#btnYesConfirm").click(function(){
            $("#confirmPanel").fadeOut(okfunc);
        });
        $("#btnNoConfirm").unbind( "click" );
        $("#btnNoConfirm").click(function(){
            $("#confirmPanel").fadeOut(nofunc);
        });
    });
}

$.fn.dataTableExt.oApi.fnStandingRedraw = function(oSettings) {
    if(oSettings.oFeatures.bServerSide === false){
        var before = oSettings._iDisplayStart;
        oSettings.oApi._fnReDraw(oSettings);
        // iDisplayStart has been reset to zero - so lets change it back
        oSettings._iDisplayStart = before;
        oSettings.oApi._fnCalculateEnd(oSettings);
    }
    // draw the 'current' page
    oSettings.oApi._fnDraw(oSettings);
};

function errorAjax(jqXHR, exception, errorThrown){
  if (jqXHR.status === 0) {
            $.displayError('Not connect.\nVerify Network.');
        } else if (jqXHR.status == 404) {
            $.displayError('Requested page not found. [404]');
        } else if (jqXHR.status == 500) {
            $.displayError('Internal Server Error [500].');
        } else if (exception === 'parsererror') {
            $.displayError('Requested JSON parse failed.');
        } else if (exception === 'timeout') {
            $.displayError('Time out error.');
        } else if (exception === 'abort') {
            $.displayError('Ajax request aborted.');
        } else {
            $.displayError('Uncaught Error.\n' + jqXHR.responseText);
        }
  $(".loading").hide();
 }

 jQuery.fn.SearchBox2 = function(param){
    var target = $(this).attr('target');
    if(target != undefined && target != '')
    {
        var _this = $(this);
        //caller = $(this);
        var title = $(this).attr('targettitle');
        if(title == undefined || title == '')
            title = 'Search';
        var btn = $('<button class="btnLink" type="button"><i class="fa fa-search"></i></button>');
        var w = 800;
        var h = 400;
        var left = (screen.width/2)-(w/2);
        var top = (screen.height/2)-(h/2);

        btn.click(function(){
            $(".loading").show();
            if(win != null && win.location != null && win_target == target)
            {
                win.focus();
                $(".loading").hide();
                return;
            }
            win_target = target;
            var caller = $(this).parent().find('input[type=text]');
            win = window.open(target,title,"toolbar=no, scrollbars=yes, resizable=yes, top=" + top +", left=" + left +", width="+ w + ", height="+h);
            $(win.window).load(function(){
                $(win.document).contents().find('#tblData').on('click','tbody tr',function(){
                    var tds = $(this).find('td .POConfirm');
                    var id = tds[0];
                    $(caller).val($(id).val());
                    win.close();
                    if(param != undefined)
                    {
                        if(param.after != undefined)
                        {
                            param.after(_this);
                        }
                    }
                });
            });
            win.focus();
            $(".loading").hide();
        });
        $(this).after(btn);
        if(param != undefined)
        {
            if(param.onchange != undefined)
            {
                $(this).keypress(param.onchange);
            }
        }
    }
}