<?php
set_time_limit(0);
require_once(__DIR__ . '/../../config.php');
require_once('../lib.php');
require_once('../service.php');
$dir = $CFG->dataroot . '/estimate_pdf'; //放置pdf文件夹
if (!file_exists($dir)) {
    if (!mkdir($dir, 0777, true)) { //创建和写入权限
        echo '文件夹创建失败';
        exit;
    };
}
if (!$USER) {  //登录超时重新登录
    redirect("/login/index.php");
    exit;
}
$html = $CFG->wwwroot . '/my/report/estimate_pdf.php?id=' . $USER->id; //需要导出pdf地址

$pdfName = date("Y-m-d_His") . '.pdf';
$path = $dir . '/' . $pdfName;

if (strstr(php_uname('s'), "Windows ")) {
    shell_exec(" wkhtmltopdf $html $path"); //windows  wkhtmltopdf 调用
} else {
    shell_exec("/usr/local/bin/wkhtmltopdf $html $path"); // linux  wkhtmltopdf 调用
}
upload($path, $pdfName);

