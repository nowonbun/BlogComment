<?php
error_reporting ( E_ALL ^ E_NOTICE || E_WARNING );
class DBConn {
	private $hostname = "";
	private $username = "";
	private $password = "";
	private $dbname = "";
	public function get() {
		return new mysqli ( $this->hostname, $this->username, $this->password, $this->dbname ); 
	}
}
abstract class AbstractDao {
	private $db;
	private $mysqli;
	
	private $sui = "";
	private $sue = "";
	private $host = "";
	
	protected function getStmt($qy) {
		$this->db = new DBConn ();
		$this->mysqli = $this->db->get ();
		$stmt = $this->mysqli->prepare ( $qy );
		return $stmt;
	}
	protected function close() {
		$this->mysqli->close ();
	}
	public function validateIdx($val){
		if($val == null){
			header ( "HTTP/1.1 401 Unauthorized"  );
			http_response_code ( 401 );
			die ();
		}
		if($val < 1){
			header ( "HTTP/1.1 401 Unauthorized"  );
			http_response_code ( 401 );
			die ();
		}
	}
	public function validateUrl($val){
		if($val == null || trim($val) == ""){
			header ( "HTTP/1.1 401 Unauthorized"  );
			http_response_code ( 401 );
			die ();
		}
		if(preg_match("/".$this->host."/i", $val) == 0){
			header ( "HTTP/1.1 401 Unauthorized"  );
			http_response_code ( 401 );
			die ();
		}
	}
	public function validate($val){
		if($val == null || trim($val) == ""){
			header ( "HTTP/1.1 401 Unauthorized"  );
			http_response_code ( 401 );
			die ();
		}
	}
	public function execute(){
		header("content-type: application/json; charset=utf-8");
		//header("Access-Control-Allow-Origin:*");
		header("Access-Control-Allow-Origin:http://".$this->host);
		return json_encode($this->run());
	}
	protected abstract function run();
	protected function getSuperId(){
		return $this->sui;
	}
	protected function getSuperEmail(){
		return $this->sue;
	}
	protected function getHost(){
		return $this->host;
	}
	protected function isSuperUser($id, $email){
		return trim($this->sui) == trim($id) && trim($this->sue) == trim($email);
	}
}
class Comment{
	private $idx;
	private $id;
	private $email;
	private $parent;
	private $createdate;
	private $lastupdated;
	private $comment;
	private $child;
	private $ip;
	private $deleted;
	private $url;
	
	public function setIdx($idx){
		$this->idx = $idx;
	}
	public function getIdx(){
		return $this->idx;
	}
	public function setId($id){
		$this->id = $id;
	}
	public function getId(){
		return $this->id;
	}
	public function setEmail($email){
		$this->email = $email;
	}
	public function getEmail(){
		return $this->email;
	}
	public function setParent($parent){
		$this->parent = $parent;
	}
	public function getParent(){
		return $this->parent;
	}
	public function setCreatedate($createdate){
		$this->createdate = $createdate;
	}
	public function getCreatedate(){
		return $this->createdate;
	}
	public function setLastupdated($lastupdated){
		$this->lastupdated = $lastupdated;
	}
	public function getLastupdated(){
		return $this->lastupdated;
	}
	public function setComment($comment){
		$this->comment = $comment;
	}
	public function getComment(){
		return $this->comment;
	}
	public function setChild($child){
		$this->child = $child;
	}
	public function getChild(){
		return $this->child;
	}
	public function setIp($ip){
		return $this->ip = $ip;
	}
	public function getIp(){
		return $this->ip;
	}
	public function setDeleted($deleted){
		$this->deleted = $deleted;
	}
	public function isDeleted(){
		return $this->deleted;
	}
	public function setUrl($url){
		$this->url = $url;
	}
	public function getUrl(){
		return $this->url;
	}		
}
?>