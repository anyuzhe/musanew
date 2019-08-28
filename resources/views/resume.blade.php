<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta http-equiv="X-UA-Compatible" content="ie=edge" />
    <title>resumeDownload</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            list-style: none;
            box-sizing: border-box;
        }
        .tableResume {
            width: 60%;
            height: auto;
            margin: 10px auto;
            background-color: #fff;
            border-collapse: collapse;
            border-color: #000;
            max-width: 900px;
        }
        .resumeHead {
            background-color: #959595;
            height: 30px;
            padding: 0 10px;
        }
        tr,td {
            line-height: 30px;
        }
        td {
            padding: 0 5px;
            white-space: pre;
        }
        .subTr {
            height: 30px;
        }
        span {
            text-overflow: ellipsis !important;
            white-space: normal !important;
            /*max-width: 600px;*/
        }
        body{
            width: 100%;
        }
    </style>
</head>

<body>
<table border="1" class="tableResume">
    <caption>
        <h3>{!! $data['name'] !!}简历</h3>
    </caption>
    <tr class="resumeHead">
        <td colspan="5">个人信息</td>
    </tr>
    <tr class="subTr">
        <td>姓名</td>
        <td>{!! $data['name'] !!}</td>
        <td>电话号码</td>
        <td colspan="2">{!! $data['phone'] !!}</td>
    </tr>
    <tr class="subTr">
        <td>性别</td>
        <td>{!! $data['gender']==1?'男':'女' !!}</td>
        <td>出生年月</td>
        <td colspan="2">{!! $data['birthdate'] !!}</td>
    </tr>
    <tr class="subTr">
        <td>婚姻状况</td>
        <td>{!! $data['is_married']==1?'已婚':'未婚' !!}</td>
        <td>工作开始时间</td>
        <td colspan="2">{!! $data['start_work_at'] !!}</td>
    </tr>
    <tr class="subTr">
        <td>最高学历</td>
        <td>{!! $data['education_text'] !!}</td>
        <td>期望职位</td>
        <td colspan="2">{!! $data['hope_job_text'] !!}</td>
    </tr>
    <tr class="subTr">
        <td>户籍地址</td>
        <td>{!! $data['permanent_province_text'] !!}{!! $data['permanent_city_text'] !!}{!! $data['permanent_district_text'] !!}</td>
        <td>现居地址</td>
        <td colspan="2">{!! $data['residence_province_text'] !!}{!! $data['residence_city_text'] !!}{!! $data['residence_district_text'] !!}</td>
    </tr>
    <tr class="resumeHead">
        <td colspan="5">学习经历</td>
    </tr>
    <tr class="subTr">
        <td>学校名称</td>
        <td>学习时间</td>
        <td>所学专业</td>
        <td>是否统招</td>
        <td>学历</td>
    </tr>
    @foreach($data['educations'] as $ed)
        <tr class="subTr">
            <td>{!! $ed['school_name'] !!}</td>
            <td>{!! $ed['start_date'] !!}</td>
            <td>{!! $ed['major'] !!}</td>
            <td>{!! $ed['national']==1?'是':'否' !!}</td>
            <td>{!! $ed['education_text'] !!}</td>
        </tr>
    @endforeach
    <tr class="resumeHead">
        <td colspan="5">工作经历</td>
    </tr>

    @foreach($data['companies'] as $com)
        <tr class="subTr">
            <td>公司名称</td>
            <td>{!! $com['company_name'] !!}</td>
            <td>所属行业</td>
            <td colspan="2">{!! $com['industry_text'] !!}</td>
        </tr>
        <tr class="subTr">
            <td>职位名称</td>
            <td>{!! $com['job_title'] !!}</td>
            <td>职位类别</td>
            <td colspan="2">{!! $com['job_category_text'] !!}</td>
        </tr>
        <tr class="subTr">
            <td>税前月薪</td>
            <td>{!! $com['salary'] !!}</td>
            <td>在职时间</td>
            <td colspan="2">{!! $com['job_start'] !!}-{!! $com['job_end'] !!}</td>
        </tr>
        <tr class="subTr">
            <td>工作描述</td>
            <td colspan="4">&nbsp{!! $com['job_desc'] !!}</td>
        </tr>
    @endforeach
    <tr class="resumeHead">
        <td colspan="5">项目经历</td>
    </tr>

    @foreach($data['projects'] as $pro)
        <tr class="subTr">
            <td>项目名称</td>
            <td>{!! $pro['project_name'] !!}</td>
            <td>所属公司</td>
            <td colspan="2">{!! $pro['relate_company'] !!}&nbsp;</td>
        </tr>
        <tr class="subTr">
            <td>项目时间</td>
            <td colspan="4">{!! $pro['project_start'] !!}-{!! $pro['project_end'] !!}</td>
        </tr>
        <tr class="subTr">
            <td>项目描述</td>
            <td colspan="4"><span>{!! $pro['project_desc'] !!}</span>&nbsp;</td>
        </tr>
        <tr class="subTr">
            <td>个人职责</td>
            <td colspan="4">{!! $pro['responsibility'] !!}&nbsp;</td>
        </tr>
    @endforeach
    <tr class="resumeHead">
        <td colspan="5">专业技能</td>
    </tr>
    <tr class="subTr">
        <td colspan="2">技能名称</td>
        <td colspan="2">掌握程度</td>
        <td>使用时长</td>
    </tr>

    @foreach($data['skills'] as $skill)
        <tr class="subTr">
            <td colspan="2">{!! $skill['skill_name'] !!}</td>
            <td colspan="2">{!! $skill['skill_level_text'] !!}</td>
            <td>{!! $skill['used_time'] !!}</td>
        </tr>
    @endforeach
    <tr class="resumeHead">
        <td colspan="5">自我评价</td>
    </tr>
    <tr class="subTr">
        <td colspan="5">{!! $data['self_evaluation'] !!}</td>
    </tr>
    <tr class="resumeHead">
        <td colspan="5">外包评价</td>
    </tr>
    <tr class="subTr">
        <td colspan="5">{!! $data['third_party_evaluation'] !!}</td>
    </tr>
</table>
</body>
</html>
