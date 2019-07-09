<?php
function getAge($birthdate)
{
    if ($birthdate === false) {
        return false;
    }
    list($y1, $m1, $d1) = explode('-', date('Y-m-d', $birthdate));
    $now = strtotime('now');
    list($y2, $m2, $d2) = explode('-', date('Y-m-d', $now));
    $age = $y2 - $y1;
    if ((int)($m2 . $d2) < (int)($m1 . $d1)) {
        $age -= 1;
    }
    return $age;
}

function getLevel($levelName)
{
    switch ($levelName) {
        case "概念级别":
            $level = 1;
            break;
        case "实践级别":
            $level = 2;
            break;
        case "指导级别":
            $level = 3;
            break;
        case"专家级别":
            $level = 4;
            break;
        default:
            $level = 0;
    }
    return $level;
}

function getSkillmate($skills)
{
    $nameArr = array();
    $namestr="";
    if ($skills) {
        foreach ($skills as $k => $v) {
            $nameArr[] = "'" . $v->name . "'";
        }
        if ($nameArr) {
            $namestr = implode(',', $nameArr);
        }
    }
    return $namestr;
}


function getMbti($result)
{
    $MBTI = "";
    $section = 0;
    foreach ($result as $record) {
        if ($record->questionnaireid > $section) {
            $section = $record->questionnaireid;
            switch ($section) {
                case 1:// EI
                    if ($record->score <= 50) {
                        $MBTI = "'E'" . ',';
                    } else {
                        $MBTI = "'I'" . ',';
                    }
                    break;
                case 3:// SN
                    if ($record->score <= 50) {
                        $MBTI .= "'S'" . ',';
                    } else {
                        $MBTI .= "'N'" . ',';
                    }
                    break;
                case 4:// TF
                    if ($record->score <= 50) {
                        $MBTI .= "'T'" . ',';
                    } else {
                        $MBTI .= "'F'" . ',';
                    }
                    break;
                case 5:// JP
                    if ($record->score <= 50) {
                        $MBTI .= "'J'";
                    } else {
                        $MBTI .= "'P'";
                    }
                    break;
                default:
            }
        }
    }

    return $MBTI;
}

//文件输出浏览器下载
function upload($path, $pdfName)
{
    if (!file_exists($path)) {
        echo '文件不存在';
        exit;
    }
    $filename = realpath($path); //文件名
    Header("Content-type:  application/octet-stream ");
    Header("Accept-Ranges:  bytes ");
    Header("Accept-Length: " . filesize($filename));
    header("Content-Disposition:  attachment;  filename= $pdfName");
    echo file_get_contents($filename);
    readfile($filename);
    unlink($path);
}

/**
 * @param $arr
 * @param $ret
 * @return array
 * 判断简历里技能和岗位要求技能占比
 */
function getPercentage($arr, $ret)
{
    $must = $keXuan = $jia = $total = array();
    if (!$arr) { //简历无技能 均不达标
        return array('mP' => 0, "kP" => 0, "jP" => 0);
    }
    if (!$ret) {//岗位无技能要求 均达标
        return array('mP' => 100, "kP" => 100, "jP" => 100);
    }
    foreach ($ret as $v) {
        if ($v->skillid) {
            if ($v->opt == '必要项') {
                $must[] = $v->skillid;
            } else if ($v->opt == '可选项') {
                $keXuan[] = $v->skillid;
            } else if ($v->opt == '加分项') {
                $jia[] = $v->skillid;
            }
        }
    }

    $mustCount = count($must);
    $keXuanCount = count($keXuan);
    $jiaCount = count($jia);
    foreach ($arr as $v) {
        $total[] = $v->skillid;
    }
    //计算重复个数
    $m = count(array_intersect($must, $total));
    $ke = count(array_intersect($keXuan, $total));
    $j = count(array_intersect($jia, $total));

    if ($m > $mustCount) {
        $m = 100;
    } else {
        $mP = ($m / $mustCount) * 100;
    }
    if ($ke > $keXuanCount) {
        $kP = 100;
    } else {
        $kP = ($ke / $mustCount) * 100;
    }
    if ($j > $jiaCount) {
        $jP = 100;
    } else {
        $jP = ($j / $mustCount) * 100;
    }
    $percentage = array('mP' => $mP, "kP" => $kP, "jP" => $jP);
    return $percentage;
}


