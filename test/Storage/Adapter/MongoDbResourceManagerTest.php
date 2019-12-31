<?php

/**
 * @see       https://github.com/laminas/laminas-cache for the canonical source repository
 * @copyright https://github.com/laminas/laminas-cache/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-cache/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\Cache\Storage\Adapter;

use Laminas\Cache\Exception;
use Laminas\Cache\Storage\Adapter\MongoDbResourceManager;

/**
 * @group      Laminas_Cache
 */
class MongoDbResourceManagerTest extends \PHPUnit_Framework_TestCase
{
    protected $object;

    public function setUp()
    {
        if (!defined('TESTS_LAMINAS_CACHE_MONGODB_ENABLED') || !TESTS_LAMINAS_CACHE_MONGODB_ENABLED) {
            $this->markTestSkipped("Skipped by TestConfiguration (TESTS_LAMINAS_CACHE_MONGODB_ENABLED)");
        }

        if (!extension_loaded('mongo') || !class_exists('\Mongo') || !class_exists('\MongoClient')) {
            // Allow tests to run if Mongo extension is loaded, or we have a polyfill in place
            $this->markTestSkipped("Mongo extension is not loaded");
        }

        $this->object = new MongoDbResourceManager();
    }

    public function testSetResourceAlreadyCreated()
    {
        $this->assertAttributeEmpty('resources', $this->object);

        $id = 'foo';

        $clientClass = (version_compare(phpversion('mongo'), '1.3.0', '<')) ? '\Mongo' : '\MongoClient';
        $client = new $clientClass(TESTS_LAMINAS_CACHE_MONGODB_CONNECTSTRING);
        $resource = $client->selectCollection(TESTS_LAMINAS_CACHE_MONGODB_DATABASE, TESTS_LAMINAS_CACHE_MONGODB_COLLECTION);

        $this->object->setResource($id, $resource);

        $this->assertSame($resource, $this->object->getResource($id));
    }

    public function testSetResourceArray()
    {
        $this->assertAttributeEmpty('resources', $this->object);

        $id     = 'foo';
        $server = 'mongodb://test:1234';

        $this->object->setResource($id, array('server' => $server));

        $this->assertSame($server, $this->object->getServer($id));
    }

    public function testSetResourceThrowsException()
    {
        $id = 'foo';
        $resource = new \stdClass();

        $this->setExpectedException('Laminas\Cache\Exception\InvalidArgumentException');
        $this->object->setResource($id, $resource);
    }

    public function testHasResourceEmpty()
    {
        $id = 'foo';

        $this->assertFalse($this->object->hasResource($id));
    }

    public function testHasResourceSet()
    {
        $id = 'foo';

        $this->object->setResource($id, array('foo' => 'bar'));

        $this->assertTrue($this->object->hasResource($id));
    }

    public function testGetResourceNotSet()
    {
        $id = 'foo';

        $this->assertFalse($this->object->hasResource($id));

        $this->setExpectedException('Laminas\Cache\Exception\RuntimeException');
        $this->object->getResource($id);
    }

    public function testGetResourceInitialized()
    {
        $id = 'foo';

        $clientClass = (version_compare(phpversion('mongo'), '1.3.0', '<')) ? '\Mongo' : '\MongoClient';
        $client = new $clientClass(TESTS_LAMINAS_CACHE_MONGODB_CONNECTSTRING);
        $resource = $client->selectCollection(TESTS_LAMINAS_CACHE_MONGODB_DATABASE, TESTS_LAMINAS_CACHE_MONGODB_COLLECTION);

        $this->object->setResource($id, $resource);

        $this->assertSame($resource, $this->object->getResource($id));
    }

    public function testGetResourceNewResource()
    {
        $id                = 'foo';
        $server            = TESTS_LAMINAS_CACHE_MONGODB_CONNECTSTRING;
        $connectionOptions = array('connectTimeoutMS' => 5);
        $database          = TESTS_LAMINAS_CACHE_MONGODB_DATABASE;
        $collection        = TESTS_LAMINAS_CACHE_MONGODB_COLLECTION;

        $this->object->setServer($id, $server);
        $this->object->setConnectionOptions($id, $connectionOptions);
        $this->object->setDatabase($id, $database);
        $this->object->setCollection($id, $collection);

        $this->assertInstanceOf('\MongoCollection', $this->object->getResource($id));
    }

    public function testGetResourceUnknownServerThrowsException()
    {
        $id                = 'foo';
        $server            = 'mongodb://unknown.unknown';
        $connectionOptions = array('connectTimeoutMS' => 5);
        $database          = TESTS_LAMINAS_CACHE_MONGODB_DATABASE;
        $collection        = TESTS_LAMINAS_CACHE_MONGODB_COLLECTION;

        $this->object->setServer($id, $server);
        $this->object->setConnectionOptions($id, $connectionOptions);
        $this->object->setDatabase($id, $database);
        $this->object->setCollection($id, $collection);

        $this->setExpectedException('Laminas\Cache\Exception\RuntimeException');
        $this->object->getResource($id);
    }
}