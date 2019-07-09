<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link rel="stylesheet" href="css/base.css">
    <link rel="stylesheet" href="css/index.css">
    <script src="js/jquery-1.11.3.min.js"></script>
    <script src="js/echarts.js"></script>
    <script src="js/index.js"></script>
    <title>人才报告评估</title>
</head>
<body>
<!--第一页-->
<div class="page_box" id="has_bg" style="page-break-inside:avoid;">
    <div class="i_logo">
        <img src="image/logo1.jpeg">
    </div>
    <p class="title">言某人才评估报告</p>
    <table class="info_table">
        <input type="hidden" id="id" value="<?php echo $id; ?>">
        <tr>
            <td class="text_left">姓名：</td>
            <td class="text_right"><?php echo $users->lastname; ?></td>
        </tr>
        <tr>
            <td class="text_left">年龄：</td>
            <td class="text_right"><?php echo $age; ?></td>
        </tr>
        <tr>
            <td class="text_left">性别：</td>
            <td class="text_right"><?php echo $profile['gender']; ?></td>
        </tr>
        <tr>
            <td class="text_left">学历：</td>
            <td class="text_right"><?php echo $basic->topeducation; ?></td>
        </tr>
        <tr>
            <td class="text_left">目标行业：</td>
            <td class="text_right"><?php echo $basic->industry; ?></td>
        </tr>
        <tr>
            <td class="text_left">目标职业：</td>
            <td class="text_right"><?php echo $basic->career; ?></td>
        </tr>
        <tr>
            <td class="text_left">测评时间：</td>
            <td class="text_right"><?php echo date("Y-m-d"); ?></td>
        </tr>
    </table>
</div>

<!--第二页-->
<div class="page_box" style="page-break-inside:avoid;">
    <header class="head">
        <img class="c_logo" src="image/logo.jpeg">
        <p class="t_title">言某人才评估</p>
        <p class="fr h_text">个人报告</p>
    </header>
    <div class="section_title">
        <i class="icon"></i>
        <span>人才评估综合画像</span>
    </div>
    <div class="content">
        <ul>
            <p><i class="point"></i>MBTI推荐岗位：架构师/BA/DBA/Programmer/QA/Security/Infra</p>
            <p><i class="point"></i>目标岗位：<?php echo $position; ?></p>
        </ul>
        <div id="radar"></div>
        <ul>
            <p><i class="point"></i>岗位匹配结果：<span class="text_strong">推荐面试</span></p>
        </ul>
        <div class="yellow_box">
            <div id="pie" style=" width: 450px;height: 300px; margin: 0 auto;"></div>
        </div>
        <?php if ($Arr) { ?>
            <table class="line_table">
                <tr>
                    <th>测试名称</th>
                    <th>预计用时</th>
                    <th>实际用时</th>
                    <th>测试分数</th>
                </tr>
                <?php foreach ($Arr as $k => $v) { ?>
                    <tr>
                        <th class="light_text"><?php echo $v['name']; ?></th>
                        <td><?php echo $v['introformat']; ?> 小时</td>
                        <td><?php echo $v['time']; ?> 秒</td>
                        <td><?php echo $v['grade']; ?> </td>
                    </tr>
                <?php } ?>
                <!--            <tr>-->
                <!--                <th class="light_text">Java基础测试</th>-->
                <!--                <td>80</td>-->
                <!--                <td>10分钟</td>-->
                <!--                <td>8分钟</td>-->
                <!--                <td>79</td>-->
                <!--            </tr>-->
            </table>
        <?php } ?>
        <p class="tip">注：岗位匹配结果基于技能匹配和技能测试结果 </p>
    </div>
    <div class="bottom_line"></div>
</div>

