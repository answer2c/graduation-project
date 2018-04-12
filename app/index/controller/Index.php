<?php
namespace app\index\controller;
use think\Controller;

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

}

