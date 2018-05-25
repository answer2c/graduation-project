$(function(){
    $(".detail").hide();
})

    function  logout(){
     
        var r=confirm("确定退出吗？");    
        if(r==true){
            window.location="http://www.answer2c.cn/book/index/logout";
        }else{
            return false;
        }
   
    }




    $("#reset").click(function(){
        window.location="/book/index/upload";
    })
