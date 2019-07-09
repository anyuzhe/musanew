<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title></title>
    <style>
        ul {
            display: block;
            list-style: none;
            cursor: pointer;
        }

        .lv2U {
            display: none;
        }

        <?php  for ($i = 1;$i<=count($allSkills);$i++){?>
        .lv3U<?php echo $i;?> {
            display: none;
        }

        <?php  }?>
        <?php  for ($i = 1;$i<=count($allSkills);$i++){?>
        .lv3UU<?php echo $i;?> {
            display: none;
        }

        <?php  }?>
        .tree img {
            display: block;
            float: left;
            width: 20px;
            height: 20px;
        }

        .lv3Checks {
            display: block;
            float: left;
            clear: left;
            width: 15px;
            height: 15px;
        }
    </style>
    <script type="text/javascript" src="/public/js/common.js?v=4.04281"></script>
    <script type="text/javascript" src="/public/js/jquery-3.4.1.min.js"></script>
    <script charset="utf-8" src="/public/kindeditor-4.1.2/kindeditor-min.js"></script>
    <script charset="utf-8" src="/public/kindeditor-4.1.2/lang/zh_CN.js"></script>
    <script>
        var editor;
        KindEditor.ready(function (K) {
            editor = K.create('textarea[name="description"]', {
                allowFileManager: true,
                allowImageUpload: false
            });

        });
    </script>
</head>
<body>

