<!DOCTYPE html>
<html>
<head>
    <title>我的简历</title>
    <style>
        div {
            width: 100%;
            font-family: "微软雅黑";
            letter-spacing: 1px;
        }
    </style>

</head>
<body>
<div style="width: 100%;">
    <div style="background-color: #F0A22E;margin-top: 10px;">基础信息</div>
    <table cellpadding="10">
        <tr>
            <td>姓名：<?php echo $users->lastname; ?><?php echo $users->firstname; ?></td>
            <td>性别：<?php echo $profile['gender']; ?></td>
        </tr>
        <tr>
            <td>出生日期：<?php echo $birthdate; ?></td>
            <td>个人最高学历：<?php echo $basic->topeducation; ?></td>
        </tr>
        <tr>
            <td>个人电话：<?php echo $profile['mobilephone']; ?></td>
            <td>个人邮箱：<?php echo $users->email; ?></td>
        </tr>
        <tr>
            <td>开始工作时间：<?php echo $basic->startwork; ?></td>
            <td>现居住地：<?php echo $basic->workplace; ?></td>
        </tr>
    </table>

    <div style="background-color: #F0A22E;margin-top: 10px;;margin-top: 10px;">求职意向</div>
    <table cellpadding="10">
        <tr>
            <td>求职状态：<?php echo $basic->jobstatus; ?></td>
            <td>期望从事行业：<?php echo $basic->industry; ?></td>
        </tr>
        <tr>
            <td>期望工作性质：<?php echo $basic->jobtype; ?></td>
            <td>期望从事职业：<?php echo $basic->career; ?></td>
        </tr>
        <tr>
            <td>期望工作地点：<?php echo $basic->workplace; ?></td>
            <td>期望从事职业月薪：<?php echo $basic->salary; ?></td>
        </tr>
    </table>
    <div style="background-color: #F0A22E;margin-top: 10px;">工作经验</div>
    <?php foreach (array_reverse($companys, true) as $company) {
        ; ?>
        <table cellpadding="10">
            <tr>
                <td><?php echo $company->jobstart . "--" . $company->jobend; ?></td>
                <td><?php echo $company->companyname; ?></td>
                <td><?php echo $company->jobtype; ?></td>
            </tr>
        </table>
        <div>所属行业：<?php echo $company->industry; ?></div>
        <div>职位类别：<?php echo $company->jobtype; ?></div>
        <div>税前薪资：<?php echo $company->salary; ?></div>
        <div>工作描述：<?php echo $company->jobdesc; ?></div>
    <?php }; ?>


    <div style="background-color: #F0A22E;margin-top: 10px;">项目经验</div>
    <?php foreach (array_reverse($projects, true) as $project) {
        ; ?>
        <div>项目名称：<?php echo $project->projectname; ?></div>
        <div>项目时间：<?php echo $project->projectstart . "--" . $project->projectendc; ?></div>
        <div>项目描述：<?php echo $project->projectdesc; ?></div>
        <div>个人职责：<?php echo $project->responsibility; ?></div>
    <?php }; ?>
    <div style="background-color: #F0A22E;margin-top: 10px;;">专业技能</div>
    <table cellpadding="10">
        <?php foreach ($skills as $skill) {
            ; ?>
            <tr>
                <td>技能名称：<?php echo $skill->name; ?></td>
                <td>所用时长：<?php echo (int)$skill->used_month; ?> 个月</td>
                <td>掌握程度：<?php echo $skill->level; ?></td>
            </tr>
        <?php }; ?>
    </table>
    <div style="background-color: #F0A22E;margin-top: 10px;">教育经历</div>
    <?php foreach (array_reverse($educations, true) as $education) {
        ; ?>
        <table cellpadding="10">
            <tr>
                <td>所学专业:<?php echo $education->major; ?></td>
                <td>是否统招：<?php echo $education->national; ?></td>
            </tr>
            <tr>
                <td>学历：<?php echo $education->degree; ?></td>
                <td>学校名称：<?php echo $education->schoolname; ?></td>
            </tr>
            <tr>
                <td>学习时间：<?php echo $education->start . "--" . $education->end; ?></td>
            </tr>
        </table>
    <?php }; ?>
    <div style="background-color: #F0A22E;margin-top: 10px;">自我评价</div>
    <div><?php echo $basic->intro; ?></div>
</div>
</body>
</html>
