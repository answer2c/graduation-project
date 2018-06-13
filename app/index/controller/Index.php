<?php
namespace app\index\controller;
use think\Controller;
use \PDO;
class Index extends Controller
{
    public function index()
    {
	return $this->fetch();
    }
    
    public function file()
    {
    return $this->fetch();
    }

    public  function mess(){
       
         $mem=new \Memcached();
         $mem->addServer('127.0.0.1',11211);
         $data=array('name'=>'ybc','pwd'=>'123');
         $mem->setMulti($data,200);
      
        return $this->fetch();
   
    }

    public function login()
    {
        return $this->fetch();
    }

    public function test()

    {
       $dsn = 'mysql:dbname=test;host=127.0.0.1';
	$user = 'root';
	$password = 'Ybc131419';

	try {
    $dbh = new PDO($dsn, $user, $password);
	} catch (PDOException $e) {
    echo 'Connection failed: ' . $e->getMessage();
		}

	
       $username = $_GET['username'];
       $pwd = $_GET['pwd'];



       $sql = "select * from user where username='".$username."'and pwd = '".$pwd."'";
  //var_dump($sql);exit;
       $result = $dbh->query($sql);
$num = $result->rowCount();

//var_dump($result->rowCount());exit;
	if($num > 0){
	echo "success";
}else{
	echo "failed";
}


	}

}

