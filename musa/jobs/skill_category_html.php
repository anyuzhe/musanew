<!DOCTYPE html>
<html>
<head>
	<title></title>
</head>
<body>
  <form action="<?php echo $redirecturl?>" method="post">
  	<input type="hidden" name="action" value="add" />
  	<input type="text" name="fieldname"/>
  	<button type="submit">添加</button>
  </form>
  上级分类：<?php echo $pname?>
  <br>
  <ul>
	  <?php foreach($categories as $c):?>
	  	<li><a href="?pid=<?php echo $c->id?>&level=<?php echo $c->level?>"><?php echo $c->category_name ?></a></li>
	  <?php endforeach;?>
</ul>
</body>
</html>