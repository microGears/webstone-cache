<?php
/**
 * This file is part of WebStone\Cache.
 *
 * (C) 2009-2024 Maxim Kirichenko <kirichenko.maxim@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace WebStone\Cache\Tests\IO;

use PHPUnit\Framework\TestCase;
use WebStone\Cache\IO\File;
use WebStone\Stdlib\Classes\AutoInitialized;


class FileTest extends TestCase
{
    private File $driver;

    protected function setUp(): void
    {
        $this->driver = new File();
        $this->driver->setPath(dirname(__DIR__, 2) . '/runtime/files/');
        $this->driver->setLifetime(10);
    }

    public function testSave()
    {
        $id = 'test_id';
        $data = 'test_data';
        $this->assertTrue($this->driver->save($id, $data));
    }

    public function testGet()
    {
        $id = 'test_id';
        $this->assertEquals('test_data', $this->driver->get($id));
    }

    public function testGetMetadata()
    {
        $id = 'test_id';
        $metadata = $this->driver->getMetadata($id);
        $this->assertArrayHasKey('expire', $metadata);
        $this->assertArrayHasKey('time', $metadata);
    }

    public function testDelete()
    {
        $id = 'test_id';
        $this->driver->save($id, 'test_data');
        $this->assertTrue($this->driver->delete('test_id'));
    }

    public function testSaveObject()
    {
        for ($i = 0; $i < 10; $i++) {
            $this->assertTrue($this->driver->save("my_task_$i", new MyTask(['id' => $i, 'message' => 'test'])));
        }
    }

    public function testGetObject()
    {
        $id = 'my_task_9';
        $this->assertInstanceOf('WebStone\\Cache\\Tests\\IO\\MyTask', $task = $this->driver->get($id));
        $this->assertEquals(9, $task->id);
        $this->assertEquals('test', $task->message);
    }

    public function testClean()
    {
        $this->assertTrue($this->driver->clean());
    }
}


class MyTask extends AutoInitialized
{
    public function run()
    {
        /** some action */
    }
}
