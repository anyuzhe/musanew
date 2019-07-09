<!DOCTYPE html>
<html>

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
</head>

<body>
    <div class="course_category_tree clearfix category-browse category-browse-0">
        <div class="collapsible-actions"></div>
        <div class="content">
            <div class="subcategories" id="yui_3_17_2_1_1553044988711_124">
                <div class="category with_children loaded" data-categoryid="6" data-depth="1" data-showcourses="15"
                data-type="0" id="yui_3_17_2_1_1553044988711_129" aria-expanded="true">
                <div class="info">
                    <h3 class="categoryname" id="yui_3_17_2_1_1553044988711_132"><a href="#"> 企业关系编辑</a></h3>
                </div>
                <div class="content">
                    <?php if(is_siteadmin()):?>
                    <span style="color: red"><?php echo $message?></span>
                    <?php endif;?>
                  <?php if(is_siteadmin()):?>
                    <form method="post" action="<?php echo $CFG->wwwroot ?>/company/relationship/edit.php" style="margin-top:30px;">

                      <?php if(isset($relationship)):?>
                          <select class="" name="status"  autocomplete="off">
                              <option value="-2" <?php if($relationship->status==-2) echo 'selected=selected' ?>>审核失败</option>
                              <option value="-1" <?php if($relationship->status==-1) echo 'selected=selected' ?>>解除</option>
                              <option value="0"  <?php if($relationship->status==0) echo 'selected=selected' ?>>待审核</option>
                              <option value="1"  <?php if($relationship->status==1) echo 'selected=selected' ?>>正常</option>
                          </select>

                          <input type="hidden" name="id" value="<?php echo $relationship->id ?>">
                      <?php else:?>

                          <select class="" name="company_id"  autocomplete="off">
                              <option value="">选择企业</option>
                          <?php foreach ($companys as $k => $v): ?>
                              <option value="<?php echo $v->id ?>"><?php echo $v->company_alias ?></option>
                          <?php endforeach; ?>
                          </select>

                          <select class="" name="relationship_id"  autocomplete="off">
                              <option value="" ?>>选择第三方企业</option>
                              <?php foreach ($thirdPartyCompanys as $k => $v): ?>
                                  <option value="<?php echo $v->id ?>" ><?php echo $v->company_alias ?></option>
                              <?php endforeach; ?>
                          </select>

                          <select class="" name="status"  autocomplete="off">
                              <option value="">选择关系</option>
                              <option value="-2">审核失败</option>
                              <option value="-1">解除</option>
                              <option value="0">待审核</option>
                              <option value="1">正常</option>
                          </select>

                          <input type="hidden" name="id" value="0">
                      <?php endif;?>
                        <input type="submit" class="" value="确定"/>
                    </form>
                  <?php endif;?>
</body>

</html>

<script type="text/javascript">
</script>