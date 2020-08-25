<?php
namespace OCA\LogAnalyzer\Controller;

use OCP\IRequest;
use OCP\AppFramework\Http\TemplateResponse;
use OCP\AppFramework\Http\DataResponse;
use OCP\AppFramework\Controller;
use OCP\DB\QueryBuilder\IQueryBuilder;
use OCP\IDBConnection;

class DBController extends Controller {
	private $userId;
  protected $connection;

	public function __construct($AppName, IRequest $request, $UserId, IDBConnection $connection ){
		parent::__construct($AppName, $request);
		$this->userId = $UserId;
    $this->connection=$connection;
	}
  public function getUserList()
  {
    $dane=[];
    $querybuilder = $this->connection->getQueryBuilder();
    if($this->getAdminStatus($this->userId))
    {
      $querybuilder->select('user')->from('activity')->groupBy('user');
    }
    else
    {
      $querybuilder->select('user')->from('activity')->groupBy('user')->where("user Like ?")->setParameter(0,$this->userId);
    }
    $res =  $querybuilder->execute();
    foreach ($res as $row) {
        $dane[$row['user']]=$this->getUserName($row['user']);
    }
    $res->closeCursor();
    return $dane;
  }
   public function getUserName($uid)
   {
      if($uid)
      {
      
        if(!strcmp('admin',$uid))
          return 'admin';
        elseif(strpos($uid,'@'))
          return $uid;
        else
        { 
            $querybuilder = $this->connection->getQueryBuilder();
            $querybuilder->select('*')->from('accounts')->where("uid Like ".'"'.$uid.'"');
            $res =  $querybuilder->execute();
            $a=(json_decode(($res->fetch())['data'], true)["displayname"]["value"]);
            return $a;
        }
      }
      else
        return NULL;
   }

   public function getLogData($uid, $date1, $date2, $user, $category)
   {
        $katalog=array(
          'file_changed',
          'file_created',
          'file_deleted',
          'file_downloaded',
          'file_restored',
          'public_links',
          'shared'
        );  
        $index=0;
        $data[]=NULL; 
        $path[]=NULL;                                  
     
        if($this->getAdminStatus($uid))
        {
          if(!$user || $user=='selected')
            $user ='%';
          if(!$category || $category=='selected')
            $category ='%';
          $querybuilder = $this->connection->getQueryBuilder();
          $querybuilder->select('*')->from('activity')
                                    ->where("user Like :user")
                                    ->orwhere("affecteduser Like :usera")
                                    ->orwhere("subjectparams Like :users")
                                    ->andwhere('timestamp >= UNIX_TIMESTAMP(:date1)')
                                    ->andwhere('timestamp <= UNIX_TIMESTAMP(:date2)')
                                    ->andwhere("type Like :category")
                                    ->setParameters(array('user' => $user,'usera' => $user,'users' => "%'.$user.'%",'date1' => $date1,'date2' => $date2,'category' => $category));
        }
        else
        {
          if(!$category || $category=='selected')
            $category ='%';
          $querybuilder = $this->connection->getQueryBuilder();
          $querybuilder->select('*')->from('activity')
                                    ->where("user Like :user")
                                    ->orwhere("affecteduser Like :usera")
                                    ->orwhere("subjectparams Like :users")
                                    ->andwhere('timestamp >= UNIX_TIMESTAMP(:date1)')
                                    ->andwhere('timestamp <= UNIX_TIMESTAMP(:date2)')
                                    ->andwhere("type Like :category")
                                    ->setParameters(array('user' => $uid, 'usera' => $uid,'users' => "%'.$uid.'%",'date1' => $date1,'date2' => $date2,'category' => $category));
        }
      
      $res =  $querybuilder->execute();
      foreach ($res as $row) {
        unset($path);
        date_default_timezone_set("Europe/Warsaw");
        $date=date('Y-m-d H:i:s',$row['timestamp']);
        $user = $row["user"];
        $affecteduser = $row["affecteduser"];
        $type = $row["type"];
        $subject = $row["subject"];
        $subjectparams = json_decode($row["subjectparams"],true);
        $downloader=$subjectparams[1];
        if(!is_array($subjectparams[0]))
          $path[]=$subjectparams[0];        
        if(in_array($type,$katalog)){
            foreach($subjectparams as $key => $value){
              foreach($value as $key2 => $value2){
                $path[]=$value2;
              }  
            }
            $data[$index]['timestamp']=$date;
            $data[$index]['user']=$this->getUserName($user);
            $data[$index]['affecteduser']=$this->getUserName($affecteduser);
            $data[$index]['type']=$type;
            $data[$index]['subject']=$subject;
            $data[$index]['downloader']=$this->getUserName($downloader);
            $data[$index]['path']=$path;
            $index++;
        }  
      }
      $res->closeCursor();
      return $data;
   }

