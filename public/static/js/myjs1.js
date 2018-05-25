 
    function  logout(){
     
        var r=confirm("确定退出吗？");    
        if(r==true){
            window.location="http://www.answer2c.cn/book/index/logout";
        }else{
            return false;
        }
   
    }


           $(function(){
               $(".detail").hide();
           })
     
     