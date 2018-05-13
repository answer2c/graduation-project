<?php
    namespace app\common;
       class page{
        public  $uri;
        public $page;
        public $pagenum;


        function __construct(){
            $this->page=!empty($_GET['page'])?$_GET['page']:1;
            $this->uri=$this->geturi();
        }
        

        /**
         *  @return string 返回去掉page参数的url
         */
      private function geturi(){
          
           $uri=$_SERVER['REQUEST_URI'].(strpos($_SERVER['REQUEST_URI'],'?')?'':'?');
            $query=parse_url($uri); //把url转化为数组               
            if(isset($query['query'])){ //若url中存在参数部分
                parse_str($query['query'],$params); //把参数部分赋值给一个数组
                unset($params['page']);        //去掉存在的 page参数
                $uri=$query['path'].'?'.http_build_query($params);
             }
            return $uri;                 
        }

    
           /**
            * 返回当前已显示的总条数   
            */
          
          private function end(){
              return min($this->total,$this->page*$this->rows);
              
          }
          /**
           * 返回当前页面开始的记录
           * @return number
           */
          private function start(){
              if($this->page==1){
                  return 1;
              }else
              return ($this->page-1)*$this->rows+1;
              
          }
          
          
          
          /**
           * 下一页
           * @return string
           */
          public function next(){
              if($this->page==$this->pagenum)
                  return "&page={$this->pagenum}";
               else 
                   return "&page=".($this->page+1);
          }
          
          /**
           * 上一页
           * @return string
           */
          public function pre(){
              if($this->page==1)
                  return "&page=1";
              else 
                  return "&page=".($this->page-1);
          }
          
          
 
   
          /**
           * 页面列表
           * @return string|number
           */
         public function pagelist(){
              $listnum = 4;
              $list = "";
              for($i = $listnum;$i > 0;$i--){
                  $page = $this->page - $i;
                  if($page <= 0)continue;
                 $list .= "<li><a href='{$this->uri}&page=$page'>".$page.'</a></li>';
                 
              }
              $list .= '<li><a href="#" class="disabled">'.$this->page.'</a></li>';

              for($i=1;$i<$listnum;$i++){
                  $page=$this->page+$i;
                  if($page>$this->pagenum) break;
                  
                  $list.="<li><a href='{$this->uri}&page=$page'>".$page.'</a></li>';

              }
              return $list;
              
          }
          
   
 
          
        
         
        
        
        
   }