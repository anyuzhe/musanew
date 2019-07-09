var itemid = null;
// document ready
$(function(){
    $("#workform").hide();
    $("#projectform").hide();
    $("#educationform").hide();
    $("#skillform").hide();

    $('#jobstart').datepicker({
        dateFormat: 'yy-mm',
        changeMonth : true
    });
    $('#jobend').datepicker({
        dateFormat: 'yy-mm',
        changeMonth : true
    });
    $('#projectstart').datepicker({
        dateFormat: 'yy-mm',
        changeMonth : true
    });
    $('#projectend').datepicker({
        dateFormat: 'yy-mm',
        changeMonth : true
    });
    $('#start').datepicker({
        dateFormat: 'yy-mm',
        changeMonth : true
    });
    $('#end').datepicker({
        dateFormat: 'yy-mm',
        changeMonth : true
    });
})

function cancel(form){
    $("#"+form).hide();
    $("."+form).show();    
}

function additem(form){
    itemid = null;
    $("#"+form).show();
    $("."+form).hide();
}
// 删除工作经验数据
function delitem(type, itemid){
    if(confirm('确认删除该条记录？')){
        var data = {
            category: 'del_' + type,
            id: itemid,
        };
        $.post('/my/resumeapi.php', data, function(resp){
            checkForm();
        });
    }
}
function edit_work(item){
    additem("workform");
    $("#companyname").val($("#companyname"+item).text());
    $("#industry").val($("#industry"+item).text());
    $("#jobtitle").val($("#jobtitle"+item).text());
    $("#jobtype").val($("#jobtype"+item).text());
    $("#jobstart").val($("#jobstart"+item).text());
    $("#jobend").val($("#jobend"+item).text());
    $("#salary").val($("#salary"+item).text());
    $("#jobdesc").val($("#jobdesc"+item).text().trim());
    itemid = item;
}

function save_work(){
    var data = {
        category: 'company',
        id: itemid,
        companyname: $("#companyname").val(),
        industry: $("#industry").val(),
        jobtitle: $("#jobtitle").val(),
        jobtype: $("#jobtype").val(),
        jobstart: $("#jobstart").val(),
        jobend: $("#jobend").val(),
        salary: $("#salary").val(),
        jobdesc: $("#jobdesc").val(),
        resumeid : resumeid
    };
    
    $.post('/my/resumeapi.php', data, function(resp) {        
        checkForm();
    });
}

function edit_project(item){
    additem("projectform");
    $("#projectname").val($("#projectname"+item).text());
    $("#relatecompany").val($("#relatecompany"+item).text());    
    $("#projectstart").val($("#projectstart"+item).text());
    $("#projectend").val($("#projectend"+item).text());
    $("#projectdesc").val($("#projectdesc"+item).text().trim());
    $("#responsibility").val($("#responsibility"+item).text().trim());
    itemid = item;
}

function save_project(){
    var data = {
        category: 'project',
        id: itemid,
        projectname: $("#projectname").val(),
        relatecompany: $("#relatecompany").val(),
        projectstart: $("#projectstart").val(),
        projectend: $("#projectend").val(),
        projectdesc: $("#projectdesc").val(),
        responsibility: $("#responsibility").val(),
        resumeid : resumeid
    };

    $.post('/my/resumeapi.php', data, function(resp) { 
        checkForm();
    });
}

function edit_education(item){
    additem("educationform");
    $("#schoolname").val($("#schoolname"+item).text());
    $("#major").val($("#major"+item).text());    
    $("#start").val($("#start"+item).text());
    $("#end").val($("#end"+item).text());
    $("input:radio[name='national'][value='"+$("#national"+item).text()+"']").attr('checked','true');
    $("#degree").val($("#degree"+item).text());
    itemid = item;
}

function save_education(){
    var data = {
        category: 'education',
        id: itemid,
        schoolname: $("#schoolname").val(),
        major: $("#major").val(),
        start: $("#start").val(),
        end: $("#end").val(),
        national: $("input[name='national']:checked").val(),
        degree: $("#degree").val(),
        resumeid : resumeid
    };
    
    $.post('/my/resumeapi.php', data, function(resp) { 
        checkForm();
    });
}

function edit_skill(item){
    additem("skillform");
    $("#skillName").val($("#skillName"+item).text());
    $("#skillUsedMonth").val($("#skillUsedMonth"+item).text());
    $("input:radio[name='skillLevel'][value='"+$("#skillLevel"+item).text()+"']").attr('checked','true');
    itemid = item;
}

function save_skills(){
    skillid =$("#skillName").find("option:selected").attr('data-id');
    usedMonth = $("#skillUsedMonth").val()
    if (!skillid) {
        window.alert('请选择一个技能');return;
    }
    if (usedMonth <= 0 || usedMonth > 360) {
        window.alert('请填写正确的时间');return;
    }
    var data = {
        category: 'skill',
        id: itemid,
        skill_id: skillid,
        used_month: $("#skillUsedMonth").val(),
        level:  $("input:radio[name='skillLevel']:checked").val(),
        resumeid: resumeid
    };
    $.post('/my/resumeapi.php', data, function(resp) { 
        checkForm();
    });
}

function selfEvaluation(){
    location.href="/mod/questionnaire/complete.php?id=117";
}

function checkForm(){    
    if(document.form1.value==""){
        window.alert("不能为空");
        return;
    } else {
        document.form1.submit();
    }
}

