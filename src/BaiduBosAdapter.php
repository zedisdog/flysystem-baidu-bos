<?php
declare(strict_types=1);
/**
 * Created by PhpStorm.
 * User: zed
 * Date: 17-10-20
 * Time: 下午5:53
 */
namespace Dezsidog\BaiduBos;

use BaiduBce\Exception\BceServiceException;
use BaiduBce\Services\Bos\BosClient;
use League\Flysystem\Adapter\AbstractAdapter;
use League\Flysystem\Config;

class BaiduBosAdapter extends AbstractAdapter
{
    /**
     * @var BosClient
     */
    protected $client;

    /**
     * @var string
     */
    protected $bucket;

    /**
     * 带协议头的域名(不要最后的/)
     * @var string
     */
    protected $domain;

    /**
     * uri的前缀,比如前缀是 'upload',则最后的结果时http://www.xxx.com/upload/xxx.jpg
     * @var string
     */
    protected $uri_prefix;

    public function __construct(BosClient $client, string $bucket, string $domain, string $uri_prefix = '')
    {
        $this->client = $client;
        $this->bucket = $bucket;
        $this->domain = $domain;
        if (substr($uri_prefix,0,1) == '/') {
            $uri_prefix = substr($uri_prefix,1);
        }
        $this->uri_prefix = substr($uri_prefix,-1) == '/' ?: $uri_prefix.'/';
    }

    /**
     * Write a new file.
     *
     * @param string $path
     * @param string $contents
     * @param Config $config Config object
     *
     * @return array|false false on failure file meta data on success
     */
    public function write($path, $contents, Config $config)
    {
        $options = $config->get('options',[]);
        return $this->client->putObjectFromString($this->bucket,$path,$contents,$options);
    }

    /**
     * Write a new file using a stream.
     *
     * @param string $path
     * @param resource $resource
     * @param Config $config Config object
     *
     * @return array|false false on failure file meta data on success
     */
    public function writeStream($path, $resource, Config $config)
    {
        $options = $config->get('options',[]);
        return $this->client->putObjectFromString($this->bucket,$path,$resource,$options);
    }

    /**
     * Update a file.
     *
     * @param string $path
     * @param string $contents
     * @param Config $config Config object
     *
     * @return array|false false on failure file meta data on success
     */
    public function update($path, $contents, Config $config)
    {
        $options = $config->get('options',[]);
        return $this->client->putObjectFromString($this->bucket,$path,$contents,$options);
    }

    /**
     * Update a file using a stream.
     *
     * @param string $path
     * @param resource $resource
     * @param Config $config Config object
     *
     * @return array|false false on failure file meta data on success
     */
    public function updateStream($path, $resource, Config $config)
    {
        $options = $config->get('options',[]);
        return $this->client->putObjectFromString($this->bucket,$path,$resource,$options);
    }

    /**
     * Rename a file.
     *
     * @param string $path
     * @param string $newpath
     *
     * @return bool
     */
    public function rename($path, $newpath)
    {
        // TODO: Implement rename() method.
    }

    /**
     * Copy a file.
     *
     * @param string $path
     * @param string $newpath
     *
     * @return bool
     */
    public function copy($path, $newpath)
    {
        // TODO: Implement copy() method.
    }

    /**
     * Delete a file.
     *
     * @param string $path
     *
     * @return bool
     */
    public function delete($path)
    {
        return (bool)$this->client->deleteObject($this->bucket,$path);
    }

    /**
     * Delete a directory.
     *
     * @param string $dirname
     *
     * @return bool
     */
    public function deleteDir($dirname)
    {
        // TODO: Implement deleteDir() method.
    }

    /**
     * Create a directory.
     *
     * @param string $dirname directory name
     * @param Config $config
     *
     * @return array|false
     */
    public function createDir($dirname, Config $config)
    {
        // TODO: Implement createDir() method.
    }

    /**
     * Set the visibility for a file.
     *
     * @param string $path
     * @param string $visibility
     *
     * @return array|false file meta data
     */
    public function setVisibility($path, $visibility)
    {
        // TODO: Implement setVisibility() method.
    }

    /**
     * Check whether a file exists.
     *
     * @param string $path
     *
     * @return array|bool|null
     */
    public function has($path)
    {
        try{
            $this->getMetadata($path);
        }catch (BceServiceException $e){
            if ($e->getStatusCode() == 404) {
                return false;
            }else{
                throw $e;
            }
        }

        return true;
    }

    /**
     * Read a file.
     *
     * @param string $path
     *
     * @return array|false
     */
    public function read($path)
    {
        try {
            return ['contents' => $this->client->getObjectAsString($this->bucket, $path)];
        } catch (BceServiceException $e) {
            if ($e->getStatusCode() == 404) {
                return false;
            }else{
                throw $e;
            }
        }
    }

    /**
     * Read a file as a stream.
     *
     * @param string $path
     *
     * @return array|false
     */
    public function readStream($path)
    {
        $outPutStream = fopen('php://memory','r+');
        $this->client->getObject($this->bucket,$path,$outPutStream);
        rewind($outPutStream);
        return ['stream' => $outPutStream];
    }

    /**
     * List contents of a directory.
     *
     * @param string $directory
     * @param bool $recursive
     *
     * @return array
     */
    public function listContents($directory = '', $recursive = false)
    {
        $object = $this->client->listObjects($this->bucket);
        return json_decode(json_encode($object),true)['contents'];
    }

    /**
     * Get all the meta data of a file or directory.
     *
     * @param string $path
     *
     * @return array|false
     */
    public function getMetadata($path)
    {
        return $this->client->getObjectMetadata($this->bucket,$path);
    }

    /**
     * Get the size of a file.
     *
     * @param string $path
     *
     * @return array|false
     */
    public function getSize($path)
    {
        // TODO: Implement getSize() method.
    }

    /**
     * Get the mimetype of a file.
     *
     * @param string $path
     *
     * @return array|false
     */
    public function getMimetype($path)
    {
        // TODO: Implement getMimetype() method.
    }

    /**
     * Get the timestamp of a file.
     *
     * @param string $path
     *
     * @return array|false
     */
    public function getTimestamp($path)
    {
        // TODO: Implement getTimestamp() method.
    }

    /**
     * Get the visibility of a file.
     *
     * @param string $path
     *
     * @return array|false
     */
    public function getVisibility($path)
    {
        // TODO: Implement getVisibility() method.
    }

    public function getClient()
    {
        return $this->client;
    }

    public function getBucket()
    {
        return $this->bucket;
    }

    /**
     * 获取url
     * 访问方式为cdn域名+key，如：
     * http://amiedu.cdn.bcebos.com/jpg_dyp_app-img-banner.jpg
     * @param string $path
     * @return string
     */
    public function getUrl(string $path): string
    {
        if (!$this->domain) {
            return '';
        }
        $url = $this->domain;

        if (substr($url,-1) != '/') {
            $url .= '/';
        }

        if ($this->uri_prefix) {
            $url .= $this->uri_prefix;
        }

        if (substr($path,0,1) == '/') {
            $path = substr($path,1);
        }

        return $url.$path;
    }
}