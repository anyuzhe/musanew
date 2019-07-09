<!DOCTYPE html>
<html>

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
</head>

<body>
<form action="company.php" method="post" accept-charset="utf-8" id="mform1"
      class="mform">
    <fieldset class="clearfix collapsible" id="career_moodle">
        <input type="hidden" name="action" value="add"/>
        <input type="hidden" name="plan" value="<?php echo $jobs->testname; ?>"/>
        <input type="hidden" name="userid" value="<?php echo $userid; ?>"/>
        <input type="hidden" name="jobsid" value="<?php echo $jobs->id; ?>"/>
        <input type="hidden" name="companyid" value="<?php echo $companyid; ?>"/>
        <legend class="ftoggler"><a href="#" class="fheader" role="button">公司简介</a></legend>
        <div class="content">
            <h3 class="sectionname" style="margin-left: 5rem;">
                <span id=""><?php echo $company->companyname; ?> </span>
            </h3>
            <div id="fitem_id_country" class="fitem fitem_fselect  ">
                <div class="fitemtitle"><label>企业规模</label></div>
                <div id="" class="felement ftext"
                     style="padding-top: 5px;"><?php echo $company->scale; ?> 人
                </div>
            </div>
            <div id="fitem_id_lastname" class="fitem required fitem_ftext  ">
                <div class="fitemtitle"><label>企业官网</label></div>
                <div id="" class="felement ftext"
                     style="padding-top: 5px;"><?php echo $company->websiteurl; ?>
                </div>
            </div>
            <div id="fitem_id_lastname" class="fitem required fitem_ftext  ">
                <div class="fitemtitle"><label>企业描述</label></div>
                <div id="" class="felement ftext"
                     style="padding-top: 5px;"><?php echo $company->description; ?>
                </div>
            </div>
        </div>
    </fieldset>
    <fieldset class="clearfix collapsible" id="career_moodle">
        <legend class="ftoggler"><a href="#" class="fheader" role="button">职位介绍</a></legend>
        <div class="content">
            <h3 class="sectionname" style="margin-left: 5rem;">
                <span id=""><?php echo $jobs->position; ?></span>
            </h3>
            <div id="fitem_id_country" class="fitem fitem_fselect  ">
                <div class="fitemtitle"><label>部门</label></div>
                <div id="" class="felement ftext"
                     style="padding-top: 5px;"><?php echo $company->companyname; ?> -- <?php echo $jobs->department; ?>
                </div>
            </div>
            <div id="fitem_id_country" class="fitem fitem_fselect  ">
                <div class="fitemtitle"><label>岗位代码</label></div>
                <div id="" class="felement ftext"
                     style="padding-top: 5px;">  <?php echo $jobs->position_code; ?>
                </div>
            </div>
            <div id="fitem_id_country" class="fitem fitem_fselect  ">
                <div class="fitemtitle"><label>职业</label></div>
                <div id="" class="felement ftext"
                     style="padding-top: 5px;">  <?php echo $occupation; ?>
                </div>
            </div>
            <div id="fitem_id_country" class="fitem fitem_fselect  ">
                <div class="fitemtitle"><label>职级</label></div>
                <div id="" class="felement ftext"
                     style="padding-top: 5px;">  <?php echo $occupation_rank; ?>
                </div>
            </div>
            <div id="fitem_id_country" class="fitem fitem_fselect  ">
                <div class="fitemtitle"><label>工作性质</label></div>
                <div id="" class="felement ftext"
                     style="padding-top: 5px;">  <?php echo $job_function ?>
                </div>
            </div>
            <div id="fitem_id_country" class="fitem fitem_fselect  ">
                <div class="fitemtitle"><label>学历要求</label></div>
                <div id="" class="felement ftext"
                     style="padding-top: 5px;">  <?php echo $education; ?>
                </div>
            </div>
            <div id="fitem_id_country" class="fitem fitem_fselect  ">
                <div class="fitemtitle"><label>工作地点(省)</label></div>
                <div id="" class="felement ftext"
                     style="padding-top: 5px;">  <?php echo $province; ?>
                </div>
            </div>
            <div id="fitem_id_country" class="fitem fitem_fselect  ">
                <div class="fitemtitle"><label>工作地点(市)</label></div>
                <div id="" class="felement ftext"
                     style="padding-top: 5px;">  <?php echo $city; ?>
                </div>
            </div>
            <div id="fitem_id_country" class="fitem fitem_fselect  ">
                <div class="fitemtitle"><label>技能要求</label></div>
                <?php foreach ($job_skill as $v) {
                    ; ?>
                    <div id="" class="felement ftext"
                         style="padding-top: 5px;"> 技能名称: &nbsp;<?php echo $v->name; ?> &nbsp;&nbsp;
                        使用时长:&nbsp; <?php echo $v->used_month; ?>
                        &nbsp;&nbsp;掌握程度:&nbsp;<?php echo $v->level; ?>
                    </div>
                <?php }; ?>
            </div>
            <div id="fitem_id_country" class="fitem fitem_fselect  ">
                <div class="fitemtitle"><label>岗位职责</label></div>
                <div id="" class="felement ftext"
                     style="padding-top: 5px;">  <?php echo $jobs->description; ?>
                </div>
            </div>

        </div>
    </fieldset>
    <?php if (!$companyUser) {
        ; ?>
        <div style="margin:10px 0 0 60px; ">
            <button type="submit" value="">应聘企业</button>
        </div>
    <?php } ?>
    <br/>
    <?php if ($companyUser) {
        ; ?>
        <div style="margin:10px 0 0 60px; ">
            <button type="submit" value="" disabled="disabled">已应聘</button>
        </div>
    <?php } ?>
</form>
</body>
</html>