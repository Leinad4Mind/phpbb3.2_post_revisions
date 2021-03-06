<?php

/**
 *
 * @package Prime Post Revision
 * @copyright (c) 2018 Leinad4Mind
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */
namespace leinad4mind\post_revisions\controller;

class main {
	protected $auth;
	protected $template;
	protected $db;
	protected $post_revisions_table;
	protected $user;
	protected $helper;
	
	public function __construct(\phpbb\template\template $template, \phpbb\db\driver\driver_interface $db, $post_revisions_table, \phpbb\auth\auth $auth, \phpbb\user $user, \phpbb\controller\helper $helper) {
		$this->template = $template;
		$this->db = $db;
		$this->post_revisions_table = $post_revisions_table;
		$this->auth = $auth;
		$this->user = $user;
		$this->helper = $helper;
	}
	
	public function view($post_id) {
		$page_name = $this->user->lang ['POST_REVISIONS_TITLE'];
		
		if ($this->auth->acl_get ( 'u_post_revisions_view' )) {
			
			// content start
			// topic title
			$topic_query = "SELECT post_subject
							 FROM " . POSTS_TABLE . "
							 WHERE post_id = " . $post_id . "";
			$topic_query_g = $this->db->sql_query ( $topic_query );
			$topic_query_arr = $this->db->sql_fetchrow ( $topic_query_g );
			$topic_title = $topic_query_arr ['post_subject'];
			$this->template->assign_var ( 'TOPIC_TITLE', $this->user->lang ['POST_REVISIONS_REVISION'] . " \"$topic_title\"" );
			
			// echo $post_id;
			// list
			$query = "SELECT p.revision_id, p.post_id, p.post_subject, p.post_text, p.bbcode_uid, p.post_edit_time, p.post_edit_user, p.bbcode_bitfield, p.post_edit_reason, u.*
							 FROM " . $this->post_revisions_table . " p,
						#" . POSTS_TABLE . " po,
						" . USERS_TABLE . " u
							 WHERE p.post_id = " . $post_id . "
							 #AND po.post_id=p.post_id
						    AND p.post_edit_user = u.user_id
						   ORDER BY revision_id DESC";
			$list_query = $this->db->sql_query ( $query );
			while ( $list = $this->db->sql_fetchrow ( $list_query ) ) {
				$username = $list ['username'];
				$user_colour = ($list ['user_colour']) ? ' style="color:#' . $list ['user_colour'] . '" class="username-coloured"' : '';
				$user_id = $list ['user_id'];
				
				$post_text = generate_text_for_display ( $list ['post_text'], $list ['bbcode_uid'], $list ['bbcode_bitfield'], 9999 );
				// $date = $this->user->format_date($list['post_time']);
				// echo $username.$post_text."<br>";
				
				if ($this->auth->acl_get ( 'u_post_revisions_delete' )) {
					$delete = $this->helper->route ( 'leinad4mind_post_revisionsdelete_controller', array (
							'revision_id' => $list ['revision_id'] 
					) );
				} else {
					$delete = false;
				}
				
				if ($this->auth->acl_get ( 'u_post_revisions_restore' )) {
					$restore = $this->helper->route ( 'leinad4mind_post_revisionsrestore_controller', array (
							'revision_id' => $list ['revision_id'] 
					) );
				} else {
					$restore = false;
				}
				
				$reason = $list ['post_edit_reason'];
				
				$this->template->assign_block_vars ( 'post_revisions_list', array (
						'POST_ID' => $list ['revision_id'],
						'REAL_POST_ID' => $list ['post_id'],
						'USERNAME' => $username,
						'USERNAME_COLOUR' => $user_colour,
						'DATE' => $this->user->format_date ( $list ['post_edit_time'] ),
						'POST_TEXT' => $post_text,
						'POST_SUBJECT' => $list ['post_subject'],
						'U_RESTORE' => $restore,
						'U_DELETE' => $delete,
						'EDIT_REASON' => $reason 
				) );
			}
		} // permission
		
		return $this->helper->render ( 'post_revisions_body.html', $page_name );
	}
	
