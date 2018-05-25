    
    function  logout(){
     
        var r=confirm("确定退出吗？");    
        if(r==true){
            window.location="http://www.answer2c.cn/book/index/logout";
        }else{
            return false;
        }
   
    }
   
    
        $(".ajaxcheck").blur(function(){
           var pv=$(this).val();
           $.ajax({
               type:"POST",
               url:"/book/index/ajaxcheck",
               data:{username:pv},
               success:function(data){
                   $("#nametip").html(data);
               
               }
           })
         
           })
   
   
   
           $("#sub").click(function(){
               var tag=1;
               $(".tip").each(function(){
                   if($(this).val()!=""){
                       tag=0;
                   }
               });
              $("input").each(function(){
                  if($(this).val()==""){
                      tag=0;
                      $(this).next().html("不能为空");
                  }
              })
              alert(tag);
               if(tag==1){
                  
                   $("#myform").submit();
               }
            
              
           })
     
     