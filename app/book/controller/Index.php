<?php
    namespace app\book\controller;
    use \think\Controller;
    use \think\Session;
    use \lib\Qq;
    use \think\Request;
    use \think\Db;
    use app\book\model\User;

    class Index extends Controller{
        public static $redirect_uri='http://www.answer2c.cn/book/index/callback';

        public function index(){

            $login=$this->checkUser();
            $this->assign('username',Session::get('username'));
            $this->assign('loginMess',$login);
            $this->assign('redirect_uri',urlencode(self::$redirect_uri));
            
            $bookset=Db::table('book')->order('borrow_num desc')->limit(5)->select();
            // dump($bookset);
            for($i=0;$i<count($bookset);$i++){
                $this->assign('book'.($i+1).'img',$bookset[$i]['img']);
                $this->assign('book'.($i+1).'name',$bookset[$i]['bookname']);
            }

            return $this->fetch();
        }



        public function login(Request $request){
            $username=$request->post('username');
            $pwd=md5($request->post('pwd'));
            $result=Db::table('user')->where("username",$username)->where("passwd",$pwd)->select();
            
            if($result==null){
                echo '<script>alert("用户名密码不正确");</script>';
            }else{         
               $src=$result[0]['img'];
               Session::set('username',$username);
               Session::set('ouruser','yes');
               Session::set('touxiang',$src);
               $this->redirect('/book');
            }
        }

      
        public function regi(Request $request){   
                return $this->fetch();      
        }


        /**
         * 注册添加用户
         */
        public function adduser(Request $request){
           if($request->post('username')){
                $addUser=new User();
                $addUser->username=$request->post('username');
                $addUser->passwd=md5($request->post('pwd'));
                $addUser->sex=$request->post('sex');
                $addUser->tel=$request->post('tel');
                $addUser->email=$request->post('email');
                $addUser->idnum=$request->post('idnum');
                $addUser->regitime=time();
                $addUser->authority=1;
                $addUser->save();
                Session::set('username',$request->post('username'));
                echo "<script>alert('注册成功');</script>";
                $this->redirect('/book/index/index');
           }
        }



        /**
         * 第三方登录处理函数
         */

         public function callback(){

            if(isset($_GET['code'])){
                $appid="101458359";
                $app_secret="3a9ee71ad91cd47e5c7cee6de84f8c19";
                $url="http://www.answer2c.cn/book/index/callback";
                $qq=new Qq($appid,$app_secret,$url);
                $data=$qq->returnData();
               
               
                Session::set('username',$data['nickname']);
                Session::set('touxiang',$data['figureurl_qq_1']);
                $this->redirect('index');

            }else{
                exit('Request failed');
            }
         }



        //  data-toggle="modal" data-target="#myModal"

        /**
         * 检查是否登录
         */
        protected function checkUser(){
            if(Session::has('username')){
                if(Session::has('ouruser')){
                    $login='
                    <li><a href="#" id="touxiang"><img src="http://www.answer2c.cn/'.Session::get("touxiang").'" >'.Session::get("username").'</a></li>
                    <li><a href="#" onclick="logout()">注销</a></li>';
                }else{
                    $login='
                    <li><a href="#" id="touxiang"><img src="'.Session::get("touxiang").'" >'.Session::get("username").'</a></li>
                    <li><a href="#" onclick="logout()">注销</a></li>';
                  
                }
             
            }else{
                $login='<li><a href="#" data-toggle="modal" data-target="#myModal" >登录</a></li>
                <li><a href="/book/index/regi">注册</a></li>';
                
            }

            return $login;
        }


        /**
         * 注销
         */
        public function logout(){
            Session::clear();
            $this->redirect('index');

        }


        public function ajaxcheck(Request $request){
            if($request->post('username')){
          
                $name=$request->post('username');
                $result=Db::table('user')->where('username',$name)->select();
                if($result!=null){
                    echo '用户名已存在';
                }else{
                    echo "";
                }
            }else{
                echo "";
            }
          
        }


        /**
         * 借书页面
         */
        public function lend(){
            if(!Session::has('username')){
                echo "<script>alert('请先登录');window.location='/book/index';</script>";
            
            }else{
                return $this->fetch();
            }
           
        }

        /**
         * 上传书籍页面
         */
        public function upload(){
            if(!Session::has('username')){
                echo "<script>alert('请先登录');window.location='/book/index';</script>";
            
            }else{
                $login=$this->checkUser();
                $this->assign('username',Session::get('username'));
                $this->assign('loginMess',$login);
                return $this->fetch();
            }
        }


        /**
         * 添加书籍
         */
        public function addbook(Request $request){
        //    var_dump($_POST);
          
           $iftag=0;//判断该书籍的标签在tags表中是否有对应
           $isbn=$request->post('isbn');
           $bookname=$request->post('bookname');
           $author=$request->post('author');
           $publisher=$request->post('publisher');
           $pubdate=$request->post('pubdate');
           $booktag=explode(' ',$request->post('booktag'));
           $price=substr($request->post('price'),0,-3);
           $bookimg=$request->post('bookimg');


           //查看当前书籍信息是否存在
           $ifbook=Db::table('book')->where('isbn',$isbn)->select();
           if(!empty($ifbook)){
               //图书存在，判断该用户是否上传过该书，若无则更新upload表
               $ifupload=Db::table('upload')->where('username',Session::get('username'))->where('isbn',$isbn)->select();
               if(empty($ifupload)){
                $uploaddata=['username'=>Session::get('username'),'isbn'=>$isbn,'rent'=>'0'];
                $ifsuccess=Db::table('upload')->insert($uploaddata);
                if($ifsuccess!=1){
                    $this->error('上传失败');
                }
               }
       
             
           }else{
               //添加新的图书信息
                $bookdata=['isbn'=>$isbn,'author'=>$author,'publisher'=>$publisher,'pubdate'=>$pubdate,'price'=>$price,'bookname'=>$bookname,'img'=>$bookimg];
                Db::table('book')->insert($bookdata);
                //添加上传表数据
                $uploaddata=['username'=>Session::get('username'),'isbn'=>$isbn,'rent'=>'0'];
                Db::table('upload')->insert($uploaddata);

                //遍历图书的标签，查看该图书的标签在tags表中是否存在，若存在，则对应标签数量加1
                for($i=0;$i<count($booktag)-1;$i++){
                    $result=Db::table('tags')->where('tagname',$booktag[$i])->select();
                    if(!empty($result)){
                    // dump($result[0]);
                    $tagnumber=$result[0]['number'];
                    $num=$result[0]['sum']+1;
                    Db::table('tags')->where('tagname',$booktag[$i])->update(['sum'=>$num]);//更新标签的sum

                    $booktagdata=['isbn'=>$isbn,'tagnumber'=>$result[0]['number']];
                    $ifsuccess=Db::table('booktag')->insert($booktagdata);
                    if($ifsuccess<1){
                        $this->error('上传失败');
                    }
                    $iftag=1;
                        }                     
                }

              //若无对应标签，则默认添加至‘其它’标签中
                   if($iftag==0){
                    $result=Db::table('tags')->where('number',48)->select();
                    $num=$result[0]['sum']+1;
                    Db::table('tags')->where('number',48)->update(['sum'=>$num]);//更新其它标签的sum
                    $booktagdata=['isbn'=>$isbn,'tagnumber'=>$result[0]['number']];
                    $ifsuccess=Db::table('booktag')->insert($booktagdata);
                    if($ifsuccess<1){
                        $this->error('上传失败');
                    }
                   
                   }

           }

           $this->success('上传成功','/book/');


        }

    }

    

