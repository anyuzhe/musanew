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
            max-width: 1000px;
            min-width: 600px;
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
            max-width: 500px;
        }
        .subTr {
            height: 30px;
        }
        span {
            text-overflow: ellipsis;
            white-space: normal;
            /*text-indent: 32px;*/
            display: inline-block;
            word-break: break-all;
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
    @if($matching)
        <tr class="resumeHead">
            <td colspan="5">简历匹配</td>
        </tr>
        <tr class="subTr">
            <td>总分</td>
            <td>{!! $matching['score'] !!}</td>
            <td>学历分数</td>
            <td colspan="2">{!! $matching['education_score'] !!}</td>
        </tr>
        <tr class="subTr">
            <td>年资分数</td>
            <td>{!! $matching['working_years_score'] !!}</td>
            <td>技能分数</td>
            <td colspan="2">{!! $matching['skills_score'] !!}</td>
        </tr>
        <tr class="resumeHead">
            <td colspan="5">技能对比</td>
        </tr>
        @if(isset($matching['skills_data']) && count($matching['skills_data'])>0)
            <tr class="subTr">
                <td colspan="2">技能名称</td>
                <td>需要水平</td>
                <td>实际水平</td>
                <td>分数</td>
            </tr>
            @foreach($matching['skills_data'] as $skill)
            <tr class="subTr">
                <td colspan="2">{!! $skill['skill_name'] !!}</td>
                <td>{!! $skill['job_level_text'] !!}</td>
                <td>{!! $skill['resume_level_text'] !!}</td>
                <td>{!! $skill['sroce'] !!}</td>
            </tr>
            @endforeach
        @endif
    @endif
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
        @if(isset($data['education_text']))
            <td>{!! $data['education_text'] !!}</td>
        @else
            <td></td>
        @endif
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
            <td>{!! $ed['start_date'] !!}{!! $ed['end_date']?' - '.$ed['end_date']:'' !!}</td>
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
            <td colspan="2">{!! $com['industry']?$com['industry_text']:'' !!}</td>
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
            <td colspan="4"><span>{!! $com['job_desc'] !!}</span></td>
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
            <td colspan="4"><span>{!! $pro['responsibility'] !!}</span>&nbsp;</td>
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
        <td colspan="5"><span>{!! $data['self_evaluation'] !!}</span></td>
    </tr>
    <tr class="resumeHead">
        <td colspan="5">外包评价</td>
    </tr>
    <tr class="subTr">
        <td colspan="5"><span>{!! $data['third_party_evaluation'] !!}</span></td>
    </tr>
</table>
</body>
</html>
