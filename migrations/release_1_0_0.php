<?php

/** 
 * 
 * @package Prime Post Revision
 * @copyright (c) 2018 Leinad4Mind
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2 
 * 
 */
namespace leinad4mind\post_revisions\migrations;

class release_1_0_0 extends \phpbb\db\migration\migration {
	static public function depends_on() {
		return array (
				'\phpbb\db\migration\data\v310\gold' 
		);
	}
	public function update_schema() {
		return array (
				'add_tables' => array (
						$this->table_prefix . 'post_revisions' => array (
							'COLUMNS' => array (
								'revision_id' => array('UINT',null,'auto_increment'),
								'post_id' => array('UINT',0),
								'post_subject' => array('VCHAR',''),
								'post_text' => array('TEXT',''),
								'bbcode_uid' => array('VCHAR:8',''),
								'bbcode_bitfield' => array('VCHAR:255',''),
								'post_edit_time' => array('TIMESTAMP',0),
								'post_edit_user' => array('UINT',0),
								'post_edit_reason' => array('VCHAR:255',''),
							),
							'PRIMARY_KEY' => 'revision_id' 
						)
				) 
		);
		
	}

	public function revert_schema() {
		return array (
				'drop_tables' => array (
					$this->table_prefix . 'post_revisions' 
				) 
		);
	}

	public function update_data() {
		return array(
				array('permission.add', array('u_post_revisions_view',true)),
				array('permission.add',array('u_post_revisions_delete',true)),
				array('permission.add',array('u_post_revisions_restore',true)),
				array('permission.permission_set',array('ROLE_ADMIN_FULL','u_post_revisions_view','rule',true)),
				array('permission.permission_set',array('ROLE_ADMIN_FULL','u_post_revisions_delete','rule',true)),
				array('permission.permission_set',array('ROLE_ADMIN_FULL','u_post_revisions_restore','rule',true))
		);
	}
}