   public function getAdminStatus($uid){
    try{
      if($uid)
      {
            $querybuilder = $this->connection->getQueryBuilder();
            $querybuilder->select('*')->from('group_user')->where("uid Like ?")->setParameter(0,$uid);
            $res =  $querybuilder->execute();
            if($res->fetch()['gid'])
                return true;
            else
            return false;
            exit;
      }
      else
        return false;
    }
    catch(Exception $e){
      echo $e->getMessage();
    }
   }
   public function formatLogs($logs){
    try{
      $i=0;
      foreach($logs as $log)
      {

        $wynik[$i]['timestamp']=$log['timestamp'];
        $wynik[$i]['user']=$log['user'];
        $wynik[$i]['account']=$log['affecteduser'];
        $wynik[$i]['description']=$this->subjectDictionary($log['subject']).' '.$log['downloader'];
        $wynik[$i]['object']=$this->pathinfo($log['path']);
        $i++;
      }
     return $wynik;
    }
    catch(Exception $e){
      echo $e->getMessage();
    }
   }

   public function pathinfo($path)
   {
     $wynik=null;
     unset($sciezka);
     try{
      
      foreach($path as $key => $value)
      {
        $fileinfo=pathinfo($value);
        if($fileinfo['extension'] && strlen($fileinfo['extension'])<=4)
        {
          $sciezka[]=str_replace('//','/',$fileinfo['dirname'].'/'.$fileinfo['basename']);
        }
        else
        {
          $sciezka[]=str_replace('//','/',$fileinfo['dirname'].'/'.$fileinfo['basename']);
        }
      }

      foreach($sciezka as $key => $kategoria)
      {
        $wynik.=$kategoria.'<br><br>';
      }
      $wynik=substr($wynik,0,strlen($wynik)-4);
      return $wynik;

     }
     catch(Exception $e){
      echo $e->getMessage();
    }
   }

