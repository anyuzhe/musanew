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
                    <h3 class="categoryname" id="yui_3_17_2_1_1553044988711_132"><a href="#">职位需求</a></h3>
                </div>
                <div class="content">
                    <form method="get" action="<?php echo $CFG->wwwroot ?>/my/jobs.php" style="margin-top:30px;">
                        <input type="submit" class="" value="添加新职位"></input>
                    </form>
                    <div id="inst46" class="block_myoverview  block" role="complementary" data-block="myoverview" data-instanceid="46" aria-labelledby="instance-46-header"><div class="header"><div class="title" id="yui_3_17_2_1_1557214449480_252"><div class="block_action"><img class="block-hider-hide" src="/theme/image.php/lambda/core/1556240688/t/switch_minus" tabindex="0"><img class="block-hider-show" src="/theme/image.php/lambda/core/1556240688/t/switch_plus" tabindex="0"></div><h2 id="instance-46-header">已添加职位</h2></div></div><div class="content"><div id="block-myoverview-5cd134ef6c15a5cd134ef4d3212" class="block-myoverview block-cards" data-region="myoverview" role="navigation" data-init="true">
                        <!-- <div data-region="filter" class="d-flex align-items-center flex-wrap" aria-label="Course overview controls">
                            <div class="dropdown m-b-1 mr-auto">
                                <button id="groupingdropdown" type="button" class="btn btn-outline-secondary dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" aria-label="Grouping dropdown">
                                    <img class="icon " alt="" aria-hidden="true" src="/theme/image.php/lambda/core/1556240688/i/filter">
                                    <span class="d-sm-inline-block" data-active-item-text=""> 所有职位</span>
                                </button>
                                <ul class="dropdown-menu" data-show-active-item="" data-active-item-text="" aria-labelledby="groupingdropdown">
                                    <li>
                                        <a class="dropdown-item " href="#" data-filter="grouping">隐藏的</a>
                                    </li>
                                </ul>
                            </div>
                        </div> -->

                        <div class="container-fluid p-0">
                            <table data-region="paged-content-page" data-page="1" class="collection">
                                <tr>
                                    <th>职位</th>
                                    <th>公司</th>
                                    <th>部门</th>
                                    <th>发布时间</th>
                                    <th>状态</th>
                                    <th>操作</th>
                                </tr>
                            <?php
                            foreach ($jobs as $job) {
                            ?>
                                <tr>
                                    <td><?php echo $job->position?></td>
                                    <td><?php echo $job->company_name?></td>
                                    <td><?php echo $job->department?></td>
                                    <td><?php echo date('Y-m-d', $job->create_time)?></td>
                                    <td><?php echo jobStatus($job->status)?></td>
                                    <td>
                              <div class="col-md-1 span1 p-0 d-flex">
                                <div class="ml-auto dropdown">
                                    <button class="btn btn-link btn-icon icon-size-3 coursemenubtn" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" aria-label="Actions for current course">
                                        <img class="icon " src="/theme/image.php/lambda/core/1556240688/i/moremenu" alt="" title="">
                                    </button>
                                    <ul class="dropdown-menu pull-right">
                                        <li class="" data-action="add-favourite" data-course-id="15" aria-controls="favorite-icon-15-9">
                                            <a class="dropdown-item p-x-1" href="/my/jobs.php?id=<?php echo $job->id?>&action=edit">
                                                编辑
                                            </a>
                                        </li>
                                    <?php if ($job->status != -1):?>
                                        <li class="" data-action="add-favourite" data-course-id="15" aria-controls="favorite-icon-15-9">
                                            <a class="dropdown-item p-x-1" href="javascript::void(0)" onclick="closejob(<?php echo $job->id?>)">
                                                关闭该职位
                                            </a>
                                        </li>
                                    <?php endif;?>
                                    </ul>
                                </div>            
                            </div>
                        </td>
                       </tr> 
                    <?php }?>
                        </table>
                    </div>                    
                </div>
            </div>
        </div>
    </div>
</body>

</html>

<script type="text/javascript">
    function closejob(id) {
        window.location.href ='/jobs/jobapi.php?method=closejob&id='+id;
    }
</script>