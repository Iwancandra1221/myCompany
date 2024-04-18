$(document).ready(function(){

     

    $(".loadingItem").click(function(){
        if($(".loading:visible").length == 0)
            $(".popupForm").fadeOut();
    });
    $(".loadingItem form").click(function(e){
        // e.preventDefault();
        e.stopPropagation();
    });
	$(".closePopUp").click(function(){
		$(".popupForm").fadeOut();
	});
    $('.maxtodaydtpicker').datetimepicker({
        lang:'en',
        timepicker:false,
        format:'Y-m-d',
        formatDate:'Y-m-d',
        maxDate: 0
    });
    $('.datepicker').datetimepicker({
        scrollMonth:false,
        lang:'en',
        timepicker:false,
        format:'d-M-Y',
        formatDate:'d-M-Y'
    });

     $('.datetimepicker').datetimepicker({
        scrollMonth:false,
        lang:'en',
        timepicker:true,
        format:'d-M-Y H:i:s',
        formatDate:'d-M-Y H:i:s'
    });

   /* $(document).on('datetimepicker','.datepicker',function(){
         scrollMonth:false,
        lang:'en',
        timepicker:false,
        format:'d-M-Y',
        formatDate:'d-M-Y'
    });*/

	$(".loadingItem").click(function()
    {
        if ($(".loading:visible").length==0)
		  $(".PopUpForm").fadeOut();
	});
    $(".loadingItem form").click(function(e)
    {
        e.stopPropagation();
    });
	$(".ClosePopUp").click(function()
    {
        if(win != null && win.location != null)
        {
            win.close();
        }
		$(".PopUpForm").fadeOut();
	});
    $('.maxtodaydtpicker').datetimepicker({
        lang:'en',
        timepicker:false,
        format:'Y-m-d',
        formatDate:'Y-m-d',
        maxDate: 0
    });
    $('.timepicker').datetimepicker({
        lang:'en',
        datepicker:false,
        timepicker:true,
        format:'H:i:s',
        formatDate:''
    });
    $('.yearpicker').datetimepicker({
        lang:'en',
        datepicker:false,
        format:'Y',
        formatDate:'Y'
    });

    $(".tabHead li.active").each(function(){
        var target = $(this).find('a').attr('href');
        $('.tabItem').hide();
        $(target).show();
    });

    $(".tabHead li a").click(function(e){
        e.preventDefault();
        $(".tabHead li").removeClass('active');
        $(this).parent().addClass('active');
        var target = $(this).attr('href');
        $('.tabItem').hide();
        $(target).show();
    });

});