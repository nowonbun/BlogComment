<?php
include_once $_SERVER ['DOCUMENT_ROOT'] . '/common.php';
class Controller extends AbstractDao{
	protected function run(){
		if(strtoupper ( $_SERVER ['REQUEST_METHOD'] ) == 'POST'){
			$url = $_REQUEST["url"];
			$ip = $_SERVER["REMOTE_ADDR"];
			$id = $_REQUEST["id"];
			$email = $_REQUEST["email"];
			$comment = $_REQUEST["comment"];
			$parent = (int)$_REQUEST["parent"];
			if($parent < 1){
				$parent = null;
			}
			parent::validateUrl($url);
			parent::validate($ip);
			parent::validate($id);
			parent::validate($email);
			parent::validate($comment);
			$stmt = parent::getStmt ( "INSERT INTO COMMENT_T (URL, IP ,ID, EMAIL, COMMENT, PARENT, CREATEDDATE, LASTUPDATED) VALUES (?, ?, ?, ?, ?, ?, now(), now())" );
			$stmt->bind_param ( "sssssi", $url, $ip, $id, $email, base64_encode  ($comment), $parent);
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
