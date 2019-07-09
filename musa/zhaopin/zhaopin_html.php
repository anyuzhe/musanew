<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>招聘求职信息</title>
    <link rel="stylesheet" type="text/css" href="css/style.css"/>
    <script type="text/javascript" src="../public/js/jquery-3.4.1.min.js"></script>
</head>
<body>
<form action="/zhaopin/zhaopin.php" method="get">
    <div id="content-container">
        <div class="content_left">
            <div id="positionHead" class="">
                <ul id="filterBox" class="filter-wrapper">
                    <div class="details" id="filterCollapse">
                        <li class="multi-chosen"><span class="title">工作地点：</span>
                            <select name="province" id="id_province">
                                <option value="-1"> 不限</option>
                                <?php foreach ($provinceArr as $v) {
                                    ; ?>
                                    <option value="<?php echo $v->id; ?>" <?php if ($province == $v->id) {
                                        ; ?> selected <?php }; ?>>  <?php echo $v->cname; ?></option>
                                <?php }; ?>
                            </select>
                            <select name="city" id="id_city">
                                <?php if ($cityArr) {
                                    ; ?>
                                    <option value="<?php echo $cityArr->id; ?>">  <?php echo $cityArr->cname; ?></option>
                                <?php } else {
                                    ; ?>
                                    <option value="">不限</option>
                                <?php }; ?>
                            </select>
                        </li>
                        <li class="multi-chosen"><span class="title">学历要求：</span>
                            <select name="education">
                                <option value="" <?php if ($education == "") {
                                    ; ?> selected <?php }; ?>>不限
                                </option>
                                <option value="1" <?php if ($education == 1) {
                                    ; ?> selected <?php }; ?>>大专
                                </option>
                                <option value="2" <?php if ($education == 2) {
                                    ; ?> selected <?php }; ?>>本科
                                </option>
                                <option value="3" <?php if ($education == 3) {
                                    ; ?> selected <?php }; ?>>硕士
                                </option>
                                <option value="4" <?php if ($education == 4) {
                                    ; ?> selected <?php }; ?>>博士
                                </option>
                            </select>
                        </li>

                        <li class="multi-chosen"><span class="title">行业：</span>
                            <select name="occupation">
                                <option value=""> 不限</option>
                                <?php foreach ($occupationArr as $v) {
                                    ; ?>
                                    <option value="<?php echo $v->id; ?>" <?php if ($occupation == $v->id) {
                                        ; ?> selected <?php }; ?>>  <?php echo $v->name; ?></option>
                                <?php }; ?>
                            </select>
                        </li>
                        <li class="multi-chosen"><span class="title">职级：</span>
                            <select name="occupation_rank">
                                <option value=""> 不限</option>
                                <option value="1" <?php if ($occupation_rank == 1) {
                                    ; ?> selected <?php }; ?>>初级
                                </option>
                                <option value="2" <?php if ($occupation_rank == 2) {
                                    ; ?> selected <?php }; ?>>中级
                                </option>
                                <option value="3" <?php if ($occupation_rank == 3) {
                                    ; ?> selected <?php }; ?>>高级
                                </option>

                            </select>
                        </li>
                        <li class="multi-chosen"><span class="title">工作性质：</span>
                            <select name="job_function">
                                <option value=""> 不限</option>
                                <option value="0" <?php if ($job_function == 0) {
                                    ; ?> selected <?php }; ?>>全职
                                </option>
                                <option value="1" <?php if ($job_function == 1) {
                                    ; ?> selected <?php }; ?>>实习
                                </option>
                                <option value="2" <?php if ($job_function == 2) {
                                    ; ?> selected <?php }; ?>>兼职
                                </option>

                            </select>
                        </li>
                    </div>
                </ul>
            </div>
        </div>
    </div>
    <div class="search-wrapper  ">
        <div id="searchBar" class="search-bar">
            <div class="input-wrapper" data-lg-tj-track-code="search_search" data-lg-tj-track-type="1">
                <div class="keyword-wrapper">
                    <input type="text" id="keyword" name="position" maxlength="64" placeholder="搜索职位"
                           value="<?php echo $position; ?>"/>
                </div>
                <input type="submit" value="搜索"/>
            </div>
        </div>
    </div>
</form>
<div id="main_container" style="width:90%;">
    <div class="s_position_list " id="s_position_list">
        <div class="list_item_top">
                <div class="p_top" style="width: 58%;float: left;">
                    <h3>发布职位</h3>
                </div>
                <div>
                    <h3>发布公司</h3>
                </div>
        </div>
        <ul class="item_con_list">

            <?php foreach ($jobs as $j) {
                ; ?>
                <li class="con_list_item default_list" data-index="0">
                    <div class="list_item_top">
                        <div class="position">
                            <div class="p_top">
                                <a class="position_link"
                                   href="/zhaopin/company.php?companyid=<?php echo $j->companyid; ?>&jobid=<?php echo $j->id; ?>"
                                   target="_blank">
                                    <h3><?php echo $j->position; ?></h3>
                                </a>
                            </div>
                        </div>
                        <div class="company">
                            <div class="company_name">
                                <?php echo $j->companyname; ?>
                            </div>
                        </div>
                    </div>
                </li>
            <?php }; ?>
        </ul>
    </div>
</body>
</html>
<script>
    $(document).on('change', '#id_province', function () {
        var pid = $(this).val();
        $.ajax({
            url: '/jobs/jobapi.php?method=arealist&id=' + pid,
            type: 'get',
            dataType: 'json',
            success: function (data) {
                options = '';
                for (let v in data) {
                    options += '<option value="' + data[v].id + '">' + data[v].cname + '</option>';
                }
                $('#id_city').html(options);
            }
        });
    });
</script>