<?php
    /**
     * Author: ybc
     */
    namespace app\book\controller;
    use \think\Controller;
    use \think\Session;
    use \lib\Qq;
    use \think\Request;
    use \think\Db;
    use app\book\model\User;
    use app\book\model\Book;
    use app\book\model\Borrow;
    use app\book\model\Upload;
    use app\book\model\Comment;
    use app\book\model\Tags;
    use app\common\Page;


    class Index extends Controller
    {
        public static $redirect_uri='http://www.answer2c.cn/book/index/callback';

        public function index(){

            $login=checkUser();
            $book = new Book;

            $this->assign('username',Session::get('username'));
            $this->assign('loginMess',$login);
            $this->assign('redirect_uri',urlencode(self::$redirect_uri));

            $bookset = $book->order('borrow_num','desc')->limit(5)->select();
            $this->assign('bookset',$bookset);
            return $this->fetch('index');
        }

        public function login(Request $request){
            $username=$request->post('username');
            $pwd=md5($request->post('pwd'));
            $result=Db::table('user')->where("username",$username)->where("passwd",$pwd)->select();
            if ($result == null){
                _ard("用户名和密码不正确","ERR");
            }else{
                $authority = $result[0]['authority'];
                $status = $result[0]['status'];
                if ($status != 1){
                    _ard("账户异常 请联系管理人员","ERR");
                }else{
                    $src=$result[0]['img'];
                    Session::set('username',$username);
                    Session::set('authority',$authority);
                    Session::set('ouruser','yes');
                    Session::set('touxiang',$src);

                    if ($authority == 2){
                        _ard("登录成功","MAN");
                    }else{
                        _ard("登录成功","USER");
                    }
                }


            }
        }

      
        public function regi(Request $request){   
                return $this->fetch();      
        }


        /**
         * 添加QQ用户账号
         */
        public function addQQuser(Request $request)
        {

            $user = new User;
            $touxiang = $request->post("touxiang");
            $nickname = $request->post("nickname");
            $user->username = $request->post("username");
            $user->idnum=$request->post('idnum');
            $user->address = $request->post('address');
            $user->tel=$request->post('tel');


            $user->regitime=time();
            $user->authority = 1;


            $add = $user->save();
            if ($add > 0){
                Session::set("username",$nickname);
                Session::set("touxiang",$touxiang);
                Session::set("authority",1);
                _ard("注册成功" , "OK");
            }else{
                _ard("注册失败" , "ERR");
            }
        }


        /**
         * 注册添加用户
         */
        public function adduser(Request $request){
           if($request->post('username')){
                $addUser = new User();
                $addUser->username=$request->post('username');
                $addUser->passwd=md5($request->post('pwd'));
                $addUser->sex=$request->post('sex');
                $addUser->tel=$request->post('tel');
                $addUser->email=$request->post('email');
                $addUser->idnum=$request->post('idnum');
                $addUser->address = $request->post('address');
                $addUser->regitime=time();
                $addUser->authority=1;
                $addUser->save();
                echo "<script>alert('注册成功');</script>";
                $this->redirect('/book/index/');
           }
        }


        /**
         * 第三方登录处理函数
         */
         public function callback(){
             _cs();
            if(isset($_GET['code'])){
                $user = new User;
                $appid = "101458359";
                $app_secret = "3a9ee71ad91cd47e5c7cee6de84f8c19";
                $url = "http://www.answer2c.cn/book/index/callback";
                $qq  = new Qq($appid,$app_secret,$url);
                $data = $qq->returnData();

                $if_exists = $user->where("username",$data['openid'])->select();

                $this->assign("openid",$data['openid']);
                $this->assign("touxiang",$data['figureurl_qq_1']);
                $this->assign("nickname",$data['nickname']);



                //判断该QQ用户是否已有用户信息
                if (!empty($if_exists)){
                    Session::set("username",$data['nickname']);
                    Session::set('authority',"1");
                    Session::set('touxiang',$data['figureurl_qq_1']);
                    return $this->index();
                }else{
                    return $this->fetch('addQQ');
                }
            }else{
                exit('Request failed');
            }
         }


        /**
         * @param $qqname
         * @param $qqimg
         * @return mixed
         * 增加QQ用户信息页面
         */
         public function addQQ($qqname,$qqimg)
         {
             return $this->fetch('addQQ');
         }


        /**
         * 注销
         */
        public function logout(){
            _cs();
            $this->redirect('index');
        }


        public function ajaxcheck(Request $request){
            $user = new User;
             if($request->post('username')){
                 $name=$request->post('username');
                 $result = $user->where("username",$name)->count();
                 if ($result > 0){
                     $data = array("status" => "ERR" , "msg" => "用户名已存在");
                 }else {
                     $data = array("status" => "OK", "msg" => "可以使用");
                 }
              }else{
                 $data = array("status" => "ERR", "msg" => "数据不存在");
             }

             die(json_encode($data));
        }


        /**
         * 借书页面
         */
        public function lend(){

                $login = checkUser();
                 $this->assign('loginMess',$login);
                 $this->assign('redirect_uri',self::$redirect_uri);

                 $tags=Db::table('tags')->field('number,tagname')->select();
                 $this->assign('tags',$tags);
                 $where = '';

                 //确定where条件
                 if (isset($_GET['book'])){
                     $where.="and isbn = '".$_GET['book']."' or bookname  like '%".$_GET['book']."%' or author like '%".$_GET['book']."%'";
                 }
                 if(isset($_GET['tag'])){
                     $sql = "select isbn from booktag where tagnumber = ".$_GET['tag'];
                     $isbnList = Db::query($sql);
                     if (!empty($isbnList)) {
                         $isbn_in = "";
                         foreach ($isbnList as $val) {
                             $isbn_in .= $val['isbn'] . ',';
                         }
                         $isbn_in = rtrim($isbn_in, ',');
                         $where .= " and isbn in (" . $isbn_in . ")";
                     }else{
                          $where .= " and 1=0";
                     }
                 }

                 //确定limit条件与页数
                 $limit  = 20;
                 $offset = 0;
                 $totalSql = "select count(*) as total from book where 1=1 ".$where;
                 $total = Db::query($totalSql);
                 $pages = ceil($total[0]['total'] / $limit);

                $tpage = new Page($pages);
                $pagelist = $tpage->pagelist();
                $this->assign("pagelist",$pagelist);
                 //确定分页信息
                $pageStart = 0;
                $pageEnd = 0;
                if(!isset($_GET['page'])){
                    $_GET['page'] = 1;
                }


                     $offset = $limit * ($_GET['page'] - 1);


                //搜索所有符合条件的书籍
                $sql = "select * from book where 1=1 ".$where." limit {$offset},{$limit}";
                $bookList = Db::query($sql);
                $this->assign('total',$total[0]);
                $this->assign('pages',$pages);
                $this->assign('bookList',$bookList);
                return $this->fetch('lend');

           
        }

        /**
         * 上传书籍页面
         */
        public function upload(){
            if(!Session::has('username')){
                echo "<script>alert('请先登录');window.history.back();</script>";
            
            }else{
                $login=checkUser();
                $this->assign('username',Session::get('username'));
                $this->assign('loginMess',$login);
                return $this->fetch();
            }
        }


        /**
         * 添加书籍
         */
       //tp5的ajax 必须关闭debug
        public function addbook(Request $request)
        {
            $iftag = 0;//判断该书籍的标签在tags表中是否有对应
            $isbn = $request->post('isbn');
            $bookname = $request->post('bookname');
            $author = $request->post('author');
            $publisher = $request->post('publisher');
            $pubdate = $request->post('pubdate');
            $booktag = explode(' ', $request->post('booktag'));
            $price = substr($request->post('price'), 0, -3);
            $bookimg = $request->post('bookimg');
            $book_intro = $request->post('intro');

//           //查看当前书籍信息是否存在
            $ifbook = Db::table('book')->where('isbn', $isbn)->select();
            if (!empty($ifbook)) {
                //图书存在，判断该用户是否上传过该书，若无则更新upload表
                $ifupload = Db::table('upload')->where('username', Session::get('username'))->where('isbn', $isbn)->select();
                if (empty($ifupload)) {
                    $uploaddata = ['username' => Session::get('username'), 'isbn' => $isbn, 'rent' => '0'];
                    $ifsuccess = Db::table('upload')->insert($uploaddata);
                    if ($ifsuccess == 1) {
                        die(json_encode(array('status' => 1, 'data' => '上传成功')));
                    } else {
                        die(json_encode(array('status' => 0, 'data' => '上传失败')));
                    }
                }else{
                    die(json_encode(array('status' => 0, 'data' => '您已上传过该书')));
                }

            }else {
                //添加新的图书信息
                $bookdata = ['isbn' => $isbn, 'author' => $author, 'publisher' => $publisher, 'pubdate' => $pubdate, 'price' => $price, 'bookname' => $bookname, 'img' => $bookimg ];
                $insertBook=Db::table('book')->insert($bookdata);
                if($insertBook != 1){
                    die(json_encode(array('status' => 0, 'data' => '上传失败')));
                }
                //添加上传表数据
                $uploaddata = ['username' => Session::get('username'), 'isbn' => $isbn, 'rent' => '0'];
                $ifupload=Db::table('upload')->insert($uploaddata);
                if($ifupload != 1) {
                    die(json_encode(array('status' => 0, 'data' => '上传失败')));
                }
                //遍历图书的标签，查看该图书的标签在tags表中是否存在，若存在，则对应标签数量加1
                    for ($i = 0; $i < count($booktag) - 1; $i++) {
                        $result = Db::table('tags')->where('tagname', $booktag[$i])->select();
                        if (!empty($result)) {
                            $tagnumber = $result[0]['number'];
                            $num = $result[0]['sum'] + 1;
                            $ifupload=Db::table('tags')->where('tagname', $booktag[$i])->update(['sum' => $num]);//更新标签的sum
                            if($ifupload < 1){
                                die(json_encode(array('status' => 0, 'data' => '上传失败')));
                            }
                            $booktagdata = ['isbn' => $isbn, 'tagnumber' => $result[0]['number']];
                            $ifsuccess = Db::table('booktag')->insert($booktagdata);
                            if ($ifsuccess < 1) {
                                die(json_encode(array('status' => 0, 'data' => '上传失败')));
                            }
                            $iftag = 1;
                        }
                    }

              //若无对应标签，则默认添加至‘其它’标签中
                   if($iftag == 0){
                        $result = Db::table('tags')->where('number',48)->select();
                        $num = $result[0]['sum'] + 1;
                       //更新其它标签的sum
                       $if_update =Db::query("update tags set sum =".$num." where number = 48");
                       if($if_update === false){
                           die(json_encode(array('status' => 0, 'data' => '上传失败')));
                       }
                        $booktagdata=['isbn'=>$isbn,'tagnumber'=>$result[0]['number']];
                        $ifsuccess=Db::table('booktag')->insert($booktagdata);
                        if($ifsuccess < 1){
                            die(json_encode(array('status' => 0, 'data' => '上传失败')));
                        }else {
                            die(json_encode(array('status' => 1, 'data' => '上传成功')));
                        }
                   }else{
                       die(json_encode(array('status' => 1, 'data' => '上传成功')));
                   }

                 }

           }



        public function userpage(){
            $login=checkUser();
            $user = new User;
            $this->assign('loginMess',$login);
            $this->assign('username',Session::get('username'));

            $result = $user->where('username',Session::get('username'))->select();
            $this->assign('sex',$result[0]->sex);
            $this->assign('tel',$result[0]->tel);
            $this->assign('address',$result[0]->address);
            $this->assign('email',$result[0]->email);
            $this->assign('userphoto','http://www.answer2c.cn/'.$result['0']['img']);

            return $this->fetch();
        }


      /**
       * 书籍归还部分
       */

      public function returnbook()
      {
          if(!Session::has('username')){
              echo "<script>alert('请先登录');window.history.back();</script>";
          }else{
              $login = checkUser();
              $username = Session::get('username');
              $borrow = new Borrow;
              $upload = new Upload;
              $book = new Book;

              $borrow_info = $borrow->where('borrow_user', $username)->where('is_return',0)->select();
              foreach ($borrow_info as &$item) {
                  $upload_info = $upload->where("share_id",$item->share_id)->find();
                  $item['upload_user'] = $upload_info->username;
                  $item['bookname'] = $book->where('isbn',$upload_info->isbn)->find()->bookname;
              }

              $total = $borrow->where('borrow_user',$username)->count();
              $limit = 30;
              $pages = ceil($total[0]['total'] / $limit);

              $tpage = new Page($pages);
              $pagelist = $tpage->pagelist();
              $this->assign("pagelist",$pagelist);
              $this->assign('total',$total);

              $this->assign("loginMess",$login);
              $this->assign("borrow_info",$borrow_info);
              return $this->fetch();
          }



      }

        /**
         * 图书借阅
         */
        public function lendbook()
        {
            if(!Session::has('username')){
                echo "<script>alert('请先登录')</script>";
                return  $this->lend();exit;
            }else{
                $login = checkUser();
                $isbn = $_GET['isbn'];
                $username = Session::get('username');

                $book = new Book;
                $borrow = new Borrow;
                $upload = new Upload;
                $user   = new User;
                $bookname = $book->where('isbn',$isbn)->find()['bookname'];

                $upload_infos = $upload->where('isbn',$isbn)->where('rent',0)->order('upload_time','desc')->select();
                foreach ($upload_infos as &$upload_info){
                    $upload_user = $upload_info['username'];
                    $upload_user_info = $user->where('username',$upload_user)->find();
                    $upload_info['user_info'] =$upload_user_info;
                }

                $this->assign("bookname",$bookname);
                $this->assign("loginMess",$login);
                $this->assign("upload_infos",$upload_infos);
                return $this->fetch();
            }
        }


        public function lendconfirm()
        {
            $login = checkUser();
            $share_id = $_GET['share_id'];
            $tel = $_GET['tel'];
            $book = new Book;
            $upload = new Upload;
            $borrow = new Borrow;
            $borrow_user = Session::get('username');

            //更新书籍表中的borrow_num字段
            $upload_info = $upload->where("share_id",$share_id)->find();
            $isbn = $upload_info->isbn;
            $book_info = $book->where("isbn",$isbn)->find();
            $borrow_num = $book_info + 1;
            $book->save(['borrow_num' => $borrow_num ],["isbn" => $isbn]);

            //借书表新增数据
            $borrow->borrow_user = $borrow_user;
            $borrow->share_id = $share_id;
            $ifborrow = $borrow->save();


            //upload更新rent字段的值
            $iflend = $upload->where('share_id',$share_id)->update(["rent" => 1]);

            if($iflend <= 0 || $ifborrow <= 0 ){
                echo "<script>alert('借阅失败');window.history.back();</script>";
                return;
            }

            $this->assign('tel',$tel);
            $this->assign("loginMess",$login);
            return $this->fetch();
        }


      /**
       * 图书详情
       */
      public function bookdetail()
      {
          $this->assign("redirect_uri",self::$redirect_uri);
          $login = checkUser();
          $isbn = $_GET['isbn'];
          $url = "https://api.douban.com/v2/book/isbn/".$isbn;
          $curl = curl_init($url);
          curl_setopt($curl, CURLOPT_HEADER, 0);
          curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
          curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false); // 跳过证书检查
          $tmpInfo = curl_exec($curl);     //返回api的json对象
          curl_close($curl);

          $book_info = json_decode($tmpInfo,true);
          $booktag = "";
          foreach ($book_info['tags'] as $tag){
              $booktag .= $tag['title'].' &nbsp;';
          }
          $data = ['bookname' => $book_info['title'],'subtitle' => $book_info['subtitle'],'author' => $book_info['author'],'publisher' => $book_info['publisher'] ,'pubdate' => $book_info['pubdate'] ,
              'book_intro' => $book_info['summary'] , 'author_intro' => $book_info['author_intro'] ,'booktag' => $booktag ,'pages' => $book_info['pages'] ,
              'img_src' => $book_info['images']['small'],'binding' => $book_info['binding'],'rating' => $book_info['rating']['average'],'price' => $book_info['price'],
              'isbn' => $isbn,'loginMess' => $login ];

          $comment = new Comment;
          $commentList = $comment->where('isbn',$isbn)->select();

          if(Session::has('username')){
              $this->assign("username",Session::get('username'));
          }
          $this->assign("commentList",$commentList);
          $this->assign($data);
          return $this->fetch();

      }


        /**
         * 我的图书管理
         */
        public function mybook($view)
        {
            if(!Session::has('username')){
                echo '<script>alert("请先登录");window.history.back();</script>';

            }else{
                $login = checkUser();
                $username = Session::get('username');

                $upload = new Upload;
                $borrow = new Borrow;
                $book = new Book;
                $limit = 10;

                $bookdata = array();
                $this->assign("view",$view);
                if ($view == 'islended'){
                    $islends = $upload->where('username',$username)->where("rent",1)->select(); //正在借出的书
                    $total = $upload->where('username',$username)->where("rent",1)->count();
                    foreach ($islends as &$value){
                        $value->bookinfo = $book->where('isbn',$value->isbn)->find();
                    }
                    $bookdata = $islends;
                }elseif ($view == 'nolended'){
                    $nolends =  $upload->where('username',$username)->where("rent",0)->select();//未借出的书
                    $total = $upload->where('username',$username)->where("rent",0)->count();
                    foreach ($nolends as &$value){
                        $value->bookinfo = $book->where('isbn',$value->isbn)->find();
                    }
                    $bookdata = $nolends;
                }

                $this->assign('bookdata',$bookdata);
                $pages = ceil($total / $limit);
                $tpage = new Page($pages);
                $pagelist = $tpage->pagelist();
                $this->assign("pagelist",$pagelist);
                $this->assign('total',$total);

                $this->assign("pagelist",$pagelist);
                $this->assign('total',$total);
                $this->assign("loginMess",$login);

                return $this->fetch();
            }

        }


      /**
       * 评论书籍
       */
      public function comment()
      {
          $username = Session::get('username');
          $content = $_POST['content'];
          $isbn = $_POST['isbn'];

          if (empty($username)){
              die(json_encode(array("status" => 'ERR' , 'msg' => '请先登录')));
          }

          $comment = new Comment;
          $comment->cname = $username;
          $comment->isbn = $isbn;
          $comment->content = $content;
          $if = $comment->save();
          if ($if > 0){
              die(json_encode(array("status" => 'OK' , 'msg' => "评论成功")));
          }else{
              die(json_encode(array("status" => 'ERR' , 'msg' => "评论失败")));
          }
      }

      /**
       * 用户删除评论
       */
      public function delcomment(Request $request)
      {
          $cid = $request->post('cid');
          $comment = new Comment;
          $comment->where('cid',$cid)->delete();
          _ard("","OK");

      }


        /**
         * 借书流程
         * @return mixed
         */
      public function process()
      {
          $login = checkUser();
          $this->assign("loginMess",$login);
          return $this->fetch();
      }

        /**
         * 常见问题
         * @return mixed
         */
      public function question()
      {
          $login = checkUser();
          $this->assign("loginMess",$login);
          return $this->fetch();
      }

    }

    

