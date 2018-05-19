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

    class Manage extends Controller
    {
        public static $redirect_uri='http://www.answer2c.cn/book/index/callback';
        public function index()
        {
            $login=checkUser();
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
    }