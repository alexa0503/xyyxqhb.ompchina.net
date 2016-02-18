<?php
namespace AppBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class StorageType extends AbstractType
{
	public function buildForm(FormBuilderInterface $builder, array $options)
	{
		$builder
			->add('title', 'text', array(
				'label' => '仓库名称',
			))
			->add('type', 'text', array(
				'label' => '物业类型',
			))
			->add('district', 'text', array(
				'label' => '地区',
			))
			->add('city', 'text', array(
				'label' => '城市',
			))
			->add('area', 'text', array(
				'label' => '面积(整数)',
			))
			->add('areaDesc', 'text', array(
				'label' => '面积描述',
			))
			->add('storageDesc', 'textarea', array(
				'label' => '仓库描述',
			))
			->add('imgUrl', 'file', array(
				'label' => '缩略图',
				'data_class' => null,
				'required' => false,
			))
			->add('mapUrl', 'file', array(
				'label' => '地图',
				'data_class' => null,
				'required' => false,
			))
			->add('save', 'submit', array('label' => '保存'))
		;
	}
	public function getName()
	{
		return 'storage';
	}
}