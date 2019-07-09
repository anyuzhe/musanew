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
                    <h3 class="categoryname" id="yui_3_17_2_1_1553044988711_132"><a href="#"> 企业关系</a></h3>
                </div>
                <div class="content">
                  <?php if(is_siteadmin()):?>
                    <form method="get" action="<?php echo $CFG->wwwroot ?>/company/relationship/edit.php" style="margin-top:30px;">
                        <input type="submit" class="" value="添加新关系"></input>
                    </form>
                  <?php endif;?>
                        <div class="container-fluid p-0">
                            <table cellspacing="0" class="flexible generaltable generalbox" id="participants">
                                <th>企业</th>
                                <th>第三方企业</th>
                                <th>关系状态</th>
                                <th>操作</th>
                            <?php
                            foreach ($companyRelationships as $companyRelationship) {
                            ?>
                                <tr>
                                    <td><?php echo $companyRelationship->company->company_name?></td>
                                    <td><?php echo $companyRelationship->relationship->company_name?></td>
                                    <td><?php echo $companyRelationship->status_str?></td>
                                    <td>
                                      <div class="col-md-1 p-0 d-flex">
                                        <a class="dropdown-item p-x-1" href="/company/relationship/edit.php?id=<?php echo $companyRelationship->id?>">
                                            编辑
                                        </a>
                                        <a class="dropdown-item p-x-1" style="color: red" href="/company/relationship/delete.php?id=<?php echo $companyRelationship->id?>">
                                            删除
                                        </a>
                                      </div>
                                    </td>
                                </tr> 
                            <?php }?>
                        </table>
                </div>                    
</body>

</html>

<script type="text/javascript">
</script>