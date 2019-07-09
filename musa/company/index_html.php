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
                    <h3 class="categoryname" id="yui_3_17_2_1_1553044988711_132"><a href="#"> 企业中心</a></h3>
                </div>
                <div class="content">
                  <?php if(is_siteadmin()):?>
                    <form method="get" action="<?php echo $CFG->wwwroot ?>/company/edit.php" style="margin-top:30px;">
                        <input type="submit" class="" value="添加新公司"></input>
                    </form>
                  <?php endif;?>
                        <div class="container-fluid p-0">
                            <table cellspacing="0" class="flexible generaltable generalbox" id="participants">
                            <?php
                            foreach ($companys as $company) {
                            ?>
                                <tr>
                                    <td><?php echo $company->company_code?></td>
                                    <td><?php echo $company->company_name?></td>
                                    <td><?php echo $company->is_third_party?'第三方':'非第三方'?></td>
                                    <td>
                                      <div class="col-md-1 span1 p-0 d-flex">
                                        <div class="ml-auto dropdown">
                                            <button class="btn btn-link btn-icon icon-size-3 coursemenubtn" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" aria-label="Actions for current course">
                                                <img class="icon " src="/theme/image.php/lambda/core/1556240688/i/moremenu" alt="" title="">
                                            </button>
                                            <ul class="dropdown-menu pull-right">
                                                <li>
                                                    <a class="dropdown-item p-x-1" href="/company/edit.php?id=<?php echo $company->id?>" target="_blank">
                                                        编辑
                                                    </a>
                                                </li>
                                                <li>
                                                    <a class="dropdown-item p-x-1" href="/company/users.php?id=<?php echo $company->id?>" target="_blank">
                                                        人员
                                                    </a>
                                                </li>
<!--                                                <li>-->
<!--                                                    <a class="dropdown-item p-x-1" href="/company/resume.php" target="_blank">-->
<!--                                                        新增简历-->
<!--                                                    </a>-->
<!--                                                </li>-->
                                                <?php if (is_siteadmin()):?>
                                                <li>
                                                    <a class="dropdown-item p-x-1" href="/company/roles/admins.php?id=<?php echo $company->id?>" target="_blank">
                                                        设置企业管理员
                                                    </a>
                                                </li>
<!--                                                <li>-->
<!--                                                  <a class="dropdown-item p-x-1" href="/company/del?id=--><?php //echo $company->id?><!--">-->
<!--                                                        删除-->
<!--                                                    </a>-->
<!--                                                </li>-->
                                              <?php endif;?>
                                            </ul>
                                        </div>
                                      </div>            
                                    </td>
                                </tr> 
                            <?php }?>
                        </table>
                </div>                    
</body>

</html>

<script type="text/javascript">
    function closejob(id) {
        window.location.href ='/jobs/jobapi?method=closejob&id='+id;
    }
</script>