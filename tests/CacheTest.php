<?php
/**
 * This file is part of WebStone\Cache.
 *
 * (C) 2009-2024 Maxim Kirichenko <kirichenko.maxim@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace WebStone\Cache\Tests;

use PHPUnit\Framework\TestCase;
use WebStone\Cache\Cache;
use WebStone\Cache\IO\File;
use WebStone\Cache\IO\Redis;
use WebStone\Stdlib\Classes\AutoInitialized;

class CacheTest extends TestCase
{
    private ?Cache $cacheFile = null;
    private ?Cache $cacheRedis = null;

    protected function setUp(): void
    {
        $this->cacheFile = new Cache([
            'driver' => [
                'class' => File::class,
                'lifetime' => 60,
                'path' => dirname(__DIR__, 1) . '/runtime/files/'
            ],
            'enabled' => true
        ]);

        $this->cacheRedis = new Cache([
            'driver' => [
                'class' => Redis::class,
                'lifetime' => 60,
                'host' => 'localhost',
                'port' => 6379,
                'db' => 1,
                'namespace' => 'test_cache'
            ],
            'enabled' => true
        ]);
        
    }

    public function testConstructor()
    {
        $this->assertInstanceOf(File::class, $this->cacheFile->getDriver());
        $this->assertInstanceOf(Redis::class, $this->cacheRedis->getDriver());
    }

    public function testSave()
    {
        $id = 'test_id';
        $data = 'test_data';

        $this->assertTrue($this->cacheFile->save($id, $data));
        $this->assertTrue($this->cacheRedis->save($id, $data));
    }

    public function testGet()
    {
        $id = 'test_id';
        $this->assertEquals('test_data', $this->cacheFile->get($id));
        $this->assertEquals('test_data', $this->cacheRedis->get($id));
    }

    public function testGetMetadata()
    {
        $id = 'test_id';
        $metadata = $this->cacheFile->getMetadata($id);
        $this->assertArrayHasKey('expire', $metadata);
        $this->assertArrayHasKey('time', $metadata);

        $metadata = $this->cacheRedis->getMetadata($id);
        $this->assertArrayHasKey('expire', $metadata);
        $this->assertArrayHasKey('time', $metadata);
    }

    public function testDelete()
    {
        $id = 'test_id';
        $this->assertTrue($this->cacheFile->delete($id));
        $this->assertTrue($this->cacheRedis->delete($id));
    }

    public function testSaveObject()
    {
        for ($i = 0; $i < 10; $i++) {
            $this->assertTrue($this->cacheFile->save("my_task_$i", new MyTask(['id' => $i, 'message' => 'test'])));
            $this->assertTrue($this->cacheRedis->save("my_task_$i", new MyTask(['id' => $i, 'message' => 'test redis'])));
        }
    }

    public function testGetObject()
    {
        $id = 'my_task_9';
        $this->assertInstanceOf('WebStone\\Cache\\Tests\\MyTask', $task = $this->cacheFile->get($id));
        $this->assertEquals(9, $task->id);
        $this->assertEquals('test', $task->message);

        $this->assertInstanceOf('WebStone\\Cache\\Tests\\MyTask', $task = $this->cacheRedis->get($id));
        $this->assertEquals(9, $task->id);
        $this->assertEquals('test redis', $task->message);
    }

    public function testClean()
    {
        $this->assertTrue($this->cacheFile->clean());
        $this->assertTrue($this->cacheRedis->clean());
    }

    public function testGetEnabled()
    {
        $this->assertTrue($this->cacheFile->getEnabled());
        $this->assertTrue($this->cacheRedis->getEnabled());
    }

    public function testSetEnabled()
    {
        $this->cacheFile->setEnabled(false);
        $this->assertFalse($this->cacheFile->getEnabled());

        $this->cacheRedis->setEnabled(false);
        $this->assertFalse($this->cacheFile->getEnabled());        
    }
}

class MyTask extends AutoInitialized
{
    public function run()
    {
        /** some action */
    }
}
