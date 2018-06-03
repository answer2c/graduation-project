<?php
    namespace app\common;
       class page{
        public  $uri;
        public $page;
        public $pagenum;


        function __construct($pagenum){
            $this->page=!empty($_GET['page'])?$_GET['page']:1;
            $this->pagenum = $pagenum;
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
              $list .="<li><a href='".$this->uri.$this->pre()."'><<</a></li>";

              $start = $this->page - floor($listnum / 2);
              $start = $start<1 ? 1 : $start;
              $end = $this->page + floor($listnum / 2);
              $end = $end > $this->pagenum ? $this->pagenum : $end;

              $num = $end - $start + 1;

              if ($num < $listnum && $start > 1){
                  $start = $start  - ($listnum - $num);
                  $start = $start<1 ? 1 : $start;
                  $num = $end - $start + 1;
              }

              if ($num < $listnum && $end < $this->pagenum){
                  $end = $end + ($listnum - $num);
                  $end = $end>$this->pagenum? $this->pagenum : $end;
              }

              for($i = $start ; $i < $this->page ; $i ++){
                  $list .= "<li><a href='{$this->uri}&page=$i'>".$i.'</a></li>';
              }
//              for($i = $listnum - 1;$i > 0;$i--){
//                  $page = $this->page - $i;
//                  if($page <= 1)continue;
//                 $list .= "<li><a href='{$this->uri}&page=$page'>".$page.'</a></li>';
//
//              }
              $list .= '<li><a href="#" class="disabled active btn-primary" readonly>'.$this->page.'</a></li>';

              for($j = $this->page + 1; $j <= $end ; $j++){
                  $list.="<li><a href='{$this->uri}&page=$j'>".$j.'</a></li>';
              }
//              for($i=1; $i<$listnum -1 ;$i++){
//                  $page=$this->page+$i;
//                  if($page>$this->pagenum) break;
//
//                  $list.="<li><a href='{$this->uri}&page=$page'>".$page.'</a></li>';
//
//              }

              $list .="<li><a href='".$this->uri.$this->next()."'>>></a></li>";
              return $list;
              
          }
          
   
 
          
        
         
        
        
        
   }