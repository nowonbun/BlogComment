<?php
session_start();
//https://codeseven.github.io/toastr/demo.html
//https://blackrockdigital.github.io/startbootstrap-sb-admin/tables.html
include_once $_SERVER ['DOCUMENT_ROOT'] . '/common.php';
class Controller extends AbstractDao{
	private $sui = "xx";
	private $sup = "xx";
	private $pid;
	private $pwd;
	private $type;
	private $error;
	private $login = false;
	private $ajax = false;
	protected function run(){
		$this->pid = $_POST["pid"];
		$this->pwd = $_POST["pwd"];
		$this->type = $_REQUEST["type"];
		if ($this->type == "SELECT" && strtoupper ( $_SERVER ['REQUEST_METHOD'] ) == 'POST' && $_SESSION["LOGIN"]){
			$this->ajax = true;
			$stmt = parent::getStmt ( "SELECT IDX, URL, ID, IP, EMAIL, COMMENT, PARENT, ISDELETED, CREATEDDATE, LASTUPDATED from COMMENT_T ORDER BY IDX DESC" );
			$stmt->execute ();
			$stmt->bind_result ( $idx, $url, $id, $ip, $email, $comment, $parent, $isdeleted ,$createddate ,$lastupdated );
			$rslt = array ();
			while ( $stmt->fetch () ) {
				$clz = new Comment ();
				$clz->setIdx ( $idx );
				$clz->setUrl ( $url );
				$clz->setId ( $id );
				$clz->setIp ( $ip );
				$clz->setEmail ( $email );
				$clz->setComment ( base64_decode  ($comment) );
				$clz->setParent ( $parent );
				$clz->setDeleted ( $isdeleted );
				$clz->setCreatedate ( $createddate );
				$clz->setLastupdated ( $lastupdated );
				array_push ( $rslt, $clz );
			}
			parent::close();
			
			$list = array();
			for($i=0;$i<count($rslt);$i++){
				if($rslt[$i]->getParent() == null){
					array_push ( $list, $this->createJsonArray($rslt[$i]) );
					$list = $this->getChild($rslt, $rslt[$i]->getIdx(), $list);
				}
			}
			return array ( "data" => $list);
		} else if ($this->type == "MODIFY" && strtoupper ( $_SERVER ['REQUEST_METHOD'] ) == 'POST' && $_SESSION["LOGIN"]){
			$idx = (int)trim($_REQUEST["idx"]);
			$comment = $_REQUEST["comment"];
			parent::validateIdx($idx);
			parent::validate($comment);
			$comment = str_replace("\r\n", " ", $comment);
			$comment = str_replace("\r", " ", $comment);
			$comment = str_replace("\n", " ", $comment);
			
			$stmt = null;
			$stmt = parent::getStmt ( "INSERT INTO COMMENT_H (oIDX,URL,ID,IP,EMAIL,COMMENT,PARENT,ISDELETED,CREATEDDATE,LASTUPDATED,HISTORYDATE) 
										SELECT IDX,URL,ID,IP,EMAIL,COMMENT,PARENT,ISDELETED,CREATEDDATE,LASTUPDATED,now() FROM COMMENT_T 
											WHERE IDX=?" );
			$stmt->bind_param ( "i", $idx);
			if ($stmt->execute ()) {
				parent::close();
			} else {
				parent::close();
				return array ("result" => "NG");
			}
			$stmt = null;
			$stmt = parent::getStmt ( "UPDATE COMMENT_T SET COMMENT=?, LASTUPDATED=now() WHERE IDX=?" );
			$stmt->bind_param ( "si", base64_encode ($comment), $idx);
			if ($stmt->execute ()) {
				parent::close();
				return array ("result" => "OK");
			} else {
				parent::close();
				return array ("result" => "NG");
			}
		} else if ($this->type == "DELETE" && strtoupper ( $_SERVER ['REQUEST_METHOD'] ) == 'POST' && $_SESSION["LOGIN"]){
			$idx = (int)trim($_REQUEST["idx"]);
			parent::validateIdx($idx);
			$stmt = null;
			$stmt = parent::getStmt ( "UPDATE COMMENT_T SET ISDELETED=1, LASTUPDATED=now() WHERE IDX=?" );
			$stmt->bind_param ( "i", $idx);
			if ($stmt->execute ()) {
				parent::close();
				return array ("result" => "OK");
			} else {
				parent::close();
				return array ("result" => "NG");
			}
		} else if ($this->type == "ACTIVE" && strtoupper ( $_SERVER ['REQUEST_METHOD'] ) == 'POST' && $_SESSION["LOGIN"]){
			$idx = (int)trim($_REQUEST["idx"]);
			parent::validateIdx($idx);
			$stmt = null;
			$stmt = parent::getStmt ( "UPDATE COMMENT_T SET ISDELETED=0, LASTUPDATED=now() WHERE IDX=?" );
			$stmt->bind_param ( "i", $idx);
			if ($stmt->execute ()) {
				parent::close();
				return array ("result" => "OK");
			} else {
				parent::close();
				return array ("result" => "NG");
			}
		} else if(strtoupper ( $_SERVER ['REQUEST_METHOD'] ) == 'POST'){
			if(($this->pid == $this->sui && $this->pwd == $this->sup) || $_SESSION["LOGIN"]){
				$_SESSION["LOGIN"] = true;
				$this->login = true;
			} else {
				$this->error = "아이디, 패스워드를 확인해 주세요.";
			}
		} else {
			$_SESSION["LOGIN"] = null;
		}
	}
	private function getChild($list, $idx, $datalist){
		for($i=count($list)-1;$i>=0;$i--){
			if($idx == $list[$i]->getParent()){
				array_push ( $datalist, $this->createJsonArray($list[$i]) );
			}
		}
		return $datalist;
	}
	private function createJsonArray($node){
		return array (
			"idx" => $node->getIdx(),
			"url" => $node->getUrl(),
			"id" => $node->getId(),
			"ip" => $node->getIp(),
			"email" => $node->getEmail(),
			"comment" => $node->getComment(),
			"createdate" => $node->getCreatedate(),
			"lastupdated" => $node->getLastupdated(),
			"deleted" => $node->IsDeleted(),
			"parent" => $node->getParent()
		);
	}
	public function getId(){
		return $this->pid;
	}
	public function getPwd(){
		return $this->pwd;
	}
	public function getError(){
		return $this->error;
	}
	public function isLogin(){
		return $this->login;
	}
	public function getAjax() {
		return $this->ajax;
	}
}
$obj = new Controller();
$data = $obj->execute();
if(trim($data) == "null"){
	header("content-type: text/html; charset=utf-8");
} else {
	echo $data;
	die();
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="">
    <title>SB Admin 2 - Bootstrap Admin Theme</title>
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.5.0/css/all.css" integrity="sha384-B4dIYHKNBt8Bc12p+WXckhzcICo0wtJAoU8YZTY5qE0Id1GSseTk6S+L3BlXeVIU" crossorigin="anonymous">
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-MCw98/SFnGE8fJT3GXwEOngsV7Zt27NXFoaoApmYm81iuXoPkFOJwJ8ERdknLPMO" crossorigin="anonymous">
    <link href="https://cdn.jsdelivr.net/npm/startbootstrap-sb-admin@5.0.2/css/sb-admin.min.css" rel="stylesheet">
	<link href="https://cdnjs.cloudflare.com/ajax/libs/datatables/1.10.19/css/dataTables.bootstrap4.min.css" rel="stylesheet">
	<link href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/css/toastr.min.css" rel="stylesheet">
	<style>
		.custom-table td{
			white-space: normal;
		}
		.custom-table td, .custom-table th, .custom-table textarea {
			padding:5px;
			font-size: 10pt;
			width: 500px;
		}
		.custom-table td:nth-child(7), .custom-table th:nth-child(7) {
			min-width: 500px;
		}
		.custom-table .control{
			max-width: 20px!important;
			width: 20px!important;
			min-width: 20px!important;
		}
		.custom-table th.control:before{
			display:none!important;
		}
		.custom-table th.control:after{
			display:none!important;
		}
		.custom-table th.control{
			cursor: default!important;
		}
		.dtr-data{
			white-space: normal;
		}
		.custom-table-wrapper div.row:nth-child(2){
			overflow-x: auto;
		}
	</style>
</head>
<?php if(!$obj->isLogin()) {?>
<body class="bg-dark">
	<div class="container">
		<div class="card card-login mx-auto mt-5">
			<div class="card-header">Login</div>
			<div class="card-body">
				<form method="POST">
					<div class="form-group">
						<div class="form-label-group">
							<input type="text" name="pid" id="pid" class="form-control" placeholder="ID" required="required" autofocus="autofocus" autocomplete="false" value="<?=$obj->getId()?>">
							<label for="pid">ID</label>
						</div>
					</div>
					<div class="form-group">
						<div class="form-label-group">
							<input type="password" name="pwd" id="pwd" class="form-control" placeholder="Password" required="required">
							<label for="pwd">Password</label>
						</div>
					</div>
					<input type="submit" class="btn btn-primary btn-block" value="Login">
				</form>
			</div>
		</div>
    </div>
<?php } else { ?>
	<body id="page-top">
    <nav class="navbar navbar-expand navbar-dark bg-dark static-top">
      <a class="navbar-brand mr-1" href="index.html">명월 개발일기 관리화면</a>
      <!-- Navbar -->
      <ul class="navbar-nav ml-auto mr-0 mr-md-3 my-2 my-md-0">
        <li class="nav-item dropdown no-arrow">
          <a class="nav-link dropdown-toggle" href="javascript:void(0);" id="userDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
            <i class="fas fa-user-circle fa-fw"></i>
          </a>
          <div class="dropdown-menu dropdown-menu-right" aria-labelledby="userDropdown">
            <a class="dropdown-item" href="#" data-toggle="modal" data-target="#logoutModal">Logout</a>
          </div>
        </li>
      </ul>
    </nav>
    <div id="wrapper">
      <div id="content-wrapper">
        <div class="container-fluid">
          <ol class="breadcrumb">
            <li class="breadcrumb-item active">댓글 관리 시스템</li>
          </ol>
          <!-- DataTables Example -->
          <div class="card mb-3">
            <div class="card-header">
              <i class="fas fa-table"></i>
              Comment</div>
            <div class="card-body">
              <div class="custom-table-wrapper">
                <table class="table table-bordered custom-table" id="dataTable" width="100%" cellspacing="0">
                  <thead>
                    <tr>
                      <th>I</th>
					  <th>P</th>
                      <th>URL</th>
                      <th>ID</th>
                      <th>IP</th>
                      <th>EMAIL</th>
                      <th>COMMENT</th>
					  <th></th>
					  <th></th>
					  <th>CREATEDDATE</th>
					  <th>LASTUPDATED</th>
                    </tr>
                  </thead>
                </table>
              </div>
            </div>
          </div>
        </div>
        <footer class="sticky-footer" style="width: 100%;">
          <div class="container my-auto">
            <div class="copyright text-center my-auto">
              <span>Copyright www.nowonbun.com 2018</span>
            </div>
          </div>
        </footer>

      </div>
    </div>
    <a class="scroll-to-top rounded" href="#page-top">
      <i class="fas fa-angle-up"></i>
    </a>
    <div class="modal fade" id="logoutModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
      <div class="modal-dialog" role="document">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="exampleModalLabel">Ready to Leave?</h5>
            <button class="close" type="button" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">×</span>
            </button>
          </div>
          <div class="modal-body">Select "Logout" below if you are ready to end your current session.</div>
          <div class="modal-footer">
            <button class="btn btn-secondary" type="button" data-dismiss="modal">Cancel</button>
            <a class="btn btn-primary" href="/admin.php">Logout</a>
          </div>
        </div>
      </div>
    </div>
	
<?php }?>
    <script src="https://code.jquery.com/jquery-3.3.1.min.js" integrity="sha256-FgpCb/KJQlLNfOu91ta32o/NMZxltwRo8QtmkMRdAu8=" crossorigin="anonymous"></script>
    <script src="https://blackrockdigital.github.io/startbootstrap-sb-admin/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/startbootstrap-sb-admin@5.0.2/js/sb-admin.min.js"></script>
	<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/js/toastr.min.js"></script>
	<script src="https://cdnjs.cloudflare.com/ajax/libs/datatables/1.10.19/js/jquery.dataTables.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/datatables/1.10.19/js/dataTables.bootstrap4.min.js"></script>
	<script>
		function commentAjax(data,cb){
			$.ajax({
				url: location.origin + location.pathname+"?"+data,
				type: "POST",
				dataType: "json",
				success: function (data, textStatus, jqXHR) {
					cb.call(this, data);
				},
				error: function (jqXHR, textStatus, errorThrown) {
					console.log(jqXHR);
					toastr.success("시스템 에러입니다.");
				},
				complete: function (jqXHR, textStatus) { }
			});
		}
		function convert(){
			$(".custom-table tbody>tr").each(function(){
				if($(this).data("load") !== 'ok'){
					var $col = $(this).find("td:nth-child(7)");
					var data = $.trim($col.text());
					$col.html("");
					$col.append($("<textarea></textarea>").addClass("form-control").val(data));
					$col = $(this).find("td:nth-child(8)");
					$col.html("");
					$col.append($("<a href='javascript:void(0);' class='modify-link'>수정</a>"));
					$col = $(this).find("td:nth-child(9)");
					var data = $.trim($col.text());
					$col.html("");
					if(data === '0'){
						$col.append($("<a href='javascript:void(0);' class='delete-link'>삭제</a>"));
					} else {
						$col.append($("<a href='javascript:void(0);' class='active-link'>활성</a>"));
					}
					$(this).data("load","ok");
				}
			});
		}
		$(document).ready(function() {
			var table = $('#dataTable').DataTable({
				ajax : {
					url : "admin.php?type=SELECT",
					type : "POST",
					complete : function() {
						var $info = $(".custom-table-wrapper div.row:nth-child(3) .dataTables_info");
						var text = $info.text();
						$info.html("");
						$info.append("<a href='javascript:void(0)' class='refresh-link'><i class='fa fa-refresh' style='margin-right:5px;'></i></a>"+text);
						
						convert();
					},
					error : function(xhr, error, thrown) { }
				},
				columns: [
					{
						data: "idx",
						className : 'idx'
					},
					{data: "parent"},
					{data: "url"},
					{data: "id"},
					{data: "ip"},
					{data: "email"},
					{data: "comment"},
					{data: null, defaultContent : ' '},
					{data: "deleted"},
					{data: "createdate"},
					{data: "lastupdated"},
				],
				autoWidth: false,
				ordering: false,
			});
			$(document).on("click",".pagination a", function(){
				convert();
			});
			$(document).on("change",".dataTables_wrapper input[type=search]", function(){
				convert();
			});
			$(document).on("change",".dataTables_wrapper select", function(){
				convert();
			});
			$(document).on("click",".delete-link", function(){
				$parent = $(this).parent();
				var tr = $(this).parent().parent();
				var data = "idx="+tr.find(".idx").text()+"&type=DELETE";
				commentAjax(data, function(){
					toastr.success("삭제되었습니다.");
					$parent.html("");
					$parent.append($("<a href='javascript:void(0);' class='active-link'>활성</a>"));
				});
			});
			$(document).on("click",".active-link", function(){
				$parent = $(this).parent();
				var tr = $(this).parent().parent();
				var data = "idx="+tr.find(".idx").text()+"&type=ACTIVE";
				commentAjax(data, function(){
					toastr.success("활성되었습니다.");
					$parent.html("");
					$parent.append($("<a href='javascript:void(0);' class='delete-link'>삭제</a>"));
				});
			});
			$(document).on("click",".modify-link", function(){
				var tr = $(this).parent().parent();
				var data = "idx="+tr.find(".idx").text() + "&" +
						   "comment="+tr.find("textarea").val() + "&" +
						   "type=MODIFY";
				commentAjax(data, function(){
					toastr.success("수정되었습니다.");
				});
			});
			$(document).on("click",".refresh-link", function(){
				table.ajax.reload();
			});
		});
	</script>
<?php if($obj->getError() != NULL && trim($obj->getError()) != ""){?>
	<script>
		toastr.error('<?=$obj->getError()?>');
	</script>
<?php }?>
</body>
</html>
