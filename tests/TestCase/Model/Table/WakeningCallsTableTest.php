<?php
namespace App\Test\TestCase\Model\Table;

use App\Model\Table\WakeningCallsTable;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Table\WakeningCallsTable Test Case
 */
class WakeningCallsTableTest extends TestCase
{

    /**
     * Test subject
     *
     * @var \App\Model\Table\WakeningCallsTable
     */
    public $WakeningCalls;

    /**
     * Fixtures
     *
     * @var array
     */
    public $fixtures = [
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
        $config = TableRegistry::exists('WakeningCalls') ? [] : ['className' => WakeningCallsTable::class];
        $this->WakeningCalls = TableRegistry::get('WakeningCalls', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    public function tearDown()
    {
        unset($this->WakeningCalls);

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
}
