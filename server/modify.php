<?php
include_once $_SERVER ['DOCUMENT_ROOT'] . '/common.php';
class Controller extends AbstractDao{
	protected function run(){
		if(strtoupper ( $_SERVER ['REQUEST_METHOD'] ) == 'POST'){
			$url = $_REQUEST["url"];
			$idx = (int)trim($_REQUEST["idx"]);
			$ip = $_SERVER["REMOTE_ADDR"];
			$id = $_REQUEST["id"];
			$email = $_REQUEST["email"];
			$comment = $_REQUEST["comment"];
			parent::validateUrl($url);
			parent::validateIdx($idx);
			parent::validate($ip);
			parent::validate($id);
			parent::validate($email);
			parent::validate($comment);
			$comment = str_replace("\r\n", " ", $comment);
			$comment = str_replace("\r", " ", $comment);
			$comment = str_replace("\n", " ", $comment);
			$stmt = null;
			if(parent::isSuperUser($id, $email)){
				$stmt = parent::getStmt ( "INSERT INTO COMMENT_H (oIDX,URL,ID,IP,EMAIL,COMMENT,PARENT,ISDELETED,CREATEDDATE,LASTUPDATED,HISTORYDATE) 
											SELECT IDX,URL,ID,IP,EMAIL,COMMENT,PARENT,ISDELETED,CREATEDDATE,LASTUPDATED,now() FROM COMMENT_T 
												WHERE IDX=? AND URL=? AND ISDELETED=0" );
				$stmt->bind_param ( "is", $idx, $url);
			} else {
				$stmt = parent::getStmt ( "INSERT INTO COMMENT_H (oIDX,URL,ID,IP,EMAIL,COMMENT,PARENT,ISDELETED,CREATEDDATE,LASTUPDATED,HISTORYDATE) 
											SELECT IDX,URL,ID,IP,EMAIL,COMMENT,PARENT,ISDELETED,CREATEDDATE,LASTUPDATED,now() FROM COMMENT_T 
												WHERE IDX=? AND URL=? AND ID=? AND EMAIL=? AND ISDELETED=0" );
				$stmt->bind_param ( "isss", $idx, $url, $id, $email);
			}
			if ($stmt->execute ()) {
				parent::close();
			} else {
				parent::close();
				return array ("result" => "NG");
			}
			$stmt = null;
			if(parent::isSuperUser($id, $email)){
				$stmt = parent::getStmt ( "UPDATE COMMENT_T SET COMMENT=?, IP=?, LASTUPDATED=now() WHERE IDX=? AND URL=? AND ISDELETED=0" );
				$stmt->bind_param ( "ssis", base64_encode ($comment), $ip, $idx, $url);
			} else {
				$stmt = parent::getStmt ( "UPDATE COMMENT_T SET COMMENT=?, IP=?, LASTUPDATED=now() WHERE IDX=? AND URL=? AND ID=? AND EMAIL=? AND ISDELETED=0" );
				$stmt->bind_param ( "ssisss", base64_encode ($comment), $ip, $idx, $url, $id, $email);
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
