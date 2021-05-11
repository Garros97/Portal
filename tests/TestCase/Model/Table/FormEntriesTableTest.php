<?php
namespace App\Test\TestCase\Model\Table;

use App\Model\Table\FormEntriesTable;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Table\FormEntriesTable Test Case
 */
class FormEntriesTableTest extends TestCase
{

    /**
     * Test subject
     *
     * @var \App\Model\Table\FormEntriesTable
     */
    public $FormEntries;

    /**
     * Fixtures
     *
     * @var array
     */
    public $fixtures = [
        'app.form_entries',
        'app.forms'
    ];

    /**
     * setUp method
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();
        $config = TableRegistry::exists('FormEntries') ? [] : ['className' => FormEntriesTable::class];
        $this->FormEntries = TableRegistry::get('FormEntries', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    public function tearDown()
    {
        unset($this->FormEntries);

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