<div id="page-content" class="row-fluid">
    <section id="region-main" class="span12">
        <span class="notifications" id="user-notifications"></span>
        <div role="main" id="yui_3_17_2_1_1558419326474_114"><span id="maincontent"></span>
            <h2>新增职位</h2>
            <a class="btn" href="/my/jobs.php?action=copy&cpid=<?php echo $job->id?>" style="float: right" target="_blank">复制</a>
            <form autocomplete="off" action="/my/jobs.php" method="post"
                  accept-charset="utf-8"
                  id="mform1" class="mform">
                <input name="click" id="click" type="hidden" value="0">
                <input name="id" type="hidden" value="<?php echo $id; ?>">
                <input name="companyid" type="hidden" value="<?php echo $company->id; ?>">
                <input name="actions" type="hidden" value="<?php echo $action; ?>">
                <fieldset class="clearfix collapsible" id="id_category_company">
                    <legend class="ftoggler"><a href="#" class="fheader">企业信息</a></legend>
                    <div class="fcontainer clearfix">
                        <div id="fitem_id_company_name" class="fitem fitem_ftext  ">
                            <div class="fitemtitle"><label for="id_company_name">企业名称 </label></div>
                            <div class="felement ftext" data-fieldtype="text">
                                <input name="company_name" type="text" value="<?php echo $company->companyname; ?>"
                                       required/>
                            </div>
                        </div>
                    </div>
                </fieldset>
                <fieldset class=" clearfix collapsible" id="id_category_basic">
                    <legend class="ftoggler"><a href="#" class="fheader" role="button">职位信息</a>
                    </legend>
                    <div class="fcontainer clearfix">
                        <div id="fitem_id_department" class="fitem required fitem_ftext  ">
                            <div class="fitemtitle"><label for="id_department">部门&nbsp;<span
                                            style="color: red">*</span>
                                </label></div>
                            <div class="felement ftext" data-fieldtype="text">
                                <input name="department" type="text" value="<?php echo $job->department; ?>" required>
                            </div>
                        </div>
                        <div id="fitem_id_position" class="fitem required fitem_ftext  ">
                            <div class="fitemtitle"><label for="id_position">职位名称&nbsp;<span
                                            style="color: red">*</span>
                                </label></div>
                            <div class="felement ftext" data-fieldtype="text">
                                <input name="position" type="text" value="<?php echo $job->position; ?>" required>
                            </div>
                        </div>
                        <div id="fitem_id_position_code" class="fitem fitem_ftext  ">
                            <div class="fitemtitle"><label for="id_position_code">职位代码 </label>&nbsp;<span
                                        style="color: red">*</span></div>
                            <div class="felement ftext" data-fieldtype="text">
                                <input name="position_code" type="text" value="<?php echo $job->position_code; ?>"
                                       required></div>
                        </div>
                        <div id="fitem_id_occupationid" class="fitem required fitem_fselect  ">
                            <div class="fitemtitle"><label for="id_occupationid">职业&nbsp;<span
                                            style="color: red">*</span>
                                </label></div>
                            <div class="felement fselect" data-fieldtype="select">
                                <select name="occupationid" required>
                                    <option value="">请选择...</option>
                                    <option value="1" <?php if ($job->occupationid == 1) {
                                        ; ?>  selected <?php }; ?>>软件开发
                                    </option>
                                    <option value="2" <?php if ($job->occupationid == 2) {
                                        ; ?>  selected <?php }; ?>>财务
                                    </option>
                                    <option value="3" <?php if ($job->occupationid == 3) {
                                        ; ?>  selected <?php }; ?>>运营
                                    </option>
                                    <option value="4" <?php if ($job->occupationid == 4) {
                                        ; ?>  selected <?php }; ?>>HR
                                    </option>
                                </select>
                            </div>
                        </div>
                        <div id="fitem_id_occupation_rank" class="fitem required fitem_fselect  ">
                            <div class="fitemtitle"><label for="id_occupation_rank">职级&nbsp;<span
                                            style="color: red">*</span>
                                </label></div>
                            <div class="felement fselect" data-fieldtype="select">
                                <select name="occupation_rank" required>
                                    <option value="">请选择...</option>
                                    <option value="1"<?php if ($job->occupation_rank == 1) {
                                        ; ?>  selected <?php }; ?>>初级
                                    </option>
                                    <option value="2" <?php if ($job->occupation_rank == 2) {
                                        ; ?>  selected <?php }; ?>>中级
                                    </option>
                                    <option value="3" <?php if ($job->occupation_rank == 3) {
                                        ; ?>  selected <?php }; ?>>高级
                                    </option>
                                </select></div>
                        </div>
                        <div id="fitem_id_job_function" class="fitem required fitem_fselect  ">
                            <div class="fitemtitle"><label for="id_job_function">工作性质&nbsp;<span
                                            style="color: red">*</span>
                                </label></div>
                            <div class="felement fselect" data-fieldtype="select">
                                <select name="job_function" required>
                                    <option value="">请选择...</option>
                                    <option value="0" <?php if ($job->job_function == 0) {
                                        ; ?>  selected <?php }; ?>>全职
                                    </option>
                                    <option value="1" <?php if ($job->job_function == 1) {
                                        ; ?>  selected <?php }; ?>>实习
                                    </option>
                                    <option value="2" <?php if ($job->job_function == 2) {
                                        ; ?>  selected <?php }; ?>>兼职
                                    </option>
                                </select></div>
                        </div>
                        <div id="fitem_id_province" class="fitem required fitem_fselect  ">
                            <div class="fitemtitle"><label>工作地点(省)&nbsp;<span
                                            style="color: red">*</span>
                                </label></div>
                            <div class="felement fselect" data-fieldtype="select">
                                <select name="province" id="id_province" required>
                                    <option value="">请选择...</option>
                                    <?php foreach ($provinceArr as $v) {
                                        ; ?>
                                        <option value="<?php echo $v->id; ?>" <?php if ($job->province == $v->id) {
                                            ; ?> selected <?php }; ?>>  <?php echo $v->cname; ?></option>
                                    <?php }; ?>
                                </select>
                            </div>
                        </div>
                        <div id="fitem_id_city" class="fitem required fitem_fselect femptylabel ">
                            <div class="fitemtitle"><label>工作地点(市)&nbsp;<span
                                            style="color: red">*</span>
                                </label></div>
                            <div class="felement fselect" data-fieldtype="select">
                                <select name="city" id="id_city">
                                    <option value="<?php echo $cityArr->id; ?>">  <?php echo $cityArr->cname; ?></option>
                                </select>
                            </div>
                        </div>
                        <div id="fitem_id_status" class="fitem required fitem_fselect  ">
                            <div class="fitemtitle"><label for="id_status">开放范围&nbsp;<span
                                            style="color: red">*</span>
                                </label></div>
                            <div class="felement fselect" data-fieldtype="select">
                                <select name="status">
                                    <option value="0" <?php if ($job->status == 0) {
                                        ; ?>  selected <?php }; ?>>所有人可见
                                    </option>
                                    <option value="1" <?php if ($job->status == 1) {
                                        ; ?>  selected <?php }; ?>>仅自己可见
                                    </option>
                                </select></div>
                        </div>
                        <div id="fitem_id_position" class="fitem required fitem_ftext  ">
                            <div class="fitemtitle"><label for="id_position">职位空缺数&nbsp;<span
                                            style="color: red">*</span>
                                </label></div>
                            <div class="felement ftext" data-fieldtype="text">
                                <input name="vacancy_number" type="text" value="<?php echo $job->vacancy_number; ?>" required>
                            </div>
                        </div>
                        <div id="fitem_id_description" class="fitem required fitem_feditor  ">
                            <div class="fitemtitle"><label for="id_description">岗位职责&nbsp;<span
                                            style="color: red">*</span>
                                </label></div>
                            <div class="felement feditor">
                                <div>
                                    <textarea name="description"
                                              style="width:800px;height:400px;visibility:hidden;"><?php echo $job->description; ?> </textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                </fieldset>
                <fieldset class="clearfix collapsible " id="id_category_condition">
                    <legend class="ftoggler"><a href="#" class="fheader">岗位要求</a></legend>
                    <div class="fcontainer clearfix">
                        <div id="fitem_id_education" class="fitem fitem_fselect  ">
                            <div class="fitemtitle"><label for="id_education">学历 </label></div>
                            <div class="felement fselect" data-fieldtype="select">
                                <select name="education" id="id_education">
                                    <option value="">请选择...</option>
                                    <option value="0" <?php if ($job->education == 0) {
                                        ; ?>  selected <?php }; ?> >不限
                                    </option>
                                    <option value="1" <?php if ($job->education == 1) {
                                        ; ?>  selected <?php }; ?>>大专及以上
                                    </option>
                                    <option value="2" <?php if ($job->education == 2) {
                                        ; ?>  selected <?php }; ?>>本科及以上
                                    </option>
                                    <option value="3" <?php if ($job->education == 3) {
                                        ; ?>  selected <?php }; ?>>硕士及以上
                                    </option>
                                    <option value="4" <?php if ($job->education == 4) {
                                        ; ?>  selected <?php }; ?>>博士
                                    </option>
                                </select>
                            </div>
                        </div>
                        <div id="fitem_id_major" class="fitem fitem_fselect  ">
                            <div class="fitemtitle"><label for="id_major">专业 </label></div>
                            <div class="felement fselect" data-fieldtype="select">
                                <select name="major" id="id_major">
                                    <option value="">请选择...</option>
                                    <option value="0" <?php if ($job->major == 0) {
                                        ; ?>  selected <?php }; ?>>计算机类
                                    </option>
                                    <option value="1" <?php if ($job->major == 1) {
                                        ; ?>  selected <?php }; ?>>财务类
                                    </option>
                                    <option value="2" <?php if ($job->major == 2) {
                                        ; ?>  selected <?php }; ?>>金融类
                                    </option>
                                    <option value="3" <?php if ($job->major == 3) {
                                        ; ?>  selected <?php }; ?>>外语类
                                    </option>
                                    <option value="4" <?php if ($job->major == 4) {
                                        ; ?>  selected <?php }; ?>>精算
                                    </option>
                                    <option value="5" <?php if ($job->major == 5) {
                                        ; ?>  selected <?php }; ?>>数学类
                                    </option>
                                </select>
                            </div>
                        </div>
                    </div>
                </fieldset>
                <fieldset class="clearfix collapsible  " id="id_category_skill">
                    <legend class="ftoggler"><a href="#" class="fheader">专业技能</a>
                    </legend>
                    <div class="fcontainer clearfix">
                        <div class="skills">
                            <a class="btn addskill" onclick="addskill()">添加</a>
                            <div class="skill0">
                                <a class="btn removeskill" href="javascript:;" style="float:right">删除</a>
                                <div id="fgroup_id_skill_0_opt" class="fitem fitem_fgroup femptylabel"
                                     data-groupname="opt">
                                    <div class="fitemtitle">
                                        <div class="fgrouplabel"><label> </label></div>
                                    </div>
                                    <fieldset class="felement fgroup" data-fieldtype="group">
                                                        <span><input name="skill1[opt]" value="必要项"
                                                                     type="radio"><label>必要项</label></span>
                                        <span><input name="skill1[opt]" value="可选项"
                                                     type="radio"><label>可选项</label></span>
                                        <span><input name="skill1[opt]" value="加分项"
                                                     type="radio"><label>加分项</label></span>
                                    </fieldset>
                                </div>
                                <div id="fitem_id_skill_0_skillid" class="fitem fitem_fselect  ">
                                    <div class="fitemtitle"><label>技能名称 </label></div>
                                    <div class="felement html">
                                        <div class="tree">
                                            <ul class="lv1U">
                                                <img src="/public/img/add.png" class="lv1M1"/>
                                                <li>I级类别
                                                    <ul class="lv2U1" style="clear: left;">
                                                        <?php for ($i = 1; $i <= count($allSkills); $i++) { ?>
                                                            <img src="/public/img/add.png" class="lv2M<?php echo $i; ?>"
                                                                <?php if ($i == 1) {
                                                                    ; ?>  style="clear: left;" <?php } ?> />
                                                            <li><?php echo $allSkills[$i]->category_name; ?>
                                                                <ul class="lv3U<?php echo $i; ?>" <?php if ($i == 1) {
                                                                    ; ?>  style="clear: left;" <?php } ?> >
                                                                    <?php foreach ($allSkills[$i]->child as $k => $v) {
                                                                        ; ?>
                                                                        <img src="/public/img/add.png"
                                                                             class="lv2N<?php echo $k; ?>"
                                                                            <?php if ($k == 1) {
                                                                                ; ?>  style="clear: left;" <?php } ?> />
                                                                        <li><?php echo $v->category_name; ?>
                                                                            <ul class="lv3N<?php echo $k; ?>"
                                                                                style="clear: left;">
                                                                                <?php foreach ($allSkills[$i]->child[$k]->childen as $kk => $vv) {
                                                                                    ; ?>
                                                                                    <input type="radio"
                                                                                           name="skill1[skillid]"
                                                                                           class="lv3Checks"
                                                                                           value="<?php echo $vv->id; ?>"/>
                                                                                    <li><?php echo $vv->name; ?></li>
                                                                                <?php }; ?>
                                                                            </ul>
                                                                        </li>

                                                                    <?php }; ?>
                                                                </ul>
                                                            </li>

                                                        <?php } ?>
                                                    </ul>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>

                                </div>
                                <div id="fitem_id_skill_0_used_month" class="fitem fitem_ftext  ">
                                    <div class="fitemtitle">
                                        <label for="id_skill_0_used_month">使用时长（单位/月） </label>
                                    </div>
                                    <div class="felement ftext" data-fieldtype="text">
                                        <input name="skill1[used_month]" type="text" value=""
                                               placeholder="输入数字" oninput="value=value.replace(/[^\d]/g,'')">
                                    </div>
                                </div>
                                <div id="fgroup_id_skill_0_level" class="fitem fitem_fgroup "
                                     data-groupname="level">
                                    <div class="fitemtitle">
                                        <div class="fgrouplabel"><label>掌握程度 </label></div>
                                    </div>
                                    <fieldset class="felement fgroup" data-fieldtype="group">
                                        <span><input name="skill1[level]" value="1"
                                                     type="radio"><label>概念级别</label></span>
                                        <span><input name="skill1[level]" value="2"
                                                     type="radio"><label>实践级别</label></span>
                                        <span><input name="skill1[level]" value="3"
                                                     type="radio"><label>指导级别</label></span>
                                        <span><input name="skill1[level]" value="4"
                                                     type="radio"><label>专家级别</label></span>
                                    </fieldset>
                                </div>
                            </div>
                            <div class="skill1" style="display: none">
                                <hr/>
                                <a class="btn removeskill" href="javascript:;" style="float:right">删除</a>
                                <div id="fgroup_id_skill_0_opt" class="fitem fitem_fgroup femptylabel"
                                     data-groupname="opt">
                                    <div class="fitemtitle">
                                        <div class="fgrouplabel"><label> </label></div>
                                    </div>
                                    <fieldset class="felement fgroup" data-fieldtype="group">
                                                        <span><input name="skill2[opt]" value="必要项"
                                                                     type="radio"><label>必要项</label></span>
                                        <span><input name="skill2[opt]" value="可选项"
                                                     type="radio"><label>可选项</label></span>
                                        <span><input name="skill2[opt]" value="加分项"
                                                     type="radio"><label>加分项</label></span>
                                    </fieldset>
                                </div>
                                <div id="fitem_id_skill_0_skillid" class="fitem fitem_fselect  ">
                                    <div class="fitemtitle"><label>技能名称 </label></div>
                                    <div class="felement html">
                                        <div class="tree">
                                            <ul class="lv1U">
                                                <img src="/public/img/add.png" class="lv1M2"/>
                                                <li>I级类别
                                                    <ul class="lv2U2" style="clear: left;">
                                                        <?php for ($i = 1; $i <= count($allSkills); $i++) { ?>
                                                            <img src="/public/img/add.png"
                                                                 class="lv2MM<?php echo $i; ?>"
                                                                <?php if ($i == 1) {
                                                                    ; ?>  style="clear: left;" <?php } ?> />
                                                            <li><?php echo $allSkills[$i]->category_name; ?>
                                                                <ul class="lv3UU<?php echo $i; ?>" <?php if ($i == 1) {
                                                                    ; ?>  style="clear: left;" <?php } ?> >
                                                                    <?php foreach ($allSkills[$i]->child as $k => $v) {
                                                                        ; ?>
                                                                        <img src="/public/img/add.png"
                                                                             class="lv2NN<?php echo $k; ?>"
                                                                            <?php if ($k == 1) {
                                                                                ; ?>  style="clear: left;" <?php } ?> />
                                                                        <li><?php echo $v->category_name; ?>
                                                                            <ul class="lv3NN<?php echo $k; ?>"
                                                                                style="clear: left;">
                                                                                <?php foreach ($allSkills[$i]->child[$k]->childen as $kk => $vv) {
                                                                                    ; ?>
                                                                                    <input type="radio"
                                                                                           name="skill2[skillid]"
                                                                                           class="lv3Checks"
                                                                                           value="<?php echo $vv->id; ?>"/>
                                                                                    <li><?php echo $vv->name; ?></li>
                                                                                <?php }; ?>
                                                                            </ul>
                                                                        </li>

                                                                    <?php }; ?>
                                                                </ul>
                                                            </li>

                                                        <?php } ?>
                                                    </ul>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>

                                </div>
                                <div id="fitem_id_skill_0_used_month" class="fitem fitem_ftext  ">
                                    <div class="fitemtitle">
                                        <label for="id_skill_0_used_month">使用时长（单位/月） </label>
                                    </div>
                                    <div class="felement ftext" data-fieldtype="text">
                                        <input name="skill2[used_month]" type="text" value=""
                                               placeholder="输入数字" oninput="value=value.replace(/[^\d]/g,'')">
                                    </div>
                                </div>
                                <div id="fgroup_id_skill_0_level" class="fitem fitem_fgroup "
                                     data-groupname="level">
                                    <div class="fitemtitle">
                                        <div class="fgrouplabel"><label>掌握程度 </label></div>
                                    </div>
                                    <fieldset class="felement fgroup" data-fieldtype="group">
                                        <span><input name="skill2[level]" value="1"
                                                     type="radio"><label>概念级别</label></span>
                                        <span><input name="skill2[level]" value="2"
                                                     type="radio"><label>实践级别</label></span>
                                        <span><input name="skill2[level]" value="3"
                                                     type="radio"><label>指导级别</label></span>
                                        <span><input name="skill2[level]" value="4"
                                                     type="radio"><label>专家级别</label></span>
                                    </fieldset>
                                </div>
                            </div>
                            <div class="skill2" style="display: none">
                                <hr/>
                                <a class="btn removeskill" href="javascript:;" style="float:right">删除</a>
                                <div id="fgroup_id_skill_0_opt" class="fitem fitem_fgroup femptylabel"
                                     data-groupname="opt">
                                    <div class="fitemtitle">
                                        <div class="fgrouplabel"><label> </label></div>
                                    </div>
                                    <fieldset class="felement fgroup" data-fieldtype="group">
                                                        <span><input name="skill3[opt]" value="必要项"
                                                                     type="radio"><label>必要项</label></span>
                                        <span><input name="skill3[opt]" value="可选项"
                                                     type="radio"><label>可选项</label></span>
                                        <span><input name="skill3[opt]" value="加分项"
                                                     type="radio"><label>加分项</label></span>
                                    </fieldset>
                                </div>
                                <div id="fitem_id_skill_0_skillid" class="fitem fitem_fselect  ">
                                    <div class="fitemtitle"><label>技能名称 </label></div>
                                    <div class="felement html">
                                        <div class="tree">
                                            <ul class="lv1U">
                                                <img src="/public/img/add.png" class="lv1M3"/>
                                                <li>I级类别
                                                    <ul class="lv2U3" style="clear: left;">
                                                        <?php for ($i = 1; $i <= count($allSkills); $i++) { ?>
                                                            <img src="/public/img/add.png"
                                                                 class="lv2MMM<?php echo $i; ?>"
                                                                <?php if ($i == 1) {
                                                                    ; ?>  style="clear: left;" <?php } ?> />
                                                            <li><?php echo $allSkills[$i]->category_name; ?>
                                                                <ul class="lv3UUU<?php echo $i; ?>" <?php if ($i == 1) {
                                                                    ; ?>  style="clear: left;" <?php } ?> >
                                                                    <?php foreach ($allSkills[$i]->child as $k => $v) {
                                                                        ; ?>
                                                                        <img src="/public/img/add.png"
                                                                             class="lv2NNN<?php echo $k; ?>"
                                                                            <?php if ($k == 1) {
                                                                                ; ?>  style="clear: left;" <?php } ?> />
                                                                        <li><?php echo $v->category_name; ?>
                                                                            <ul class="lv3NNN<?php echo $k; ?>"
                                                                                style="clear: left;">
                                                                                <?php foreach ($allSkills[$i]->child[$k]->childen as $kk => $vv) {
                                                                                    ; ?>
                                                                                    <input type="radio"
                                                                                           name="skill3[skillid][]"
                                                                                           class="lv3Checks"
                                                                                           value="<?php echo $vv->id; ?>"/>
                                                                                    <li><?php echo $vv->name; ?></li>
                                                                                <?php }; ?>
                                                                            </ul>
                                                                        </li>

                                                                    <?php }; ?>
                                                                </ul>
                                                            </li>

                                                        <?php } ?>
                                                    </ul>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>

                                </div>
                                <div id="fitem_id_skill_0_used_month" class="fitem fitem_ftext  ">
                                    <div class="fitemtitle">
                                        <label for="id_skill_0_used_month">使用时长（单位/月） </label>
                                    </div>
                                    <div class="felement ftext" data-fieldtype="text">
                                        <input name="skill3[used_month]" type="text" value=""
                                               placeholder="输入数字" oninput="value=value.replace(/[^\d]/g,'')">
                                    </div>
                                </div>
                                <div id="fgroup_id_skill_0_level" class="fitem fitem_fgroup "
                                     data-groupname="level">
                                    <div class="fitemtitle">
                                        <div class="fgrouplabel"><label>掌握程度 </label></div>
                                    </div>
                                    <fieldset class="felement fgroup" data-fieldtype="group">
                                        <span><input name="skill3[level]" value="1"
                                                     type="radio"><label>概念级别</label></span>
                                        <span><input name="skill3[level]" value="2"
                                                     type="radio"><label>实践级别</label></span>
                                        <span><input name="skill3[level]" value="3"
                                                     type="radio"><label>指导级别</label></span>
                                        <span><input name="skill3[level]" value="4"
                                                     type="radio"><label>专家级别</label></span>
                                    </fieldset>
                                </div>
                            </div>
                            <div class="skill3" style="display: none">
                                <hr/>
                                <div id="fgroup_id_skill_0_opt" class="fitem fitem_fgroup femptylabel"
                                     data-groupname="opt">
                                    <div class="fitemtitle">
                                        <div class="fgrouplabel"><label> </label></div>
                                    </div>
                                    <fieldset class="felement fgroup" data-fieldtype="group">
                                                        <span><input name="skill4[opt]" value="必要项"
                                                                     type="radio"><label>必要项</label></span>
                                        <span><input name="skill4[opt]" value="可选项"
                                                     type="radio"><label>可选项</label></span>
                                        <span><input name="skill4[opt]" value="加分项"
                                                     type="radio"><label>加分项</label></span>
                                    </fieldset>
                                </div>
                                <div id="fitem_id_skill_0_skillid" class="fitem fitem_fselect  ">
                                    <div class="fitemtitle"><label>技能名称 </label></div>
                                    <div class="felement html">
                                        <div class="tree">
                                            <ul class="lv1U">
                                                <img src="/public/img/add.png" class="lv1M4"/>
                                                <li>I级类别
                                                    <ul class="lv2U4" style="clear: left;">
                                                        <?php for ($i = 1; $i <= count($allSkills); $i++) { ?>
                                                            <img src="/public/img/add.png"
                                                                 class="lv2MMMM<?php echo $i; ?>"
                                                                <?php if ($i == 1) {
                                                                    ; ?>  style="clear: left;" <?php } ?> />
                                                            <li><?php echo $allSkills[$i]->category_name; ?>
                                                                <ul class="lv3UUUU<?php echo $i; ?>" <?php if ($i == 1) {
                                                                    ; ?>  style="clear: left;" <?php } ?> >
                                                                    <?php foreach ($allSkills[$i]->child as $k => $v) {
                                                                        ; ?>
                                                                        <img src="/public/img/add.png"
                                                                             class="lv2NNNN<?php echo $k; ?>"
                                                                            <?php if ($k == 1) {
                                                                                ; ?>  style="clear: left;" <?php } ?> />
                                                                        <li><?php echo $v->category_name; ?>
                                                                            <ul class="lv3NNNN<?php echo $k; ?>"
                                                                                style="clear: left;">
                                                                                <?php foreach ($allSkills[$i]->child[$k]->childen as $kk => $vv) {
                                                                                    ; ?>
                                                                                    <input type="radio"
                                                                                           name="skill4[skillid][]"
                                                                                           class="lv3Checks"
                                                                                           value="<?php echo $vv->id; ?>"/>
                                                                                    <li><?php echo $vv->name; ?></li>
                                                                                <?php }; ?>
                                                                            </ul>
                                                                        </li>

                                                                    <?php }; ?>
                                                                </ul>
                                                            </li>

                                                        <?php } ?>
                                                    </ul>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>

                                </div>
                                <div id="fitem_id_skill_0_used_month" class="fitem fitem_ftext  ">
                                    <div class="fitemtitle">
                                        <label for="id_skill_0_used_month">使用时长（单位/月） </label>
                                    </div>
                                    <div class="felement ftext" data-fieldtype="text">
                                        <input name="skill4[used_month]" type="text" value=""
                                               placeholder="输入数字" oninput="value=value.replace(/[^\d]/g,'')">
                                    </div>
                                </div>
                                <div id="fgroup_id_skill_0_level" class="fitem fitem_fgroup "
                                     data-groupname="level">
                                    <div class="fitemtitle">
                                        <div class="fgrouplabel"><label>掌握程度 </label></div>
                                    </div>
                                    <fieldset class="felement fgroup" data-fieldtype="group">
                                                        <span><input name="skill4[level]" value="1"
                                                                     type="radio"><label>概念级别</label></span>
                                        <span><input name="skill4[level]" value="2"
                                                     type="radio"><label>实践级别</label></span>
                                        <span><input name="skill4[level]" value="3"
                                                     type="radio"><label>指导级别</label></span>
                                        <span><input name="skill4[level]" value="4"
                                                     type="radio"><label>专家级别</label></span>
                                    </fieldset>
                                </div>
                            </div>
                            <div class="skill4" style="display: none">
                                <hr/>
                                <div id="fgroup_id_skill_0_opt" class="fitem fitem_fgroup femptylabel"
                                     data-groupname="opt">
                                    <div class="fitemtitle">
                                        <div class="fgrouplabel"><label> </label></div>
                                    </div>
                                    <fieldset class="felement fgroup" data-fieldtype="group">
                                                        <span><input name="skill5[opt]" value="必要项"
                                                                     type="radio"><label>必要项</label></span>
                                        <span><input name="skill5[opt]" value="可选项"
                                                     type="radio"><label>可选项</label></span>
                                        <span><input name="skill5[opt]" value="加分项"
                                                     type="radio"><label>加分项</label></span>
                                    </fieldset>
                                </div>
                                <div id="fitem_id_skill_0_skillid" class="fitem fitem_fselect  ">
                                    <div class="fitemtitle"><label>技能名称 </label></div>
                                    <div class="felement html">
                                        <div class="tree">
                                            <ul class="lv1U">
                                                <img src="/public/img/add.png" class="lv1M5"/>
                                                <li>I级类别
                                                    <ul class="lv2U5" style="clear: left;">
                                                        <?php for ($i = 1; $i <= count($allSkills); $i++) { ?>
                                                            <img src="/public/img/add.png"
                                                                 class="lv2MMMMM<?php echo $i; ?>"
                                                                <?php if ($i == 1) {
                                                                    ; ?>  style="clear: left;" <?php } ?> />
                                                            <li><?php echo $allSkills[$i]->category_name; ?>
                                                                <ul class="lv3UUUUU<?php echo $i; ?>" <?php if ($i == 1) {
                                                                    ; ?>  style="clear: left;" <?php } ?> >

                                                                    <?php foreach ($allSkills[$i]->child as $k => $v) {
                                                                        ; ?>

                                                                        <img src="/public/img/add.png"
                                                                             class="lv2NNNNN<?php echo $k; ?>"
                                                                            <?php if ($k == 1) {
                                                                                ; ?>  style="clear: left;" <?php } ?> />
                                                                        <li><?php echo $v->category_name; ?>
                                                                            <ul class="lv3NNNNN<?php echo $k; ?>"
                                                                                style="clear: left;">
                                                                                <?php foreach ($allSkills[$i]->child[$k]->childen as $kk => $vv) {
                                                                                    ; ?>
                                                                                    <input type="radio"
                                                                                           name="skill5[skillid][]"
                                                                                           class="lv3Checks"
                                                                                           value="<?php echo $vv->id; ?>"/>
                                                                                    <li><?php echo $vv->name; ?></li>
                                                                                <?php }; ?>
                                                                            </ul>
                                                                        </li>

                                                                    <?php }; ?>
                                                                </ul>
                                                            </li>

                                                        <?php } ?>
                                                    </ul>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>

                                </div>
                                <div id="fitem_id_skill_0_used_month" class="fitem fitem_ftext  ">
                                    <div class="fitemtitle">
                                        <label for="id_skill_0_used_month">使用时长（单位/月） </label>
                                    </div>
                                    <div class="felement ftext" data-fieldtype="text">
                                        <input name="skill5[used_month]" type="text" value=""
                                               placeholder="输入数字" oninput="value=value.replace(/[^\d]/g,'')">
                                    </div>
                                </div>
                                <div id="fgroup_id_skill_0_level" class="fitem fitem_fgroup "
                                     data-groupname="level">
                                    <div class="fitemtitle">
                                        <div class="fgrouplabel"><label>掌握程度 </label></div>
                                    </div>
                                    <fieldset class="felement fgroup" data-fieldtype="group">
                                                        <span><input name="skill5[level]" value="1"
                                                                     type="radio"><label>概念级别</label></span>
                                        <span><input name="skill5[level]" value="2"
                                                     type="radio"><label>实践级别</label></span>
                                        <span><input name="skill5[level]" value="3"
                                                     type="radio"><label>指导级别</label></span>
                                        <span><input name="skill5[level]" value="4"
                                                     type="radio"><label>专家级别</label></span>
                                    </fieldset>
                                </div>
                            </div>
                        </div>
                    </div>
                </fieldset>
                <fieldset class="clearfix collapsible  " id="id_category_test">
                    <legend class="ftoggler"><a href="#" class="fheader">求职测试</a>
                    </legend>
                    <div class="fcontainer clearfix">

                        <div id="fitem_id_testname" class="fitem fitem_fselect  ">
                            <div class="fitemtitle"><label for="id_testname">测试名称 </label></div>
                            <div class="felement fselect" data-fieldtype="select">
                                <select name="testname" id="id_testname">
                                    <option value="">请选择...</option>
                                    <?php foreach ($testnames as $k => $v) {
                                        ; ?>
                                        <option value="<?php echo $k; ?>" <?php if ($job->testname == $k) {
                                            ; ?>  selected <?php }; ?>><?php echo $v; ?> </option>
                                    <?php }; ?>
                                </select>
                            </div>
                        </div>
                        <div id="fitem_id_testcourse" class="fitem fitem_fselect  ">
                            <div class="fitemtitle"><label for="id_testcourse">培训课程 </label></div>
                            <div class="felement fselect" data-fieldtype="select">
                                <select name="testcourse" id="id_testcourse">
                                    <option value="">请选择...</option>
                                    <?php foreach ($testcourses as $k => $v) {
                                        ; ?>
                                        <option value="<?php echo $k; ?>" <?php if ($job->testcourse == $k) {
                                            ; ?>  selected <?php }; ?> ><?php echo $v; ?> </option>
                                    <?php }; ?>
                                </select>
                            </div>
                        </div>
                    </div>
                </fieldset>
                <fieldset class="hidden">
                    <div>
                        <div id="fgroup_id_buttonar" class="fitem fitem_actionbuttons fitem_fgroup "
                             data-groupname="buttonar">
                            <div class="felement fgroup" data-fieldtype="group">
                                <input name="" value="保存" type="submit" id="id_submitbutton">
                            </div>
                        </div>
                        <div class="fdescription required">必须填写有&nbsp;<span
                                    style="color: red">*</span>
                            标记的字段
                        </div>
                    </div>
                </fieldset>
            </form>
        </div>
    </section>
