<?php
namespace App\Test\TestCase\Model\Table;

use App\Model\Table\WakeningCallSubscribersTable;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Table\WakeningCallSubscribersTable Test Case
 */
class WakeningCallSubscribersTableTest extends TestCase
{

    /**
     * Test subject
     *
     * @var \App\Model\Table\WakeningCallSubscribersTable
     */
    public $WakeningCallSubscribers;

    /**
     * Fixtures
     *
     * @var array
     */
    public $fixtures = [
        'app.wakening_call_subscribers',
        'app.wakening_calls'
    ];

    /**
     * setUp method
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();
        $config = TableRegistry::exists('WakeningCallSubscribers') ? [] : ['className' => WakeningCallSubscribersTable::class];
        $this->WakeningCallSubscribers = TableRegistry::get('WakeningCallSubscribers', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    public function tearDown()
    {
        unset($this->WakeningCallSubscribers);

        parent::tearDown();
    }

    /**
     * Test initialize method
     *
     * @return void
     */
    public function testInitialize()
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test validationDefault method
     *
     * @return void
     */
    public function testValidationDefault()
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test buildRules method
     *
     * @return void
     */
    public function testBuildRules()
    {
        $this->markTestIncomplete('Not implemented yet.');
    }
}
