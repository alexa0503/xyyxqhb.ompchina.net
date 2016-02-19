<?php
namespace AppBundle\Controller;

//use AppBundle\Wechat\Wechat;
use Foo\Bar\B;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Filesystem\Filesystem;
use AppBundle\Helper;
use AppBundle\Entity;
use Imagine\Gd\Imagine;
use Imagine\Image\Box;
use Imagine\Image\Point;
use Imagine\Image\ImageInterface;
#use Symfony\Component\Validator\Constraints\Image;

class DefaultController extends Controller
{
	protected $config = array(
			array('01:00','23:10',400000,150000),
			array('11:00','11:05',100000,150000),
			array('12:00','12:05',200000,150000),
			array('13:00','13:05',100000,150000),
			array('14:00','14:05',100000,150000),
			array('15:00','15:05',100000,150000),
			array('16:00','16:05',100000,150000),
			array('17:00','17:05',100000,150000),
			array('18:00','18:05',100000,150000),
			array('19:00','19:05',150000,150000),
			array('20:00','20:05',150000,150000),
			array('21:00','21:05',150000,150000),
			array('22:00','22:05',150000,150000),
		);
	protected $date_time = array('2016-02-18 00:00:00','2016-02-23 23:59:59');
	public function getUser()
	{
		$session = $this->get('session');
		if(null != $session->get('user_id')){
			$user = $this->getDoctrine()->getRepository('AppBundle:WechatUser')->find($session->get('user_id'));
		}
		else{
			$user = $this->getDoctrine()->getRepository('AppBundle:WechatUser')->findOneByOpenId($session->get('open_id'));
		}
		return $user;
	}
	/**
	 * @Route("/", name="_index")
	 */
	public function indexAction(Request $request)
	{
		$date_time = $this->date_time;
		$config = $this->config;
		$timestamp = time();
		$i = null;
		foreach ($config as $key => $value) {
			if(strtotime(date('Y-m-d ').$value[0]) <= $timestamp && strtotime(date('Y-m-d ').$value[1]) > $timestamp){
				$i = $key;
				break;
			}
		}
		if( null !== $i)
			$end_time = date('Y/m/d ').$config[$i][0];
		else
			$end_time = '2016/02/18';
		$is_end = strtotime($date_time[1]) < $timestamp ? true : false;
		$is_gaming = $i !== null && $is_end != true ? true : false;
		return $this->render('AppBundle:default:index.html.twig', array(
			'EndTime'=>$end_time,
			'isGaming'=> $is_gaming,
			'isEnd' => $is_end,
		));
	}
	/**
	 * @Route("/lottery", name="_lottery")
	 */
	public function lotteryAction(Request $request)
	{

		$date_time = $this->date_time;
		$config = $this->config;
		$timestamp = time();
		$i = null;
		foreach ($config as $key => $value) {
			if(strtotime(date('Y-m-d ').$value[0]) <= $timestamp && strtotime(date('Y-m-d ').$value[1]) > $timestamp){
				$i = $key;
				break;
			}
		}

		if( $timestamp < strtotime($date_time[0])){
			$result = array(
				'ret' => 2001,
				'msg' => '活动未开始'
			);
		}
		elseif($timestamp > strtotime($date_time[1])){
			$result = array(
				'ret' => 2002,
				'msg' => '活动已结束'
			);
		}
		elseif (null === $i) {
			$result = array(
				'ret' => 2003,
				'msg' => '活动还没有开始喔'
			);
		}
		else{
			$user = $this->getUser();
			$em = $this->getDoctrine()->getManager();
			$em->getConnection()->beginTransaction();
			try{
				$repo = $em->getRepository('AppBundle:LotteryLog');
				$qb = $repo->createQueryBuilder('a');
				$qb->select('COUNT(a)');
				$qb->where('a.user = :user AND a.createTime >= :createTime1 AND a.createTime < :createTime2');
				$qb->setParameter(':user', $user);
				$qb->setParameter(':createTime1', new \DateTime($config[$i][0]), \Doctrine\DBAL\Types\Type::DATETIME);
				$qb->setParameter(':createTime2', new \DateTime($config[$i][1]), \Doctrine\DBAL\Types\Type::DATETIME);
				$num1 = $qb->getQuery()->getSingleScalarResult();

				$repo = $em->getRepository('AppBundle:LotteryLog');
				$qb = $repo->createQueryBuilder('a');
				$qb->select('SUM(a.credit)');
				$qb->where('a.createTime >= :createTime1 AND a.createTime < :createTime2');
				$qb->setParameter(':createTime1', new \DateTime($config[$i][0]), \Doctrine\DBAL\Types\Type::DATETIME);
				$qb->setParameter(':createTime2', new \DateTime($config[$i][1]), \Doctrine\DBAL\Types\Type::DATETIME);
				$sum_credit = $qb->getQuery()->getSingleScalarResult();
				
				$repo = $em->getRepository('AppBundle:LotteryLog');
				$qb = $repo->createQueryBuilder('a');
				$qb->select('COUNT(a)');
				$qb->where('a.createTime >= :createTime1 AND a.createTime < :createTime2');
				$qb->setParameter(':createTime1', new \DateTime($config[$i][0]), \Doctrine\DBAL\Types\Type::DATETIME);
				$qb->setParameter(':createTime2', new \DateTime($config[$i][1]), \Doctrine\DBAL\Types\Type::DATETIME);
				$count = $qb->getQuery()->getSingleScalarResult();


				if($num1 >= 5){
					$result = array(
						'ret' => 1001,
						'msg' => '您已经抽了5次了'
					);
				}
				elseif($sum_credit >= $config[$i][2] || $count >= $config[$i][3]){
					$result = array(
						'ret' => 1002,
						'msg' => '红包已经抽完了'
					);
				}
				else{
					$repo = $em->getRepository('AppBundle:LotteryLog');
					$qb = $repo->createQueryBuilder('a');
					$qb->select('SUM(a.credit)');
					$qb->where('a.user = :user AND a.createTime >= :createTime1 AND a.createTime < :createTime2');
					$qb->setParameter('user', $user);
					$qb->setParameter(':createTime1', new \DateTime($config[$i][0]), \Doctrine\DBAL\Types\Type::DATETIME);
					$qb->setParameter(':createTime2', new \DateTime($config[$i][1]), \Doctrine\DBAL\Types\Type::DATETIME);
					$user_credit = $qb->getQuery()->getSingleScalarResult();

					$credit = rand(1,1500-$user_credit*100-5+$num1)/100;
					$log = new Entity\LotteryLog();
					$log->setUser($user);
					$log->setCreateIp($request->getClientIp());
					$log->setCreateTime(new \DateTime('now'));
					$log->setCredit($credit);
					$em->persist($log);
					$em->flush();
					$result = array(
						'ret' => 0,
						'drawNum'=>5-$num1-1,
						'credit'=>$credit,
						'msg' => ''
					);
					//请求接口
				}
				$em->getConnection()->commit();
			}
			catch (Exception $e) {
				$em->getConnection()->rollback();
				$result = array(
					'ret' => 1101,
					'msg' => $e->getMessage()
				);
			}
		}
		return new Response(json_encode($result));
	}

