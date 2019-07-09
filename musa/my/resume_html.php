<!DOCTYPE html>
<html>

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <script type="text/javascript" src="resume.js?v=4.04281"></script>
    <script type="text/javascript" src="/public/js/jquery-ui-1.12.1/jquery-ui.min.js"></script>
    <style>
        #workform, #projectform, #educationform {
            background: #f4f4f4;
            padding-top: 40px;
            border: 1px solid #8ec63f;
        }

        .center {
            text-align: center;
        }

        .addBtn {
            width: 90%;
            margin: 20px 0;
        }

        .normal {
            font-size: 14px;
            font-weight: normal;
        }

        .greenIcon {
            color: #8ec63f;
            padding-left: 10px;
        }

        .padding10 {
            padding: 10px;
        }
    </style>


</head>

<body>
<form name="form1" autocomplete="off" action="resume.php" method="post" accept-charset="utf-8" id="mform1"
      class="mform">

    <fieldset class="clearfix collapsible" id="career_moodle">
        <legend class="ftoggler"><a href="#" class="fheader" role="button" aria-controls="id_moodle"
                                    aria-expanded="true" id="yui_3_17_2_1_1552490143732_270">求职意向</a></legend>
        <div class="userprofile">
            <div class="profile_tree">

                <div class="fcontainer clearfix" id="yui_3_17_2_1_1552490143732_714">
                    <div id="fitem_id_country" class="fitem fitem_fselect  ">
                        <div class="fitemtitle">
                            <label for="id_country">求职状态<span class="req"><img class="icon" alt="此处不能为空。"
                                                                               title="此处不能为空。"
                                                                               src="<?php echo $CFG->wwwroot; ?>/theme/image.php/lambda/core/1552471212/req"></span></label>
                        </div>
                        <div class="felement fselect" data-fieldtype="select">
                            <select autocomplete="country" name="jobstatus" id="id_country">
                                <option value="">请选择...</option>
                                <?php
                                $arr = array("在校，即将毕业", "离职，能快速到岗", "在职，有好机会可以考虑", "在职，暂不考虑换工作");

                                foreach ($arr as $v) {
                                    ?>
                                    <option value="<?php echo $v; ?>" <?php if ($v == $basic->jobstatus) {
                                        echo 'selected';
                                    } ?>>
                                        <?php echo $v; ?>
                                    </option>
                                    <?php
                                }
                                ?>
                            </select>
                        </div>
                    </div>
                    <div id="fitem_id_country" class="fitem fitem_fselect  ">
                        <div class="fitemtitle"><label for="id_country">期望工作性质<span class="req"><img class="icon "
                                                                                                     alt="此处不能为空。"
                                                                                                     title="此处不能为空。"
                                                                                                     src="<?php echo $CFG->wwwroot; ?>/theme/image.php/lambda/core/1552471212/req"></span></label>
                        </div>
                        <div class="felement fselect" data-fieldtype="select">
                            <select autocomplete="country" name="hjobtype" id="id_country">
                                <option value="">请选择...</option>
                                <?php
                                $arr = array("全职", "实习", "兼职");
                                foreach ($arr as $v) {
                                    ?>
                                    <option value="<?php echo $v; ?>" <?php if ($v == $basic->jobtype) {
                                        echo 'selected';
                                    } ?>>
                                        <?php echo $v; ?>
                                    </option>
                                    <?php
                                }
                                ?>
                            </select>
                        </div>
                    </div>
                    <div id="fitem_id_lastname" class="fitem required fitem_ftext  ">
                        <div class="fitemtitle"><label for="id_lastname">期望工作地点<span class="req"><img class="icon "
                                                                                                      alt="此处不能为空。"
                                                                                                      title="此处不能为空。"
                                                                                                      src="<?php echo $CFG->wwwroot; ?>/theme/image.php/lambda/core/1552471212/req"></span>
                            </label></div>
                        <div class="felement fselect" data-fieldtype="select">
                            <select autocomplete="country" name="workplace" id="id_country">
                                <option value="">请选择...</option>
                                <?php
                                $arr = array("北京", "上海", "广州", "深圳");

                                foreach ($arr as $v) {
                                    ?>
                                    <option value="<?php echo $v; ?>" <?php if ($v == $basic->workplace) {
                                        echo 'selected';
                                    } ?>>
                                        <?php echo $v; ?>
                                    </option>
                                    <?php
                                }
                                ?>
                            </select>
                        </div>
                    </div>
                    <div id="fitem_id_lastname" class="fitem required fitem_ftext  ">
                        <div class="fitemtitle"><label for="id_lastname">开始工作时间<span class="req"><img class="icon "
                                                                                                      alt="此处不能为空。"
                                                                                                      title="此处不能为空。"
                                                                                                      src="<?php echo $CFG->wwwroot; ?>/theme/image.php/lambda/core/1552471212/req"></span>
                            </label></div>
                        <div class="felement fselect" data-fieldtype="select">
                            <select autocomplete="country" name="startwork" id="id_country">
                                <option value="">请选择...</option>
                                <?php
                                $arr = array();
                                for ($i = 2000; $i <= 2019; $i++) {
                                    array_push($arr, $i);
                                }

                                foreach ($arr as $v) {
                                    ?>
                                    <option value="<?php echo $v; ?>" <?php if ($v == $basic->startwork) {
                                        echo 'selected';
                                    } ?>>
                                        <?php echo $v; ?>
                                    </option>
                                    <?php
                                }
                                ?>
                            </select>

                        </div>
                    </div>
                    <div id="fitem_id_lastname" class="fitem required fitem_ftext  ">
                        <div class="fitemtitle"><label for="id_lastname">期望从事行业<span class="req"><img class="icon "
                                                                                                      alt="此处不能为空。"
                                                                                                      title="此处不能为空。"
                                                                                                      src="<?php echo $CFG->wwwroot; ?>/theme/image.php/lambda/core/1552471212/req"></span>
                            </label>
                        </div>
                        <div class="felement fselect" data-fieldtype="select">
                            <select autocomplete="country" name="hindustry" id="id_country">
                                <option value="">请选择...</option>
                                <?php
                                $arr = array("互联网/电子商务", "计算机软件", "IT服务（系统/数据/维护）", "计算机硬件", "网络游戏", "保险", "银行", "证券/基金/期货/投资", "房地产/建筑业");

                                foreach ($arr as $v) {
                                    ?>
                                    <option value="<?php echo $v; ?>" <?php if ($v == $basic->industry) {
                                        echo 'selected';
                                    } ?>>
                                        <?php echo $v; ?>
                                    </option>
                                    <?php
                                }
                                ?>
                            </select>
                        </div>
                    </div>
                    <div id="fitem_id_lastname" class="fitem required fitem_ftext  ">
                        <div class="fitemtitle"><label for="id_lastname">期望从事职业<span class="req"><img class="icon "
                                                                                                      alt="此处不能为空。"
                                                                                                      title="此处不能为空。"
                                                                                                      src="<?php echo $CFG->wwwroot; ?>/theme/image.php/lambda/core/1552471212/req"></span>
                            </label></div>
                        <div class="felement fselect" data-fieldtype="select">
                            <select autocomplete="country" name="career" id="id_country">
                                <option value="">请选择...</option>
                                <?php
                                $arr = array("用户界面（UI）设计", "JAVA开发", "前端开发", "计算机硬件", "销售业务", "人力资源");

                                foreach ($arr as $v) {
                                    ?>
                                    <option value="<?php echo $v; ?>" <?php if ($v == $basic->career) {
                                        echo 'selected';
                                    } ?>>
                                        <?php echo $v; ?>
                                    </option>
                                    <?php
                                }
                                ?>
                            </select>
                        </div>
                    </div>
                    <div id="fitem_id_lastname" class="fitem required fitem_ftext  ">
                        <div class="fitemtitle"><label for="id_lastname">期望薪资待遇<span class="req"><img class="icon "
                                                                                                      alt="此处不能为空。"
                                                                                                      title="此处不能为空。"
                                                                                                      src="<?php echo $CFG->wwwroot; ?>/theme/image.php/lambda/core/1552471212/req"></span>
                            </label></div>
                        <div class="felement fselect" data-fieldtype="select">
                            <select autocomplete="country" name="hsalary" id="id_country">
                                <option value="">请选择...</option>
                                <?php
                                $arr = array("5000元/月以下", "5000-8000元/月", "8000-10000元/月", "10000-15000元/月", "15000-20000元/月", "20000元/月以上");

                                foreach ($arr as $v) {
                                    ?>
                                    <option value="<?php echo $v; ?>" <?php if ($v == $basic->salary) {
                                        echo 'selected';
                                    } ?>>
                                        <?php echo $v; ?>
                                    </option>
                                    <?php
                                }
                                ?>

                            </select>
                        </div>
                    </div>
                    <div id="fitem_id_lastname" class="fitem required fitem_ftext  ">
                        <div class="fitemtitle"><label for="id_lastname">个人最高学历<span class="req"><img class="icon "
                                                                                                      alt="此处不能为空。"
                                                                                                      title="此处不能为空。"
                                                                                                      src="<?php echo $CFG->wwwroot; ?>/theme/image.php/lambda/core/1552471212/req"></span>
                            </label></div>
                        <div class="felement fselect" data-fieldtype="select">
                            <select autocomplete="country" name="topeducation" id="id_country">
                                <option value="">请选择...</option>
                                <?php
                                $arr = array("大专", "本科", "硕士", "博士");
                                foreach ($arr as $v) {
                                    ?>
                                    <option value="<?php echo $v; ?>" <?php if ($v == $basic->topeducation) {
                                        echo 'selected';
                                    } ?>>
                                        <?php echo $v; ?>
                                    </option>
                                    <?php
                                }
                                ?>
                            </select>
                        </div>
                    </div>

                </div>

            </div>
        </div>
    </fieldset>

    <fieldset class="clearfix collapsible" id="career_moodle">
        <legend class="ftoggler"><a href="#" class="fheader" role="button" aria-controls="id_moodle"
                                    aria-expanded="true" id="yui_3_17_2_1_1552490143732_270">工作经验</a></legend>
        <?php
        foreach ($companys as $company) {
            ?>
            <div class="content">
                <h3 class="sectionname" style="margin-left: 5rem;">
                    <span id="jobtitle<?php echo $company->id ?>"><?php echo $company->jobtitle ?></span> |
                    <span id="companyname<?php echo $company->id ?>"><?php echo $company->companyname ?></span>
                    <span class="normal">（<span
                                id="jobstart<?php echo $company->id ?>"><?php echo $company->jobstart ?></span> - <span
                                id="jobend<?php echo $company->id ?>"><?php echo $company->jobend ?></span>）</span>
                    <a href="javascript:void(0);" onclick="edit_work(<?php echo $company->id ?>);"><i
                                class="far fa-edit greenIcon"></i></a>
                    <a href="javascript:void(0);" onclick="delitem('company', <?php echo $company->id ?>);"><i
                                class="far fa-trash-alt greenIcon"></i></a>
                </h3>

                <div id="fitem_id_country" class="fitem fitem_fselect  ">
                    <div class="fitemtitle"><label>所属行业</label></div>
                    <div id="industry<?php echo $company->id ?>" class="felement ftext"
                         style="padding-top: 5px;"><?php echo $company->industry; ?></div>
                </div>
                <div id="fitem_id_lastname" class="fitem required fitem_ftext  ">
                    <div class="fitemtitle"><label>职位类别</label></div>
                    <div id="jobtype<?php echo $company->id ?>" class="felement ftext"
                         style="padding-top: 5px;"><?php echo $company->jobtype; ?></div>
                </div>
                <div id="fitem_id_lastname" class="fitem required fitem_ftext  ">
                    <div class="fitemtitle"><label>税前月薪</label></div>
                    <div id="salary<?php echo $company->id ?>" class="felement ftext"
                         style="padding-top: 5px;"><?php echo $company->salary; ?></div>
                </div>
                <div id="fitem_id_lastname" class="fitem required fitem_ftext  ">
                    <div class="fitemtitle"><label>工作描述</label></div>
                    <div id="jobdesc<?php echo $company->id ?>" class="felement ftext"
                         style="padding-top: 5px;"><?php echo $company->jobdesc; ?></div>
                </div>
            </div>
            <?php
        }
        ?>

        <div class="workform content center">
            <button type="button" class="addBtn" onclick="additem('workform');"><i class="fas fa-plus"></i> 添加工作经验
            </button>
        </div>

        <div class="fcontainer clearfix" id="workform">
            <div id="fitem_id_country" class="fitem fitem_fselect  ">
                <div class="fitemtitle"><label for="id_country">公司名称<span class="req"><img class="icon "
                                                                                           alt="此处不能为空。" title="此处不能为空。"
                                                                                           src="<?php echo $CFG->wwwroot; ?>/theme/image.php/lambda/core/1552471212/req"></span></label>
                </div>
                <div class="felement ftext" data-fieldtype="text">
                    <input maxlength="100" size="30" autocomplete="family-name" name="companyname" type="text"
                           id="companyname">
                </div>
            </div>
            <div id="fitem_id_country" class="fitem fitem_fselect  ">
                <div class="fitemtitle"><label for="id_country">所属行业<span class="req"><img class="icon "
                                                                                           alt="此处不能为空。" title="此处不能为空。"
                                                                                           src="<?php echo $CFG->wwwroot; ?>/theme/image.php/lambda/core/1552471212/req"></span></label>
                </div>
                <div class="felement fselect" data-fieldtype="select">
                    <select autocomplete="country" name="industry" id="industry" value="">
                        <option value="">请选择...</option>
                        <?php
                        $arr = array("互联网/电子商务", "计算机软件", "IT服务（系统/数据/维护）", "计算机硬件", "网络游戏", "保险", "银行", "证券/基金/期货/投资", "房地产/建筑业");

                        foreach ($arr as $v) {
                            ?>
                            <option value="<?php echo $v; ?>">
                                <?php echo $v; ?>
                            </option>
                            <?php
                        }
                        ?>
                    </select>
                </div>
            </div>
            <div id="fitem_id_lastname" class="fitem required fitem_ftext  ">
                <div class="fitemtitle"><label for="id_lastname">职位名称<span class="req"><img class="icon "
                                                                                            alt="此处不能为空。"
                                                                                            title="此处不能为空。"
                                                                                            src="<?php echo $CFG->wwwroot; ?>/theme/image.php/lambda/core/1552471212/req"></span>
                    </label></div>
                <div class="felement ftext" data-fieldtype="text"><input maxlength="100" size="30"
                                                                         autocomplete="family-name" name="jobtitle"
                                                                         type="text"
                                                                         id="jobtitle"></div>
            </div>
            <div id="fitem_id_lastname" class="fitem required fitem_ftext  ">
                <div class="fitemtitle"><label for="id_lastname">职位类别<span class="req"><img class="icon "
                                                                                            alt="此处不能为空。"
                                                                                            title="此处不能为空。"
                                                                                            src="<?php echo $CFG->wwwroot; ?>/theme/image.php/lambda/core/1552471212/req"></span>
                    </label>
                </div>
                <div class="felement fselect" data-fieldtype="select">
                    <select autocomplete="country" name="jobtype" id="jobtype">
                        <option value="">请选择...</option>
                        <?php
                        $arr = array("用户界面（UI）设计", "JAVA开发", "前端开发", "计算机硬件", "销售业务", "人力资源");

                        foreach ($arr as $v) {
                            ?>
                            <option value="<?php echo $v; ?>">
                                <?php echo $v; ?>
                            </option>
                            <?php
                        }
                        ?>
                    </select>
                </div>
            </div>
            <div id="fitem_id_lastname" class="fitem required fitem_ftext  ">
                <div class="fitemtitle"><label for="id_lastname">在职时间<span class="req"><img class="icon "
                                                                                            alt="此处不能为空。"
                                                                                            title="此处不能为空。"
                                                                                            src="<?php echo $CFG->wwwroot; ?>/theme/image.php/lambda/core/1552471212/req"></span>
                    </label></div>
                <div class="felement fselect" data-fieldtype="select">
                    <input maxlength="100" size="30" style="width: 40%" placeholder="如:2010-07"
                           autocomplete="family-name" name="jobstart" type="text" id="jobstart" class="datepicker">
                    至
                    <input maxlength="100" size="30" style="width: 40%" placeholder="如:2012-09"
                           autocomplete="family-name" name="jobend" type="text" id="jobend" class="datepicker">
                    <input type="checkbox" id="wtillnow" name="now"><label for="wtillnow">至今</label>
                </div>
            </div>
            <div id="fitem_id_lastname" class="fitem required fitem_ftext  ">
                <div class="fitemtitle"><label for="id_lastname">税前月薪<span class="req"><img class="icon "
                                                                                            alt="此处不能为空。"
                                                                                            title="此处不能为空。"
                                                                                            src="<?php echo $CFG->wwwroot; ?>/theme/image.php/lambda/core/1552471212/req"></span>
                    </label></div>
                <div class="felement fselect" data-fieldtype="select">
                    <select autocomplete="country" name="salary" id="salary">
                        <option value="">请选择...</option>
                        <?php
                        $arr = array("5000元/月以下", "5000-8000元/月", "8000-10000元/月", "10000-15000元/月", "15000-20000元/月", "20000元/月以上");
                        foreach ($arr as $v) {
                            ?>
                            <option value="<?php echo $v; ?>">
                                <?php echo $v; ?>
                            </option>
                            <?php
                        }
                        ?>
                    </select>
                </div>
            </div>
            <div id="fitem_id_lastname" class="fitem required fitem_ftext">
                <div class="fitemtitle"><label for="id_lastname">工作描述<span class="req"><img class="icon "
                                                                                            alt="此处不能为空。"
                                                                                            title="此处不能为空。"
                                                                                            src="<?php echo $CFG->wwwroot; ?>/theme/image.php/lambda/core/1552471212/req"></span>
                    </label></div>
                <div class="felement ftext" data-fieldtype="text">
                    <textarea rows="6" cols="80" spellcheck="true" name="jobdesc" id="jobdesc"></textarea>
                </div>
            </div>

            <div id="fitem_id_country" class="fitem fitem_fselect">
                <div class="fitemtitle"></div>
                <div class="felement ftext" data-fieldtype="text"><input type="button" onclick="save_work()" value="保存">
                    <a class="padding10" href="javascript:void(0);" onclick="cancel('workform');">取消</a></div>
            </div>
        </div>
    </fieldset>

    <fieldset class="clearfix collapsible" id="career_moodle">
        <legend class="ftoggler"><a href="#" class="fheader" role="button" aria-controls="id_moodle"
                                    aria-expanded="true" id="yui_3_17_2_1_1552490143732_270">项目经验</a></legend>

        <?php
        foreach ($projects as $project) {
            ?>
            <div class="content">
                <h3 class="sectionname" style="margin-left: 5rem;"><span
                            id="projectname<?php echo $project->id ?>"><?php echo $project->projectname ?></span> |
                    <span id="relatecompany<?php echo $project->id ?>"><?php echo $project->relatecompany ?></span><span
                            class="normal">（<span
                                id="projectstart<?php echo $project->id ?>"><?php echo $project->projectstart ?></span> - <span
                                id="projectend<?php echo $project->id ?>"><?php echo $project->projectend ?></span>）</span>
                    <a href="javascript:void(0);" onclick="edit_project(<?php echo $project->id ?>);"><i
                                class="far fa-edit greenIcon"></i></a>
                    <a href="javascript:void(0);" onclick="delitem('project',<?php echo $project->id ?>);"><i
                                class="far fa-trash-alt greenIcon"></i></a></h3>

                <div id="fitem_id_lastname" class="fitem required fitem_ftext">
                    <div class="fitemtitle"><label>项目描述</label></div>
                    <div id="projectdesc<?php echo $project->id ?>" class="felement ftext" style="padding-top: 5px;">
                        <?php echo $project->projectdesc; ?>
                    </div>
                </div>
                <div id="fitem_id_lastname" class="fitem required fitem_ftext">
                    <div class="fitemtitle"><label>个人职责</label></div>
                    <div id="responsibility<?php echo $project->id ?>" class="felement ftext" style="padding-top: 5px;">
                        <?php echo $project->responsibility; ?>
                    </div>
                </div>
            </div>
            <?php
        }
        ?>

        <div class="projectform content center">
            <button type="button" class="addBtn" onclick="additem('projectform');"><i class="fas fa-plus"></i> 添加项目经验
            </button>
        </div>

        <div class="fcontainer clearfix" id="projectform">
            <div id="fitem_id_country" class="fitem fitem_fselect  ">
                <div class="fitemtitle"><label for="id_country">项目名称<span class="req"><img class="icon "
                                                                                           alt="此处不能为空。" title="此处不能为空。"
                                                                                           src="<?php echo $CFG->wwwroot; ?>/theme/image.php/lambda/core/1552471212/req"></span></label>
                </div>
                <div class="felement ftext" data-fieldtype="text"><input maxlength="100" size="30"
                                                                         autocomplete="family-name" name="projectname"
                                                                         type="text" id="projectname"></div>
            </div>
            <div id="fitem_id_country" class="fitem fitem_fselect  ">
                <div class="fitemtitle"><label for="id_country">所属公司<span class="req"><img class="icon "
                                                                                           alt="此处不能为空。" title="此处不能为空。"
                                                                                           src="<?php echo $CFG->wwwroot; ?>/theme/image.php/lambda/core/1552471212/req"></span></label>
                </div>
                <div class="felement ftext" data-fieldtype="text"><input maxlength="100" size="30"
                                                                         autocomplete="family-name" name="relatecompany"
                                                                         type="text"
                                                                         value="<?php echo $relatecompany; ?>"
                                                                         id="relatecompany"></div>
            </div>
            <div id="fitem_id_lastname" class="fitem required fitem_ftext  ">
                <div class="fitemtitle"><label for="id_lastname">项目时间<span class="req"><img class="icon "
                                                                                            alt="此处不能为空。"
                                                                                            title="此处不能为空。"
                                                                                            src="<?php echo $CFG->wwwroot; ?>/theme/image.php/lambda/core/1552471212/req"></span>
                    </label></div>
                <div class="felement fselect" data-fieldtype="select">
                    <input maxlength="100" size="30" style="width: 40%" autocomplete="family-name" name="projectstart"
                           type="text" id="projectstart" placeholder="如:2012-09">
                    至
                    <input maxlength="100" size="30" style="width: 40%" autocomplete="family-name" name="projectend"
                           type="text" id="projectend" placeholder="如:2012-12">
                    <input type="checkbox" id="ptillnow" name="tillnow"><label for="ptillnow">至今</label>
                </div>
            </div>
            <div id="fitem_id_lastname" class="fitem required fitem_ftext  ">
                <div class="fitemtitle"><label for="id_lastname">项目描述<span class="req"><img class="icon "
                                                                                            alt="此处不能为空。"
                                                                                            title="此处不能为空。"
                                                                                            src="<?php echo $CFG->wwwroot; ?>/theme/image.php/lambda/core/1552471212/req"></span>
                    </label></div>
                <div class="felement ftext" data-fieldtype="text">
                    <textarea rows="5" cols="80" spellcheck="true" name="projectdesc" id="projectdesc"></textarea>
                </div>
            </div>
            <div id="fitem_id_lastname" class="fitem required fitem_ftext  ">
                <div class="fitemtitle"><label for="id_lastname">个人职责<span class="req"><img class="icon "
                                                                                            alt="此处不能为空。"
                                                                                            title="此处不能为空。"
                                                                                            src="<?php echo $CFG->wwwroot; ?>/theme/image.php/lambda/core/1552471212/req"></span>
                    </label></div>
                <div class="felement ftext" data-fieldtype="text">
                    <textarea rows="5" cols="80" spellcheck="true" name="responsibility" id="responsibility"></textarea>
                </div>
            </div>

            <div id="fitem_id_country" class="fitem fitem_fselect">
                <div class="fitemtitle">
                </div>
                <div class="felement ftext" data-fieldtype="text"><input type="button" onclick="save_project()"
                                                                         value="保存"> <a class="padding10"
                                                                                        href="javascript:void(0);"
                                                                                        onclick="cancel('projectform');">取消</a>
                </div>
            </div>
        </div>
    </fieldset>

    <fieldset class="clearfix collapsible" id="career_moodle">
        <legend class="ftoggler"><a href="#" class="fheader" role="button" aria-controls="id_moodle"
                                    aria-expanded="true" id="yui_3_17_2_1_1552490143732_270">教育经历</a></legend>
        <?php foreach ($educations as $education) { ?>
            <div class="content">
                <h3 class="sectionname" style="margin-left: 5rem;"><span
                            id="degree<?php echo $education->id ?>"><?php echo $education->degree ?></span> | <span
                            id="schoolname<?php echo $education->id ?>"><?php echo $education->schoolname ?></span><span
                            class="normal">（<span
                                id="start<?php echo $education->id ?>"><?php echo $education->start ?></span> - <span
                                id="end<?php echo $education->id ?>"><?php echo $education->end ?></span>）</span>
                    <a href="javascript:void(0);" onclick="edit_education(<?php echo $education->id ?>);"><i
                                class="far fa-edit greenIcon"></i></a>
                    <a href="javascript:void(0);" onclick="delitem('education',<?php echo $education->id ?>);"><i
                                class="far fa-trash-alt greenIcon"></i></a></h3>

                <div id="fitem_id_lastname" class="fitem required fitem_ftext  ">
                    <div class="fitemtitle"><label>所学专业</label></div>
                    <div id="major<?php echo $education->id ?>" class="felement fselect"
                         style="padding-top: 5px;"><?php echo $education->major; ?></div>
                </div>
                <div id="fitem_id_lastname" class="fitem required fitem_ftext  ">
                    <div class="fitemtitle"><label>是否统招</label></div>
                    <div id="national<?php echo $education->id ?>" class="felement fselect"
                         style="padding-top: 5px;"><?php echo $education->national; ?></div>
                </div>
            </div>
        <?php } ?>

        <div class="educationform content center">
            <button type="button" class="addBtn" onclick="additem('educationform');"><i class="fas fa-plus"></i> 添加教育经历
            </button>
        </div>

        <div class="fcontainer clearfix" id="educationform">
            <div id="fitem_id_country" class="fitem fitem_fselect  ">
                <div class="fitemtitle"><label for="id_country">学校名称<span class="req"><img class="icon "
                                                                                           alt="此处不能为空。" title="此处不能为空。"
                                                                                           src="<?php echo $CFG->wwwroot; ?>/theme/image.php/lambda/core/1552471212/req"></span></label>
                </div>
                <div class="felement ftext" data-fieldtype="text"><input maxlength="100" size="30"
                                                                         autocomplete="family-name" name="schoolname"
                                                                         type="text" id="schoolname"></div>
            </div>
            <div id="fitem_id_lastname" class="fitem required fitem_ftext  ">
                <div class="fitemtitle"><label for="id_lastname">学习时间<span class="req"><img class="icon "
                                                                                            alt="此处不能为空。"
                                                                                            title="此处不能为空。"
                                                                                            src="<?php echo $CFG->wwwroot; ?>/theme/image.php/lambda/core/1552471212/req"></span>
                    </label></div>
                <div class="felement fselect" data-fieldtype="select">
                    <input maxlength="100" size="30" style="width: 40%" autocomplete="family-name" name="start"
                           type="text" id="start" placeholder="如:2008-09">
                    至
                    <input maxlength="100" size="30" style="width: 40%" autocomplete="family-name" name="end"
                           type="text" id="end" placeholder="如:2012-07">
                    <input type="checkbox" id="etillnow" name="tillnow"><label for="etillnow">至今</label>
                </div>
            </div>
            <div id="fitem_id_country" class="fitem fitem_fselect  ">
                <div class="fitemtitle"><label for="id_country">所学专业<span class="req"><img class="icon "
                                                                                           alt="此处不能为空。" title="此处不能为空。"
                                                                                           src="<?php echo $CFG->wwwroot; ?>/theme/image.php/lambda/core/1552471212/req"></span></label>
                </div>
                <div class="felement ftext" data-fieldtype="text"><input maxlength="100" size="30"
                                                                         autocomplete="family-name" name="major"
                                                                         type="text" id="major"></div>
            </div>
            <div id="fitem_id_lastname" class="fitem required fitem_ftext  ">
                <div class="fitemtitle"><label for="id_lastname">是否统招<span class="req"><img class="icon "
                                                                                            alt="此处不能为空。"
                                                                                            title="此处不能为空。"
                                                                                            src="<?php echo $CFG->wwwroot; ?>/theme/image.php/lambda/core/1552471212/req"></span>
                    </label></div>
                <div class="felement fselect" data-fieldtype="select">
                    <label for="nationalY"><input type="radio" id="nationalY" name="national" value="是">是</label>
                    <label for="nationalN"><input type="radio" id="nationalN" name="national" value="否">否</label>
                </div>
            </div>
            <div id="fitem_id_lastname" class="fitem required fitem_ftext  ">
                <div class="fitemtitle"><label for="id_lastname">学历<span class="req"><img class="icon "
                                                                                          alt="此处不能为空。" title="此处不能为空。"
                                                                                          src="<?php echo $CFG->wwwroot; ?>/theme/image.php/lambda/core/1552471212/req"></span>
                    </label></div>
                <div class="felement fselect" data-fieldtype="select">
                    <select autocomplete="country" name="degree" id="degree">
                        <option value="">请选择...</option>
                        <?php
                        $arr = array("博士研究生", "硕士研究生", "本科", "大专", "中专", "高中", "初中");
                        foreach ($arr as $v) {
                            ?>
                            <option value="<?php echo $v; ?>">
                                <?php echo $v; ?>
                            </option>
                            <?php
                        }
                        ?>
                    </select>
                </div>
            </div>

            <div id="fitem_id_country" class="fitem fitem_fselect">
                <div class="fitemtitle">
                </div>
                <div class="felement ftext" data-fieldtype="text"><input type="button" onclick="save_education()"
                                                                         value="保存"> <a class="padding10"
                                                                                        href="javascript:void(0);"
                                                                                        onclick="cancel('educationform');">取消</a>
                </div>
            </div>

        </div>
    </fieldset>
    <!-- 专业技能 -->
    <fieldset class="clearfix collapsible" id="skill_moodle">
        <legend class="ftoggler"><a href="#" class="fheader" role="button" aria-controls="id_moodle"
                                    aria-expanded="true" id="yui_3_17_2_1_1552490143732_270">专业技能</a></legend>
        <?php
        foreach ($skills as $skill) {
            ?>
            <div class="content">
                <h3 class="sectionname" style="margin-left: 5rem;">
                    <span id="skillName<?php echo $skill->id ?>"><?php echo $skill->name ?></span>
                    </span>
                    <a href="javascript:void(0);" onclick="edit_skill(<?php echo $skill->id ?>);"><i
                                class="far fa-edit greenIcon"></i></a>
                    <a href="javascript:void(0);" onclick="delitem('skill',<?php echo $skill->id ?>);"><i
                                class="far fa-trash-alt greenIcon"></i></a></h3>
                <div id="fitem_id_lastname" class="fitem required fitem_ftext  ">
                    <div class="fitemtitle"><label>使用时长</label></div>
                    <div class="felement fselect" id="skillUsedMonth<?php echo $skill->id ?>"
                         style="padding-top: 5px;"><?php echo $skill->used_month ?></div>
                </div>
                <div id="fitem_id_lastname" class="fitem required fitem_ftext  ">
                    <div class="fitemtitle"><label>掌握程度</label></div>
                    <div class="felement fselect" style="padding-top: 5px;"
                         id="skillLevel<?php echo $skill->id ?>"><?php echo $skill->level ?></div>
                </div>
            </div>
            <?php
        }
        ?>

        <div class="skillform content center">
            <button type="button" class="addBtn" onclick="additem('skillform');"><i class="fas fa-plus"></i> 添加专业技能
            </button>
        </div>

        <div class="fcontainer clearfix" id="skillform">
            <div id="fitem_id_country" class="fitem fitem_fselect  ">
                <div class="fitemtitle"><label for="id_country">技能名称<span class="req"><img class="icon "
                                                                                           alt="此处不能为空。" title="此处不能为空。"
                                                                                           src="<?php echo $CFG->wwwroot; ?>/theme/image.php/lambda/core/1552471212/req"></span></label>
                </div>
                <div class="felement fselect" data-fieldtype="select">
                    <select autocomplete="country" name="skillName" id="skillName">
                        <option value="">请选择...</option>
                        <?php
                        foreach ($allSkills as $v) {
                            ?>
                            <option data-id="<?php echo $v->id ?>"
                                    value="<?php echo $v->name; ?>" <?php if ($v->name == $skill->name) {
                                echo 'selected';
                            } ?>>
                                <?php echo $v->name; ?>
                            </option>
                            <?php
                        }
                        ?>
                    </select>
                </div>
            </div>
            <div id="fitem_id_lastname" class="fitem required fitem_ftext  ">
                <div class="fitemtitle"><label for="id_country">使用时长<span class="req"><img class="icon "
                                                                                           alt="此处不能为空。" title="此处不能为空。"
                                                                                           src="<?php echo $CFG->wwwroot; ?>/theme/image.php/lambda/core/1552471212/req"></span></label>
                </div>
                <div class="felement ftext" data-fieldtype="text"><input maxlength="3" size="30"
                                                                         autocomplete="family-name"
                                                                         name="skillUsedMonth" type="text"
                                                                         id="skillUsedMonth" placeholder="1 ~ 360">个月
                </div>
            </div>
            <div id="fitem_id_skill_level" class="fitem required fitem_ftext  ">
                <div class="fitemtitle"><label for="id_skill_level">掌握程度<span class="req"><img class="icon"
                                                                                               alt="此处不能为空。"
                                                                                               title="此处不能为空。"
                                                                                               src="<?php echo $CFG->wwwroot; ?>/theme/image.php/lambda/core/1552471212/req"></span>
                    </label></div>
                <div class="felement fselect" data-fieldtype="select">
                    <?php
                    $arr = array("概念级别", "实践级别", "指导级别", "专家级别");
                    foreach ($arr as $k => $v) {
                        ?>
                        <label for="level<?php echo $k; ?>"><input type="radio" id="level<?php echo $k; ?>"
                                                                   name="skillLevel"
                                                                   value="<?php echo $v; ?>"><?php echo $v; ?></label>
                        <?php
                    }
                    ?>
                </div>
            </div>

            <div id="fitem_id_country" class="fitem fitem_fselect">
                <div class="fitemtitle">
                </div>
                <div class="felement ftext" data-fieldtype="text"><input type="button" onclick="save_skills()"
                                                                         value="保存"> <a class="padding10"
                                                                                        href="javascript:void(0);"
                                                                                        onclick="cancel('skillform');">取消</a>
                </div>
            </div>
        </div>
    </fieldset>

    <fieldset class="clearfix collapsible" id="career_moodle">
        <legend class="ftoggler"><a href="#" class="fheader" role="button" aria-controls="id_moodle"
                                    aria-expanded="true" id="yui_3_17_2_1_1552490143732_270">自我评价</a></legend>
        <div class="fcontainer clearfix" id="yui_3_17_2_1_1552490143732_714">
            <div id="fitem_id_lastname" class="fitem required fitem_ftext  ">
                <div class="fitemtitle"><label for="id_lastname">自我评价<span class="req"><img class="icon "
                                                                                            alt="此处不能为空。"
                                                                                            title="此处不能为空。"
                                                                                            src="<?php echo $CFG->wwwroot; ?>/theme/image.php/lambda/core/1552471212/req"></span>
                    </label></div>
                <div class="felement ftext" data-fieldtype="text">
                        <textarea rows="6" cols="80" spellcheck="true" name="intro"
                                  id="id_country"><?php echo $basic->intro; ?></textarea>
                </div>
            </div>

        </div>
    </fieldset>

    <input type="hidden" name="operation" value="submit">
</form>
<hr>
<div>
    <button onclick="window.open('pdf_resume.php?id=<?php echo $USER->id; ?>')">智能化简历生成</button>
    <button type="button" onclick="checkForm()">提交保存</button>
</div>

</body>

</html>
