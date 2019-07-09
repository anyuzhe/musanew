<?php 
header ( "content-type:text/html;charset=utf-8" );
echo '<pre>';
print_r($_GET);
echo '</pre>';
?>
<html>
	<head>
		<title>Default Examples</title>
		<style>
			form {
				margin: 0;
			}
			textarea {
				display: block;
			}
		</style>
		<script charset="utf-8" src="kindeditor-min.js"></script>
		<script charset="utf-8" src="lang/zh_CN.js"></script>
		<script>
			var editor;
			KindEditor.ready(function(K) {
				editor = K.create('textarea[name="content"]', {
					allowFileManager : true,
					allowImageUpload : false 
				});
				
			});
		</script>
	</head>
	<body>
		<h3>默认模式</h3>
		<form>
			<textarea name="content" style="width:800px;height:400px;visibility:hidden;">KindEditor</textarea>
			<input type="submit" value="提交">
		</form>
	</body>
</html>

