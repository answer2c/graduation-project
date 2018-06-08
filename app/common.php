<?php

use \think\Session;
// 应用公共文件
/**
//         * 检查是否登录
//         */
    function checkUser(){
            if(Session::has('username')){
                    $login='<li class="dropdown" >
                                <a href="" data-toggle="dropdown" class="dropdown-toggle" id="touxiang">';
                    if (Session::has('ouruser')){
                        $login .= ' <img src="http://www.answer2c.cn/'.Session::get("touxiang").'" >'.Session::get("username").'</a>
                                </a>
                                <ul class="dropdown-menu">
                                    <li><a href="/book/index/userpage" >个人信息</a></li>';
                    }else{
                        $login .= ' <img src="'.Session::get("touxiang").'" >'.Session::get("username").'</a>
                                </a>
                                <ul class="dropdown-menu">
                                    <li><a href="/book/index/userpage" >个人信息</a></li>';
                    }


                    if(Session::get("authority") == 1){
                        $login .= ' <li class="divider"></li> <li><a href="/book/index/notice">通知消息</a></li>';
                    }
                    $login .= '</ul>
                            </li>
                             <li><a href="#" onclick="logout()">注销</a></li>\'';

            }else{
                $login='<li><a href="#" data-toggle="modal" data-target="#myModal" >登录</a></li>
                <li><a href="/book/index/regi">注册</a></li>';

            }

            return $login;
        }


/**
 * 清除session
 */
function _cs(){
    Session::clear();
}

/**
 * 格式化ajax返回的数据
 */
function _ard($msg,$status = "ERR")
{
    $data = array("status" => $status, "msg" => $msg);
    die(json_encode($data));
}
