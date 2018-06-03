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
    use app\common\Page;
    use app\book\model\Comment;

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
            $user_num = $user->count();
            $book_num = $book->count();
            $borrow_num = $borrow->count();

            $this->assign('booknum',$book_num);
            $this->assign('usernum',$user_num);
            $this->assign('borrownum',$borrow_num);

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


    }