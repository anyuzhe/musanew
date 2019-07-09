<!DOCTYPE html>
<html>

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
</head>
<body>
    <div class="content">
        <legend class="ftoggler"><a href="#" class="fheader" role="button" aria-controls="id_moodle"
                                    aria-expanded="true" id="yui_3_17_2_1_1552490143732_270">人才库</a></legend>
        <?php
        foreach($result as $applicant){
            //加载自定义数据
            $applicant->profile = array();
            require_once($CFG->dirroot.'/user/profile/lib.php');
            profile_load_custom_fields($applicant);
            $profile = (object)$applicant->profile;
            ?>
            <div class="fcontainer clearfix" id="yui_3_17_2_1_1552490143732_714">
                <div id="fitem_id_lastname" class="fitem required fitem_ftext">
                    <div class="fitemtitle">
                        <label for="id_lastname" style="display: inline-block;width: 15%;">姓名:<?php echo $applicant->lastname.$applicant->firstname?></label>
                        <label for="id_lastname" style="display: inline-block;width: 15%;">年龄:<?php echo getAge($profile->birthdate); ?>岁</label>
                        <label for="id_lastname" style="display: inline-block;width: 15%;">性别:<?php echo $profile->gender ?></label>
                        <div class="felement fselect" data-fieldtype="select" style="display: inline-block;width: 20%;">
                            <select autocomplete="country" name="chas" id="see" onchange="window.location=this.value;">
                                <option value="">请选择...</option>
                                <option value="<?php echo $CFG->wwwroot;?>/user/resume.php?id=<?PHP echo $applicant->id ?>">个人简历</option>
                                <option value="<?php echo $CFG->wwwroot;?>/my/report/estimate.php?id=<?PHP echo $applicant->id ?>">人才评估报告</option>
                                <option value="<?php echo $CFG->wwwroot;?>/my/mycourse.php?id=<?PHP echo $applicant->id ?>">课程</option>
                            </select>
                        </div>
                        <fieldset class="clearfix collapsible" id="career_moodle" style="display: inline-block;width: 10%;">
                            <input type="button" value="查看" onclick="window.location.href= '<?php echo $CFG->wwwroot;?>/user/resume.php?id=<?PHP echo $applicant->id ?>'" />
                        </fieldset>
                    </div>
                </div>
            </div>
        <?php
        }
        ?>
    </div>
</body>

</html>