<!--第三页-->
<div class="page_box" style="page-break-inside:avoid;">
    <header class="head">
        <img class="c_logo" src="image/logo.jpeg">
        <p class="t_title">言某人才评估</p>
        <p class="fr h_text">个人报告</p>
    </header>
    <div class="section_title">
        <i class="icon"></i>
        <span>性格倾向分析</span>
    </div>
    <div class="content">
        <ul>
            <p><i class="point"></i>答题情况：本测试共X道题，预计用时XX分钟，测试者完成X题，实际用时X分X秒。作答过程异常。</p>
        </ul>
        <ul>
            <p><i class="point"></i>MBTI测评结果</p>
            <div id="bar" style=" width: 450px;height: 300px; margin: 0 auto;"></div>
        </ul>
        <div class="yellow_box">
            <ul class="paragraph">
                <li class="p_title">您的MBTI性格为：(<?php echo $str; ?>)</li>
                <li><?php echo $desc; ?></li>
                <!--                <li>具有以下特征：</li>-->
                <!--                <li> 1.严肃、安静、藉由集中心 志与全力投入、及可被信赖获致成功。</li>-->
                <!--                <li>2.行事务实、有序、实际 、 逻辑、真实及可信赖。</li>-->
                <!--                <li>3.十分留意且乐于任何事，工作、居家、生活均有良好组织及有序。</li>-->
                <!--                <li>4.负责任。</li>-->
                <!--                <li>5.照设定成效来作出决策且不畏阻挠与闲言会坚定为之。</li>-->
                <!--                <li>6.重视传统与忠诚。</li>-->
                <!--                <li>7.传统性的思考者或经理。</li>-->
            </ul>
        </div>
        <p class="tip">注：上述建议仅供参考，具体请在专业人士的指导下进行解读。</p>
    </div>
    <div class="bottom_line"></div>
</div>
<!--第四页-->
<div class="page_box" style="page-break-inside:avoid;">
    <header class="head">
        <img class="c_logo" src="image/logo.jpeg">
        <p class="t_title">言某人才评估</p>
        <p class="fr h_text">个人报告</p>
    </header>
    <div class="content">
        <div>
            这类性格的共性是有很强的责任心与事业心，他们忠诚、按时完成任务，推崇安全、礼议、规则和服从，他们被一种服务于社会需要的强烈动机所驱使。他们坚定、尊重权威、等级制度，持保守的价值观。他们充当着保护者、管理员、稳压器、监护人的角色。大约有50%左右SJ偏爱的人为政府部门及军事部门的职务所吸引，并且显现出卓越成就。其中在美国执政过的41位总统中有20位是SJ偏爱的人。例如：
            <ul style="padding: 10px 20px;">
                <li>乔治.布什　　　　　　 George Bush</li>
                <li>女王维多利亚　　　　　Queen Victoria</li>
                <li>女王伊丽莎白.伊伊　　Queen Elizabeth</li>
                <li>乔治.华盛顿　　　　　　George Washington</li>
            </ul>
        </div>
        <div class="yellow_box">
            <div>
                <p><i class="point"></i>适合职业：</p>
                <p>首席信息系统执行官/天文学家/数据库管理/会计/房地产经纪人/侦探/行政管理/信用分析师。</p>
            </div>
            <div>
                <p><i class="point"></i>IT岗位推荐：</p>
                <ul style="padding: 10px 60px;">
                    <li>架构师</li>
                    <li>BA</li>
                    <li>DBA</li>
                    <li>Programmer</li>
                    <li>QA</li>
                    <li>Security</li>
                    <li>Infra</li>
                </ul>
            </div>
        </div>
    </div>
    <div class="bottom_line"></div>
</div>
<!--第五页-->
<div class="page_box" style="page-break-inside:avoid;">
    <header class="head">
        <img class="c_logo" src="image/logo.jpeg">
        <p class="t_title">言某人才评估</p>
        <p class="fr h_text">个人报告</p>
    </header>
    <div class="section_title">
        <i class="icon"></i>
        <span>学习能力测试</span>
    </div>
    <div class="content">
        <ul>
            <p><i class="point"></i>答题情况：本测试共X道题，预计用时XX分钟，测试者完成X题，实际用时X分X秒。作答过程异常。</p>
        </ul>
        <ul>
            <p><i class="point"></i>测评结果</p>
            <p style="text-indent: 2em">综合总分：34, 较好</p>
            <table class="line_table">
                <tr>
                    <th></th>
                    <th>学习习惯</th>
                    <th>记忆方法</th>
                    <th>进取心</th>
                </tr>
                <tr>
                    <th class="light_text">满分</th>
                    <td>80</td>
                    <td>10</td>
                    <td>8</td>
                </tr>
                <tr>
                    <th class="light_text">测评分数</th>
                    <td>80</td>
                    <td>10</td>
                    <td>8</td>
                </tr>
            </table>
        </ul>
    </div>
    <div class="section_title">
        <i class="icon"></i>
        <span>综合背景</span>
    </div>
    <div class="content">
        <ul>
            <p><i class="point"></i>考察标准</p>
            <table class="line_table">
                <tr>
                    <th>考察项目</th>
                    <th>占比 （%）</th>
                </tr>
                <tr>
                    <th class="light_text">逻辑测试</th>
                    <td>40</td>
                </tr>
                <tr>
                    <th class="light_text">学历</th>
                    <td>20</td>
                </tr>
                <tr>
                    <th class="light_text">英文能力</th>
                    <td>10</td>
                </tr>
                <tr>
                    <th class="light_text">工作经验</th>
                    <td>30</td>
                </tr>
            </table>

            <p><i class="point"></i>总得分 <span class="text_red">80分</span></p>

        </ul>
    </div>
    <div class="bottom_line"></div>
