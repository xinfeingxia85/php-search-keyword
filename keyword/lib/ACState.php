<?php
/**
 * @author: jessica.yang
 * @date: 2011-10-24
 * @filename: php.ac.pretreatment.php
 * @description: Aho Corasick多模式匹配算法，简称AC算法，包含两个阶段，第一个是预处理阶段，即字典树的生成；第二个是搜索查找阶段，该文件完成第一阶段的预处理功能
 */

/**
 * @classname: State
 * @description: 状态类，用于表示字典树中的每一个状态节点
 */
class State {
    private $depth;// int类型，表示每一个状态对象的深度，从0开始表示
    private $edgeList;// 类似于列表，用于包含该状态下所包含的下一级所有State对象
    private $fail;// State对象，表示状态对象失效之后要跳转的地方
    private $outputs;// array对象，存放某一状态下可以输出的内容
    /**
     * @function State 构造函数
     * @param int depth 状态所处的深度
     * @return
     */
    public function State($depth) {
        $this->depth = $depth;
        //$this->edgeList = new SparseEdgeList();
        $this->edgeList = new DenseEdgeList();
        $this->fail = NULL;
        $this->outputs = array();
    }

    /**
     *@function extend 添加单个搜索词
     *@param char character 单个搜索词，或者一个字母、数字、或者一个汉字等
     *@return State
     **/
    public function extend($character) {
        if (!is_null($this->edgeList->get($character))){
            return $this->edgeList->get($character);
        }

        $nextState = new State($this->depth+1);
        $this->edgeList->put($character, $nextState);
        return $nextState;
    }
    /**
     *@function extendAll 添加搜索词
     *@param array contents 搜索词数组
     *@return State
     **/
    public function extendAll($contents) {
        $state = $this;
        $cnt = count($contents);
        for($i=0; $i<$cnt; $i++) {
            // 如果搜索的关键词存在，则直接返回该 关键词所处的State对象，否则添加该关键词
            if(!is_null($state->edgeList->get($contents[$i]))){
                $state = $state->edgeList->get($contents[$i]);
            }else{
                $state = $state->extend($contents[$i]);
            }
        }
        return $state;
    }
    /**
     * @function 计算搜索词的总长度
     * @param
     * @return int
     */
    public function size() {
        $keys = $this->edgeList->keys();
        $result = 1;
        $length = count($keys);
        for ($i=0; $i<$length; $i++){
            $result += $this->edgeList->get($keys[$i])->size();
        }
        return $result;
    }
    /**
     * @function 获取单个关键词所处的State对象
     * @param char character
     * @return State
     */
    public function get($character) {
        $res = $this->edgeList->get($character);
        return $res;
    }
    /**
     * @function 向State对象中添加下一级的搜索词及对应的State值
     * @param char character
     * @param State state
     * @return
     */
    public function put($character, $state) {
        $this->edgeList->put($character, $state);
    }
    /**
     * @function 获取State对象下一级的所有关键词
     * @param
     * @return Array
     */
    public function keys() {
        return $this->edgeList->keys();
    }
    /**
     * @function 获取State对象失效时对应的失效值
     * @param
     * @return State
     */
    public function getFail() {
        return $this->fail;
    }
    /**
     * @function 设置State对象失效时对应的失效值
     * @param
     * @return
     */
    public function setFail($state) {
        $this->fail = $state;
    }
    /**
     * @function 向State对象的outputs中添加输出内容
     * @param
     * @return
     */
    public function addOutput($str) {
        array_push($this->outputs, $str);
    }
    /**
     * @function 获取State对象的输出内容
     * @param
     * @return Array
     */
    public function getOutputs() {
        return $this->outputs;
    }
    /**
     * @function 设置State对象的输出内容
     * @param
     * @return
     */
    public function setOutputs($arr=array()){
        $this->outputs = $arr;
    }
}

////////////////////////////////////////////////////////
/**
 * @classname: DenseEdgeList
 * @description: 存储State对象下一级对应的所有State内容，以数组形式存储
 */
class DenseEdgeList{
    private $array;// State对象，包含对应的搜索词及State值
    /**
     * 构造函数
     */
    public function DenseEdgeList() {
        $this->array = array();
    }
    /**
     * @function 从链表存储形式的内容转为数组存储形式的内容
     * @param SparseEdgeList list
     * @return DenseEdgeList
     */
    public function fromSparse($list) {
        $keys = $list->keys();
        $newInstance = new DenseEdgeList();
        for($i=0; $i<count($keys); $i++) {
            $newInstance->put($keys[$i], $list->get($keys[$i]));
        }
        return $newInstance;
    }
    /**
     * @function 获取搜索词对应的State值
     * @param char word
     * @return 如果存在则返回对应的State对象，否则返回NULL
     */
    public function get($word) {
        if(array_key_exists($word, $this->array)){
            return $this->array["$word"];
        }else{
            return NULL;
        }
    }
    /**
     * @function 添加搜索词及对应的State值到数组中
     * @param char word 单个搜索词
     * @param State state 搜索词对应的State对象
     * @return
     */
    public function put($word, $state) {
        $this->array["$word"] = $state;
    }
    /**
     * @function 获取所有的搜索词
     * @param
     * @return Array
     */
    public function keys() {
        return array_keys($this->array);
    }
}

///////////////////////////////////////
/**
 * @classname: SparseEdgeList
 * @description: 存储State对象下一级对应的所有State内容，以链表形式存储
 */
class SparseEdgeList{
    private $head;// Cons对象
    /**
     * 构造函数
     */
    public function SparseEdgeList() {
        $this->head = NULL;
    }
    /**
     * @function 获取搜索词对应的State值
     * @param char word
     * @return 如果存在则返回对应的State对象，否则返回NULL
     */
    public function get($word) {
        $cons = $this->head;
        while(!is_null($cons)){
            if ($cons->word === $word){
                return $cons->state;
            }
            $cons = $cons->next;
        }
        return NULL;
    }
    /**
     * @function 添加搜索词及对应的State值到链接中
     * @param char word 单个搜索词
     * @param State state 搜索词对应的State对象
     * @return
     */
    public function put($word, $state){
        $this->head = new Cons($word, $state, $this->head);
    }
    /**
     * @function 获取所有的搜索词
     * @param
     * @return Array
     */
    public function keys() {
        $result = array();
        $c = $this->head;
        while(!is_null($c)){
            array_push($result, $c->word);
            $c = $c->next;
        }
        return $result;
    }

}
/**
 * @classname: Cons
 * @description: 用于SparseEdgeList生成链表时表示的节点对象
 */
class Cons {
    var $word;// 单个搜索词
    var $state;// State对象
    var $next;// Cons对象
    /**
     * 构造函数
     */
    public function Cons($word, $state, $next){
        $this->word = $word;
        $this->state = $state;
        $this->next = $next;
    }
}