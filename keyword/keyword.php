<?php
/**
 * 测试
 */
//require("./lib/AC.php");
//$obj = new ACAppClass();
//$res1 = $obj->findWordsInFile("./keyword.txt", "./content.txt");
//print_r($res1);
ini_set("display_errors", 1);
require("./lib/KeywordDict.php");
require("./lib/KeywordManager.php");
$keywordArr = readFileContent("./keyword.txt");
$keywordObj = new KeywordDict();
$startTime = microtime(true);
foreach ($keywordArr as  $keyword){
    $keywordObj->addWord($keyword);
}
$cacheArr = $keywordObj->getDictCache();

//生成字典缓存
$fileName = "./cache.php";
//$conentStr = "<?php\r\nreturn ".var_export($cacheArr, 1).";";
//$fp = fopen($fileName, "w+");
//fputs($fp, $conentStr);
//fclose($fp);
$dictCache = include($fileName);
$endTime = microtime(true);
$keywordObj->setDictCache($dictCache);
echo "生成字典总共耗时:".($endTime - $startTime)."\n";
echo "--------------------------------------------\n";

$kManagerObj = new KeywordManager($keywordObj);
$textStr = file_get_contents("./content.txt");
$startTime1 = microtime(true);
$keywordArr = $kManagerObj->fetchAllKeyword($textStr);
print_r($keywordArr);
$endTime1 = microtime(true);
echo "查找总共耗时:".(($endTime1 - $startTime1)*1000)."ms\n";

/***********************test function ******************/
function readFileContent($fileName){
    // 打开文件
    $handle1 = fopen($fileName, "r");
    $arr = array();
    try{
        while (!feof($handle1)) {
            $line = trim(fgets($handle1));
            if(strlen($line)!=0){
                $arr[] = $line;
            }
        }
    }catch(Excption $e){
        echo $e->getMessage();
        return;
    }
    // 关闭文件
    fclose($handle1);
    return $arr;
}