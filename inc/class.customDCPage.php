<?php

/* -- BEGIN LICENSE BLOCK ----------------------------------
 *
 * This file is part of dcCustom, a plugin for Dotclear 2.
 *
 * Copyright(c) 2015 Onurb Teva <dev@taktile.fr>
 *
 * Licensed under the GPL version 2.0 license.
 * A copy of this license is available in LICENSE file or at
 * http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 *
 * -- END LICENSE BLOCK ------------------------------------ */
if (!defined('DC_RC_PATH')) {
	return;
}

class dcPage extends dcPageOrig {

	private static $loaded_css = array();
	private static $loaded_js = array();

	public static function jsDatePicker() {
		global $core, $dcc_dir;
		$pf_url = 'index.php?pf=dcCustom';
		return self::cssRefactor($dcc_dir, $pf_url, 'css', 'jquery-ui.css') .
				self::cssRefactor($dcc_dir, $pf_url, 'css', 'jquery-ui-timepicker-addon.css') .
				self::jsLoad("$pf_url/js/jquery-ui-timepicker-addon.js") .
				self::jsLoad("$pf_url/locales/" . $core->blog->settings->system->lang . "/datepicker.js") .
				self::jsLoad($pf_url . '/js/jsDatePicker.js');
	}

	/*	 * *
	 * css filerefactoring
	 * 
	 * generates a cached version of $file with corrected url() urls and 
	 * prints the <link> tag to insert it.
	 * 
	 * @param string	$path		base disk path (plugin main directory)
	 * @param string	$p_url		plugin url
	 * @param string	$css_path	css subdirectory
	 * @param string	$file		css filename
	 * 
	 */

	public static function cssRefactor($path, $p_url, $css_path, $file) {
		global $dcc_dir;
		$cssfile = "$path/$css_path/$file";
		$cachedir = "$dcc_dir/cache/$css_path";
		$cachefile = "$cachedir/$file";

		$escaped_src = html::escapeHTML($cachefile);
		if (isset(self::$loaded_css[$escaped_src])) {
			return;
		}
		if (!file_exists($cssfile)) {
			return '';
		}
		if (!file_exists($cachefile) || (filemtime($cssfile) > filemtime($cachefile))) {
			if (!file_exists($cachedir)) {
				if (!mkdir($cachedir, 0755, true))
					throw new Exception("dcPage::cssRefactor : can't create $cachedir.");
			}else if (!is_dir($cachedir)) {
				throw new Exception("cssRefactor : can't create $cachedir, already exists and is not a directory.");
			}
			$css = preg_replace('/url\((\"|\')([^\1]*)\1\)/U', 'url(\1' . $p_url . $css_path . '\2\1)', file_get_contents($path . "/" . $css_path . "/" . $file));
			file_put_contents("$cachefile", $css);
		}
		self::$loaded_css[$escaped_src] = true;
		return "<link rel=\"stylesheet\" type=\"text/css\" href=\"$p_url/cache/$css_path/$file\">\n";
	}

	/*	 * *
	 * js gettext support
	 * 
	 * extracts all __("") strings from the given js file and creates a js file 
	 * with all the translated strings stored in $var js object.
	 * it also appends the content of original js file
	 * this file is cached and updated only on js file change. 
	 * prints the code to insert cached file.
	 * 
	 * @param string	$path		base disk path (plugin main directory)
	 * @param string	$p_url		plugin url
	 * @param string	$js_path	js subdirectory
	 * @param string	$file		js filename
	 * @param string	$var		js variable to store the translated strings in
	 */

