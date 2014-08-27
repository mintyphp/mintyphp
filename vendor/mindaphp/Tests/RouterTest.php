<?php
namespace MindaPHP\Tests;

use MindaPHP\Router;

class RouterTest extends \PHPUnit_Framework_TestCase
{
	protected $path      = false;
	protected $pages     = false;
	protected $templates = false;
	
	protected function setUp()
	{	
		$this->path = sys_get_temp_dir().'/mindaphp_test';
		
		Router::$baseUrl      = '/';
		Router::$pageRoot     = $this->path.'/pages/';
		Router::$templateRoot = $this->path.'/templates/';
		
		$this->pages = array(
			'admin/posts/index().php',
			'admin/posts/index(admin).phtml',
			'admin/posts/view($id).php',
			'admin/posts/view(admin).phtml',
			'admin/index().php',
			'admin/index(admin).phtml',
			'admin/login().php',
			'admin/login(login).phtml',
			'error/forbidden(error).phtml',
			'error/method_not_allowed(error).phtml',
			'error/not_found(error).phtml',
			'home().php',
			'home(default).phtml',
			'index($slug).php',
			'index(default).phtml',
		);
		$this->templates = array(
			'admin.php',
			'admin.phtml',
			'default.phtml',
			'error.phtml',
			'login.phtml',
		);
		
		foreach ($this->pages as $file) {
			$path = Router::$pageRoot.$file;
			if (!file_exists(dirname($path))) {
				mkdir(dirname($path), 0755, true);
			}
			file_put_contents($path, '');
		}
		foreach ($this->templates as $file) {
			$path = Router::$templateRoot.$file;
			if (!file_exists(dirname($path))) {
				mkdir(dirname($path), 0755, true);
			}
			file_put_contents($path, '');
		}
		
		$_SERVER['SCRIPT_NAME'] = $this->path.'/web/index.php';
	}
	
	protected function request($method, $uri)
	{
		$_SERVER['REQUEST_METHOD'] = $method;
		$_SERVER['REQUEST_URI'] = $uri;
		Router::$initialized = false;
	}
	
	public function testAdmin()
	{
		$this->request('GET','/admin');
		$this->assertEquals(Router::$templateRoot.'admin.php', Router::getTemplateAction());
		$this->assertEquals(Router::$templateRoot.'admin.phtml', Router::getTemplateView());
		$this->assertEquals(Router::$pageRoot.'admin/index().php', Router::getAction());
		$this->assertEquals(Router::$pageRoot.'admin/index(admin).phtml', Router::getView());
	}

	public function testRootRoute()
	{
		$this->request('GET','/');
		Router::addRoute('','home');
		$this->assertEquals(Router::$pageRoot.'home().php', Router::getAction());
		$this->assertEquals(Router::$pageRoot.'home(default).phtml', Router::getView());
	}
	
	public function testPageNotFoundOnNoIndex()
	{
		$this->request('GET','/error/this-page-does-not-exist');
		$this->assertEquals(false, Router::getTemplateAction());
		$this->assertEquals(Router::$templateRoot.'error.phtml', Router::getTemplateView());
		$this->assertEquals(false, Router::getAction());
		$this->assertEquals(Router::$pageRoot.'error/not_found(error).phtml', Router::getView());
	}
	
	public function testRootParameters()
	{	
		$this->request('GET','/2014-some-blog-title');
		$this->assertEquals(array('slug'=>'2014-some-blog-title'), Router::getParameters());
	}

	protected function tearDown()
	{
		system('rm -Rf '.$this->path);
	}
	
}