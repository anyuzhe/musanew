jQuery(function () {
    var $ = jQuery;

    // echart图形在浏览窗口放大或缩小时动态改变大小
    var resizeE = (function () {
        var _ecArr = [];
        var clearTimer = null;

        return function (cb) {
            if (_ecArr.length === 0) {
                var fn = window.onresize;
                if (fn) _ecArr.push(fn);
            }

            if (cb && typeof cb === 'function') {
                _ecArr.push(cb);
                window.onresize = function () {
                    clearTimeout(clearTimer);
                    clearTimer = setTimeout(function () {
                        for (var i = 0; i < _ecArr.length; i++) _ecArr[i]();
                    }, 200);
                }
            } else {
                console.log('The param is not a funtion');
            }
        }
    })()

    // 雷达图
    var radar = {
        tooltip: {
            show: false,
        },
        radar: [
            {
                indicator: [
                    {text: '学习能力', max: 100},
                    {text: '保险项目经验', max: 100},
                    {text: '综合背景', max: 100},
                    {text: 'IT专业技能', max: 100},
                    {text: 'IT管理技能', max: 100},

                ],
                center: ['50%', '50%'],
                radius: '40%',
                name: {
                    formatter: '{value}',
                    textStyle: {
                        color: '#333',
                        fontSize: 12
                    }
                },
            }
        ],
        animation: false,
        series: [
            {
                type: 'radar',
                color: ['#f5ad23', '#e26363'],
                data: [
                    {
                        value: [23, 45, 67, 12, 90],
                        name: '能力评估'
                    }
                ]
            }
        ]

    }
    //初始化echarts实例
    var myChart = echarts.init(document.getElementById('radar'));

    //使用制定的配置项和数据显示图表
    myChart.setOption(radar);

})