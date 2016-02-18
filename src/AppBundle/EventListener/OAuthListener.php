<?php
/**
 * Created by PhpStorm.
 * User: Alexa
 * Date: 15/6/4
 * Time: 下午3:16
 */
namespace AppBundle\EventListener;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\Httpkernel\Event\FilterControllerEvent;
use Symfony\Component\HttpFoundation\RedirectResponse;
//use AppBundle\Wechat;

class OAuthListener
{
	protected $container;
	protected $router;
	public function __construct($router, \Symfony\Component\DependencyInjection\Container $container)
	{
		$this->container = $container;
		$this->router = $router;
	}
	/*
	public function onKernelController(FilterControllerEvent $event)
	{
		//$controller = $event->getController();
		// 此处controller可以被该换成任何PHP可回调函数
		//$event->setController($controller);
	}
	*/
	public function onKernelRequest(GetResponseEvent $event)
	{
    // Exclude assets
    $route = $event->getRequest()->get('_route');
		$request = $event->getRequest();
		$session = $request->getSession();
		if(stripos($request->attributes->get('_controller'), 'DefaultController') !== false){
			if($request->getClientIp() == '127.0.0.1'){
				$session->set('open_id', 'o32vks-w6bon4_arCHmwGq4w-vR0');
				$session->set('user_id', 1);
			}
			else{
				if( $session->get('open_id') === null 
					&& $request->attributes->get('_route') !== '_callback'
					&& $request->attributes->get('_route') !== '_index'
					&& $request->attributes->get('_route') !== '_update'
				){
					$app_id = $this->container->getParameter('wechat_appid');
					$session->set('redirect_url', $request->getUri());
					$state = '';
					$callback_url = $request->getUriForPath('/callback');
					$url = "http://base_wx.ompchina.net/sns/oauth2?appid=".$app_id."&redirecturl=".$callback_url."&oauthscope=snsapi_userinfo";
					$session->set('redirect_url', $request->getUri());
					$event->setResponse(new RedirectResponse($url));
				}
			}
			$session->set('shareAppid',$this->container->getParameter('wechat_appid'));
			$session->set('shareTitle','好时新意，分享甜蜜');
			$session->set('shareDesc','你有一个来自好时的现金红包未领取');
			$session->set('shareLink',$request->getUriForPath('/'));
			$session->set('shareImg','http://'.$request->getHost().'/bundles/app/default/images/share.jpg');
		}
	}
	/*
	public function onKernelResponse(FilterResponseEvent $event)
	{
		$response = $event->getResponse();
		$request = $event->getRequest();
		if ($request->query->get('option') == 3) {
			$response->headers->setCookie(new Cookie("test", 1));
		}
	}
	*/
}