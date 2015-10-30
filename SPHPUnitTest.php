<?php
/**
 * SPUT -- Simple PHP Unit Test SPHPUnit
 * php测试工具类
 *
 * @author  writethesky<writethesky@163.com>
 * @version 0.1
 * Date: 2015/10/30
 */
defined('ROOT_ABS_PATH') || define('ROOT_ABS_PATH' , dirname( dirname( dirname( dirname( __FILE__))))); //项目绝对路径
defined('APP_ABS_PATH') || define('APP_ABS_PATH' ,ROOT_ABS_PATH .DIRECTORY_SEPARATOR .  'Application'); //项目Application绝对路径
defined('TEST_CONTROLLER_FILE_EXT') || define('TEST_CONTROLLER_FILE_EXT' , '.class.php');   //测试文件的后缀
defined('TEST_CONTROLLER_NAME') || define('TEST_CONTROLLER_NAME' , 'Controller');           //测试文件的控制器名称
class SPHPUnitTest {
    function __construct($test_class){
        header("Content-type:text/html;charset=utf-8");
        $this->test_class = $test_class;
    }
    const ASSERT_STATUS_FAILED = 0; //断言失败状态
    const ASSERT_STATUS_SUCCESS = 1; //断言成功状态
    const ASSERT_STATUS_ERROR = 2; //断言错误状态
    // 测试状态对应的颜色 array( background-color , font-color )
    private $assertColors = array(
        self::ASSERT_STATUS_FAILED => array('#FFA38C', 'black'), //背景浅红色 ， 字体白色
        self::ASSERT_STATUS_SUCCESS => array('#B5FF64', 'black'), //背景， 字体白色
        self::ASSERT_STATUS_ERROR => array('#AF7942' , 'black'), //背景黄色， 字体白色
    );
    // 测试状态提示语句
    private $assertStatusMessage = array(
        self::ASSERT_STATUS_FAILED => '失败',
        self::ASSERT_STATUS_SUCCESS => '成功',
        self::ASSERT_STATUS_ERROR => '参数错误'
    );
    // 测试结果输出的字段设置，可任意注释相关字段
    private $outPutField = array(
        'status' => '状态',
        'statusMessage' => '结果',
        'data'=> '测试数据',
        'message'=>'用例说明',
        'class' => '测试类',
        'method' => '测试方法' ,
        'fileLine' => '所在文件（行）',
        'runtime' => '运行时间'
    );
    private $testControllers = null; //待测试的类名数组

    /**
     * 开始执行测试
     * 测试方法必须以test开头
     * @param $autoGetTest boolean true-自动测试所有test_calss下的测试方法 false-只测试当前测试类下的当前测试方法
     */
    public function run($auto_get_test = false){
        if($auto_get_test){
            $debugBacktrace = debug_backtrace();

            $test_class = is_array($this->test_class)?$this->test_class:array($this->test_class);

        }
        else
            $test_class = null;


        foreach($test_class as $controllerName){
            $controllerObj = $controllerName;
            $controllerMethods = get_class_methods($controllerName); //获取测试类的所有方法
            foreach($controllerMethods as $k=>$method){    //遍历测试类的所有方法，判断方法是否以test开始
                if(strpos($method , 'test') === 0){
                    $controllerObj->$method();   //以test开始的方法则是测试方法
                }
            }
        }
        $this->outputAsHtml();
    }

