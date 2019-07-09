<!DOCTYPE html>
<html class="ua-mac ua-wk">
<head>
    <meta http-equiv="content-type" content="text/html; charset=UTF-8"/>
    <meta http-equiv="x-ua-compatible" content="ie=8"/>
    <script type="text/javascript" src="resume.js?v=4"></script>
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
<!--简历展示模块-->
<form name="form1" autocomplete="off" action="resume.php" method="post" accept-charset="utf-8" id="mform1"
      class="mform">

    <fieldset class="clearfix collapsible" id="career_moodle">
        <legend class="ftoggler"><a href="#" class="fheader" role="button" aria-controls="id_moodle"
                                    aria-expanded="true" id="yui_3_17_2_1_1552490143732_270">求职意向</a></legend>

        <div class="userprofile">
            <div class="profile_tree">
                <div class="fcontainer clearfix" id="yui_3_17_2_1_1552490143732_714">

                    <div id="fitem_id_country" class="fitem fitem_fselect  ">
                        <div class="fitemtitle"><label for="id_country">求职状态<span class="req"><img class="icon "
                                                                                                   alt="此处不能为空。"
                                                                                                   title="此处不能为空。"
                                                                                                   src="<?php echo $CFG->wwwroot; ?>/theme/image.php/lambda/core/1552471212/req"></span></label>
                        </div>
                        <div class="felement fselect" data-fieldtype="select"><?php echo $basic->jobstatus ?></div>
                    </div>
                    <div id="fitem_id_country" class="fitem fitem_fselect  ">
                        <div class="fitemtitle"><label for="id_country">期望工作性质<span class="req"><img class="icon "
                                                                                                     alt="此处不能为空。"
                                                                                                     title="此处不能为空。"
                                                                                                     src="<?php echo $CFG->wwwroot; ?>/theme/image.php/lambda/core/1552471212/req"></span></label>
                        </div>
                        <div class="felement fselect" data-fieldtype="select"><?php echo $basic->jobtype ?></div>
                    </div>
                    <div id="fitem_id_lastname" class="fitem required fitem_ftext  ">
                        <div class="fitemtitle"><label for="id_lastname">期望工作地点<span class="req"><img class="icon "
                                                                                                      alt="此处不能为空。"
                                                                                                      title="此处不能为空。"
                                                                                                      src="<?php echo $CFG->wwwroot; ?>/theme/image.php/lambda/core/1552471212/req"></span></label>
                        </div>
                        <div class="felement fselect" data-fieldtype="select"><?php echo $basic->workplace ?></div>
                    </div>
                    <div id="fitem_id_lastname" class="fitem required fitem_ftext  ">
                        <div class="fitemtitle"><label for="id_lastname">开始工作时间<span class="req"><img class="icon "
                                                                                                      alt="此处不能为空。"
                                                                                                      title="此处不能为空。"
                                                                                                      src="<?php echo $CFG->wwwroot; ?>/theme/image.php/lambda/core/1552471212/req"></span></label>
                        </div>
                        <div class="felement fselect" data-fieldtype="select"><?php echo $basic->startwork ?></div>
                    </div>
                    <div id="fitem_id_lastname" class="fitem required fitem_ftext  ">
                        <div class="fitemtitle"><label for="id_lastname">期望从事行业<span class="req"><img class="icon "
                                                                                                      alt="此处不能为空。"
                                                                                                      title="此处不能为空。"
                                                                                                      src="<?php echo $CFG->wwwroot; ?>/theme/image.php/lambda/core/1552471212/req"></span></label>
                        </div>
                        <div class="felement fselect" data-fieldtype="select"><?php echo $basic->industry ?></div>
                    </div>
                    <div id="fitem_id_lastname" class="fitem required fitem_ftext  ">
                        <div class="fitemtitle"><label for="id_lastname">期望从事职业<span class="req"><img class="icon "
                                                                                                      alt="此处不能为空。"
                                                                                                      title="此处不能为空。"
                                                                                                      src="<?php echo $CFG->wwwroot; ?>/theme/image.php/lambda/core/1552471212/req"></span></label>
                        </div>
                        <div class="felement fselect" data-fieldtype="select"><?php echo $basic->career ?></div>
                    </div>
                    <div id="fitem_id_lastname" class="fitem required fitem_ftext  ">
                        <div class="fitemtitle"><label for="id_lastname">期望薪资待遇<span class="req"><img class="icon "
                                                                                                      alt="此处不能为空。"
                                                                                                      title="此处不能为空。"
                                                                                                      src="<?php echo $CFG->wwwroot; ?>/theme/image.php/lambda/core/1552471212/req"></span></label>
                        </div>
                        <div class="felement fselect" data-fieldtype="select"><?php echo $basic->salary ?></div>
                    </div>
                    <div id="fitem_id_lastname" class="fitem required fitem_ftext  ">
                        <div class="fitemtitle"><label for="id_lastname">个人最高学历<span class="req"><img class="icon "
                                                                                                      alt="此处不能为空。"
                                                                                                      title="此处不能为空。"
                                                                                                      src="<?php echo $CFG->wwwroot; ?>/theme/image.php/lambda/core/1552471212/req"></span></label>
                        </div>
                        <div class="felement fselect" data-fieldtype="select"><?php echo $basic->topeducation ?></div>
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
                <h3 class="sectionname" style="margin-left: 5rem;"><span
                            id="jobtitle<?php echo $company->id ?>"><?php echo $company->jobtitle ?></span> | <span
                            id="companyname<?php echo $company->id ?>"><?php echo $company->companyname ?></span><span
                            class="normal">（<span
                                id="jobstart<?php echo $company->id ?>"><?php echo $company->jobstart ?></span> - <span
                                id="jobend<?php echo $company->id ?>"><?php echo $company->jobend ?></span>）</span></h3>
                <div class="fcontainer clearfix" id="">
                    <div id="fitem_id_country" class="fitem required fitem_ftext">
                        <div class="fitemtitle"><label for="id_country">所属行业<span class="req"><img class="icon "
                                                                                                   alt="此处不能为空。"
                                                                                                   title="此处不能为空。"
                                                                                                   src="<?php echo $CFG->wwwroot; ?>/theme/image.php/lambda/core/1552471212/req"></span></label>
                        </div>
                        <div class="felement ftext" data-fieldtype="text"><?php echo $company->industry ?></div>
                    </div>
                    <div id="fitem_id_lastname" class="fitem required fitem_ftext  ">
                        <div class="fitemtitle"><label for="id_lastname">职位类别<span class="req"><img class="icon "
                                                                                                    alt="此处不能为空。"
                                                                                                    title="此处不能为空。"
                                                                                                    src="<?php echo $CFG->wwwroot; ?>/theme/image.php/lambda/core/1552471212/req"></span></label>
                        </div>
                        <div class="felement ftext" data-fieldtype="text"><?php echo $company->jobtype ?></div>
                    </div>
                    <div id="fitem_id_lastname" class="fitem required fitem_ftext  ">
                        <div class="fitemtitle"><label for="id_lastname">税前月薪<span class="req"><img class="icon "
                                                                                                    alt="此处不能为空。"
                                                                                                    title="此处不能为空。"
                                                                                                    src="<?php echo $CFG->wwwroot; ?>/theme/image.php/lambda/core/1552471212/req"></span></label>
                        </div>
                        <div class="felement ftext" data-fieldtype="text"><?php echo $company->salary ?></div>
                    </div>
                    <div id="fitem_id_lastname" class="fitem required fitem_ftext  ">
                        <div class="fitemtitle"><label for="id_lastname">工作描述<span class="req"><img class="icon "
                                                                                                    alt="此处不能为空。"
                                                                                                    title="此处不能为空。"
                                                                                                    src="<?php echo $CFG->wwwroot; ?>/theme/image.php/lambda/core/1552471212/req"></span></label>
                        </div>
                        <div class="felement ftext" data-fieldtype="text"><?php echo $company->jobdesc; ?></div>
                    </div>
                </div>
            </div>
        <?php } ?>
    </fieldset>
    <fieldset class="clearfix collapsible" id="career_moodle">
        <legend class="ftoggler"><a href="#" class="fheader" role="button" aria-controls="id_moodle"
                                    aria-expanded="true" id="yui_3_17_2_1_1552490143732_270">项目经验</a></legend>
        <?php
        foreach ($projects

        as $project){
        ?>
        <div class="content">
            <h3 class="sectionname" style="margin-left: 5rem;"><span
                        id="projectname<?php echo $project->id ?>"><?php echo $project->projectname ?></span> | <span
                        id="relatecompany<?php echo $project->id ?>"><?php echo $project->relatecompany ?></span><span
                        class="normal">（<span
                            id="projectstart<?php echo $project->id ?>"><?php echo $project->projectstart ?></span> - <span
                            id="projectend<?php echo $company->id ?>"><?php echo $project->projectend ?></span>）</span>
            </h3>
            <div class="fcontainer clearfix" id="">
                <div id="fitem_id_lastname" class="fitem required fitem_ftext  ">
                    <div class="fitemtitle"><label for="id_lastname">项目描述<span class="req"><img class="icon "
                                                                                                alt="此处不能为空。"
                                                                                                title="此处不能为空。"
                                                                                                src="<?php echo $CFG->wwwroot; ?>/theme/image.php/lambda/core/1552471212/req"></span></label>
                    </div>
                    <div class="felement ftext" data-fieldtype="text"><?php echo $project->projectdesc; ?></div>
                </div>
                <div id="fitem_id_lastname" class="fitem required fitem_ftext  ">
                    <div class="fitemtitle"><label for="id_lastname">个人职责<span class="req"><img class="icon "
                                                                                                alt="此处不能为空。"
                                                                                                title="此处不能为空。"
                                                                                                src="<?php echo $CFG->wwwroot; ?>/theme/image.php/lambda/core/1552471212/req"></span></label>
                    </div>
                    <div class="felement ftext" data-fieldtype="text"><?php echo $project->responsibility; ?></div>
                </div>
            </div>
            <?php } ?>
    </fieldset>

    <fieldset class="clearfix collapsible" id="career_moodle">
        <legend class="ftoggler"><a href="#" class="fheader" role="button" aria-controls="id_moodle"
                                    aria-expanded="true" id="yui_3_17_2_1_1552490143732_270">教育经历</a></legend>
        <?php foreach ($educations as $education) { ?>
            <div class="content">
                <h3 class="sectionname" style="margin-left: 5rem;"><span
                            id="education<?php echo $education->id ?>"><?php echo $education->major ?></span> | <span
                            id="schoolname<?php echo $education->id ?>"><?php echo $education->schoolname ?></span><span
                            class="normal">（<span
                                id="start<?php echo $education->id ?>"><?php echo $education->start ?></span> - <span
                                id="end<?php echo $education->id ?>"><?php echo $education->end ?></span>）</span></h3>
                <div class="fcontainer clearfix" id="">
                    <div id="fitem_id_lastname" class="fitem required fitem_ftext">
                        <div class="fitemtitle"><label for="id_lastname">学历<span class="req"><img class="icon "
                                                                                                  alt="此处不能为空。"
                                                                                                  title="此处不能为空。"
                                                                                                  src="<?php echo $CFG->wwwroot; ?>/theme/image.php/lambda/core/1552471212/req"></span></label>
                        </div>
                        <div class="felement ftext" data-fieldtype="text"><?php echo $education->degree ?></div>
                    </div>
                    <div id="fitem_id_lastname" class="fitem required fitem_ftext">
                        <div class="fitemtitle"><label for="id_lastname">是否统招<span class="req"><img class="icon "
                                                                                                    alt="此处不能为空。"
                                                                                                    title="此处不能为空。"
                                                                                                    src="<?php echo $CFG->wwwroot; ?>/theme/image.php/lambda/core/1552471212/req"></span></label>
                        </div>
                        <div class="felement ftext" data-fieldtype="text"><?php echo $education->national ?></div>
                    </div>

                    <!--                    <div id="fitem_id_country" class="fitem fitem_fselect  ">-->
                    <!--                        <div class="fitemtitle"><label for="id_country">语言<span class="req"><img class="icon "-->
                    <!--                                                                                                     alt="此处不能为空。" title="此处不能为空。"-->
                    <!--                                                                                                     src="-->
                    <?php //echo $CFG->wwwroot;?><!--/theme/image.php/lambda/core/1552471212/req"></span></label></div>-->
                    <!--                        <div class="felement ftext" data-fieldtype="text">-->
                    <?php //echo $education->language?><!--</div>-->
                    <!--                    </div>-->
                </div>
            </div>
        <?php } ?>
    </fieldset>

    <fieldset class="clearfix collapsible" id="career_moodle">
        <legend class="ftoggler"><a href="#" class="fheader" role="button" aria-controls="id_moodle"
                                    aria-expanded="true" id="yui_3_17_2_1_1552490143732_270">专业技能</a></legend>
        <div class="fcontainer clearfix" id="yui_3_17_2_1_1552490143732_714">
            <div id="fitem_id_lastname" class="fitem required fitem_ftext  ">
                <?php
                foreach ($skills as $skill) {
                ?>
                <div class="content">
                    <h3 class="sectionname" style="margin-left: 5rem;">
                        <span id="skillName<?php echo $skill->id?>"><?php echo $skill->name?></span>
                        </span>
                         </h3>
                    <div id="fitem_id_firstname" class="fitem fitem_ftext  ">
                        <div class="fitemtitle"><label>使用时长</label></div>
                        <div class="felement fselect" id="skillUsedMonth<?php echo $skill->id?>" style="padding-top: 5px;"><?php echo $skill->used_month?>个月</div>
                    </div>
                    <div id="fitem_id_lastname" class="fitem fitem_ftext  ">
                        <div class="fitemtitle"><label>掌握程度</label></div>
                        <div class="felement fselect"  style="padding-top: 5px;" id="skillLevel<?php echo $skill->id?>"><?php echo $skill->level?></div>
                    </div>       
                </div>
            <?php
                }
            ?>
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
                                                                                            src="<?php echo $CFG->wwwroot; ?>/theme/image.php/lambda/core/1552471212/req"></span></label>
                </div>
                <div class="felement ftext" data-fieldtype="text"><?php echo $basic->intro ?></div>
            </div>
        </div>
    </fieldset>
    <input type="hidden" name="operation" value="submit">
</form>
<div>

</div>
</body>
</html>
