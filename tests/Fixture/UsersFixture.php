<?php
namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * UsersFixture
 *
 */
class UsersFixture extends TestFixture
{

    /**
     * Fields
     *
     * @var array
     */
    // @codingStandardsIgnoreStart
    public $fields = [
        'id' => ['type' => 'integer', 'length' => 10, 'unsigned' => false, 'null' => false, 'default' => null, 'comment' => '', 'autoIncrement' => true, 'precision' => null],
        'username' => ['type' => 'string', 'length' => 30, 'null' => false, 'default' => null, 'comment' => '', 'precision' => null, 'fixed' => null],
        'email' => ['type' => 'string', 'length' => 100, 'null' => false, 'default' => null, 'comment' => '', 'precision' => null, 'fixed' => null],
        'password' => ['type' => 'string', 'fixed' => true, 'length' => 60, 'null' => true, 'default' => null, 'comment' => '', 'precision' => null],
        'first_name' => ['type' => 'string', 'length' => 30, 'null' => false, 'default' => null, 'comment' => '', 'precision' => null, 'fixed' => null],
        'last_name' => ['type' => 'string', 'length' => 100, 'null' => false, 'default' => null, 'comment' => '', 'precision' => null, 'fixed' => null],
        'sex' => ['type' => 'string', 'fixed' => true, 'length' => 1, 'null' => false, 'default' => null, 'comment' => '', 'precision' => null],
        'street' => ['type' => 'string', 'length' => 50, 'null' => false, 'default' => null, 'comment' => '', 'precision' => null, 'fixed' => null],
        'house_number' => ['type' => 'string', 'length' => 5, 'null' => false, 'default' => null, 'comment' => '', 'precision' => null, 'fixed' => null],
        'postal_code' => ['type' => 'string', 'length' => 5, 'null' => false, 'default' => null, 'comment' => '', 'precision' => null, 'fixed' => null],
        'city' => ['type' => 'string', 'length' => 30, 'null' => false, 'default' => null, 'comment' => '', 'precision' => null, 'fixed' => null],
        'mail_verified' => ['type' => 'boolean', 'length' => null, 'null' => false, 'default' => null, 'comment' => '', 'precision' => null],
        'actcode' => ['type' => 'string', 'fixed' => true, 'length' => 32, 'null' => true, 'default' => null, 'comment' => '', 'precision' => null],
        'birthday' => ['type' => 'date', 'length' => null, 'null' => false, 'default' => null, 'comment' => '', 'precision' => null],
        'created' => ['type' => 'datetime', 'length' => null, 'null' => true, 'default' => null, 'comment' => '', 'precision' => null],
        'modified' => ['type' => 'datetime', 'length' => null, 'null' => true, 'default' => null, 'comment' => '', 'precision' => null],
        'last_login' => ['type' => 'datetime', 'length' => null, 'null' => false, 'default' => null, 'comment' => '', 'precision' => null],
        '_constraints' => [
            'primary' => ['type' => 'primary', 'columns' => ['id'], 'length' => []],
            'username' => ['type' => 'unique', 'columns' => ['username'], 'length' => []],
            'actcode' => ['type' => 'unique', 'columns' => ['actcode'], 'length' => []],
        ],
        '_options' => [
            'engine' => 'InnoDB',
            'collation' => 'utf8_general_ci'
        ],
    ];
    // @codingStandardsIgnoreEnd

    /**
     * Records
     *
     * @var array
     */
    public $records = [
        [
            'id' => 1,
            'username' => 'user1',
            'email' => 'user1@test.de',
            'password' => 'xxx',
            'first_name' => 'User',
            'last_name' => 'One',
            'sex' => 'm',
            'street' => 'Teststr.',
            'house_number' => '1',
            'postal_code' => '12345',
            'city' => 'Testcity',
            'mail_verified' => 1,
            'actcode' => 'f561aaf6ef0bf14d4208bb46a4ccb3ad',
            'birthday' => '2015-11-03',
            'created' => '2015-11-03 13:53:13',
            'modified' => '2015-11-03 13:53:13',
            'last_login' => '2015-11-03 13:53:13'
        ],
    ];
}
