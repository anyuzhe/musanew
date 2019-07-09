<?php
//15523 332
error_reporting(1);
$target = '/data/wwwroot/musanew'; // 生产环境web目录12
//$token = 'asas';
$wwwUser = 'www';
$wwwGroup = 'www';
//$json = json_decode(file_get_contents('php://input'), true);
//if (empty($json['token']) || $json['token'] !== $token) {
//    exit('error request');
//}
//$repo = $json['repository']['name'];
$cmds = array(
    "cd $target && git reset --hard && git pull && composer dump-autoload",// && composer dump-autoload
    "chown -R {$wwwUser}:{$wwwGroup} $target/",
);
foreach ($cmds as $cmd) {
    shell_exec($cmd);
}
// 感谢@墨迹凡指正，可以直接用www用户拉取代码而不用每次拉取后再修改用户组

//1
//$cmd = "sudo -Hu www-data cd $target && git pull";
//
//shell_exec($cmd);
echo 'ok';