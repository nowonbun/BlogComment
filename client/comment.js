var auth = {
	id: null,
	email: null
};
function onSignIn(googleUser) {
	$("#commentInsertForm").show();
	$("#googleSigninButton").hide();

	var profile = googleUser.getBasicProfile();
	auth.id = profile.getId();
	auth.email = profile.getEmail();
	getCommentList();
	//console.log(profile);
	//__proto__

	//var id_token = googleUser.getAuthResponse().id_token;
	//console.log("ID Token: " + id_token);
}
function onFailure(error){
	console.log(error);
}
function onLoad() {
	gapi.load('auth2,signin2', function() {
		var auth2 = gapi.auth2.init();
		auth2.then(function() {
			// Current values
			var isSignedIn = auth2.isSignedIn.get();
			var currentUser = auth2.currentUser.get();

			gapi.signin2.render('googleSigninButton', {
				'onsuccess': 'onSignIn',
				'onfailure': 'onFailure',
				'longtitle': true,
				'theme': 'dark',
				'width': '0'
			});
			/*auth2.signOut().then(function () {
			debugger;
			$('.userContent').html('');
			$('#gSignIn').slideDown('slow');
			}); */
			//console.log(currentUser);
			if (!isSignedIn) {
				$("#commentInsertForm").hide();
				$("#googleSigninButton").show();
				getCommentList();
			} else {
				$("#commentInsertForm").show();
				$("#googleSigninButton").hide();
			}
		});
	});
}
function commentAjax(url,data,reload,cb){
	$.ajax({
		url: "http://nowonbun.woobi.co.kr"+url,
		type: "POST",
		dataType: "json",
		data: data,
		success: function (data, textStatus, jqXHR) {
			cb.call(this, data);
			if(reload){
				getCommentList();
			}
		},
		error: function (jqXHR, textStatus, errorThrown) {
			console.log(jqXHR);
			$("#commentSystem").css("text-align","center");
			$("#commentSystem").html("<img src='/img/image_fix.png' style='height: 40px'>");
			toastr.success("시스템 에러입니다.");
		},
		complete: function (jqXHR, textStatus) { }
	});
}
function checkLogin(){
	if($.trim(auth.id) === ""){
		toastr.error("로그인 해주시기 바랍니다.");
		return false;
	}
	if($.trim(auth.email) === ""){
		toastr.error("로그인 해주시기 바랍니다.");
		return false;
	}
	return true;
}
function getCommentList(){
	var data = "url="+location.origin + location.pathname + "&" +
			   "id="+auth.id + "&" +
			   "email="+auth.email; 
	commentAjax("/select.php", data, false, function(node){
		$("#commentList").html("");
		var $target = $("#commentList");
		function createComment(idx, email, createdate, lastupdated, comment, ismodify, isreply){
			var div = $("<div></div>").addClass("comment-item");
			if(isreply){
				div.addClass("comment-reply");
			}
			div.append($("<input type='hidden'>").addClass("comment-idx").val(idx));
			var header = $("<div></div>").addClass("comment-header");
			$area = $("<div class='command-item-panel'></div>");
			if(isreply){
				$area.append($("<i class='fa fa-mail-reply'></i>"));
			}
			if(email == "admin"){
				email = "주인장";
			}
			$area.append($("<span></span>").text("작성자: "+email));
			$area.append($("<span></span>").text("작성일: "+createdate));
			$area.append($("<span></span>").text("수정일: "+lastupdated));
			header.append($area);
			div.append(header);
			div.append($("<div></div>").addClass("comment-box").text(comment));
			$area = $("<div style='text-align:right' class='command-item-panel'></div>");
			if(ismodify){
				$area.append($("<span><a href='javascript:void(0);' class='comment-modify' data-idx='"+idx+"'>수정</a></span>"));
				$area.append($("<span><a href='javascript:void(0);' class='comment-delete' data-idx='"+idx+"'>삭제</a></span>"));
			}
			if(!isreply && $.trim(auth.id) !== "" && $.trim(auth.email) !== ""){
				$area.append($("<span><a href='javascript:void(0);' class='comment-re' data-idx='"+idx+"'>리플달기</a></span>"));
			}
			div.append($area);
			return div;
		}
		function getItem(list,sub){
			for(var i=0;i < list.length; i++){
				var $node = list[i];
				var el = createComment($node.idx, $node.email, $node.createdate, $node.lastupdated, $node.comment, $node.ismodify, sub);
				$target.append(el);
				if($node.child != null && $node.child.length > 0){
					getItem($node.child, true);
				}
			}
		}
		getItem(node, false);
	});
}
$(function(){
	$(document).on("click", ".comment-modify", function(){
		if(!checkLogin()) return;
		var $this = $(this);
		var box = $this.parent().parent().parent().find(".comment-box");
		var comment = box.text();
		box.html("");
		box.append("<div style='text-align:right;'><button type='button' class='btn btn-sm btn-outline-success waves-effect modify-btn'>수정하기</button></div>");
		var item = $("<div></div>").addClass("md-form comment-form");
		item.append($("<textarea class='form-control md-textarea comment-textarea' maxlength='300' length='300' rows='3'></textarea>")
							.prop("id","commentText"+$this.data("idx"))
							.data("idx",$this.data("idx"))
							.text(comment));
		item.append($("<label for='commentText"+$this.data("idx")	+"' class='comment-textarea-label active'>댓글 수정하기</label>"));
		box.append(item);
		$this.remove();
	});
	$(document).on("click",".modify-btn", function(){
		if(!checkLogin()) return;
		var $this = $(this);
		var box = $this.parent().parent().parent().find(".comment-box");
		var textarea = box.find(".comment-textarea");
		var comment = textarea.val().replace("\r\n"," ").replace("\r"," ").replace("\n"," ");
		if($.trim(comment) === ""){
			toastr.error("댓글 내용을 입력해 주시기 바랍니다.");
		}
		var data = "url="+location.origin + location.pathname + "&" +
				   "idx="+textarea.data("idx") + "&" +
				   "id="+auth.id + "&" +
				   "email="+auth.email + "&" +
				   "comment="+comment; 
		commentAjax("/modify.php", data, true, function(){
			toastr.success("수정되었습니다.");
		});
	});
	$(document).on("click", ".comment-delete", function(){
		if(!checkLogin()) return;
		var $this = $(this);
		var data = "url="+location.origin + location.pathname + "&" +
				   "idx="+$this.data("idx") + "&" +
				   "id="+auth.id + "&" +
				   "email="+auth.email;
		commentAjax("/delete.php", data, true, function(){
			toastr.success("삭제되었습니다.");
		});
	});
	$(document).on("click", ".comment-re", function(){
		if(!checkLogin()) return;
		var $this = $(this);
		var box = $("<div></div>").addClass("comment-box").css("margin-top","10px");
		$this.parent().parent().append(box);
		var idx = $this.data("idx");
		box.append("<div style='text-align:right;'><button type='button' class='btn btn-sm btn-outline-success waves-effect reply-btn' data-idx='"+idx+"'>리플 달기</button></div>");
		var item = $("<div></div>").addClass("md-form comment-form");
		item.append($("<textarea class='form-control md-textarea comment-textarea' maxlength='300' length='300' rows='3'></textarea>")
							.prop("id","commentText"+idx)
							.data("idx",idx));
		item.append($("<label for='commentText"+$this.data("idx")+"' class='comment-textarea-label'>리플 달기</label>"));
		box.append(item);
		$this.parent().remove();
	});
	$(document).on("click", ".reply-btn", function(){
		if(!checkLogin()) return;
		var $this = $(this);
		var idx = $this.data("idx");
		var parent = $this.parent().parent();
		parent.parent().append($("<span><a href='javascript:void(0);' class='comment-re' data-idx='"+idx+"'>리플달기</a></span>"));
		var box = parent.parent().find(".comment-box");
		var textarea = box.find(".comment-textarea");
		var comment = textarea.val().replace("\r\n"," ").replace("\r"," ").replace("\n"," ");
		var data = "url="+location.origin + location.pathname + "&" +
				   "id="+auth.id + "&" +
				   "email="+auth.email + "&" +
				   "comment="+comment + "&" +
				   "parent=" + idx;
		commentAjax("/add.php", data, true, function(){
			toastr.success("등록되었습니다.");
			$("#commentText").val("");
			$(".comment-textarea-label").removeClass("active");
		});
		parent.remove();
	});
	$("#createComment").on("click", function(){
		if(!checkLogin()) return;
		var comment = $("#commentText").val().replace("\r\n"," ").replace("\r"," ").replace("\n"," ");
		if($.trim(comment) === ""){
			toastr.error("댓글 내용을 입력해 주시기 바랍니다.");
			return;
		}
		var data = "url="+location.origin + location.pathname + "&" +
				   "id="+auth.id + "&" +
				   "email="+auth.email + "&" +
				   "comment="+comment;
		commentAjax("/add.php", data, true, function(){
			toastr.success("등록되었습니다.");
			$("#commentText").val("");
			$(".comment-textarea-label").removeClass("active");
		});
	});
});