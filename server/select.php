<?php
include_once $_SERVER ['DOCUMENT_ROOT'] . '/common.php';
class Controller extends AbstractDao{
	protected function run(){
		if(strtoupper ( $_SERVER ['REQUEST_METHOD'] ) == 'POST'){
			$url = $_REQUEST["url"];
			$pid = $_REQUEST["id"];
			$pEmail = $_REQUEST["email"];
			parent::validateUrl($url);
			$stmt = parent::getStmt ( "SELECT IDX, ID, EMAIL, PARENT, CREATEDDATE, LASTUPDATED, COMMENT from COMMENT_T WHERE URL=? AND ISDELETED=0 ORDER BY IDX DESC" );
			$stmt -> bind_param ( "s", $url );
			$stmt->execute ();
			$stmt->bind_result ( $idx, $id, $email, $parent, $createdate ,$lastupdated, $comment );
			$rslt = array ();
			while ( $stmt->fetch () ) {
				$clz = new Comment ();
				$clz->setIdx ( $idx );
				$clz->setId ( $id );
				$clz->setEmail ( $email );
				$clz->setParent ( $parent );
				$clz->setCreatedate ( $createdate );
				$clz->setLastupdated ( $lastupdated );
				$clz->setComment ( base64_decode  ($comment) );
				array_push ( $rslt, $clz );
			}
			parent::close();
			
			$ret = array ();
			for($i=0;$i<count($rslt);$i++){
				if($rslt[$i]->getParent() == null){
					$rslt[$i]->setChild($this->getChild($rslt, $rslt[$i]->getIdx(),$pid, $pEmail));
					array_push ( $ret, $this->createJsonArray($rslt[$i],$pid, $pEmail) );
				}
			}
			return $ret;
		} else {
			header ( "HTTP/1.1 401 Unauthorized"  );
			http_response_code ( 401 );
			die ();
		}
	}
	
	private function getChild($list, $idx, $pid, $pEmail){
		$ret = array();
		for($i=count($list)-1;$i>=0;$i--){
			if($idx == $list[$i]->getParent()){
				$list[$i]->setChild($this->getChild($list, $list[$i]->getIdx(),$pid, $pEmail));
				array_push ( $ret, $this->createJsonArray($list[$i],$pid, $pEmail) );
			}
		}
		return $ret;
	}
	
	private function createJsonArray($node, $id, $email){
		$pos = strpos($node->getEmail(),"@");
		if($pos > 1){
			$emailb = substr($node->getEmail(),0, $pos);
		} else {
			$emailb = $node->getEmail();
		}
		$emailb = substr($emailb,0,3)."******";
		return array (
			"idx" => $node->getIdx(),
			"email" => parent::isSuperUser($node->getId(), $node->getEmail())?"admin":$emailb,
			"parent" => $node->getParent(),
			"createdate" => $node->getCreatedate(),
			"lastupdated" => $node->getLastupdated(),
			"comment" => $node->getComment(),
			"ismodify" => trim($node->getId()) == trim($id) && trim($node->getEmail()) == trim($email) || parent::isSuperUser($id, $email),
			"child" => $node->getChild() != null? $node->getChild():null
		);
	}
}
$obj = new Controller();
?>
<?=$obj->execute()?>

