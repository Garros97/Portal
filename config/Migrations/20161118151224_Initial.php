<?php
use Migrations\AbstractMigration;

class Initial extends AbstractMigration
{
    public function up()
    {

        $this->table('combo_box_values')
            ->addColumn('custom_field_id', 'integer', [
                'default' => null,
                'limit' => 10,
                'null' => false,
            ])
            ->addColumn('value', 'string', [
                'default' => '',
                'limit' => 50,
                'null' => false,
            ])
            ->addIndex(
                [
                    'custom_field_id',
                ]
            )
            ->create();

        $this->table('courses')
            ->addColumn('project_id', 'integer', [
                'default' => null,
                'limit' => 10,
                'null' => false,
            ])
            ->addColumn('name', 'string', [
                'default' => null,
                'limit' => 200,
                'null' => false,
            ])
            ->addColumn('description', 'text', [
                'default' => null,
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('sort', 'string', [
                'default' => '',
                'limit' => 10,
                'null' => false,
            ])
            ->addColumn('max_users', 'integer', [
                'default' => 0,
                'limit' => 6,
                'null' => false,
            ])
            ->addColumn('waiting_list_length', 'integer', [
                'default' => 0,
                'limit' => 6,
                'null' => false,
            ])
            ->addColumn('uploads_allowed', 'boolean', [
                'default' => false,
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('uploads_start', 'datetime', [
                'default' => null,
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('uploads_end', 'datetime', [
                'default' => null,
                'limit' => null,
                'null' => false,
            ])
            ->addIndex(
                [
                    'project_id',
                ]
            )
            ->create();

        $this->table('courses_registrations', ['id' => false, 'primary_key' => ['course_id', 'registration_id']])
            ->addColumn('course_id', 'integer', [
                'default' => null,
                'limit' => 10,
                'null' => false,
            ])
            ->addColumn('registration_id', 'integer', [
                'default' => null,
                'limit' => 10,
                'null' => false,
            ])
            ->addIndex(
                [
                    'course_id',
                ]
            )
            ->addIndex(
                [
                    'registration_id',
                ]
            )
            ->create();

        $this->table('custom_fields')
            ->addColumn('project_id', 'integer', [
                'default' => null,
                'limit' => 10,
                'null' => false,
            ])
            ->addColumn('section', 'string', [
                'default' => '',
                'limit' => 20,
                'null' => false,
            ])
            ->addColumn('name', 'string', [
                'default' => null,
                'limit' => 100,
                'null' => false,
            ])
            ->addColumn('help_text', 'text', [
                'default' => null,
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('type', 'integer', [
                'default' => null,
                'limit' => 2,
                'null' => false,
            ])
            ->addColumn('backend_only', 'boolean', [
                'default' => null,
                'limit' => null,
                'null' => false,
            ])
            ->addIndex(
                [
                    'project_id',
                ]
            )
            ->create();

        $this->table('custom_fields_registrations', ['id' => false, 'primary_key' => ['custom_field_id', 'registration_id']])
            ->addColumn('custom_field_id', 'integer', [
                'default' => null,
                'limit' => 10,
                'null' => false,
            ])
            ->addColumn('registration_id', 'integer', [
                'default' => null,
                'limit' => 10,
                'null' => false,
            ])
            ->addColumn('value', 'string', [
                'default' => null,
                'limit' => 100,
                'null' => false,
            ])
            ->addIndex(
                [
                    'custom_field_id',
                ]
            )
            ->addIndex(
                [
                    'registration_id',
                ]
            )
            ->create();

        $this->table('groups')
            ->addColumn('name', 'string', [
                'default' => null,
                'limit' => 50,
                'null' => false,
            ])
            ->addColumn('project_id', 'integer', [
                'default' => null,
                'limit' => 10,
                'null' => false,
            ])
            ->addColumn('password', 'string', [
                'default' => '',
                'limit' => 20,
                'null' => false,
            ])
            ->addIndex(
                [
                    'project_id',
                ]
            )
            ->create();

        $this->table('groups_users', ['id' => false, 'primary_key' => ['group_id', 'user_id']])
            ->addColumn('group_id', 'integer', [
                'default' => null,
                'limit' => 10,
                'null' => false,
            ])
            ->addColumn('user_id', 'integer', [
                'default' => null,
                'limit' => 10,
                'null' => false,
            ])
            ->addIndex(
                [
                    'group_id',
                ]
            )
            ->addIndex(
                [
                    'user_id',
                ]
            )
            ->create();

        $this->table('projects')
            ->addColumn('name', 'string', [
                'default' => null,
                'limit' => 70,
                'null' => false,
            ])
            ->addColumn('urlname', 'string', [
                'default' => null,
                'limit' => 70,
                'null' => false,
            ])
            ->addColumn('register_start', 'datetime', [
                'default' => null,
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('register_end', 'datetime', [
                'default' => null,
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('visible', 'boolean', [
                'default' => false,
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('logo_name', 'string', [
                'default' => '',
                'limit' => 25,
                'null' => false,
            ])
            ->addColumn('short_description', 'string', [
                'default' => '',
                'limit' => 255,
                'null' => false,
            ])
            ->addColumn('long_description', 'text', [
                'default' => null,
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('confirmation_mail_template', 'string', [
                'default' => '',
                'limit' => 25,
                'null' => false,
            ])
            ->addColumn('min_group_size', 'integer', [
                'default' => 0,
                'limit' => 3,
                'null' => false,
            ])
            ->addColumn('max_group_size', 'integer', [
                'default' => 0,
                'limit' => 3,
                'null' => false,
            ])
            ->addColumn('allow_multireg', 'boolean', [
                'default' => false,
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('multireg_infotext', 'text', [
                'default' => null,
                'limit' => null,
                'null' => false,
            ])
            ->addIndex(
                [
                    'visible',
                ]
            )
            ->create();

        $this->table('ratings')
            ->addColumn('scale_id', 'integer', [
                'default' => null,
                'limit' => 10,
                'null' => false,
            ])
            ->addColumn('rater', 'integer', [
                'default' => null,
                'limit' => 10,
                'null' => false,
            ])
            ->addColumn('group_id', 'integer', [
                'default' => null,
                'limit' => 10,
                'null' => false,
            ])
            ->addColumn('value', 'float', [
                'default' => null,
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('comment', 'text', [
                'default' => null,
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('created', 'datetime', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addIndex(
                [
                    'group_id',
                ]
            )
            ->addIndex(
                [
                    'scale_id',
                ]
            )
            ->addIndex(
                [
                    'scale_id',
                    'group_id',
                ]
            )
            ->create();

        $this->table('registrations')
            ->addColumn('project_id', 'integer', [
                'default' => null,
                'limit' => 10,
                'null' => false,
            ])
            ->addColumn('user_id', 'integer', [
                'default' => null,
                'limit' => 10,
                'null' => false,
            ])
            ->addColumn('created', 'datetime', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addIndex(
                [
                    'project_id',
                ]
            )
            ->addIndex(
                [
                    'user_id',
                ]
            )
            ->create();

        $this->table('rights')
            ->addColumn('name', 'string', [
                'default' => null,
                'limit' => 20,
                'null' => false,
            ])
            ->addColumn('description', 'string', [
                'default' => '',
                'limit' => 50,
                'null' => false,
            ])
            ->addIndex(
                [
                    'name',
                ],
                ['unique' => true]
            )
            ->create();

        $this->table('rights_users', ['id' => false, 'primary_key' => ['right_id', 'user_id']])
            ->addColumn('right_id', 'integer', [
                'default' => null,
                'limit' => 10,
                'null' => false,
            ])
            ->addColumn('user_id', 'integer', [
                'default' => null,
                'limit' => 10,
                'null' => false,
            ])
            ->addIndex(
                [
                    'right_id',
                ]
            )
            ->addIndex(
                [
                    'user_id',
                ]
            )
            ->create();

        $this->table('scales')
            ->addColumn('course_id', 'integer', [
                'default' => null,
                'limit' => 10,
                'null' => false,
            ])
            ->addColumn('name', 'string', [
                'default' => null,
                'limit' => 50,
                'null' => false,
            ])
            ->addColumn('hint', 'text', [
                'default' => null,
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('user_visible', 'boolean', [
                'default' => false,
                'limit' => null,
                'null' => false,
            ])
            ->addIndex(
                [
                    'course_id',
                ]
            )
            ->create();

        $this->table('tags')
            ->addColumn('name', 'string', [
                'default' => null,
                'limit' => 50,
                'null' => false,
            ])
            ->addIndex(
                [
                    'name',
                ],
                ['unique' => true]
            )
            ->create();

        $this->table('tags_courses', ['id' => false, 'primary_key' => ['tag_id', 'course_id']])
            ->addColumn('tag_id', 'integer', [
                'default' => null,
                'limit' => 10,
                'null' => false,
            ])
            ->addColumn('course_id', 'integer', [
                'default' => 0,
                'limit' => 11,
                'null' => false,
            ])
            ->addColumn('value', 'string', [
                'default' => null,
                'limit' => 200,
                'null' => true,
            ])
            ->addIndex(
                [
                    'course_id',
                ]
            )
            ->addIndex(
                [
                    'tag_id',
                ]
            )
            ->create();

        $this->table('tags_projects', ['id' => false, 'primary_key' => ['tag_id', 'project_id']])
            ->addColumn('tag_id', 'integer', [
                'default' => null,
                'limit' => 11,
                'null' => false,
            ])
            ->addColumn('project_id', 'integer', [
                'default' => null,
                'limit' => 11,
                'null' => false,
            ])
            ->addColumn('value', 'string', [
                'default' => null,
                'limit' => 200,
                'null' => true,
            ])
            ->addIndex(
                [
                    'project_id',
                ]
            )
            ->addIndex(
                [
                    'tag_id',
                ]
            )
            ->create();

        $this->table('tags_users', ['id' => false, 'primary_key' => ['tag_id', 'user_id']])
            ->addColumn('tag_id', 'integer', [
                'default' => null,
                'limit' => 10,
                'null' => false,
            ])
            ->addColumn('user_id', 'integer', [
                'default' => null,
                'limit' => 10,
                'null' => false,
            ])
            ->addColumn('value', 'string', [
                'default' => null,
                'limit' => 200,
                'null' => true,
            ])
            ->addIndex(
                [
                    'tag_id',
                ]
            )
            ->addIndex(
                [
                    'user_id',
                ]
            )
            ->create();

        $this->table('uploaded_files')
            ->addColumn('course_id', 'integer', [
                'default' => null,
                'limit' => 10,
                'null' => false,
            ])
            ->addColumn('user_id', 'integer', [
                'default' => null,
                'limit' => 10,
                'null' => false,
            ])
            ->addColumn('disk_filename', 'string', [
                'default' => null,
                'limit' => 20,
                'null' => false,
            ])
            ->addColumn('original_filename', 'string', [
                'default' => null,
                'limit' => 100,
                'null' => false,
            ])
            ->addColumn('mime_type', 'string', [
                'default' => '',
                'limit' => 70,
                'null' => false,
            ])
            ->addColumn('created', 'datetime', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('is_deleted', 'boolean', [
                'default' => false,
                'limit' => null,
                'null' => false,
            ])
            ->addIndex(
                [
                    'course_id',
                ]
            )
            ->addIndex(
                [
                    'user_id',
                ]
            )
            ->addIndex(
                [
                    'user_id',
                    'course_id',
                ]
            )
            ->create();

        $this->table('users')
            ->addColumn('username', 'string', [
                'default' => null,
                'limit' => 30,
                'null' => false,
            ])
            ->addColumn('email', 'string', [
                'default' => null,
                'limit' => 100,
                'null' => false,
            ])
            ->addColumn('password', 'string', [
                'default' => null,
                'limit' => 60,
                'null' => true,
            ])
            ->addColumn('first_name', 'string', [
                'default' => null,
                'limit' => 30,
                'null' => false,
            ])
            ->addColumn('last_name', 'string', [
                'default' => null,
                'limit' => 100,
                'null' => false,
            ])
            ->addColumn('sex', 'string', [
                'default' => null,
                'limit' => 1,
                'null' => false,
            ])
            ->addColumn('street', 'string', [
                'default' => '',
                'limit' => 50,
                'null' => false,
            ])
            ->addColumn('house_number', 'string', [
                'default' => '',
                'limit' => 5,
                'null' => false,
            ])
            ->addColumn('postal_code', 'string', [
                'default' => '',
                'limit' => 5,
                'null' => false,
            ])
            ->addColumn('city', 'string', [
                'default' => '',
                'limit' => 30,
                'null' => false,
            ])
            ->addColumn('birthday', 'date', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('created', 'datetime', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('modified', 'datetime', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('last_login', 'datetime', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addIndex(
                [
                    'username',
                ],
                ['unique' => true]
            )
            ->create();

        $this->table('combo_box_values')
            ->addForeignKey(
                'custom_field_id',
                'custom_fields',
                'id',
                [
                    'update' => 'RESTRICT',
                    'delete' => 'RESTRICT'
                ]
            )
            ->update();

        $this->table('courses')
            ->addForeignKey(
                'project_id',
                'projects',
                'id',
                [
                    'update' => 'RESTRICT',
                    'delete' => 'RESTRICT'
                ]
            )
            ->update();

        $this->table('courses_registrations')
            ->addForeignKey(
                'course_id',
                'courses',
                'id',
                [
                    'update' => 'RESTRICT',
                    'delete' => 'RESTRICT'
                ]
            )
            ->addForeignKey(
                'registration_id',
                'registrations',
                'id',
                [
                    'update' => 'RESTRICT',
                    'delete' => 'RESTRICT'
                ]
            )
            ->update();

        $this->table('custom_fields')
            ->addForeignKey(
                'project_id',
                'projects',
                'id',
                [
                    'update' => 'RESTRICT',
                    'delete' => 'RESTRICT'
                ]
            )
            ->update();

        $this->table('custom_fields_registrations')
            ->addForeignKey(
                'custom_field_id',
                'custom_fields',
                'id',
                [
                    'update' => 'RESTRICT',
                    'delete' => 'RESTRICT'
                ]
            )
            ->addForeignKey(
                'registration_id',
                'registrations',
                'id',
                [
                    'update' => 'RESTRICT',
                    'delete' => 'RESTRICT'
                ]
            )
            ->update();

        $this->table('groups')
            ->addForeignKey(
                'project_id',
                'projects',
                'id',
                [
                    'update' => 'RESTRICT',
                    'delete' => 'RESTRICT'
                ]
            )
            ->update();

        $this->table('groups_users')
            ->addForeignKey(
                'group_id',
                'groups',
                'id',
                [
                    'update' => 'RESTRICT',
                    'delete' => 'RESTRICT'
                ]
            )
            ->addForeignKey(
                'user_id',
                'users',
                'id',
                [
                    'update' => 'RESTRICT',
                    'delete' => 'RESTRICT'
                ]
            )
            ->update();

        $this->table('ratings')
            ->addForeignKey(
                'group_id',
                'groups',
                'id',
                [
                    'update' => 'RESTRICT',
                    'delete' => 'RESTRICT'
                ]
            )
            ->addForeignKey(
                'scale_id',
                'scales',
                'id',
                [
                    'update' => 'RESTRICT',
                    'delete' => 'RESTRICT'
                ]
            )
            ->update();

        $this->table('registrations')
            ->addForeignKey(
                'project_id',
                'projects',
                'id',
                [
                    'update' => 'RESTRICT',
                    'delete' => 'RESTRICT'
                ]
            )
            ->addForeignKey(
                'user_id',
                'users',
                'id',
                [
                    'update' => 'RESTRICT',
                    'delete' => 'RESTRICT'
                ]
            )
            ->update();

        $this->table('rights_users')
            ->addForeignKey(
                'right_id',
                'rights',
                'id',
                [
                    'update' => 'RESTRICT',
                    'delete' => 'RESTRICT'
                ]
            )
            ->addForeignKey(
                'user_id',
                'users',
                'id',
                [
                    'update' => 'RESTRICT',
                    'delete' => 'RESTRICT'
                ]
            )
            ->update();

        $this->table('scales')
            ->addForeignKey(
                'course_id',
                'courses',
                'id',
                [
                    'update' => 'RESTRICT',
                    'delete' => 'RESTRICT'
                ]
            )
            ->update();

        $this->table('tags_courses')
            ->addForeignKey(
                'course_id',
                'courses',
                'id',
                [
                    'update' => 'RESTRICT',
                    'delete' => 'RESTRICT'
                ]
            )
            ->addForeignKey(
                'tag_id',
                'tags',
                'id',
                [
                    'update' => 'RESTRICT',
                    'delete' => 'RESTRICT'
                ]
            )
            ->update();

        $this->table('tags_projects')
            ->addForeignKey(
                'project_id',
                'projects',
                'id',
                [
                    'update' => 'RESTRICT',
                    'delete' => 'RESTRICT'
                ]
            )
            ->addForeignKey(
                'tag_id',
                'tags',
                'id',
                [
                    'update' => 'RESTRICT',
                    'delete' => 'RESTRICT'
                ]
            )
            ->update();

        $this->table('tags_users')
            ->addForeignKey(
                'tag_id',
                'tags',
                'id',
                [
                    'update' => 'RESTRICT',
                    'delete' => 'RESTRICT'
                ]
            )
            ->addForeignKey(
                'user_id',
                'users',
                'id',
                [
                    'update' => 'RESTRICT',
                    'delete' => 'RESTRICT'
                ]
            )
            ->update();

        $this->table('uploaded_files')
            ->addForeignKey(
                'course_id',
                'courses',
                'id',
                [
                    'update' => 'RESTRICT',
                    'delete' => 'RESTRICT'
                ]
            )
            ->addForeignKey(
                'user_id',
                'users',
                'id',
                [
                    'update' => 'RESTRICT',
                    'delete' => 'RESTRICT'
                ]
            )
            ->update();
    }

    public function down()
    {
        $this->table('combo_box_values')
            ->dropForeignKey(
                'custom_field_id'
            );

        $this->table('courses')
            ->dropForeignKey(
                'project_id'
            );

        $this->table('courses_registrations')
            ->dropForeignKey(
                'course_id'
            )
            ->dropForeignKey(
                'registration_id'
            );

        $this->table('custom_fields')
            ->dropForeignKey(
                'project_id'
            );

        $this->table('custom_fields_registrations')
            ->dropForeignKey(
                'custom_field_id'
            )
            ->dropForeignKey(
                'registration_id'
            );

        $this->table('groups')
            ->dropForeignKey(
                'project_id'
            );

        $this->table('groups_users')
            ->dropForeignKey(
                'group_id'
            )
            ->dropForeignKey(
                'user_id'
            );

        $this->table('ratings')
            ->dropForeignKey(
                'group_id'
            )
            ->dropForeignKey(
                'scale_id'
            );

        $this->table('registrations')
            ->dropForeignKey(
                'project_id'
            )
            ->dropForeignKey(
                'user_id'
            );

        $this->table('rights_users')
            ->dropForeignKey(
                'right_id'
            )
            ->dropForeignKey(
                'user_id'
            );

        $this->table('scales')
            ->dropForeignKey(
                'course_id'
            );

        $this->table('tags_courses')
            ->dropForeignKey(
                'course_id'
            )
            ->dropForeignKey(
                'tag_id'
            );

        $this->table('tags_projects')
            ->dropForeignKey(
                'project_id'
            )
            ->dropForeignKey(
                'tag_id'
            );

        $this->table('tags_users')
            ->dropForeignKey(
                'tag_id'
            )
            ->dropForeignKey(
                'user_id'
            );

        $this->table('uploaded_files')
            ->dropForeignKey(
                'course_id'
            )
            ->dropForeignKey(
                'user_id'
            );

        $this->dropTable('combo_box_values');
        $this->dropTable('courses');
        $this->dropTable('courses_registrations');
        $this->dropTable('custom_fields');
        $this->dropTable('custom_fields_registrations');
        $this->dropTable('groups');
        $this->dropTable('groups_users');
        $this->dropTable('projects');
        $this->dropTable('ratings');
        $this->dropTable('registrations');
        $this->dropTable('rights');
        $this->dropTable('rights_users');
        $this->dropTable('scales');
        $this->dropTable('tags');
        $this->dropTable('tags_courses');
        $this->dropTable('tags_projects');
        $this->dropTable('tags_users');
        $this->dropTable('uploaded_files');
        $this->dropTable('users');
    }
}