	public static function jsGettext($path, $pf_url, $js_path, $file, $var) {
		global $dcc_dir;
		global $dcc_pfurl;
		$jsfile = "$path/$js_path/$file";
		$cachedir = "$dcc_dir/cache/$js_path";
		$cachefile = "$cachedir/__$file";
		$pf_cachefile = "$dcc_pfurl/cache/$js_path/__$file";
		$pf_jsfile = "$pf_url/$js_path/$file";
		$pf_jsgettext = "$dcc_pfurl/js/jsgettext.js";

		$escaped_src = html::escapeHTML($jsfile);
		if (isset(self::$loaded_js[$escaped_src]))
			return;
		if (!file_exists($jsfile)) {
			return '';
		}
		if (!file_exists($cachefile) || (filemtime($jsfile) > filemtime($cachefile))) {
			if (!file_exists($cachedir)) {
				if (!mkdir($cachedir, 0755, true))
					throw new Exception("dcPage::jsGettext : can't create $cachedir.");
			}else if (!is_dir($cachedir)) {
				throw new Exception("dcPage::jsGettext : can't create $cachedir, already exists and is not a directory.");
			}

			$jsfic = file_get_contents($jsfile);
			if (preg_match("/^\/\/nogettextization please/", $jsfic)) {
				self::$loaded_js[$escaped_src] = true;
				return self::jsLoad("$dcc_pfurl/js/jsgettext.js") . self::jsLoad("$pf_jsfile");
			}
			//_{2}\(([\'"])(.+)\1(?:\s*,\s*([\'"])(.*)\3\s*,([^\)]*))?\)
			//
			$pat = '/_{2}\(([\'"])(?U:(.+))\1(?:\s*,\s*([\'"])(.*)\3\s*,\s*(([\d\w\.\s\$-]*|(\()?(?(7)(?-3)\)))+))?\)/';
			$trjsfic = preg_replace_callback($pat, function(array $matches) {
				//first, strip the |comment part from the translated string
				$singular = preg_replace('/^([^\|]*(?:\|\|[^\|]*)*)(\|.*)?$/', '$1', __($matches[2]));
				$plural = isset($matches[4]) ? preg_replace('/^([^\|]*(?:\|\|[^\|]*)*)(\|.*)?$/', '$1', __($matches[2], $matches[4], 2)) : null;
				if ($plural) {
					return "(" . $matches[5] . ">1?" . $matches[1] . $singular . $matches[1] . ":" . $matches[3] . $plural . $matches[3] . ")";
				} else {
					return $matches[1] . $singular . $matches[1];
				}
			}, $jsfic);
//			if(preg_match_all($pat, $jsfic,$gtstrings)){
//				foreach ($gtstrings[2] as $k => $v) {
//					$gtstring.=self::jsVar($var."['".$v."']", __($v));
//				}
//				foreach ($gtstrings[4] as $k => $v) {
//					if($v!="")
//						$gtstring.=self::jsVar($var."['".$v."']", __($v));					
//				}
//			}
//			
			file_put_contents($cachefile, $trjsfic);
			chmod($cachefile, 0664);
		}
		self::$loaded_js[$escaped_src] = true;
		return self::jsLoad("$pf_cachefile");
	}

