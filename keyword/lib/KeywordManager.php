<?php

/**
 * 关键词管理类
 *
 * 用于字符在1万以内的，2-3千性能最佳
 */
Class KeywordManager
{
    //文字编码
    const CHAR_ENCODINNG = "GBK";

    //关键词生成字典类
    private $keywordDict;

    //关键词和次数
    private $keywordResult;

    //是否存在符合条件关键词
    private $isExistKeyword = false;

    public function KeywordManager($keywordDict)
    {
        $this->keywordDict = $keywordDict;
    }

    /**
     * 根据文本内容查找关键词
     * <pre>
     * @param string $text 文本内容
     * @param int $sType 默认为1(查找所有的关键词)，2(只验证是否有符合的关键词)
     * @return  array 结果关键词
     * </pre>
     */
    private function searchWord($text, $sType = 1)
    {
        $index = 0;
        $textLength = mb_strlen($text, self::CHAR_ENCODINNG);
        $currentChar = "";
        $maxWordLength = $this->keywordDict->getMaxWordLength();
        $minWordLength = $this->keywordDict->getMinWordLength();
        $fastCheck = $this->keywordDict->getFastCheck();
        $words = $this->keywordDict->getWords();
        $fastLength = $this->keywordDict->getFastLength();
        $isExists = false;

        //对大小写敏感
        $text = mb_convert_case($text, MB_CASE_LOWER, self::CHAR_ENCODINNG);

        while ($index < $textLength) {
            $currentChar = mb_substr($text, $index, 1, self::CHAR_ENCODINNG);

            //找到以某个字符开头的关键词
            if (($fastCheck[$currentChar] & 1) == 0) {
                do {
                    $index++;
                    $currentChar = mb_substr($text, $index, 1, self::CHAR_ENCODINNG);
                } while (($index < $textLength) && (($fastCheck[$currentChar] & 1) == 0));
                if ($index >= $textLength) break;
            }

            //此时已经判定，当前的这个字符出现在关键词的第一位上，进行处理
            $jump = 1;
            for ($j = 0; $j <= min($maxWordLength, $textLength - $index - 1); $j++) {
                $current = mb_substr($text, $j + $index, 1, self::CHAR_ENCODINNG);

                //判断当前字符是否在对应的位置上, 实现快速的判断
                if (($fastCheck[$current] & (1 << min($j, $maxWordLength))) == 0) {
                    break;
                }

                //当判断符合条件的长度大雨或者等于最小长度时，当前的截取字符串有可能会是关键字，要做详细的判定
                if ($j + 1 >= $minWordLength) {
                    if (($fastLength[$currentChar] & (1 << min($j, $maxWordLength))) > 0) {
                        $sub = mb_substr($text, $index, $j + 1, self::CHAR_ENCODINNG);

                        //在字典中搜索判断，得出结论
                        if ((sizeof($words[$currentChar]) > 1) && ($words[$currentChar][$sub] == $sub)) {

                            if (2 == $sType) {
                                $this->isExistKeyword = true;
                                return '';
                            } else {
                                $this->keywordResult[$sub] += 1;
                            }
                        }
                    }
                }
            }

            $index += $jump;
        }
    }

    /**
     * 文本内容出现的所有关键词
     * <pre>
     * @param $text 文本内容
     * @return array 排重后的关键词
     * </pre>
     */
    public function fetchAllKeyword($text)
    {
        $this->searchWord($text);
        if ($this->keywordResult) return array_keys($this->keywordResult);
        return "";
    }

    /**
     * 文本内容出现的所有关键词次数
     * <pre>
     * @param $text 文本内容
     * @return array 排重后的关键词次数
     * </pre>
     */
    public function fetchAllKeywordTimes($text)
    {
        $this->searchWord($text);
        return $this->keywordResult;
    }

    /**
     * 文本内容出现关键词
     * <pre>
     * @param $text 文本内容
     * @return boolean true|false
     * </pre>
     */
    public function isExistsKeyword($text){
        $this->searchWord($text, 2);
        return $this->isExistKeyword;
    }
}