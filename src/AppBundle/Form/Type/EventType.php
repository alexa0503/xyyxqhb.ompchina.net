<?php
namespace AppBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class EventType extends AbstractType
{
	public function buildForm(FormBuilderInterface $builder, array $options)
	{
		$builder
			->add('title', 'text', array(
				'label' => '标题',
			))
			->add('info', 'textarea', array(
				'label' => '内容',
				'attr' => array('class' => 'tinymce'),
			))
			->add('imgUrl', 'file', array(
				'label' => '图片',
				'data_class' => null,
				'required' => false,
			))
			->add('save', 'submit', array('label' => '保存'))
		;
	}
	public function getName()
	{
		return 'event';
	}
}