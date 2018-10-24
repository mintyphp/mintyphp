<?php
namespace MindaPHP\Tests;

use MindaPHP\Router;

class RouterTest extends \PHPUnit\Framework\TestCase
{
    protected static $path = false;
    protected static $pages = false;
    protected static $templates = false;

    public static function setUpBeforeClass()
    {
        self::$path = sys_get_temp_dir() . '/mindaphp_test';

        Router::$baseUrl = '/';
        Router::$pageRoot = self::$path . '/pages/';
        Router::$templateRoot = self::$path . '/templates/';
        Router::$executeRedirect = false;

        self::$pages = array(
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
            'rss().php',
            'home().php',
            'home(default).phtml',
            'index($slug).php',
            'index(default).phtml',
        );
        self::$templates = array(
            'admin.php',
            'admin.phtml',
            'default.phtml',
            'error.phtml',
            'login.phtml',
        );

        foreach (self::$pages as $file) {
            $path = Router::$pageRoot . $file;
            if (!file_exists(dirname($path))) {
                mkdir(dirname($path), 0755, true);
            }
            file_put_contents($path, '');
        }
        foreach (self::$templates as $file) {
            $path = Router::$templateRoot . $file;
            if (!file_exists(dirname($path))) {
                mkdir(dirname($path), 0755, true);
            }
            file_put_contents($path, '');
        }

        $_SERVER['SCRIPT_NAME'] = self::$path . '/web/index.php';
    }

    protected function request($method, $uri)
    {
        $_SERVER['REQUEST_METHOD'] = $method;
        $_SERVER['REQUEST_URI'] = $uri;
        Router::$initialized = false;
    }

    public function testAdmin()
    {
        $this->request('GET', '/admin');
        $this->assertEquals(Router::$templateRoot . 'admin.php', Router::getTemplateAction());
        $this->assertEquals(Router::$templateRoot . 'admin.phtml', Router::getTemplateView());
        $this->assertEquals(Router::$pageRoot . 'admin/index().php', Router::getAction());
        $this->assertEquals(Router::$pageRoot . 'admin/index(admin).phtml', Router::getView());
    }

    public function testRootRoute()
    {
        $this->request('GET', '/');
        Router::addRoute('', 'home');
        $this->assertEquals(Router::$pageRoot . 'home().php', Router::getAction());
        $this->assertEquals(Router::$pageRoot . 'home(default).phtml', Router::getView());
    }

    public function testTrailingSlashOnIndex()
    {
        $this->request('GET', '/admin/posts/');

        $this->assertEquals('/admin/posts', Router::getRedirect());
    }

    public function testExplicitIndexRedirect()
    {
        $this->request('GET', '/admin/posts/index');

        $this->assertEquals('/admin/posts', Router::getRedirect());
        $this->assertEquals(Router::$templateRoot . 'admin.php', Router::getTemplateAction());
        $this->assertEquals(Router::$templateRoot . 'admin.phtml', Router::getTemplateView());
        $this->assertEquals(Router::$pageRoot . 'admin/posts/index().php', Router::getAction());
        $this->assertEquals(Router::$pageRoot . 'admin/posts/index(admin).phtml', Router::getView());
    }

    public function testTrailingSlash()
    {
        $this->request('GET', '/admin/posts/view/12/');

        $this->assertEquals('/admin/posts/view/12', Router::getRedirect());
    }

    public function testPageNotFoundOnIndex()
    {
        $this->request('GET', '/admin/posts/asdada');

        $this->assertEquals('/admin/posts', Router::getRedirect());
    }

    public function testPageNotFoundOnNoIndex()
    {
        $this->request('GET', '/error/this-page-does-not-exist');

        $this->assertEquals(null, Router::getRedirect());
        $this->assertEquals(false, Router::getTemplateAction());
        $this->assertEquals(Router::$templateRoot . 'error.phtml', Router::getTemplateView());
        $this->assertEquals(false, Router::getAction());
        $this->assertEquals(Router::$pageRoot . 'error/not_found(error).phtml', Router::getView());
    }

    public function testRootParameters()
    {
        $this->request('GET', '/2014-some-blog-title');
        $this->assertEquals(array('slug' => '2014-some-blog-title'), Router::getParameters());
    }

    public function testActionWithoutView()
    {
        $this->request('GET', '/rss');
        $this->assertEquals(false, Router::getTemplateAction());
        $this->assertEquals(false, Router::getTemplateView());
        $this->assertEquals(Router::$pageRoot . 'rss().php', Router::getAction());
        $this->assertEquals(false, Router::getView());
    }

    public static function tearDownAfterClass()
    {
        system('rm -Rf ' . self::$path);
    }

}
