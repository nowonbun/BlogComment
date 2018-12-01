<?php
include_once $_SERVER ['DOCUMENT_ROOT'] . '/common.php';
class Controller extends AbstractDao{
	protected function run(){
		if(strtoupper ( $_SERVER ['REQUEST_METHOD'] ) == 'POST'){
			$url = $_REQUEST["url"];
			$idx = (int)trim($_REQUEST["idx"]);
			$id = $_REQUEST["id"];
			$email = $_REQUEST["email"];
			parent::validateUrl($url);
			parent::validateIdx($idx);
			parent::validate($id);
			parent::validate($email);
			
			$stmt = null;
			if(parent::isSuperUser($id, $email)){
				$stmt = parent::getStmt ( "UPDATE COMMENT_T SET ISDELETED=1, LASTUPDATED=now() WHERE IDX=? AND URL=?" );
				$stmt->bind_param ( "is", $idx, $url);
			} else {
				$stmt = parent::getStmt ( "UPDATE COMMENT_T SET ISDELETED=1, LASTUPDATED=now() WHERE IDX=? AND URL=? AND ID=? AND EMAIL=?" );
				$stmt->bind_param ( "isss", $idx, $url, $id, $email);
			}
			if ($stmt->execute ()) {
				parent::close();
				return array ("result" => "OK");
			} else {
				parent::close();
				return array ("result" => "NG");
			}
		} else {
			header ( "HTTP/1.1 401 Unauthorized"  );
			http_response_code ( 401 );
			die ();
		}
	}
}
$obj = new Controller();
?>
<?=$obj->execute()?>
