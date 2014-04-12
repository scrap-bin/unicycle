<?php

namespace unit\R2\ORM;

use R2\ORM\EntityManager;
use R2\DBAL\PDOMySQL;

class EntityManagerTest extends \PHPUnit_Framework_TestCase
{
    /** @var \R2\DBAL\DBALInterface */
    protected static $dbh;

    public static function setUpBeforeClass()
    {
        \R2\Command\DBALCommand::dropSchema(['env' => 'test'], []);
        \R2\Command\DBALCommand::createSchema(['env' => 'test'], []);
        $resource = __DIR__.'/../../../../app/config/parameters/test.yml';
        $loader = new \R2\Config\YamlFileLoader();
        $dbParams = $loader->load($resource)['parameters']['db_params'];
        self::$dbh = new PDOMySQL($dbParams);
    }

    /**
     * @var \R2\ORM\EntityManager
     */
    protected $em;

    protected function setUp()
    {
        $this->em = new EntityManager(self::$dbh);
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
        $o = new \R2\Model\User();
        $o->username = $randomName;
        $o->email = $randomName.'@example.com';
        $o->realname = $randomName;
        $o->created = $o->undated = $time;
        $this->em->persist($o);
    }

    protected function getNumRows()
    {
        return self::$dbh->query("SELECT COUNT(*) FROM `:p_users`")->result();
    }

    /**
     * @covers R2\ORM\EntityManager::getConnection
     */
    public function testGetConnection()
    {
        $this->assertSame(self::$dbh, $this->em->getConnection());
    }

    /**
     * @covers R2\ORM\EntityManager::getRepository
     */
    public function testGetRepository()
    {
        $o = new \R2\Model\User();
        $repo = $this->em->getRepository($o);
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
        $this->em->rollback();
        $this->em->beginTransaction();
        $this->assertEquals($num, $this->getNumRows());

        $this->insertRow();
        $this->em->commit();
        $this->em->beginTransaction();
        $this->assertEquals($num + 1, $this->getNumRows());
    }

    /**
     * @covers R2\ORM\EntityManager::getMeta
     */
    public function testGetMeta()
    {
        $meta = $this->em->getMeta('R2\\Model\\User');
        // Remove the following lines when you implement this test.
        $this->assertEquals('users', $meta['table']);
    }

    /**
     * @covers R2\ORM\EntityManager::getFieldByColumn
     */
    public function testGetFieldByColumn()
    {
        $o = new \R2\Model\User();
        $this->assertEquals('username', $this->em->getFieldByColumn($o, 'username'));
    }

    /**
     * @covers R2\ORM\EntityManager::getColumnByField
     */
    public function testGetColumnByField()
    {
        $o = new \R2\Model\User();
        $this->assertEquals('username', $this->em->getColumnByField($o, 'username'));
    }

    /**
     * @covers R2\ORM\EntityManager::__call
     */
    public function testCall()
    {
        // EntityManager gets shortcut to find* methods of repository
        $o = $this->em->find('R2\\Model\\User', 1);
        $this->assertEquals('guest', $o->username);
    }

    /**
     * @covers R2\ORM\EntityManager::nativeQuery
     * @covers R2\ORM\EntityManager::get
     */
    public function testNativeQuery()
    {
        $o = $this->em->nativeQuery("SELECT * FROM `:p_users` WHERE `id`=1")
            ->get('R2\\Model\\User');
        $this->assertEquals('guest', $o->username);
    }

    /**
     * @covers R2\ORM\EntityManager::getAll
     * @todo   Implement testGetAll().
     */
    public function testGetAll()
    {
        $iter = $this->em->nativeQuery("SELECT * FROM `:p_users` WHERE `id`<=3")
            ->getAll('R2\\Model\\User');
        $this->assertEquals(3, $iter->count());
    }
}
