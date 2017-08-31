<?php
/**
 * @author: jessica.yang
 * @date: 2011-11-03
 * @filename: php.ac.app.php
 * @description: AC多模式匹配算法应用类
 */
// 引入文件
include("ACSearch.php");

class ACAppClass{
    private $showtimeFlag;// 是否显示运行时间，false：不显示；true：显示，默认为false
    /**?
     * @function 构造函数
     * @param
     * @return
     */
    public function ACAppClass(){
        $this->showtimeFlag = true;
    }
    /**
     * @function 从字符串中查找单个关键词
     * @param string word 关键词
     * @param string text 被查找的字符串
     * @return Array
     */
    public function findSingleWord($word, $text){
        try{
            if(strlen(trim($word))==0){
                throw new Exception("Key word's content is empty.");
            }
        }catch(Exception $e){
            echo $e->getMessage();
            return;
        }

        $arr = array(trim($word));
        return $this->findWordsInArray($arr, $text);

    }
    /**
     * @function 从字符串中查找多个关键词
     * @param Array words 关键词数组
     * @param string text 被查找的字符串
     * @return Array
     */
    public function findWordsInArray($words, $text){
        $len = count($words);
        try{
            if($len==0){
                throw new Exception("Array of keywords is empty.");
            }
        }catch(Exception $e){
            echo $e->getMessage();
            return;
        }
        if($this->showtimeFlag){
            $starttime = $this->getmicrotime();
        }
        echo "开始构建...";
        $tree = new AhoCorasick();
        try{
            for ($i=0; $i<$len; $i++) {
                if(trim($words[$i])==""){
                    throw new Exception("Key word's content is empty.");
                }
                echo "编号：" .($i+1)." ".$words[$i]."\n";
                $tree->add(trim($words[$i]));

            }
        }catch(Exception $e){
            echo $e->getMessage();
            return;
        }

        $tree->prepare();
        echo "构建完成....";
        if($this->showtimeFlag){
            $endtime1 = $this->getmicrotime();
            echo "<br>add run time is: ".($endtime1-$starttime)."ms<br>";
        }
        $res = array();
        $obj = $tree->search($text);
        while($obj->hasNext()){
            $result = $obj->next();
            $res = array_unique(array_merge($res, $result->getOutputs()));
        }
        if($this->showtimeFlag){
            $endtime2 = $this->getmicrotime();
            echo "<br>search run time is: ".($endtime2-$endtime1)."ms<br>";
        }
        return $res;
    }

    /**
     * @function 从文件中查找关键词
     * @param string $keyfile 关键词所在的文件名称及路径
     * @param string $textfile 被查找的内容所在的文件名称及路径
     * @return Array
     */
    public function findWordsInFile($keyfile, $textfile){
        /**************暂时去掉***********／
        /*try{
            if(!is_file($keyfile) || !is_file($textfile)){
                throw new Exception("Can not find the file.");
            }
        }catch(Exception $e){
            echo $e->getMessage();
            return;
        }
        // 搜索词所在的文件内容为空时，抛出异常
        try{
            if(strlen(trim(file_get_contents($keyfile)))==0){
                throw new Exception("File's content is empty.");
            }
        }catch(Exception $e){
            echo $e->getMessage();
            return;
        }*/
        /*************暂时去掉**************/
        // 打开文件
        $handle1 = fopen($keyfile, "r");
        $handle2 = fopen($textfile, "r");
        $arr = array();
        $contents = "";
        try{
            while (!feof($handle1)) {
                $line = trim(fgets($handle1));
                if(strlen($line)!=0){
                    $arr[] = $line;
                }
            }
            while (!feof($handle2)) {
                $line = trim(fgets($handle2));
                if(strlen($line)!=0){
                    $contents .= $line;
                }
            }
        }catch(Excption $e){
            echo $e->getMessage();
            return;
        }
        // 关闭文件
        fclose($handle1);
        fclose($handle2);
        return $this->findWordsInArray($arr, $contents);
    }
    /**
     * @function 获取时间戳，单位为毫秒
     * @param
     * @return float
     */
    function getmicrotime(){
        list($usec, $sec) = explode(" ",microtime());
        $value = (float)$usec+(float)$sec;
        return round($value*1000, 3);
    }
}