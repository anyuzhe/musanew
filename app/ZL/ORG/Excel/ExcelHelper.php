<?php
/**
 * Created by PhpStorm.
 * User: zhenglong
 * Date: 2018/1/15
 * Time: 下午4:43
 */

namespace App\ZL\ORG\Excel;

use Maatwebsite\Excel\Facades\Excel;

class ExcelHelper
{
    #composer require maatwebsite/excel
    public function __construct()
    {
        $dir = dirname(__FILE__);
        require $dir."/PHPExcel/PHPExcel.php";
    }
    public static function templateExport($excel_field,$name=null)
    {
        $objExcel = new \PHPExcel();
        $objWriter = new \PHPExcel_Writer_Excel2007($objExcel);
        $objExcel->setActiveSheetIndex(0);
        $objActSheet = $objExcel->getActiveSheet();

        $across = ['A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','Z','Y','Z','AA','BB','CC','DD',];

        $i = 0;
        foreach ($excel_field as $field=>$value) {
            if(is_array($value)){
                $_title = array_shift($value);
                $objActSheet->setCellValue($across[$i].'1', $_title);
                $_v = implode(',',$value);
//                dump($_v);
                for($ii=2;$ii<1000;$ii++){
                    $objValidation = null;
                    $objValidation = $objActSheet->getCell($across[$i].$ii)->getDataValidation();
//                    $objValidation -> setType(\PHPExcel_Cell_DataType::TYPE_STRING)
                    $objValidation -> setType(\PHPExcel_Cell_DataValidation::TYPE_LIST)
                        -> setErrorStyle(\PHPExcel_Cell_DataValidation::STYLE_INFORMATION)
//                        -> setAllowBlank(false)
//                        -> setShowInputMessage(true)
//                        -> setShowErrorMessage(true)
                        -> setShowDropDown(true)
//                        -> setErrorTitle('输入的值有误')
//                        -> setError('您输入的值不在下拉框列表内.')
//                        -> setPromptTitle('设备类型')
                        -> setFormula1('"'.$_v.'"');
                }
            }else{
                $objActSheet->setCellValue($across[$i].'1', $value);
            }
            $i++;
        }
//        dd(1);
//设置单元格颜色
//        $objStyleA1 = $objActSheet ->getStyle('A1');
//        $objStyleA1 ->getNumberFormat()->setFormatCode(\PHPExcel_Style_NumberFormat::FORMAT_NUMBER);
////设置CELL填充颜色
//        $objFillA1 = $objStyleA1->getFill();
//        $objFillA1->setFillType(\PHPExcel_Style_Fill::FILL_SOLID);
//        $objFillA1->getStartColor()->setARGB('FFcdcdff');
//
////设置当前活动sheet的名称
//        $objActSheet->setTitle('mySheet1');

//新增一个sheet，命名为mySheet2
//        $objExcel->createSheet();

// Add some data to the second sheet, resembling some different data types
//        $objExcel->setActiveSheetIndex(1);
//        $objExcel->getActiveSheet()->setCellValue('A1', 'item1');
//        $objExcel->getActiveSheet()->setCellValue('A2', 'item2');
//        $objExcel->getActiveSheet()->setCellValue('A3', 'item3');
//        $objExcel->getActiveSheet()->setTitle('mySheet2');
//        $objExcel->setActiveSheetIndex(0);

        $outputFileName = $name?$name.'.xlsx':"模版.xlsx";

        header("Content-Type: application/force-download");
        header("Content-Type: application/octet-stream");
        header("Content-Type: application/download");
        header('Content-Disposition:inline;filename="'.$outputFileName.'"');
        header("Content-Transfer-Encoding: binary");
        header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
        header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
        header("Pragma: no-cache");
        $objWriter->save('php://output');
    }

    public static function importExcel($excel_field,$file)
    {
        $fields = [];
        $array = self::getArr($file->getRealPath(),0,1);
        foreach ($array as $k=>$v) {
            foreach ($v as $kk=>$vv) {
                $fields[$k][self::getKey($excel_field,$kk)] = self::getValue($excel_field,$kk,$vv);
            }
        }
        return [
            'upload'=>$array,
            'data'=>$fields,
        ];
    }

    public static function getKey($excel_field,$key)
    {
        foreach ($excel_field as $k=>$v) {
            if(is_array($v))
                $v = $v[0];
            if($v==$key)
                return $k;
        }
        return null;
    }

