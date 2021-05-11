<?php
namespace App\Test\TestCase\Model\Table;

use App\Model\Table\UsersTable;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Table\UsersTable Test Case
 */
class UsersTableTest extends TestCase
{

    /**
     * Fixtures
     *
     * @var array
     */
    public $fixtures = [
        'app.users',
        //'app.registrations',
        //'app.uploaded_files',
        //'app.groups',
        //'app.groups_users',
        'app.rights',
        'app.rights_users',
        'app.tags',
        'app.courses',
        'app.projects',
        //'app.custom_fields',
        //'app.scales',
        //'app.courses_registrations',
        //'app.tags_courses',
        'app.tags_users'
    ];

    /**
     * setUp method
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();
        $config = TableRegistry::exists('Users') ? [] : ['className' => 'App\Model\Table\UsersTable'];
        $this->Users = TableRegistry::get('Users', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    public function tearDown()
    {
        unset($this->Users);

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

    public function testAutoContainTags()
    {
        $u = $this->Users->get(1); //no 'contain'
        $this->assertNotNull($u->tags);
    }

    public function testGetTagValue()
    {
        $u = $this->Users->get(1);
        $this->assertEquals('xyz', $u->getTagValue('test_tag_1'));
        $this->assertEquals('xyz', $u->getTagValue('test_tag_1', 'should_be_ignored'));
        $this->assertNull($u->getTagValue('nonexistent'));
        $this->assertEquals('default_test', $u->getTagValue('nonexistent', 'default_test'));
    }

    public function testHasTag()
    {
        $u = $this->Users->get(1);
        $this->assertTrue($u->hasTag('test_tag_1'));
        $this->assertFalse($u->hasTag('nonexistent'));
    }

    public function testAddTag()
    {
        $u = $this->Users->get(1);

        //normal add tag
        $u->addTag('t2', 'yyy');
        $this->assertTrue($u->hasTag('t2'));
        $this->assertEquals('yyy', $u->getTagValue('t2'));

        //no default value
        $u->addTag('t3');
        $this->assertTrue($u->hasTag('t3'));
        $this->assertEquals('default_test', $u->getTagValue('t3', 'default_test'));

        //change value for already existing
        $u->addTag('t2', 'zzz');
        $this->assertEquals('zzz', $u->getTagValue('t2'));
    }

    public function testAddTagNewEntity()
    {
        $u = $this->Users->newEntity();

        $u->addTag('t2', 'yyy');
        $this->assertTrue($u->hasTag('t2'));
        $this->assertEquals('yyy', $u->getTagValue('t2'));
    }

    public function testSetTagValue()
    {
        $u = $this->Users->get(1);

        $u->setTagValue('test_tag_1', 'ppp');
        $this->assertEquals('ppp', $u->getTagValue('test_tag_1'));

        $u->setTagValue('test_tag_1', null);
        $this->assertEquals('default_test', $u->getTagValue('test_tag_1', 'default_test'));
    }

    public function testDeleteTag()
    {
        $u = $this->Users->get(1);

        $u->removeTag('test_tag_1');
        $this->assertFalse($u->hasTag('test_tag_1'));
    }

    public function testReusingTagValues()
    {
        //test that adding a tag that did already exists sometime ago does not create a new tag.
        $u = $this->Users->get(1);

        $u->addTag('x1');
        $this->Users->save($u);
        $u->removeTag('x1');
        $this->Users->save($u);
        $u->addTag('x1');
        $this->Users->save($u);

        //no asserts here, the DB checks the uniqueness.
    }

    /**
     * Test hasRight method
     *
     * @return void
     */
    public function testHasRight()
    {
        $this->assertTrue($this->Users->get(1, ['contain' => 'Rights'])->hasRight('ADMIN'));
        $this->assertFalse($this->Users->get(1, ['contain' => 'Rights'])->hasRight('NONEXISTENT'));

        //without contain:
        $this->assertTrue($this->Users->get(1)->hasRight('ADMIN'));
        $this->assertFalse($this->Users->get(1)->hasRight('NONEXISTENT'));
    }

    /**
     * Test beforeFind method
     *
     * @return void
     */
    public function testBeforeFind()
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test beforeSave method
     *
     * @return void
     */
    public function testBeforeSave()
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test afterSave method
     *
     * @return void
     */
    public function testAfterSave()
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test log method
     *
     * @return void
     */
    public function testLog()
    {
        $this->markTestIncomplete('Not implemented yet.');
    }
}
