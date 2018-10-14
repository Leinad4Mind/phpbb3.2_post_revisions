<?php

/**
 *
 * @package Prime Post Revision
 * @copyright (c) 2018 Leinad4Mind
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

/**
 * DO NOT CHANGE
 */
if (! defined ( 'IN_PHPBB' )) {
	exit ();
}

if (empty ( $lang ) || ! is_array ( $lang )) {
	$lang = array ();
}

// DEVELOPERS PLEASE NOTE
//
// All language files should use UTF-8 as their encoding and the files must not contain a BOM.
//
// Placeholders can now contain order information, e.g. instead of
// 'Page %s of %s' you can (and should) write 'Page %1$s of %2$s', this allows
// translators to re-order the output of data while ensuring it remains correct
//
// You do not need this where single placeholders are used, e.g. 'Message %d' is fine
// equally where a string contains only two placeholders which are used to wrap text
// in a url you again do not need to specify an order e.g., 'Click %sHERE%s' is fine
//
// Some characters you may want to copy&paste:
// ’ « » “ ” …
//

$lang = array_merge ( $lang, array (
		'ACL_U_POST_REVISIONS_VIEW' 	 => 'Può vedere lo storiche di modifiche dei post',
		'ACL_U_POST_REVISIONS_DELETE' 	 => 'Può eliminare post dallo storico di modifiche',
		'ACL_U_POST_REVISIONS_RESTORE' 	 => 'Può ripristinare i post dallo storico di modifiche',
		'POST_REVISIONS_REVISION' 		 => 'Revisione del post',
		'POST_REVISIONS_RETURN' 		 => 'Ritorna alla revisione del post',
		'POST_REVISIONS_TITLE' 			 => 'Revisione Post',
		'POST_REVISIONS_POST' 			 => 'Modifiche al post',
		'POST_REVISIONS_ORIGINAL' 		 => 'Versione originale del post',
		'POST_REVISIONS_RESTORED' 		 => 'Post ripristinato',
		'POST_REVISIONS_DELETED' 		 => 'Post eliminato',
		'POST_REVISIONS_POST_RESTORED' 	 => 'Post ripristinato' 
));
