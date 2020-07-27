<?php
namespace OCA\LogAnalyzer\Controller;

use OCP\IRequest;
use OCP\AppFramework\Http\TemplateResponse;
use OCP\AppFramework\Http\DataResponse;
use OCP\AppFramework\Controller;
use OCP\DB\QueryBuilder\IQueryBuilder;
use OCP\IDBConnection;

class PageController extends Controller {
	private $userId;
  protected $connection;
  protected $path=NULL;

	public function __construct($AppName, IRequest $request, $UserId, IDBConnection $connection, DBController $db ){
		parent::__construct($AppName, $request);
		$this->userId = $UserId;
    $this->connection=$connection;
    $this->db=$db;
	}
	/**
	 * CAUTION: the @Stuff turns off security checks; for this page no admin is
	 *          required and no CSRF check. If you don't know what CSRF is, read
	 *          it up in the docs or you might create a security hole. This is
	 *          basically the only required method to add this exemption, don't
	 *          add it to any other method if you don't exactly know what it does
	 *
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 */
	public function index() {
    	$res[]=NULL;
   		//$res=$this->db->getLogData($this->userId);
		$res[0]=$this->db->getUserList();
		$res[1]=$this->db->category();
		return new TemplateResponse('loganalyzer', 'index', $res); // templates/index.php
  }
   
  /**
	 * CAUTION: the @Stuff turns off security checks; for this page no admin is
	 *          required and no CSRF check. If you don't know what CSRF is, read
	 *          it up in the docs or you might create a security hole. This is
	 *          basically the only required method to add this exemption, don't
	 *          add it to any other method if you don't exactly know what it does
	 *
	 * @NoAdminRequired
	 */
  public function index2() {

	$res[]=NULL;
	if($_POST['date1'] > $_POST['date2']) 
		$_POST['date1'] = $_POST['date2'];
	$res[0]=$this->db->getUserList();
	$res[1]=$this->db->category();
    $res[2]=$this->db->getLogData($this->userId, $_POST['date1'], $_POST['date2'], $_POST['users'], $_POST['category']);
	$res[2]=$this->db->formatLogs($res[2]);
	return new TemplateResponse('loganalyzer', 'index', $res);
	}

}