</div>

</body>
</html>
<script type="text/javascript" src="/public/js/jquery-3.4.1.min.js"></script>
<script type="text/javascript">
    $(function () {
        <?php  for ($i = 1;$i <= 5;$i++){?>
        $(".lv1M<?php echo $i;?>").click(function () {
            if ($(".lv2U<?php echo $i;?>").is(":visible")) {
                //                      alert("隐藏内容");
                $(".lv1M<?php echo $i;?>").attr("src", "/public/img/add.png");
            } else {
                //                      alert("显示内容");
                $(".lv1M<?php echo $i;?>").attr("src", "/public/img/cut.png");
            }
            $(".lv2U<?php echo $i;?>").slideToggle(300);
        });
        <?php  } ?>
        <?php  for ($i = 1;$i <= count($allSkills);$i++){?>
        $(".lv2M<?php echo $i;?>").click(function () {
            if ($(".lv3U<?php echo $i;?>").is(":visible")) {
                //                      alert("隐藏内容");
                $(".lv2M<?php echo $i;?>").attr("src", "/public/img/add.png");
            } else {
                //                      alert("显示内容");
                $(".lv2M<?php echo $i;?>").attr("src", "/public/img/cut.png");
            }
            $(".lv3U<?php echo $i;?>").slideToggle(300);
        });
        <?php };?>
        <?php  for ($i = 1;$i <= 50;$i++){?>
        $(".lv2N<?php echo $i;?>").click(function () {
            if ($(".lv3N<?php echo $i;?>").is(":visible")) {
                //                      alert("隐藏内容");
                $(".lv2N<?php echo $i;?>").attr("src", "/public/img/add.png");
            } else {
                //                      alert("显示内容");
                $(".lv2N<?php echo $i;?>").attr("src", "/public/img/cut.png");
            }
            $(".lv3N<?php echo $i;?>").slideToggle(300);
        });
        <?php };?>


        <?php  for ($i = 1;$i <= count($allSkills);$i++){?>
        $(".lv2MM<?php echo $i;?>").click(function () {
            if ($(".lv3UU<?php echo $i;?>").is(":visible")) {
                //                      alert("隐藏内容");
                $(".lv2MM<?php echo $i;?>").attr("src", "/public//img/add.png");
            } else {
                //                      alert("显示内容");
                $(".lv2MM<?php echo $i;?>").attr("src", "/public/img/cut.png");
            }
            $(".lv3UU<?php echo $i;?>").slideToggle(300);
        });
        <?php };?>
        <?php  for ($i = 1;$i <= 50;$i++){?>
        $(".lv2NN<?php echo $i;?>").click(function () {
            if ($(".lv3NN<?php echo $i;?>").is(":visible")) {
                //                      alert("隐藏内容");
                $(".lv2NN<?php echo $i;?>").attr("src", "/public/img/add.png");
            } else {
                //                      alert("显示内容");
                $(".lv2NN<?php echo $i;?>").attr("src", "/public/img/cut.png");
            }
            $(".lv3NN<?php echo $i;?>").slideToggle(300);
        });
        <?php };?>

        <?php  for ($i = 1;$i <= count($allSkills);$i++){?>
        $(".lv2MMM<?php echo $i;?>").click(function () {
            if ($(".lv3UUU<?php echo $i;?>").is(":visible")) {
                //                      alert("隐藏内容");
                $(".lv2MMM<?php echo $i;?>").attr("src", "/public//img/add.png");
            } else {
                //                      alert("显示内容");
                $(".lv2MMM<?php echo $i;?>").attr("src", "/public/img/cut.png");
            }
            $(".lv3UUU<?php echo $i;?>").slideToggle(300);
        });
        <?php };?>
        <?php  for ($i = 1;$i <= 50;$i++){?>
        $(".lv2NNN<?php echo $i;?>").click(function () {
            if ($(".lv3NNN<?php echo $i;?>").is(":visible")) {
                //                      alert("隐藏内容");
                $(".lv2NNN<?php echo $i;?>").attr("src", "/public/img/add.png");
            } else {
                //                      alert("显示内容");
                $(".lv2NNN<?php echo $i;?>").attr("src", "/public/img/cut.png");
            }
            $(".lv3NNN<?php echo $i;?>").slideToggle(300);
        });
        <?php };?>


        <?php  for ($i = 1;$i <= count($allSkills);$i++){?>
        $(".lv2MMMM<?php echo $i;?>").click(function () {
            if ($(".lv3UUUU<?php echo $i;?>").is(":visible")) {
                //                      alert("隐藏内容");
                $(".lv2MNMM<?php echo $i;?>").attr("src", "/public//img/add.png");
            } else {
                //                      alert("显示内容");
                $(".lv2MMMM<?php echo $i;?>").attr("src", "/public/img/cut.png");
            }
            $(".lv3UUUU<?php echo $i;?>").slideToggle(300);
        });
        <?php };?>
        <?php  for ($i = 1;$i <= 50;$i++){?>
        $(".lv2NNNN<?php echo $i;?>").click(function () {
            if ($(".lv3NNNN<?php echo $i;?>").is(":visible")) {
                //                      alert("隐藏内容");
                $(".lv2NNNN<?php echo $i;?>").attr("src", "/public//img/add.png");
            } else {
                //                      alert("显示内容");
                $(".lv2NNNN<?php echo $i;?>").attr("src", "/public/img/cut.png");
            }
            $(".lv3NNNN<?php echo $i;?>").slideToggle(300);
        });
        <?php };?>


        <?php  for ($i = 1;$i <= count($allSkills);$i++){?>
        $(".lv2MMMMM<?php echo $i;?>").click(function () {
            if ($(".lv3UUUUU<?php echo $i;?>").is(":visible")) {
                //                      alert("隐藏内容");
                $(".lv2MNMMM<?php echo $i;?>").attr("src", "/public//img/add.png");
            } else {
                //                      alert("显示内容");
                $(".lv2MMMMM<?php echo $i;?>").attr("src", "/public/img/cut.png");
            }
            $(".lv3UUUUU<?php echo $i;?>").slideToggle(300);
        });
        <?php };?>
        <?php  for ($i = 1;$i <= 50;$i++){?>
        $(".lv2NNBNN<?php echo $i;?>").click(function () {
            if ($(".lv3NNBNN<?php echo $i;?>").is(":visible")) {
                //                      alert("隐藏内容");
                $(".lv2NNBNN<?php echo $i;?>").attr("src", "/public//img/add.png");
            } else {
                //                      alert("显示内容");
                $(".lv2NNBNN<?php echo $i;?>").attr("src", "/public/img/cut.png");
            }
            $(".lv3NNNNN<?php echo $i;?>").slideToggle(300);
        });
        <?php };?>

    });
</script>
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


    function addskill() {
        var x = $(" #click").val();
        if (x > 5) {
            alert('一次最多增加五个')
        }
        x++;
        $(".skill" + x).show();
        $("#click").val(x);
    }


</script>