   public function operationDictionary($operation){
    try{
      
      switch($operation){
        case 'file_changed':
          return 'Zmieniono obiekt';
          break;
        case 'file_created':
          return 'Utworzono obiekt';
          break;
        case 'file_deleted':
          return 'Usunięto obiekt';
          break;
        case 'file_downloaded':
          return 'Obiekt został pobrany';
          break;
        case 'file_restored':
          return 'Obiekt został przywrócony';
          break;
        case 'public_links':
          return 'Obiekt udostępniony publicznie';
          break;
        case 'shared':
          return 'Udostępniono obiekt';
          break;
        default:
          return '';
          break;
      }
     }
     catch(Exception $e){
      echo $e->getMessage();
    }
  }
  public function subjectDictionary($subject){
    try{
      
      switch($subject){

        case 'created_self':
          return 'Utworzono obiekt przez siebie';
          break;
        case 'calendar_add_self':
          return 'Utworzono obiekt kalendarza przez siebie';
          break;
        case 'email_changed_self':
          return 'Zmieniono email przez siebie';
          break;
        case 'changed_self':
          return 'Zmieniono przez siebie';
          break;
        case 'shared_link_self':
          return 'Udostępniono link do obiektu przez siebie';
          break;
        case 'group_added':
          return 'Utworzono obiekt grupy';
          break;
        case 'app_token_created':
          return 'Utworzono obiekt tokenu aplikacji';
          break;
        case 'board_create':
          return 'Utworzono obiekt pulpitu';
          break;
        case 'stack_create':
          return 'Utworzono obiekt stosu';
          break;
        case 'card_create':
          return 'Utworzono obiekt karty';
          break;
        case 'deleted_self':
          return 'Usunięto obiekt przez siebie';
          break;
        case 'shared_user_self':
          return 'Udostępniono ';
          break;
        case 'shared_with_by':
          return 'Udodostępniono obiekt przez: ';
          break;
        case 'moved_self':
          return 'Przeniesiono obiekt przez siebie';
          break;
        case 'renamed_self':
          return 'Zmieniono nazwę obiektu przez siebie';
          break;
        case 'unshared_link_self':
          return 'Cofnięto udostępnienie linku przez siebie';
          break;
        case 'created_by':
          return 'Utworzono obiekt przez';
          break;
        case 'deleted_by':
          return 'Usunięto obiekt przez';
          break;
        case 'calendar_delete':
          return 'Usunięto kalendarz';
          break;
        case 'add_comment_subject':
          return 'Utworzono komentarz obiektu';
          break;
        case 'renamed_by':
          return 'Zmieniono nazwę obiektu';
          break;
        case 'codes_generated':
          return 'Utworzono kody';
          break;
        case 'password_changed_self':
          return 'Zmieniono hasło przez siebie';
          break;
        case 'shared_with_email_password_send':
          return 'Udodstępnniono obiekt z hasłem';
          break;
        case 'shared_with_email_self':
          return 'Udostępniono obiekt emailem';
          break;
        case 'public_shared_file_downloaded':
          return 'Udostępniony obiekt został pobrany przez: ';
          break;
        case 'file_shared_with_email_downloaded':
          return 'Udostępniony (email) obiekt pobrano przez: ';
          break;
        case 'created_public':
          return 'Utworzono publiczny obiekt';
          break;
        case 'public_shared_folder_downloaded':
          return 'Publicznie utworzony obiekt został pobrany przez: ';
          break;
        case 'restored_self':
          return 'Przywrócono obiekt przez siebie';
          break;
        case 'create_tag':
          return 'Utworzono tag';
          break;
        case 'assign_tag':
          return 'Przydzielono tag';
          break;
        case 'shared_file_downloaded':
          return 'Udostępniony obiekt został pobrany przez';
          break;
        case 'unshared_user_self':
          return 'Usunięto udostępnienie przez siebie';
          break;
        case 'unshared_by':
          return 'Usunięto udostępnienie przez';
          break;
        case 'shared_with_email_password_send_self':
          return 'Obiekt udostępniony przez siebie za pomocą email';
          break;
        case 'unshared_with_email_self':
          return 'Usunięto udostępnienie przez siebie';
          break;
        case 'card_user_assign':
          return 'Przypisano użytkownika do karty';
          break;
        case 'moved_by':
          return 'Przeniesiono obiekt przez';
          break;
        case 'added_favorite':
          return 'Dodano do ulubionych';
          break;
        case 'removed_favorite':
          return 'Usunięto z ulubionych';
          break;
        case 'board_update_title':
          return 'Zaktualizowano tytuł pulpitu';
          break;
        case 'board_update_archived':
          return 'Zarchiwizowano aktualizację pulpitu';
          break;
        case 'card_delete':
          return 'Usunięto kartę';
          break;
        case 'stack_delete':
          return 'Usunięto stos';
          break;
        case 'card_user_unassign':
          return 'Cofnięto przypisanie karty';
          break;
        case 'label_assign':
          return 'Przypisano etykietę';
          break;
        case 'link_expired':
          return 'Link do obiektu wygasł';
          break;
        case 'self_unshared':
          return 'Usunięto udostępnienie przez siebie';
          break;
        case 'self_unshared_by':
          return 'Usunięto udostępnienie przez';
          break;
        case 'shared_with_email_by':
          return 'Udostępniono emailem z';
          break;
        case 'unshared_with_email_by':
          return 'Usunięto udostępnienie emailem z';
          break;
        case 'folder_shared_with_email_downloaded':
          return 'Katalog udsotępniony emailem został pobrany';
          break;
        default:
          return 'Brak wyników. Zmień kryteria wyszukiwania i spróbuj ponownie.';
        break;
      }
     }
     catch(Exception $e){
      echo $e->getMessage();
    }
  }
   public function category()
   {
   try{
        $wynik['file_changed']='Zmieniono';
        $wynik['file_created']='Utworzono';
        $wynik['file_deleted']='Usunięto';
        $wynik['file_downloaded']='Pobrano';
        $wynik['file_restored']='Przywrócono';
        $wynik['public_links']='Udostępniono email';
        $wynik['shared']='Udostępniono';

        return $wynik;
     }
     catch(Exception $e){
      echo $e->getMessage();
    }
   }
}
