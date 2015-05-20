/*
To change this license header, choose License Headers in Project Properties.
To change this template file, choose Tools | Templates
and open the template in the editor.
*/
/* 
    Created on : Jan 14, 2015, 2:47:46 PM
    Author     : jinxinzhi
*/

jQuery(".gk-bp-trend-drems .see-more").click(function(){
    var page_input = jQuery(".gk-bp-trend-drems #trend-drem-page");
    var page = parseInt(page_input.val());
    
    jQuery.post(ajaxurl, {
        action: 'see_more_trend',
        'cookie': bp_get_cookies(),
        data: {page : page}
    },
    function(response) {
       if (response == "false"){
           jQuery(".gk-bp-trend-drems .see-more").css("display", "none");
       }else{
           var more_flag = response.more_flag;
           var trend_html = response.trend_html;
           jQuery(".gk-bp-trend-drems .trend-container").append(trend_html);
           if (more_flag == false){
               jQuery(".gk-bp-trend-drems .see-more").css("display", "none");
           }
           page_input.val(page + 1);
       }
    }, 'json');
});