<?php
namespace AppBundle\Controller;

//use Guzzle\Http\Message\Response;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use AppBundle\Entity;
use Symfony\Component\Validator\Constraints\Time;
use AppBundle\Form\Type\StorageType;
use AppBundle\Form\Type\EventType;

//use Liuggio\ExcelBundle;

//use Symfony\Component\Validator\Constraints\Page;

class AdminController extends Controller
{
	protected $pageSize = 30;
	/**
	 * @Route("/admin/", name="admin_index")
	 */
	public function indexAction()
	{
		return $this->render('AppBundle:admin:index.html.twig');
	}
	/**
	 * @Route("/admin/account/", name="admin_account")
	 */
	public function accountAction()
	{
		
	}
	/**
	 * @Route("/admin/timeline", name="admin_timeline")
	 */
	public function timelineAction(Request $request)
	{
		$repository = $this->getDoctrine()->getRepository('AppBundle:Timeline');
		$queryBuilder = $repository->createQueryBuilder('a')->orderBy('a.weekFavourNum','DESC');
		
		$query = $queryBuilder->getQuery();
		$paginator  = $this->get('knp_paginator');

		$pagination = $paginator->paginate(
			$query,
			$request->query->get('page', 1),/*page number*/
			$this->pageSize
			);
		return $this->render('AppBundle:admin:photo.html.twig', array('pagination'=>$pagination));
	}
	/**
	 * @Route("/admin/export/", name="admin_export")
	 */
	public function exportAction(Request $request)
	{
		$em = $this->getDoctrine()->getManager();
		$repository = $em->getRepository('AppBundle:Timeline');
		$queryBuilder = $repository->createQueryBuilder('a');
		$queryBuilder->orderBy('a.weekFavourNum', 'DESC');
		$timelines = $queryBuilder->getQuery()->getResult();
		//$output = '';
		$arr = array(
			'id,微信昵称,微信头像,填写昵称,照片,总赞数,周赞数'
			);
		foreach($timelines as $v){
			$_string = $v->getId().','.$v->getUser()->getNickname().','.$v->getUser()->getHeadimg().','.$v->getTitle().',http://2015hersheyCNY.ompchina.net/uploads/'.$v->getImgUrl().','.$v->getFavourNum().','.$v->getWeekFavourNum();
			//$_string .= $v->getCreateTime()->format('Y-m-d H:i:s').','.$v->getCreateIp().',';
			$arr[] = $_string;
		}
		$output = implode("\n", $arr);

		//$phpExcelObject = $this->get('phpexcel')->createPHPExcelObject();
		/*
		$phpExcelObject = new \PHPExcel();
		$phpExcelObject->getProperties()->setCreator("liuggio")
			->setLastModifiedBy("Giulio De Donato")
			->setTitle("Office 2005 XLSX Test Document")
			->setSubject("Office 2005 XLSX Test Document")
			->setDescription("Test document for Office 2005 XLSX, generated using PHP classes.")
			->setKeywords("office 2005 openxml php")
			->setCategory("Test result file");
		$phpExcelObject->setActiveSheetIndex(0);
		foreach($logs as $v){
			$phpExcelObject->setCellValue('A1', $v->getId());
		}
		$phpExcelObject->getActiveSheet()->setTitle('Simple');
		// Set active sheet index to the first sheet, so Excel opens this as the first sheet
		$phpExcelObject->setActiveSheetIndex(0);

		// create the writer
		$writer = $this->get('phpexcel')->createWriter($phpExcelObject, 'Excel5');
		// create the response
		$response = $this->get('phpexcel')->createStreamedResponse($writer);
		// adding headers
		$dispositionHeader = $response->headers->makeDisposition(
			ResponseHeaderBag::DISPOSITION_ATTACHMENT,
			'stream-file.xls'
		);
		$response->headers->set('Content-Type', 'text/vnd.ms-excel; charset=utf-8');
		$response->headers->set('Pragma', 'public');
		$response->headers->set('Cache-Control', 'maxage=1');
		$response->headers->set('Content-Disposition', $dispositionHeader);
		*/

		$response = new Response($output);
		$response->headers->set('Content-Disposition', ':attachment; filename=data.csv');
		$response->headers->set('Content-Type', 'text/csv; charset=utf-8');
		$response->headers->set('Pragma', 'public');
		$response->headers->set('Cache-Control', 'maxage=1');
		return $response;
	}

}