</div>
<!--第六页-->
<div class="page_box" style="page-break-inside:avoid;">
    <header class="head">
        <img class="c_logo" src="image/logo.jpeg">
        <p class="t_title">言某人才评估</p>
        <p class="fr h_text">个人报告</p>
    </header>
    <div class="section_title">
        <i class="icon"></i>
        <span>技能盘点</span>
    </div>
    <div class="content">
        <ul>
            <p><i class="point"></i>IT专业技能</p>
            <div id="line_bar1" class="line_bar" style=" width: 450px;height: 300px; margin: 0 auto;"></div>
        </ul>
        <ul>
            <p><i class="point"></i>IT管理技能</p>
            <div id="line_bar2" class="line_bar" style=" width: 450px;height: 300px; margin: 0 auto;"></div>
        </ul>
        <ul>
            <p><i class="point"></i>业务项目能力</p>
            <div id="line_bar3" class="line_bar" style=" width: 450px;height: 300px; margin: 0 auto;"></div>
        </ul>
        <ul>
            <p><i class="point" class="line_bar"></i>推荐进阶培训课程</p>
            <div class="class_list">
                <p>√ Java编程进阶</p>
                <p>√ Hibernate </p>
            </div>
        </ul>
    </div>
    <div class="bottom_line"></div>
