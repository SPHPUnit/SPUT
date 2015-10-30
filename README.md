# SPUT
SPUT -- Simple PHP Unit Test SPHPUnit

SPUT 是一个简单的php单元测试工具类

# 声明

该类的最初版本是由[ThinkPHPUnit](https://github.com/hizt/ThinkPHPUnit)精简而来



# 文档
## 使用方法

* 下载 `SPHPUnitTest.php` 文件
* 将其放置在你的项目能够引入的位置
* 在你的项目中引入 `SPHPUnitTest.php` 文件。例如： `require ROOT_PATH. APP_PATH. "./Test/SPHPUnitTest.php";`
* 编写测试用例

## 例子
### thinkPHP中使用的例子

```php
<?php
namespace Test\Controller;
use Think\Controller;
require ROOT_PATH. APP_PATH. "./Test/SPHPUnitTest.php";
class IndexController extends Controller {
    function __construct(){
		parent::__construct();
		$arr = array(
			$home = A('Home'),
			$home = A('Show'),
			$this,
		);
		$this->test = new \SPHPUnitTest($arr);
	}
    function index(){
        //通过自动遍历测试类的方式执行测试
        $this->test->run(true);
    }

    function testController_HomeIndex_AutoNumAjax(){
    	$index = A('Home/Index');
    	$data = $this->test->fetchMethodOutput('AutoNumAjax', $index);
    	$data = json_decode($data);
    	$this->test->assert(property_exists($data, "liuliang"), '属性liuliang 是否缺失', $data);
    	$this->test->assert(property_exists($data, "fuwu"), '属性fuwu是否缺失', $data);
    	$this->test->assert($data->liuliang >= 623351, '属性liuliang值是否正确', $data);
    	$this->test->assert($data->fuwu >= 8194, '属性fuwu值是否正确', $data);
    }
}
```
浏览器访问 http://your-local-domain/Test,即可执行测试用例

### 其它的例子

待补充

## 类和方法

### 类

想要使用类中的方法，有两种方式

1. 实例化： `new \SPHPUnitTest($array)`
$array 需要执行的测试类，多个测试类以数组的形式传递

2. 继承： 

```php
class testIndex extends \SPHPUnitTest {
    function __construct(){
    	parent::__construct();
        $this->test_class = $arr;
    }
}
```

test_class 即是需要执行的测试类，多个测试类以数组的形式传递

### 方法

    /**
     * 开始执行测试
     * 测试方法必须以test开头
     * @param $autoGetTest boolean true-自动测试所有test_calss下的测试方法 false-只测试当前测试类下的当前测试方法
     */
    public function run($auto_get_test = false){

------------------

    /**
     * 抓取函数输出
     * @param string $func_name 函数名
     * @param array $arg 函数参数数组
     * @return string 函数执行后的输出内容
     */
    public function fetchFuncOutput($func_name, $arg = array()){
    
--------------

    /**
     * 抓取方法输出
     * @param string $emthod_name 方法名
     * @param object $obj 对象
     * @param array $arg 方法参数数组
     * @return string 方法执行后的输出内容
     */
    public function fetchMethodOutput($emthod_name, $obj, $arg = array()){
    
--------------

    /**
     * 断言
     * @param bool $result 断言表达式 如 $a != 2
     * @param string $message 用例名称
     * @param mixed $testData 测试数据 一般是 $a
     * @return bool 断言成功或失败
     */
    public function assert($result , $message = '' , $testData = null){