	public static function jsCommon() {
		$pf_url = 'index.php?pf=dcCustom';
		$mute_or_no = '';
		if (empty($GLOBALS['core']->blog) || $GLOBALS['core']->blog->settings->system->jquery_migrate_mute) {
			$mute_or_no .=
					'<script type="text/javascript">' . "\n" .
					"//<![CDATA[\n" .
					'jQuery.migrateMute = true;' .
					"\n//]]>\n" .
					"</script>\n";
		}

		return
				self::jsLoad("$pf_url/js/jquery-1.11.2.js") .
				self::jsLoad("$pf_url/js/jquery-ui.js") .
				$mute_or_no .
				self::jsLoad('js/jquery/jquery-migrate-1.2.1.js') .
				self::jsLoad('js/jquery/jquery.biscuit.js') .
				self::jsLoad('js/jquery/jquery.bgFade.js') .
				self::jsLoad('js/common.js') .
				self::jsLoad('js/prelude.js') .
				'<script type="text/javascript">' . "\n" .
				"//<![CDATA[\n" .
				'jsToolBar = {}, jsToolBar.prototype = { elements : {} };' . "\n" .
				self::jsVar('dotclear.nonce', $GLOBALS['core']->getNonce()) .
				self::jsVar('dotclear.img_plus_src', 'images/expand.png') .
				self::jsVar('dotclear.img_plus_alt', __('uncover')) .
				self::jsVar('dotclear.img_minus_src', 'images/hide.png') .
				self::jsVar('dotclear.img_minus_alt', __('hide')) .
				self::jsVar('dotclear.img_menu_on', 'images/menu_on.png') .
				self::jsVar('dotclear.img_menu_off', 'images/menu_off.png') .
				self::jsVar('dotclear.img_plus_theme_src', 'images/plus-theme.png') .
				self::jsVar('dotclear.img_plus_theme_alt', __('uncover')) .
				self::jsVar('dotclear.img_minus_theme_src', 'images/minus-theme.png') .
				self::jsVar('dotclear.img_minus_theme_alt', __('hide')) .
				self::jsVar('dotclear.msg.help', __('Need help?')) .
				self::jsVar('dotclear.msg.new_window', __('new window')) .
				self::jsVar('dotclear.msg.help_hide', __('Hide')) .
				self::jsVar('dotclear.msg.to_select', __('Select:')) .
				self::jsVar('dotclear.msg.no_selection', __('no selection')) .
				self::jsVar('dotclear.msg.select_all', __('select all')) .
				self::jsVar('dotclear.msg.invert_sel', __('Invert selection')) .
				self::jsVar('dotclear.msg.website', __('Web site:')) .
				self::jsVar('dotclear.msg.email', __('Email:')) .
				self::jsVar('dotclear.msg.ip_address', __('IP address:')) .
				self::jsVar('dotclear.msg.error', __('Error:')) .
				self::jsVar('dotclear.msg.entry_created', __('Entry has been successfully created.')) .
				self::jsVar('dotclear.msg.edit_entry', __('Edit entry')) .
				self::jsVar('dotclear.msg.view_entry', __('view entry')) .
				self::jsVar('dotclear.msg.confirm_delete_posts', __("Are you sure you want to delete selected entries (%s)?")) .
				self::jsVar('dotclear.msg.confirm_delete_medias', __("Are you sure you want to delete selected medias (%d)?")) .
				self::jsVar('dotclear.msg.confirm_delete_categories', __("Are you sure you want to delete selected categories (%s)?")) .
				self::jsVar('dotclear.msg.confirm_delete_post', __("Are you sure you want to delete this entry?")) .
				self::jsVar('dotclear.msg.click_to_unlock', __("Click here to unlock the field")) .
				self::jsVar('dotclear.msg.confirm_spam_delete', __('Are you sure you want to delete all spams?')) .
				self::jsVar('dotclear.msg.confirm_delete_comments', __('Are you sure you want to delete selected comments (%s)?')) .
				self::jsVar('dotclear.msg.confirm_delete_comment', __('Are you sure you want to delete this comment?')) .
				self::jsVar('dotclear.msg.cannot_delete_users', __('Users with posts cannot be deleted.')) .
				self::jsVar('dotclear.msg.confirm_delete_user', __('Are you sure you want to delete selected users (%s)?')) .
				self::jsVar('dotclear.msg.confirm_delete_category', __('Are you sure you want to delete category "%s"?')) .
				self::jsVar('dotclear.msg.confirm_reorder_categories', __('Are you sure you want to reorder all categories?')) .
				self::jsVar('dotclear.msg.confirm_delete_media', __('Are you sure you want to remove media "%s"?')) .
				self::jsVar('dotclear.msg.confirm_delete_directory', __('Are you sure you want to remove directory "%s"?')) .
				self::jsVar('dotclear.msg.confirm_extract_current', __('Are you sure you want to extract archive in current directory?')) .
				self::jsVar('dotclear.msg.confirm_remove_attachment', __('Are you sure you want to remove attachment "%s"?')) .
				self::jsVar('dotclear.msg.confirm_delete_lang', __('Are you sure you want to delete "%s" language?')) .
				self::jsVar('dotclear.msg.confirm_delete_plugin', __('Are you sure you want to delete "%s" plugin?')) .
				self::jsVar('dotclear.msg.confirm_delete_plugins', __('Are you sure you want to delete selected plugins?')) .
				self::jsVar('dotclear.msg.use_this_theme', __('Use this theme')) .
				self::jsVar('dotclear.msg.remove_this_theme', __('Remove this theme')) .
				self::jsVar('dotclear.msg.confirm_delete_theme', __('Are you sure you want to delete "%s" theme?')) .
				self::jsVar('dotclear.msg.confirm_delete_themes', __('Are you sure you want to delete selected themes?')) .
				self::jsVar('dotclear.msg.confirm_delete_backup', __('Are you sure you want to delete this backup?')) .
				self::jsVar('dotclear.msg.confirm_revert_backup', __('Are you sure you want to revert to this backup?')) .
				self::jsVar('dotclear.msg.zip_file_content', __('Zip file content')) .
				self::jsVar('dotclear.msg.xhtml_validator', __('XHTML markup validator')) .
				self::jsVar('dotclear.msg.xhtml_valid', __('XHTML content is valid.')) .
				self::jsVar('dotclear.msg.xhtml_not_valid', __('There are XHTML markup errors.')) .
				self::jsVar('dotclear.msg.warning_validate_no_save_content', __('Attention: an audit of a content not yet registered.')) .
				self::jsVar('dotclear.msg.confirm_change_post_format', __('You have unsaved changes. Switch post format will loose these changes. Proceed anyway?')) .
				self::jsVar('dotclear.msg.confirm_change_post_format_noconvert', __("Warning: post format change will not convert existing content. You will need to apply new format by yourself. Proceed anyway?")) .
				self::jsVar('dotclear.msg.load_enhanced_uploader', __('Loading enhanced uploader, please wait.')) .
				self::jsVar('dotclear.msg.module_author', __('Author:')) .
				self::jsVar('dotclear.msg.module_details', __('Details')) .
				self::jsVar('dotclear.msg.module_support', __('Support')) .
				self::jsVar('dotclear.msg.module_help', __('Help:')) .
				self::jsVar('dotclear.msg.module_section', __('Section:')) .
				self::jsVar('dotclear.msg.module_tags', __('Tags:')) .
				"\n//]]>\n" .
				"</script>\n";
	}

