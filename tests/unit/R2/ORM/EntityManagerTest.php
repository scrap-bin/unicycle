<?php

namespace unit\R2\ORM;

use R2\Application\Container;
use R2\Config\YamlFileLoader;
use R2\Application\Command\DBALCommand;

class EntityManagerTest extends \PHPUnit_Framework_TestCase
{
    /** @var string */
    protected static $entityClass;
    /** @var R2\ORM\EntityManagerInterface */
    protected static $em;

    public static function setUpBeforeClass()
    {
        $config = __DIR__.'/../../../../app/config/config.yml';
        $container = new Container(new YamlFileLoader(), $config, 'test');
        (new DBALCommand())
            ->setContainer($container)
            ->dropSchema()
            ->createSchema()
            ->loadFixtures();
        self::$em = $container->get('entity_manager');
        self::$entityClass = 'R2\\Model\\User';
    }

    protected function insertRow()
    {
        $chars = 'abcdefghijklmnopqrstuvwxyz0123456789';
        $charsLength = strlen($chars);
        $nameLength = mt_rand(6, 9);
        $randomName = '_';
        for ($i = 1; $i < $nameLength; ++$i) {
            $randomName .= $chars[mt_rand(0, $charsLength - 1)];
        }
        $time = date('Y-m-d H:i:s');
        $class = self::$entityClass;
        $o = new $class();
        $o->username = $randomName;
        $o->email = $randomName.'@example.com';
        $o->realname = $randomName;
        $o->created = $o->undated = $time;
        // EntityManager magically gets shortcut to persist methods of repository
        self::$em->persist($o);
    }

    protected function getNumRows()
    {
        // EntityManager magically gets shortcut to result and fetch* methods of dbal
        return self::$em->nativeQuery("SELECT COUNT(*) FROM `:p_users`")->result();
    }

    /**
     * @covers R2\ORM\EntityManager::getConnection
     */
    public function testGetConnection()
    {
        $this->assertInstanceOf('R2\\DBAL\\DBALInterface', self::$em->getConnection());
    }

    /**
     * @covers R2\ORM\EntityManager::getRepository
     */
    public function testGetRepository()
    {
        $o = new \R2\Model\User();
        $repo = self::$em->getRepository($o);
        $this->assertEquals('R2\\ORM\\EntityRepository', get_class($repo));
    }

    /**
     * @covers R2\ORM\EntityManager::beginTransaction
     * @covers R2\ORM\EntityManager::commit
     * @covers R2\ORM\EntityManager::rollback
     */
    public function testTransactions()
    {
        $num = $this->getNumRows();

        $this->insertRow();
        self::$em->rollback();
        self::$em->beginTransaction();
        $this->assertEquals($num, $this->getNumRows());

        $this->insertRow();
        self::$em->commit();
        self::$em->beginTransaction();
        $this->assertEquals($num + 1, $this->getNumRows());
    }

    /**
     * @covers R2\ORM\EntityManager::getMeta
     */
    public function testGetMeta()
    {
        $meta = self::$em->getMeta(self::$entityClass);
        // Remove the following lines when you implement this test.
        $this->assertEquals('users', $meta['table']);
    }

    /**
     * @covers R2\ORM\EntityManager::__call
     */
    public function testCall()
    {
        // EntityManager magically gets shortcut to find* methods of repository
        $o = self::$em->find(self::$entityClass, 1);
        $this->assertEquals('guest', $o->username);
    }

    /**
     * @covers R2\ORM\EntityManager::nativeQuery
     * @covers R2\ORM\EntityManager::get
     */
    public function testNativeQuery()
    {
        $o = self::$em->nativeQuery("SELECT * FROM `:p_users` WHERE `id`=1")
            ->get(self::$entityClass);
        $this->assertEquals('guest', $o->username);
    }

    /**
     * @covers R2\ORM\EntityManager::getAll
     * @todo   Implement testGetAll().
     */
    public function testGetAll()
    {
        $iter = self::$em->nativeQuery("SELECT * FROM `:p_users` WHERE `id`<=3")
            ->getAll(self::$entityClass);
        $this->assertEquals(3, $iter->count());
    }
}