</div>
<script>
    // 柱状图
    var bar_opetion = {
        title: {
            text: 'MBTI性格强度指标',
            left: 'center',
            textStyle: {
                // fontWeight: 'normal',
                fontSize: '20'
            }
        },
        xAxis: [
            {
                type: 'category',
                data: [<?php echo $mbti;?>],
                axisLabel: {
                    color: '#4ac1ed'
                },
                axisLine: {
                    lineStyle: {
                        color: '#333'
                    }
                },
                axisTick: {
                    show: false
                },
                axisLabel: {
                    interval: 0
                }
            }
        ],
        yAxis: [
            {
                type: 'value',
                splitLine: {
                    show: true
                },
                axisLine: {
                    show: true
                },
                axisTick: {
                    show: false
                },
                axisLabel: {
                    show: true
                }
            }
        ],
        animation: false,
        series: [
            //  {
            //     name: '橙色柱数据',
            //     type: 'bar',
            //     data: [2.3, 4.5, 4.6, 3.6,],
            //     barGap: '4%',
            //     barWidth: '24',
            //     itemStyle: {
            //         color: '#ed7d31',
            //     }
            // },
            {
                name: '黄色柱状图',
                type: 'bar',
                barWidth: '40',
                data: [5.2, 4.2, 7.8, 5.8],

                itemStyle: {
                    color: '#f5ad23'
                }
            }
        ],
        color: [
            '#ed7d31',
            // '#f5ad23'
        ],
    }

    var myChart = echarts.init(document.getElementById('bar'));
    //使用制定的配置项和数据显示图表
    myChart.setOption(bar_opetion);
    var pie_opetion = {
        legend: {
            left: 'center',
            bottom: 0,
            orient: 'horizontal',
            data: ['达标', '不达标'],
            textStyle: {
                fontSize: '9'
            }
            // itemWidth: '10',
        },
        // singleAxis: {
        //     type: 'category',
        //     bottom: '80%',
        //     data: ['必要技能', '可选技能岗位', '加分项'],
        //     axisLabel: {fontSize: 12},
        //     axisLine: {show: false},
        //     axisTick: {show: false}
        // },
        animation: false,
        series: [
            {
                name: '必要技能岗位匹配度',
                // coordinateSystem: 'singleAxis',
                type: 'pie',
                radius: '30%',
                center: ['10%', '55%'],
                data: [
                    {value:<?php echo $percentage['mP'];?>, name: '达标'},
                    {value: <?php echo 100 - (int)$percentage['mP'];?>, name: '不达标'},
                ],
                itemStyle: {
                    emphasis: {
                        shadowBlur: 10,
                        shadowOffsetX: 0,
                        shadowColor: 'rgba(0, 0, 0, 0.5)'
                    }
                }
            },
            {
                name: '可选技能岗位匹配度',
                // coordinateSystem: 'singleAxis',
                type: 'pie',
                radius: '30%',
                center: ['45%', '55%'],
                data: [
                    {value: <?php echo $percentage['kP'];?>, name: '达标'},
                    {value: <?php echo 100 - (int)$percentage['kP'];?>, name: '不达标'},
                ],
                itemStyle: {
                    emphasis: {
                        shadowBlur: 10,
                        shadowOffsetX: 0,
                        shadowColor: 'rgba(0, 0, 0, 0.5)'
                    }
                }
            },
            {
                name: '加分项岗位匹配度',
                // coordinateSystem: 'singleAxis',
                type: 'pie',
                radius: '30%',
                center: ['80%', '55%'],
                data: [
                    {value: <?php echo $percentage['jP'];?>, name: '达标'},
                    {value: <?php echo 100 - (int)$percentage['jP'];?>, name: '不达标'},
                ],
                itemStyle: {
                    emphasis: {
                        shadowBlur: 10,
                        shadowOffsetX: 0,
                        shadowColor: 'rgba(0, 0, 0, 0.5)'
                    }
                }
            },

        ],
        color: [
            '#ed7d31',
            '#f5ad23'
        ],
    }
    //初始化echarts实例
    var myChart = echarts.init(document.getElementById('pie'));

    //使用制定的配置项和数据显示图表
    myChart.setOption(pie_opetion);

    var line_bar_option1 = {
        color: ['#3398DB'],
        tooltip: {
            trigger: 'axis',
            axisPointer: {            // 坐标轴指示器，坐标轴触发有效
                type: 'shadow'        // 默认为直线，可选为：'line' | 'shadow'
            }
        },
        grid: {
            left: '3%',
            right: '4%',
            bottom: '3%',
            containLabel: true
        },
        xAxis: [
            {
                type: 'category',
                data: [<?php echo $speciality;?>],
                axisTick: {
                    alignWithLabel: true
                }
            }
        ],
        yAxis: [
            {
                type: 'value'
            }
        ],
        animation: false,
        series: [
            {
                name: '',
                type: 'bar',
                barWidth: '60%',
                data: [<?php echo $specialityStr;?>]
            }
        ]
    };
    //初始化echarts实例
    var myChart = echarts.init(document.getElementById('line_bar1'));
    //使用制定的配置项和数据显示图表
    myChart.setOption(line_bar_option1);

    // 折现加柱状图
    var line_bar_option2 = {
        color: ['#3398DB'],
        tooltip: {
            trigger: 'axis',
            axisPointer: {            // 坐标轴指示器，坐标轴触发有效
                type: 'shadow'        // 默认为直线，可选为：'line' | 'shadow'
            }
        },
        grid: {
            left: '3%',
            right: '4%',
            bottom: '3%',
            containLabel: true
        },
        xAxis: [
            {
                type: 'category',
                data: [<?php echo $manage;?>],
                axisTick: {
                    alignWithLabel: true
                }
            }
        ],
        yAxis: [
            {
                type: 'value'
            }
        ],
        series: [
            {
                name: '直接访问',
                type: 'bar',
                barWidth: '60%',
                data: [<?php echo $manageStr;?>]
            }
        ]
    };
    //初始化echarts实例
    var myChart = echarts.init(document.getElementById('line_bar2'));
    //使用制定的配置项和数据显示图表
    myChart.setOption(line_bar_option2);

    // 折现加柱状图
    var line_bar_option3 = {
        color: ['#3398DB'],
        tooltip: {
            trigger: 'axis',
            axisPointer: {            // 坐标轴指示器，坐标轴触发有效
                type: 'shadow'        // 默认为直线，可选为：'line' | 'shadow'
            }
        },
        grid: {
            left: '3%',
            right: '4%',
            bottom: '3%',
            containLabel: true
        },
        xAxis: [
            {
                type: 'category',
                data: [<?php echo $business;?>],
                axisTick: {
                    alignWithLabel: true
                }
            }
        ],
        yAxis: [
            {
                type: 'value'
            }
        ],
        series: [
            {
                name: '直接访问',
                type: 'bar',
                barWidth: '60%',
                data: [<?php echo $businessStr;?>]
            }
        ]
    };
    //初始化echarts实例
    var myChart = echarts.init(document.getElementById('line_bar3'));
    //使用制定的配置项和数据显示图表
    myChart.setOption(line_bar_option3);

</script>

</body>
</html>