	# Top of admin page

	public static function open($title = '', $head = '', $breadcrumb = '', $options = array()) {
		global $core;

		# List of user's blogs
		if ($core->auth->getBlogCount() == 1 || $core->auth->getBlogCount() > 20) {
			$blog_box = '<p>' . __('Blog:') . ' <strong title="' . html::escapeHTML($core->blog->url) . '">' .
					html::escapeHTML($core->blog->name) . '</strong>';

			if ($core->auth->getBlogCount() > 20) {
				$blog_box .= ' - <a href="' . $core->adminurl->get("admin.blogs") . '">' . __('Change blog') . '</a>';
			}
			$blog_box .= '</p>';
		} else {
			$rs_blogs = $core->getBlogs(array('order' => 'LOWER(blog_name)', 'limit' => 20));
			$blogs = array();
			while ($rs_blogs->fetch()) {
				$blogs[html::escapeHTML($rs_blogs->blog_name . ' - ' . $rs_blogs->blog_url)] = $rs_blogs->blog_id;
			}
			$blog_box = '<p><label for="switchblog" class="classic">' .
					__('Blogs:') . '</label> ' .
					$core->formNonce() .
					form::combo('switchblog', $blogs, $core->blog->id) .
					'<input type="submit" value="' . __('ok') . '" class="hidden-if-js" /></p>';
		}

		$safe_mode = isset($_SESSION['sess_safe_mode']) && $_SESSION['sess_safe_mode'];

		# Display
		header('Content-Type: text/html; charset=UTF-8');

		// Prevents Clickjacking as far as possible
		if (isset($options['x-frame-allow'])) {
			self::setXFrameOptions($options['x-frame-allow']);
		} else {
			self::setXFrameOptions();
		}
		echo
		'<!DOCTYPE html>' .
		'<html lang="' . $core->auth->getInfo('user_lang') . '">' . "\n" .
		"<head>\n" .
		'  <meta charset="UTF-8" />' . "\n" .
		'  <meta name="ROBOTS" content="NOARCHIVE,NOINDEX,NOFOLLOW" />' . "\n" .
		'  <meta name="GOOGLEBOT" content="NOSNIPPET" />' . "\n" .
		'  <meta name="viewport" content="width=device-width, initial-scale=1.0" />' . "\n" .
		'  <title>' . $title . ' - ' . html::escapeHTML($core->blog->name) . ' - ' . html::escapeHTML(DC_VENDOR_NAME) . ' - ' . DC_VERSION . '</title>' . "\n" .
		self::jsLoadIE7() .
		'  <link rel="stylesheet" href="style/default.css" type="text/css" media="screen" />' . "\n";
		if (l10n::getTextDirection($GLOBALS['_lang']) == 'rtl') {
			echo
			'  <link rel="stylesheet" href="style/default-rtl.css" type="text/css" media="screen" />' . "\n";
		}

		$core->auth->user_prefs->addWorkspace('interface');
		$user_ui_hide_std_favicon = $core->auth->user_prefs->interface->hide_std_favicon;
		if (!$user_ui_hide_std_favicon) {
			echo
			'<link rel="icon" type="image/png" href="images/favicon96-login.png" />' .
			'<link rel="shortcut icon" href="images/favicon.ico" type="image/x-icon" />';
		}
		echo
		self::jsCommon() .
		self::jsToggles() .
		$head;

		# --BEHAVIOR-- adminPageHTMLHead
		$core->callBehavior('adminPageHTMLHead');

		echo
		"</head>\n" .
		'<body id="dotclear-admin' .
		($safe_mode ? ' safe-mode' : '') . '" class="no-js">' . "\n" .
		'<ul id="prelude">' .
		'<li><a href="#content">' . __('Go to the content') . '</a></li>' .
		'<li><a href="#main-menu">' . __('Go to the menu') . '</a></li>' .
		'<li><a href="#qx">' . __('Go to search') . '</a></li>' .
		'<li><a href="#help">' . __('Go to help') . '</a></li>' .
		'</ul>' . "\n" .
		'<div id="header" role="banner">' .
		'<h1><a href="' . $core->adminurl->get("admin.home") . '"><span class="hidden">' . DC_VENDOR_NAME . '</span></a></h1>' . "\n";

		echo
		'<form action="' . $core->adminurl->get("admin.home") . '" method="post" id="top-info-blog">' .
		$blog_box .
		'<p><a href="' . $core->blog->url . '" class="outgoing" title="' . __('Go to site') .
		'">' . __('Go to site') . '<img src="images/outgoing.png" alt="" /></a>' .
		'</p></form>' .
		'<ul id="top-info-user">' .
		'<li><a class="' . (preg_match('/' . preg_quote($core->adminurl->get('admin.home')) . '$/', $_SERVER['REQUEST_URI']) ? ' active' : '') . '" href="' . $core->adminurl->get("admin.home") . '">' . __('My dashboard') . '</a></li>' .
		'<li><a class="smallscreen' . (preg_match('/' . preg_quote($core->adminurl->get('admin.user.preferences')) . '(\?.*)?$/', $_SERVER['REQUEST_URI']) ? ' active' : '') .
		'" href="' . $core->adminurl->get("admin.user.preferences") . '">' . __('My preferences') . '</a></li>' .
		'<li><a href="' . $core->adminurl->get("admin.home", array('logout' => 1)) . '" class="logout"><span class="nomobile">' . sprintf(__('Logout %s'), $core->auth->userID()) .
		'</span><img src="images/logout.png" alt="" /></a></li>' .
		'</ul>' .
		'</div>'; // end header

		echo
		'<div id="wrapper" class="clearfix">' . "\n" .
		'<div class="hidden-if-no-js collapser-box"><a href="#" id="collapser">' .
		'<img class="collapse-mm" src="images/collapser-hide.png" alt="' . __('Hide main menu') . '" />' .
		'<img class="expand-mm" src="images/collapser-show.png" alt="' . __('Show main menu') . '" />' .
		'</a></div>' .
		'<div id="main" role="main">' . "\n" .
		'<div id="content" class="clearfix">' . "\n";

		# Safe mode
		if ($safe_mode) {
			echo
			'<div class="warning" role="alert"><h3>' . __('Safe mode') . '</h3>' .
			'<p>' . __('You are in safe mode. All plugins have been temporarily disabled. Remind to log out then log in again normally to get back all functionalities') . '</p>' .
			'</div>';
		}

		// Display breadcrumb (if given) before any error message
		echo $breadcrumb;

		if ($core->error->flag()) {
			echo
			'<div class="error"><p><strong>' . (count($core->error->getErrors()) > 1 ? __('Errors:') : __('Error:')) . '</strong></p>' .
			$core->error->toHTML() .
			'</div>';
		}

		// Display notices
		echo self::notices();
	}

}
