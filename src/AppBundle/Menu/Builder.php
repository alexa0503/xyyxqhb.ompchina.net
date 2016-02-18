<?php
namespace AppBundle\Menu;

use Knp\Menu\FactoryInterface;
use Symfony\Component\DependencyInjection\ContainerAware;

class Builder extends ContainerAware
{
	/**
	 * 后台主菜单
	 * @param FactoryInterface $factory
	 * @param array $options
	 * @return \Knp\Menu\ItemInterface
	 */
	public function mainMenu(FactoryInterface $factory, array $options)
	{
		$menu = $factory->createItem('root');
		$menu->setChildrenAttribute('class', 'nav nav-pills nav-stacked nav-bracket');
		$menu->setChildrenAttribute('id', 'leftmenu');

		$menu->addChild('Dashboard', array('route' => 'admin_index'));
		$menu->addChild('照片信息', array('route' => 'admin_timeline'));
		$menu->addChild('信息导出', array('route' => 'admin_export'));
		/*
		$storage = $menu->addChild('仓库管理', array('route' => 'admin_storage'));
		$storage->setAttribute('class', 'nav-parent');
		$storage->setChildrenAttribute('class', 'children');
		$storage->addChild('查看', array('route' => 'admin_storage'));
		$storage->addChild('添加', array('route' => 'admin_storage_add'));

		$event = $menu->addChild('事件管理', array('route' => 'admin_event'));
		$event->setAttribute('class', 'nav-parent');
		$event->setChildrenAttribute('class', 'children');
		$event->addChild('查看', array('route' => 'admin_event'));
		$event->addChild('添加', array('route' => 'admin_event_add'));
		*/
		//$menu->addChild('photo', array('route' => 'admin_photo', 'label' => '照片信息'));
		return $menu;
	}
}