	/**
	 * @Route("callback/", name="_callback")
	 */
	public function callbackAction(Request $request)
	{
		$session = $request->getSession();
		$code = $request->query->get('code');
		//$state = $request->query->get('state');
		$app_id = $this->container->getParameter('wechat_appid');
		$wechat_openid = $request->get('openid');
		$url = "http://base_wx.ompchina.net/sns/UserInfo?appId={$app_id}&openid={$wechat_openid}";
		$data = Helper\HttpClient::get($url);
		$user_data = json_decode($data);

		$em = $this->getDoctrine()->getManager();
		$em->getConnection()->beginTransaction();
		try{
			$session->set('open_id', $user_data->openid);
			$repo = $em->getRepository('AppBundle:WechatUser');
			$qb = $repo->createQueryBuilder('a');
			$qb->select('COUNT(a)');
			$qb->where('a.openId = :openId');
			$qb->setParameter('openId', $user_data->openid);
			$count = $qb->getQuery()->getSingleScalarResult();
			if($count <= 0){
				$wechat_user = new Entity\WechatUser();
				$wechat_user->setOpenId($wechat_openid);
				$wechat_user->setNickName($user_data->nickname);
				$wechat_user->setCity($user_data->city);
				$wechat_user->setGender($user_data->sex);
				$wechat_user->setProvince($user_data->province);
				$wechat_user->setCountry($user_data->country);
				$wechat_user->setHeadImg($user_data->headimgurl);
				$wechat_user->setCreateIp($request->getClientIp());
				$wechat_user->setCreateTime(new \DateTime('now'));
				$em->persist($wechat_user);
				$em->flush();
			}
			else{
				$wechat_user = $em->getRepository('AppBundle:WechatUser')->findOneBy(array('openId' => $wechat_openid));
				$wechat_user->setHeadImg($user_data->headimgurl);
				$em->persist($wechat_user);
				$em->flush();
				$session->set('user_id', $wechat_user->getId());
			}

			$redirect_url = $session->get('redirect_url') == null ? $this->generateUrl('_upload') : $session->get('redirect_url');
			//var_dump($redirect_url);
			$em->getConnection()->commit();
			//return new Response('');
			return $this->redirect($redirect_url);
		}
		catch (Exception $e) {
			$em->getConnection()->rollback();
			return new Response($e->getMessage());
		}
	}
}
