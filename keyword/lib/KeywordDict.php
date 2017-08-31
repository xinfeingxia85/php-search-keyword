<?php

/**
 * 关键词字典生成类
 */
Class  KeywordDict
{
    //存放关键词最大长度
    const MAX_WORD_LENGTH = 15;

    //文字编码
    const CHAR_ENCODINNG = "GBK";

    //存放所有关键词
    private $words = array();

    private $maxStoreWordLength = 0;
    private $minStoreWordLength = 999999;

    //存放每个字符在对应位数上是否存在敏感词，以及哪几位是敏感词
    private $fastPositionCheck = array();
    private $fastLengthCheck = array();

    //字典缓存
    private $dictCache = array();

    /**
     * 关键词
     */
    public function getWords()
    {
        return $this->words;
    }

    /**
     * 最大长度
     */
    public function getMaxWordLength()
    {
        return $this->maxStoreWordLength;
    }

    /**
     * 最小长度
     */
    public function getMinWordLength()
    {
        return $this->minStoreWordLength;
    }

    /**
     * 位置存储
     */
    public function getFastCheck()
    {
        return $this->fastPositionCheck;
    }

    /**
     * 长度存储
     */
    public function getFastLength()
    {
        return $this->fastLengthCheck;
    }

    /**
     * 添加关键词生成查找字典
     */
    public function addWord($keyword)
    {
        $keyword = mb_convert_case($keyword, MB_CASE_LOWER, self::CHAR_ENCODINNG);
        $keywordLength = mb_strlen($keyword, self::CHAR_ENCODINNG);
        $this->maxStoreWordLength = max($this->maxStoreWordLength, $keywordLength);
        $this->minStoreWordLength = min($this->minStoreWordLength, $keywordLength);
        $firstChar = mb_substr($keyword, 0, 1, self::CHAR_ENCODINNG);

        //记录每个词的位置, 通过&的方式进行验证
        for ($i = 0; $i < $keywordLength; $i++) {
            $currentChar = mb_substr($keyword, $i, 1, self::CHAR_ENCODINNG);
//            $strNum = $this->getIntegerFromStringGBK($currentChar);
            $this->fastPositionCheck[$currentChar] |= (1 << $i);
        }

        //记录以某个子开头的关键字的长度信息，左移位数长度为该字符串长度减一
        $this->fastLengthCheck[$firstChar] |= (1 << ($keywordLength - 1));

        //添加关键词
        $this->words[$firstChar][$keyword] = $keyword;
    }

    /**
     * 单个字符转换为整数
     * @param $singleChar  单个字符
     * @return  int 整数
     */
    public function getIntegerFromStringGBK($singleChar)
    {
        $arr = str_split($singleChar);
        $length = sizeof($arr);
        $binStr = "";
        if ($length == 1) {
            return ord($singleChar);
        } else if ($length > 1) {
            foreach ($arr as $val) {
                $binStr .= decbin(ord($val));
            }
            return bindec($binStr);
        } else {
            return 0;
        }
    }

    /**
     * 获取字典要缓存内容
     */
    public function getDictCache(){
        $this->dictCache['words'] = $this->words;
        $this->dictCache['maxWordsLength'] = $this->maxStoreWordLength;
        $this->dictCache['minWordsLength'] = $this->minStoreWordLength;
        $this->dictCache['fastCheck'] = $this->fastPositionCheck;
        $this->dictCache['fastLength'] = $this->fastLengthCheck;

        return $this->dictCache;
    }

    /***
     * 清除生成的字典数据缓存
     */
    public function setDictCache($dictCache){
        if(is_array($dictCache) && sizeof($this->dictCache) > 1) {
            $this->dictCache = $dictCache;
        }
        $this->words = $this->dictCache['words'];
        $this->maxStoreWordLength = $this->dictCache['maxWordsLength'];
        $this->minStoreWordLength = $this->dictCache['minWordsLength'];
        $this->fastPositionCheck = $this->dictCache['fastCheck'];
        $this->fastLengthCheck = $this->dictCache['fastLength'];
    }
}