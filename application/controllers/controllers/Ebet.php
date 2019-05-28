<?php
defined('BASEPATH') OR exit('No direct script access allowed');
class Ebet extends CI_Controller{
	public function __construct(){
		parent::__construct();
		$this->load->library('rsa');	
		$this->load->library('api/ebetapi');	//ebet	
	}
	
	
	public function index(){
		//header('Content-type: application/json');
		
		// 接受HTTP请求
		$post = file_get_contents('php://input');
		
		//print_r($post);exit;
	
		// 获取HTTP 请求参数， 并解析JSON数据
		$obj = json_decode($post);
	
	
		// 读取 cmd 值
		$cmd = $obj->{'cmd'};
	
		// 处理登入业务逻辑
		if($cmd == "RegisterOrLoginReq"){
	
			// 读取参数值
	
			// eventType 是登入模式
			$eventType = $obj->{'eventType'};
	
			// 读取渠道ID
			$channelId = $obj->{'channelId'};
	
			// 读取玩家登入名
			$username = $obj->{'username'};
	
			// 获取密码
			$password = $obj->{'password'};
	
			// 读取签名
			$signature = $obj->{'signature'};
	
			// 读取timestamp
			$timestamp = $obj->{'timestamp'};
			
			$accessToken = $obj->{'accessToken'};
			
			// 如果是用户密码登入
			if ($eventType == "1") {
				// 从数据库查询用户名密码是否错误
				// 签名部分是 username + timestamp
			}
	
			// 如果是玩家在渠道手机H5 网站跳转去 eBET APP
			if ($eventType == "3") {
	
				// AppLinks 连接如 aaa://login?u=username&p=password
	
				// 从数据库查询用户名密码是否错误,
				// 签名部分是 timestamp + accessToken
			}
	
			// 如果是通过令牌登入自动跳转
			if ($eventType == "4") {
				// 从数据库里查询用户名和对应令牌是否匹配
				// 签名部分是 timestamp + accessToken
				$this->db->where('accessToken',$accessToken);
				$this->db->where('username',$username);
				$query=$this->db->get('ebet_login_token');
				$row=$query->row_array();
				if($row!=NULL){
					$this->rsa->setSignatureMode(CRYPT_RSA_SIGNATURE_PKCS1);
					$this->rsa->setHash("md5");
					$this->rsa->loadKey($this->ebetapi->getPublicKey());
					if($this->rsa->verify($timestamp.$accessToken, base64_decode($signature))){
						//刪除數據
						$this->db->delete('ebet_login_token', array('accessToken' => $accessToken,'username'=>$username));
					
						echo json_encode(array('status'=>200, 'subChannelId' => 0, 'accessToken' => $accessToken, 'username'=> $username));
						exit;
					}else{
						echo json_encode(array('status'=>410, 'subChannelId' => $channelId, 'accessToken' => $accessToken, 'username'=> $username));
						exit;
					}
				}else{
					echo json_encode(array('status'=>410, 'subChannelId' => $channelId, 'accessToken' => $accessToken, 'username'=> $username));
					exit;
				}
				
			}
	
			// 验证完成后， 返回请求响应
			// status , subChannelId, accessToken, username
			/* 如果是子渠道， 返回子渠道ID ， 不是就返回subChannelId = 0
				 accessToken => 每次通过用户名密码登入， 返回新的token,
				 username - 返回玩家登入名，
				 status - 根据验证结果， 返回对应的结果， 如200 为 验证成功 ， 401 为用户名密码失败， 410 为令牌登入失败
			*/
			$data = array('status'=>200, 'subChannelId' => 0, 'accessToken' => $token, 'username'=> $username);
	
			// 返回数据
			
			echo json_encode($data);
	
		}else{
	
			//fwrite($myfile,"Bad Request");
			//fclose($myfile);
		}
	}
	
	
	
}

?>