    /**
     * 输出测试结果为html页面
     */
    protected function outputAsHtml(){
        $colors = $this->assertColors;
        $assertMessages = $this->assertStatusMessage;
        $results = $this->getTestResult();
        echo <<<EOF
            <style>
                table {width:100% ; border-collapse: collapse}
                table td,table th{border:solid 1px #ccc;padding:5px}
                .num{font-size:1.4em;font-weight:bold}
            </style>
EOF;
        echo   '<h2 style="text-align:center;">';
        foreach($this->getTestResultTotal() as $status=>$count){
            $color = $colors[$status];
            $assertMessage = $assertMessages[$status];
            echo "<div style=\"display:inline-block;padding:5px;margin-right:10px;background:{$color[0]} ; color:{$color[1]}\">{$assertMessage}(<span class=\"num\">{$count}</span>)</div>";
        }
        echo   '</h2>';
        echo '<table>';
        echo '<tr>';
        foreach($this->outPutField as $fieldKey => $fieldName)
            echo "<th>{$fieldName}</th>";
        echo '</tr>';
        foreach($results as $result){
            $color = $colors[$result['status']];
            echo "<tr style=\"background:{$color[0]};color:{$color[1]} \">";
            foreach($this->outPutField as $fieldKey => $fieldName)
                echo "<td>{$result[$fieldKey]}</td>";
            echo "</tr>";
        }
        echo '</table>';
        exit;
    }
    /**
     * 汇总测试结果
     * @return array
     */
    private function getTestResultTotal(){
        $total = array(0, 0, 0);
        foreach($this->getTestResult() as $result){
            $total[$result['status']] ++ ;
        }
        return $total;
    }

    /**
     * 断言
     * @param bool $result 断言表达式 如 $a != 2
     * @param string $message 用例名称
     * @param mixed $testData 测试数据 一般是 $a
     * @return bool 断言成功或失败
     */
    public function assert($result , $message = '' , $testData = null){
        $this->pushTestResult($result , $message, $testData);
        return $result;
    }
    /**
     * 将某个测试结果存入集合中
     * @param $result boolean|int 断言结果
     * @param $testData mixed 测试时传入的数据
     * @param $message mixed 提示信息
     */
    protected function pushTestResult($result , $message = '' , $testData = null){
        if(is_null($testData))
            $testData = 'NULL';
        else if(is_bool($testData))
            $testData = $testData ? 'true' : 'false';
        else if(is_array($testData))
            $testData = 'Array('.count($testData).')：' . json_encode($testData , JSON_UNESCAPED_UNICODE) ;
        else if(is_object($testData)){
            $testData = 'Object：' . json_encode((Array)$testData ,  JSON_UNESCAPED_UNICODE) ;
        }
        if(strlen($testData) > 100)
            $testData = substr($testData , 0 , 100) . '...';
        if(is_bool($result)){
            $data['status'] = $result ? self::ASSERT_STATUS_SUCCESS : self::ASSERT_STATUS_FAILED ; //断言状态 ：1：成功 , 0:失败
            $data['message'] = $message;
            $debugIndex = 2;
        }
        else{
            $data['status'] = self::ASSERT_STATUS_ERROR ;  //断言状态： 2：断言方法参数错误
            $data['message'] = $result;
            $debugIndex = 3;
        }
        $assertMessage = $this->assertStatusMessage;
        $data['statusMessage'] = $assertMessage[$data['status']] ;
        $info = debug_backtrace();
        $data['class'] = $info[$debugIndex]['class'];
        $data['data'] = is_array( $testData ) ? json_encode($testData) : $testData;
        $data['file'] =  str_replace(ROOT_ABS_PATH , '' , $info[$debugIndex-1]['file']);
        $data['method'] = $info[$debugIndex]['function'];
        $data['fileLine'] =   $data['file']  . "( Line： {$info[$debugIndex-1]['line']} )";
        $data['runtime'] = self::getRuntime(true) ;
        $GLOBALS['__testResults'][] = $data;  //将测试结果存入
    }
    /**
     * 获取测试结果的存储数组
     * @return array
     */
    protected function getTestResult(){
        return $GLOBALS['__testResults'];
    }
    /**
     * 抓取函数输出
     * @param string $func_name 函数名
     * @param array $arg 函数参数数组
     * @return string 函数执行后的输出内容
     */
    public function fetchFuncOutput($func_name, $arg = array()){
        ob_start();
            call_user_func_array($func_name, $arg);
        
        $data = ob_get_flush();
        ob_clean();
        header("Content-type:text/html;charset=utf-8");
        return $data;
    }
    /**
     * 抓取方法输出
     * @param string $emthod_name 方法名
     * @param object $obj 对象
     * @param array $arg 方法参数数组
     * @return string 方法执行后的输出内容
     */
    public function fetchMethodOutput($emthod_name, $obj, $arg = array()){
        ob_start();
            call_user_method_array($emthod_name, $obj, $arg);
        
        $data = ob_get_flush();
        ob_clean();
        header("Content-type:text/html;charset=utf-8");
        return $data;
    }
    



    /**
     * @param float|boolean $startTime 开始时间
     *      一般是float类型  通过microtime（true）得到的结果
     *      如果传递true，则将上次调用该方法的时间作为 startTime
     * @param int $type  type=1 返回毫秒  ，其他返回秒
     * @return string
     */
    public static function getRuntime($startTime = null , $type = 1) //
    {
        if ($startTime === true)
            $startTime = isset($GLOBALS['lastRequestTime']) ? $GLOBALS['lastRequestTime'] : $_SERVER['REQUEST_TIME_FLOAT'];
        else if (empty($startTime))
            $startTime = $_SERVER['REQUEST_TIME_FLOAT'];
        $now = microtime(true);
        $runtime = $now - $startTime;
        $GLOBALS['lastRequestTime'] = $now;
        if ($type === 1)
            return round(($runtime * 1000) , 2) . "毫秒";
        return round($runtime , 2)."秒";
    }
}
