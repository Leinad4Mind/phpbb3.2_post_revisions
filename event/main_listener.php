<?php

/**
 *
 * @package Prime Post Revision
 * @copyright (c) 2018 Leinad4Mind
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */
namespace leinad4mind\post_revisions\event;

/**
 *
 * @ignore
 *
 */
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Event listener
 */
class main_listener implements EventSubscriberInterface {
	static public function getSubscribedEvents() {
		return array (
				'core.user_setup' => 'load_language_on_setup',
				'core.permissions' => 'permission_post_revisions',
				'core.modify_submit_post_data' => 'edit_action',
				// 'core.submit_post_modify_sql_data' => 'last_edit',
				// viewtopic
				'core.viewtopic_assign_template_vars_before' => 'query',
				'core.viewtopic_modify_post_row' => 'button' 
		);
	}
	/* @var \phpbb\template\template */
	protected $template;
	/** @var \phpbb\db\driver\driver_interface */
	protected $db;
	/** @var \phpbb\user */
	protected $user;
	protected $post_revisions_table;
	protected $root_path;
	protected $phpEx;
	/** @var \phpbb\auth\auth */
	protected $auth;
	/** @var \phpbb\controller\helper */
	protected $controller_helper;
	
	/**
	 * Constructor
	 *
	 * @param \phpbb\controller\helper $helper
	 *        	Controller helper object
	 * @param \phpbb\template $template
	 *        	Template object
	 */
	public function __construct(\phpbb\controller\helper $controller_helper, \phpbb\template\template $template, \phpbb\db\driver\driver_interface $db, \phpbb\user $user, $root_path, $phpEx, $post_revisions_table, \phpbb\auth\auth $auth) {
		$this->controller_helper = $controller_helper;
		$this->template = $template;
		$this->db = $db;
		$this->user = $user;
		$this->post_revisions_table = $post_revisions_table;
		$this->root_path = $root_path;
		$this->phpEx = $phpEx;
		$this->auth = $auth;
	}
	public function permission_post_revisions($event) {
		$permissions = $event ['permissions'];
		$permissions ['u_post_revisions_view'] = array (
				'lang' => 'ACL_U_POST_REVISIONS_VIEW',
				'cat' => 'misc' 
		);
		$permissions ['u_post_revisions_restore'] = array (
				'lang' => 'ACL_U_POST_REVISIONS_RESTORE',
				'cat' => 'misc' 
		);
		$permissions ['u_post_revisions_delete'] = array (
				'lang' => 'ACL_U_POST_REVISIONS_DELETE',
				'cat' => 'misc' 
		);
		$event ['permissions'] = $permissions;
	}
	public function load_language_on_setup($event) {
		$lang_set_ext = $event ['lang_set_ext'];
		$lang_set_ext [] = array (
				'ext_name' => 'leinad4mind/post_revisions',
				'lang_set' => 'common' 
		);
		$event ['lang_set_ext'] = $lang_set_ext;
	}
	public function query($event) {
		$array = array ();
		$qcontrol = "SELECT post_id
					 FROM " . $this->post_revisions_table . "
					 GROUP BY post_id";
		$qqcontrol = $this->db->sql_query ( $qcontrol );
		while ( $control = $this->db->sql_fetchrow ( $qqcontrol ) ) {
			$array [] = $control ['post_id'];
		}
		
		$this->post_revisions = $array;
	}
	public function button($event) {
		$pr = $event ['post_row'];
		$post_id = $pr ['POST_ID'];
		
		if (in_array ( $post_id, $this->post_revisions ) and $this->auth->acl_get ( 'u_post_revisions_view' )) {
			$pr ['U_POST_REVISIONS'] = $this->controller_helper->route ( 'leinad4mind_post_revisions_controller', array (
					'post_id' => $post_id 
			) );
		}
		$event ['post_row'] = $pr;
	}
	public function edit_action($event) {
		/*
		 * data, mode, poll, subject, topic_type, update_message, update_search_index, username
		 */
		if ($event ['mode'] == "edit") {
			$post_id = $event ['data'] ['post_id'];
			$post_edit_user = $event ['data'] ['post_edit_user'];
			// if(!$post_edit_user)
			$post_edit_reason = $event ['data'] ['post_edit_reason'];
			$user_id = $this->user->data ['user_id'];
			
			// first edit? control
			$qcontrol = "SELECT count(revision_id) as tot
						 FROM " . $this->post_revisions_table . "
						 WHERE post_id = " . $post_id;
			$qqcontrol = $this->db->sql_query ( $qcontrol );
			$control = $this->db->sql_fetchrow ( $qqcontrol );
			
			if (! $control ['tot']) {
				$query = "SELECT post_text, post_subject, bbcode_uid, bbcode_bitfield, poster_id, post_time
						  FROM " . POSTS_TABLE . "
						  WHERE post_id = " . $post_id;
				$arr = $this->db->sql_query ( $query );
				$pa = $this->db->sql_fetchrow ( $arr );
				
				$user = $pa ['poster_id'];
				
				$date = $pa ['post_time'];
				
				$sql_arr = array (
						'post_edit_user' => $user,
						'post_id' => $post_id,
						'post_edit_time' => $date,
						'post_subject' => $pa ['post_subject'],
						'post_text' => $pa ['post_text'],
						'bbcode_uid' => $pa ['bbcode_uid'],
						'bbcode_bitfield' => $pa ['bbcode_bitfield'],
						'post_edit_reason' => $this->user->lang ['POST_REVISIONS_ORIGINAL'] 
				);
				
				$sql_insert = 'INSERT INTO ' . $this->post_revisions_table . ' ' . $this->db->sql_build_array ( 'INSERT', $sql_arr );
				$this->db->sql_query ( $sql_insert );
			} // control
			  
			// normal
			$sql_arr = array (
					'post_edit_user' => $this->user->data ['user_id'],
					'post_id' => $post_id,
					'post_edit_time' => time (),
					'post_subject' => $event ['subject'],
					'post_text' => $event ['data'] ['message'],
					'bbcode_uid' => $event ['data'] ['bbcode_uid'],
					'bbcode_bitfield' => $event ['data'] ['bbcode_bitfield'],
					'post_edit_reason' => $post_edit_reason 
			);
			
			$sql_insert = 'INSERT INTO ' . $this->post_revisions_table . ' ' . $this->db->sql_build_array ( 'INSERT', $sql_arr );
			$this->db->sql_query ( $sql_insert );
		}
	}
}

