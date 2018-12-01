
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
</head>
<body>
	본 시스템은 「명월의 개발 일지」의 댓글 시스템입니다.<br />
	<a href="http://www.nowonbun.com">http://www.nowonbun.com</a><br />
	<span id="dis"></span>
	<script>
		var sec = 5;
		function display(){
			var dis = document.getElementById("dis");
			dis.innerText = sec + "초 후에 이동합니다."
			if(sec == 0){
				location.href="http://www.nowonbun.com"
			}
			sec--;
			setTimeout(display,1000);
		}
		setTimeout(display,1000);
	</script>
</body>
</html>