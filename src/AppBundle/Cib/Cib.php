<?php
/*************请求兴业年中大促第三方接口***********/
namespace AppBundle\Cib;
use AppBundle\Helper;
use Symfony\Component\HttpFoundation\Request;
class Cib{
	public function __construct($data) {
		$timestamp = time();
		$message = '<Message><amount>'.$data['credit'].'</amount><channel>1</channel><openId>'.$data['openId'].'</openId></Message>';	
		$message = $this->aes($message);
		$sign = $this->getToken($message,$timestamp);
		$params=array(
			'msgtype'=>'request_msg', 
			'format'=>'xml',
			'organizerId'=>'00010016488cf1c8',
			'version'=>'1.0',
			'timestamp'=> $timestamp,
			'sign'=> $sign,
			'method'=>'10',
			'message'=> $message,
			'serviceType'=>'com.epointchina.activity.appcibnzdc.service.CIBNZDCServiceForThirdPty',
			'serviceMethod'=>'doThirdPtyOperator',
			);
		$url='http://test.e-pointchina.com.cn/activity_cibnzdc_test/cloudDataService.do';//测试地址    	上线时注销改行
		//$url='http://cib.e-pointchina.com.cn/activity_cibnzdc/cloudDataService.do';    // 正式地址   	上线时使用正式地址
		$headers[]="Authorization: xyyhh.ompchina.net";
		$result = $this->http($url, http_build_query($params), 'POST', $headers);
		return $result;
	}

	public function getToken($message,$timestamp)
	{
		$token='B38A40548E4C4BC6F10930875E9F0CA1';
		$format='formatxml';
		$message='message'.$message;
		$method='method10';
		$msgtype='msgtyperequest_msg';
		$organizerId='organizerId00010016488cf1c8';
		$timestamp='timestamp'.$timestamp;
		$version='version1.0';
		$str = $token.$format.$message.$method.$msgtype.$organizerId.$timestamp.$version.$token;
		return strtoupper(md5($str));
	}
	

	public function aes($data){
		$str = $this->pkcs5_pad($data);
		$iv='00010016488cf1d5';  
		$key='00010016488cf1d4';       
		$td = mcrypt_module_open('rijndael-128', '', 'cbc', '');
		mcrypt_generic_init($td, $key, $iv);
		$encrypted = mcrypt_generic($td, $str);
		mcrypt_generic_deinit($td);
		mcrypt_module_close($td);
		return bin2hex($encrypted);
	}
	public function pkcs5_pad ($text) {
	  $blocksize = 16;
	  $pad = $blocksize - (strlen($text) % $blocksize);
	  return $text . str_repeat(chr($pad), $pad);
	}
	

	public function http($url, $postfields='', $method='GET', $headers=array()){
		$ci=curl_init();
		curl_setopt($ci, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($ci, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ci, CURLOPT_CONNECTTIMEOUT, 30);
		curl_setopt($ci, CURLOPT_TIMEOUT, 30);
		if($method=='POST'){
			curl_setopt($ci, CURLOPT_POST, TRUE);
			if($postfields!='')curl_setopt($ci, CURLOPT_POSTFIELDS, $postfields);
		}
		$headers[]="User-Agent: florentiavillage(m.florentiavillage.com)";
		curl_setopt($ci, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($ci, CURLOPT_URL, $url);
		$response=curl_exec($ci);
		curl_close($ci);
		if(is_null(json_decode($response))){
			return $response;
		}
		$json_r=array();
		if($response!='')
			$json_r=json_decode($response, true);
		return $json_r;
	}

}