    public static function getValue($excel_field,$key,$value)
    {
        foreach ($excel_field as $k=>$v) {
            if(is_array($v) && $v[0]==$key){
                foreach ($v as $kk=>$vv) {
                    if($vv==$value)
                        return $kk;
                }
            }else{
                if($key==$v)
                    return $value;
            }
        }
        return null;
    }

    //老胡写的读取相关方法
    public static function getExecl($filePath){
        $PHPReader = new \PHPExcel_Reader_Excel2007();
        if (!$PHPReader->canRead($filePath)) {
            $PHPReader = new \PHPExcel_Reader_Excel5();
            if (!$PHPReader->canRead($filePath)) {
                echo 'no Excel';
                return null;
            }
        }
        $excel = $PHPReader->load($filePath);
        return $excel;
    }

    public static function getSheet($filePath,$sheetNo=0)
    {
        $excel = self::getExecl($filePath);
        $sheet = $excel->getSheet($sheetNo);
        return $sheet;
    }

    public static function getArr($filePath,$sheetNo=0,$startRow=1){
        $sheet = self::getSheet($filePath,$sheetNo);
        $columnCount = $sheet->getHighestColumn();
        $rowCount = $sheet->getHighestRow();
        $arr = array();
        $keys = array();
        for($row=$startRow;$row<=$rowCount;$row++){
            $data = [];
            for($column='A';$column<=$columnCount;$column++){
                $val = trim($sheet->getCellByColumnAndRow(ord($column)-65,$row)->getValue());
                if($row == $startRow){
                    $keys[$column] = $val;
                }else{
                    $data[$keys[$column]] = $val;
                }
            }
            if($row > $startRow){
                $arr[] = $data;
            }
        }
        return $arr;
    }


    public function echoBy($str)
    {
        $qian=array(" ","　","\t","\n","\r",",");
        $hou=array("","","","","","");
        return str_replace($qian,$hou,$str);
    }

    public static function echoItem($item,$has_t=true)
    {
        if(strpos($item,'"')!==false){
            $c = "'".$item."'";
        }elseif (preg_match("/^0\d+$/",$item)){
            $c = "'".$item."'";
        }else{
            $c = $item;
        }
        if($has_t)
            return $c. "\t";
        else
            return $c;
    }




    public function dumpExcel($title,$data,$name,$headline=null){
//        $dir = dirname(__FILE__);
//        require $dir."/PHPExcel/PHPExcel.php";//引入PHPExcel
        $excel = new \PHPExcel();
        $letter = array('A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z','AA','BB','CC');

        if($headline){
            $column_index = "A";
            //清空要合并的首行单元格值，用于填充合并后的单元格值
            $excel->getActiveSheet()->setCellValue($column_index.''.'1',$headline);
            //合并单元格,值为''
            $h = $column_index.''.'1'.":".$letter[count($title)-1].''.'1';
            $excel->getActiveSheet()->mergeCells($h);
            $excel->getActiveSheet()->getStyle($h)->getFont()->setBold(true);
            $excel->getActiveSheet()->getStyle($h)->getFont()->setSize(16);
            $excel->getActiveSheet()->getStyle($h)->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            $excel->getActiveSheet()->getStyle($h)->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            //拆分单元格,将清空合并前单元格的值并还原合并前单元格的样式
//            $excel->getActiveSheet()->unmergeCells($column_index.''.'1'.":".$column_index.''.count($title));
        }

        for($i = 0;$i < count($title);$i++) {
            $_v = $headline?2:1;
            $excel->getActiveSheet()->setCellValue("$letter[$i]{$_v}","$title[$i]");

        }
        $i = $headline?3:2;
        $j = 0;
//        for ($i = 2;$i <= count($data) + 1;$i++) {
        foreach ($data as $v) {
            $j = 0;

            foreach ($v as $key=>$value) {
                $excel->getActiveSheet()->setCellValue("$letter[$j]$i","$value");

                $j++;

            }
            $i++;

        }
        $aa = new \PHPExcel_IOFactory();
        $objWriter=$aa::createWriter($excel,"Excel2007");//生成excel文件
        //$objWriter->save($dir."/export_1.xls");//保存文件
        $this->browser_export('Excel2007',$name.'.xlsx');//输出到浏览器
        $objWriter->save("php://output");
    }

    //输出到浏览器
    public function browser_export($type,$filename){
        if($type=="Excel5"){
            header('Content-Type: application/vnd.ms-excel');//告诉浏览器将要输出excel03文件
        }else{
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');//告诉浏览器数据excel07文件
        }
        header('Content-Disposition: attachment;filename="'.$filename.'"');//告诉浏览器将输出文件的名称
        header('Cache-Control: max-age=0');//禁止缓存
    }
}
