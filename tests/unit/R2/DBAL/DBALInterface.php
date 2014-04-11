<?php

namespace unit\R2\DBAL;

class DBALInterface extends \PHPUnit_Framework_TestCase
{
    /** @var \R2\DBAL\DBALInterface */
    protected static $dbh;

    public static function setUpBeforeClass()
    {
        $resource = __DIR__.'/../../../../app/config/parameters/test.yml';
        $loader = new \R2\Config\YamlFileLoader();
        $dbParams = $loader->load($resource)['parameters']['db_params'];
        // Compute class name and check if it DBALInterface
        $class = 'R2\\DBAL'.\substr(\strrchr(\get_called_class(), '\\'), 0, -4);
        self::$dbh = new $class($dbParams);
        if (!is_a(self::$dbh, 'R2\\DBAL\\DBALInterface')) {
            throw new \Exception("Class {$class} is not DBALInterface");
        }

        $schema = file_get_contents(__DIR__.'/../../../../app/install/create-schema.sql');
        $schema = array_filter(array_map('trim', explode(';', $schema)));
        foreach ($schema as $sql) {
            self::$dbh->query($sql);
        }

        $data = file_get_contents(__DIR__.'/../../../../app/install/db-fixtures.sql');
        $data = array_filter(array_map('trim', explode(';', $data)));
        foreach ($data as $sql) {
            self::$dbh->query($sql);
        }

        self::$dbh->commit();
        self::$dbh->beginTransaction();
    }

    public static function tearDownAfterClass()
    {
        self::$dbh->commit();
        self::$dbh->close();
        self::$dbh = null;
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

        return self::$dbh->query(
            "INSERT INTO `:p_users` "
            ."(`username`, `password`, `email`, `realname`, `created`, `updated`) VALUES "
            ."(:name, SHA1('password'), :email, 'New User', :time, :time)",
            [
                'name'  => $randomName,
                'email' => $randomName.'@example.com',
                'time'  => $time
            ]
        )->insertId();
    }

    protected function getNumRows()
    {
        return self::$dbh->query("SELECT COUNT(*) FROM `:p_users`")->result();
    }

    /**
     * @covers R2\DBAL\PDOMySQL::query
     */
    public function testQuery()
    {
        // Bind simple string
        $x = self::$dbh->query("SELECT :str", ['str' => 'abcde'])->result();
        $this->assertEquals('abcde', $x);
        // Do NOT bind parameter in string literal
        $num1 = self::$dbh->query("SELECT 1 FROM `:p_users` WHERE `username` = ':list'")->numRows();
        $this->assertEquals(0, $num1);
        // Bind integer parameter to LIMIT clause
        $num2 = self::$dbh->query("SELECT 1 FROM `:p_users` LIMIT :lim", ['lim' => 3])->numRows();
        $this->assertEquals(3, $num2);
        // Bind array
        $num3 = self::$dbh->query("SELECT 1 FROM `:p_users` WHERE `id` IN(:ids)", ['ids' => [1, 2, null]])->numRows();
        $this->assertEquals(2, $num3);
    }

    /**
     * @covers R2\DBAL\PDOMySQL::beginTransaction
     * @covers R2\DBAL\PDOMySQL::commit
     * @covers R2\DBAL\PDOMySQL::rollback
     */
    public function testTransactions()
    {
        $num = $this->getNumRows();

        $this->insertRow();
        self::$dbh->rollback();
        self::$dbh->beginTransaction();
        $this->assertEquals($num, $this->getNumRows());

        $this->insertRow();
        self::$dbh->commit();
        self::$dbh->beginTransaction();
        $this->assertEquals($num + 1, $this->getNumRows());
    }

    /**
     * @covers R2\DBAL\PDOMySQL::result
     */
    public function testResult()
    {
        $username = self::$dbh->query("SELECT `id`, `username` FROM `:p_users` ORDER BY `id` LIMIT 3")
                ->result(1, 1);
        $this->assertEquals('admin', $username);
    }

    /**
     * @covers R2\DBAL\PDOMySQL::fetchAssoc
     */
    public function testFetchAssoc()
    {
        $row = self::$dbh->query("SELECT `id`, `username` FROM `:p_users` ORDER BY `id` LIMIT 1")
                ->fetchAssoc();
        $this->assertEquals(['id' => 1, 'username' => 'guest'], $row);
    }

    /**
     * @covers R2\DBAL\PDOMySQL::fetchAssocAll
     */
    public function testFetchAssocAll()
    {
        $rows = self::$dbh->query("SELECT `id`, `username` FROM `:p_users` ORDER BY `id` LIMIT 3")
                ->fetchAssocAll();
        $this->assertEquals(
            [
                ['id' => 1, 'username' => 'guest'],
                ['id' => 2, 'username' => 'admin'],
                ['id' => 3, 'username' => 'artoodetoo'],
            ],
            $rows
        );
    }

    /**
     * @covers R2\DBAL\PDOMySQL::fetchRow
     */
    public function testFetchRow()
    {
        $row = self::$dbh->query("SELECT `id`, `username` FROM `:p_users` ORDER BY `id` LIMIT 1")
                ->fetchRow();
        $this->assertEquals([0 => 1, 1 => 'guest'], $row);
    }

    /**
     * @covers R2\DBAL\PDOMySQL::numRows
     */
    public function testNumRows()
    {
        $num = self::$dbh->query("SELECT 1 FROM `:p_users` WHERE `id` IN (:list)", ['list' => [1, 2, 3]])->numRows();
        $this->assertEquals(3, $num);
    }

    /**
     * @covers R2\DBAL\PDOMySQL::affectedRows
     */
    public function testAffectedRows()
    {
        $num = self::$dbh->query("UPDATE `:p_users` SET `updated`=`updated` + INTERVAL 1 SECOND WHERE `id` IN (2,3)")
                ->affectedRows();
        $this->assertEquals(2, $num);
    }

    /**
     * @covers R2\DBAL\PDOMySQL::insertId
     */
    public function testInsertId()
    {
        $previousMaxId = self::$dbh->query("SELECT MAX(`id`) FROM `:p_users`")->result();
        $id = $this->insertRow();
        $this->assertTrue($id > $previousMaxId);
    }

    /**
     * @covers R2\DBAL\PDOMySQL::freeResult
     */
    public function testFreeResult()
    {
        $rows = self::$dbh->query("SELECT `id`, `username` FROM `:p_users` ORDER BY `id` LIMIT 3")
                ->freeResult()
                ->fetchAssocAll();
        $this->assertFalse($rows);
    }

    /**
     * @covers R2\DBAL\PDOMySQL::close
     */
    public function testClose()
    {
        // NOTE: After the connection have closed, the new query will open connection again.
        // So, we have no visible effect of closing. Nevertheless, we can check private property $link.
        $reflection = new \ReflectionClass(self::$dbh);
        $property = $reflection->getProperty('link');
        $property->setAccessible(true);
        $this->assertNotEmpty($property->getValue(self::$dbh));
        self::$dbh->close();
        $this->assertEmpty($property->getValue(self::$dbh));
    }
}
