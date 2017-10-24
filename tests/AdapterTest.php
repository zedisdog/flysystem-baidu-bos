<?php
/**
 * Created by PhpStorm.
 * User: dezsidog
 * Date: 17-10-21
 * Time: 下午12:45
 */

namespace Tests;


use BaiduBce\Exception\BceServiceException;
use BaiduBce\Services\Bos\BosClient;
use Dezsidog\BaiduBos\BaiduBosAdapter;
use League\Flysystem\Filesystem;
use PHPUnit\Framework\TestCase;

class AdapterTest extends TestCase
{
    /**
     * @var Filesystem
     */
    protected $filesystem;

    /**
     * @var string
     */
    protected $file = 'tests/test.file';

    protected $bos_key = 'test.file';

    protected $bucket;


    public function setUp()
    {
        global $BOS_TEST_CONFIG;
        parent::setUp();
        $client = new BosClient($BOS_TEST_CONFIG);

        $this->bucket = 'testzed'.rand(10000,99999);
        $client->createBucket($this->bucket);

        $adapter = new BaiduBosAdapter($client,$this->bucket);
        $this->filesystem = new Filesystem($adapter);
    }

    public function tearDown()
    {
        if(file_exists($this->file)){
            unlink($this->file);
        }
        /**
         * @var BosClient $client
         */
        $client = $this->filesystem->getAdapter()->getClient();
        $bucket = $this->filesystem->getAdapter()->getBucket();
        try {
            $client->getObjectMetadata($bucket,$this->bos_key);
            $client->deleteObject($bucket,$this->bos_key);
        } catch (BceServiceException $exception) {
            return;
        }

        $client->deleteBucket($this->bucket);
    }

    public function testWrite()
    {
        $this->assertTrue($this->filesystem->write($this->bos_key,'123'));
        file_put_contents($this->file,'123');
        $this->filesystem->delete($this->bos_key);
        $this->assertTrue($this->filesystem->write($this->bos_key,$this->file));
    }

    public function testRead()
    {
        $this->assertTrue($this->filesystem->write($this->bos_key,'123'));
        $this->assertEquals('123',$this->filesystem->read($this->bos_key));
        $resource = $this->filesystem->readStream($this->bos_key);
        $this->assertTrue(is_resource($resource));
        fclose($resource);
    }

    public function testHas()
    {
        $this->filesystem->write($this->bos_key,'123');
        $this->assertFalse($this->filesystem->has('test'));
        $this->assertTrue($this->filesystem->has($this->bos_key));
    }

    public function testDelete()
    {
        $this->filesystem->write($this->bos_key,'123');
        $this->assertTrue($this->filesystem->delete($this->bos_key));
    }

    public function testUpdate()
    {
        $this->assertTrue($this->filesystem->write($this->bos_key,'123'));
        $this->assertTrue($this->filesystem->update($this->bos_key,'321'));
        $this->assertEquals('321',$this->filesystem->read($this->bos_key));
    }
}