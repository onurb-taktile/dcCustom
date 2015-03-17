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
 * -- END LICENSE BLOCK ------------------------------------*/
if (!defined('DC_RC_PATH')){return;}
if(defined('DC_CUSTOM')){return;}

global $__autoload, $core;

define('DC_CUSTOM','dcCustom');
$dcc_dir=dirname(__FILE__);

if(!file_exists("$dcc_dir/cache/lib.dc.pageorig.php") || (filemtime(DC_ROOT.'/inc/admin/lib.dc.page.php')>filemtime("$dcc_dir/cache/lib.dc.pageorig.php"))){
	$dc_root=path::real(dirname(DC_RC_PATH).'/..');
	$dcPageOrig=file_get_contents("$dc_root/inc/admin/lib.dc.page.php");
	$dcPageOrig=  str_replace("dcPage", "dcPageOrig", $dcPageOrig);
	file_put_contents("$dcc_dir/cache/lib.dc.pageorig.php", $dcPageOrig);
}

# Main class
$__autoload['dcPageOrig'] = "$dcc_dir/cache/lib.dc.pageorig.php";
$__autoload['dcPage'] = dirname(__FILE__).'/inc/class.customDCPage.php';
