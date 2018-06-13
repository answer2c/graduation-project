<?php
    namespace app\book\controller;
    use \think\Controller;
    use \think\Session;
    use \lib\Qq;
    use \think\Request;
    use \think\Db;
    use app\book\model\Book;
    use app\book\model\Borrow;
    use app\book\model\User;
    use app\book\model\Comment;
    use app\book\model\Notice;
    use app\book\model\Upload;
    use app\book\model\Tags;
    use app\common\Page;

    class Manage extends Controller
    {
        public static $redirect_uri='http://www.answer2c.cn/book/index/callback';
        public function index()
        {
            $login=checkUser();
            if(Session::get('authority') != 2){
                $this->redirect('/book/index');
            }
            $this->assign('username',Session::get('username'));
            $this->assign('loginMess',$login);
            $this->assign('redirect_uri',urlencode(self::$redirect_uri));

            $user = new User;
            $book = new Book;
            $borrow = new Borrow;
            $comment = new Comment;
            $upload = new Upload;
            $tag = new Tags;

            $user_num = $user->count();
            $book_num = $book->count();
            $comment_num = $comment->count();
            $borrow_num = $borrow->count();
            $upload_num = $upload->count();

            $wenxue_num = $tag->where('number',2)->find()->sum;
            $life_num = $tag->where('number',38)->find()->sum;
            $tech_num = $tag->where('number',46)->find()->sum;
            $liuxing_num = $tag->where('number',63)->find()->sum;
            $wenhua_num = $tag->where('number',64)->find()->sum;
            $jingguan_num = $tag->where('number',65)->find()->sum;
            $qita_num = $tag->where('number',66)->find()->sum;



            $this->assign('commentnum',$comment_num);
            $this->assign('uploadnum',$upload_num );
            $this->assign('booknum',$book_num);
            $this->assign('usernum',$user_num);
            $this->assign('borrownum',$borrow_num);

            $this->assign('wenxue_num',$wenxue_num);
            $this->assign('life_num',$life_num);
            $this->assign('tech_num',$tech_num);
            $this->assign('liuxing_num',$liuxing_num);
            $this->assign('wenhua_num',$wenhua_num);
            $this->assign('jingguan_num',$jingguan_num);
            $this->assign('qita_num',$qita_num );







            return $this->fetch();

        }


        public function bookmanage()
        {
            if(Session::get('authority') != 2){
                $this->redirect('/book/index');
            }

            $limit = 3;
            $login = checkUser();
            $book = new Book;
            $book_infos = $book->select();
            foreach ($book_infos as &$value){
                $upload = new Upload;
                $upload_infos = $upload->where("isbn",$value['isbn'])->select();
                foreach ($upload_infos as $v){
                    $borrow = new Borrow;
                    $borrow_num = $borrow->where("share_id",$v['share_id'])->count();
                    if ($borrow_num > 0){
                        $value['borrowing'] = 1;
                    }else{
                        $value['borrowing'] = 0;

                    }
                }
            }


            $total = $book->count();
            $pages = ceil($total / $limit);
            $page = new Page($pages);
            $this->assign("book_infos",$book_infos);
            $this->assign('loginMess',$login);
            return $this->fetch();
        }

        public function usermanage()
        {
            if(Session::get('authority') != 2){
                $this->redirect('/book/index');
            }
            $this->assign('redirect_uri',urlencode(self::$redirect_uri));
            $login = checkUser();
            $user = new User;
            $user_infos = $user->select();
            foreach ($user_infos as &$value){
                $value['regitime'] = date('Y-m-d H:i:s',$value['regitime']);
            }

            $this->assign('loginMess',$login);
            $this->assign("user_infos",$user_infos);
            return $this->fetch();
        }


        public function commentmanage()
        {
            $login = checkUser();
            $comment = new Comment;
            $comment_infos = $comment->select();

            $this->assign('comment_infos',$comment_infos);
            $this->assign('loginMess',$login);
            return $this->fetch();
        }

        public function ajaxBlock(Request $request)
        {
            $isbn = $request->post('isbn');
            $content = $request->post('content');
            $upload = new Upload;
            $users = $upload->where("isbn",$isbn)->select();
            foreach($users as $user){
                $notice = new Notice;
                $notice->username = $user->username;
                $notice->content = "抱歉，您上传的书籍被下架，下架原因：".$content;
                $notice->save();
            }
            $book = new Book;
            $result = $book->save(["status" => 0],['isbn' => $isbn]);
            if($result < 1 ){
                _ard("下架失败", "ERR");
            }else{
                _ard("下架成功","OK");
            }
        }

        public function ajaxPass(Request $request)
        {
            $isbn = $request->post("isbn");
            $book = new Book;
            $result = $book->save(["status" => 1 ],["isbn" => $isbn]);
            if($result < 1 ){
                _ard("审核失败", "ERR");
            }else{
                _ard("审核成功","OK");
            }
        }

        public function blockUser(Request $request)
        {
            $username = $request->post("username");
            $user = new User;
            $result = $user->save(["status" => 0 ],["username" => $username]);
            if($result < 1 ){
                _ard("拉黑失败", "ERR");
            }else{
                _ard("拉黑成功","OK");
            }
        }

        public function recoverUser(Request $request)
        {
            $username = $request->post("username");
            $user = new User;
            $result = $user->save(["status" => 1 ],["username" => $username]);
            if($result < 1 ){
                _ard("恢复失败", "ERR");
            }else{
                _ard("恢复成功","OK");
            }
        }


        public function recoverComment(Request $request)
        {
            $cid = $request->post("cid");
            $comment = new Comment;
            $result = $comment->save(["status" => 1 ],["cid" => $cid]);
            if($result < 1 ){
                _ard("恢复失败", "ERR");
            }else{
                _ard("恢复成功","OK");
            }
        }

        public function deleteComment(Request $request)
        {
            $cid = $request->post("cid");
            $comment = new Comment;
            $result = $comment->save(["status" => 0 ],["cid" => $cid]);
            if($result < 1 ){
                _ard("删除失败", "ERR");
            }else{
                _ard("删除成功","OK");
            }
        }


        public function blockremark()
        {
            return $this->fetch();
        }

        




    }