	public function delete($revision_id) {
		if ($this->auth->acl_get ( 'u_post_revisions_delete' )) {
			if (confirm_box ( true )) {
				
				$query = "SELECT p.revision_id, p.post_id
						  FROM " . $this->post_revisions_table . " p
						  WHERE p.revision_id = " . $revision_id;
				$list_query = $this->db->sql_query ( $query );
				$list = $this->db->sql_fetchrow ( $list_query );
				
				$sql = 'DELETE FROM ' . $this->post_revisions_table . ' WHERE revision_id = ' . $revision_id;
				$this->db->sql_query ( $sql );
				
				$links = "<a href=\"" . $this->helper->route ( 'leinad4mind_post_revisions_controller', array (
						'post_id' => $list ['post_id'] 
				) ) . "\">" . $this->user->lang ['POST_REVISIONS_RETURN'] . "</a>";
				trigger_error ( $this->user->lang ['POST_REVISIONS_DELETED'] . "<br />" . $links );
			} else {
				confirm_box ( false );
			}
		}
		$page_name = $this->user->lang ['POST_REVISIONS_TITLE'];
		return $this->helper->render ( 'post_revisions_body.html', $page_name );
	}
	
	public function restore($revision_id) {
		if ($this->auth->acl_get ( 'u_post_revisions_restore' )) {
			if (confirm_box ( true )) {
				$query = "SELECT p.revision_id, p.post_id, p.post_subject, p.post_text, p.bbcode_uid, p.post_edit_time, p.post_edit_user, p.bbcode_bitfield, p.post_edit_reason, u.*
								 FROM " . $this->post_revisions_table . " p,
							#" . POSTS_TABLE . " po,
							" . USERS_TABLE . " u
								 WHERE p.revision_id = " . $revision_id . "
								 #AND po.post_id=p.post_id
							    AND p.post_edit_user = u.user_id";
				$list_query = $this->db->sql_query ( $query );
				$list = $this->db->sql_fetchrow ( $list_query );
				
				$post_arr = array (
						'post_text' => $list ['post_text'],
						'post_subject' => $list ['post_subject'],
						'bbcode_uid' => $list ['bbcode_uid'],
						'bbcode_bitfield' => $list ['bbcode_bitfield'],
						'post_edit_time' => $list ['post_edit_time'],
						'post_edit_user' => $list ['post_edit_user'],
						'post_edit_reason' => $list ['post_edit_reason'] 
				);
				$this->db->sql_query ( "UPDATE " . POSTS_TABLE . " SET " . $this->db->sql_build_array ( 'UPDATE', $post_arr ) . " WHERE post_id = " . $list ['post_id'] );
				
				$post_revisions_arr = array (
						'post_edit_user' => $list ['post_edit_user'],
						'post_id' => $list ['post_id'],
						'post_edit_time' => $list ['post_edit_time'],
						'post_subject' => $list ['post_subject'],
						'post_text' => $list ['post_text'],
						'bbcode_uid' => $list ['bbcode_uid'],
						'bbcode_bitfield' => $list ['bbcode_bitfield'],
						'post_edit_reason' => $this->user->lang ['POST_REVISIONS_POST_RESTORED'] 
				);
				
				$sql_insert = 'INSERT INTO ' . $this->post_revisions_table . ' ' . $this->db->sql_build_array ( 'INSERT', $post_revisions_arr );
				$this->db->sql_query ( $sql_insert );
				
				$links = "<a href=\"" . $this->helper->route ( 'leinad4mind_post_revisions_controller', array (
						'post_id' => $list ['post_id'] 
				) ) . "\">" . $this->user->lang ['POST_REVISIONS_RETURN'] . "</a>";
				trigger_error ( $this->user->lang ['POST_REVISIONS_RESTORED'] . "<br />" . $links );
			} else {
				confirm_box ( false );
			}
		}
		$page_name = $this->user->lang ['POST_REVISIONS_TITLE'];
		return $this->helper->render ( 'post_revisions_body.html', $page_name );
	}
}
