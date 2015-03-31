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

if (!defined('DC_CONTEXT_ADMIN')){return;}

$new_version = $core->plugins->moduleInfo('dcCustom','version');
$old_version = $core->getVersion('dcCustom');
define('DC_CUSTOM_MIN_DC_VERSION',"2.6");

# Compare versions
if (version_compare($old_version,$new_version,'>=')) return;

if(!defined('DC_CUSTOM_UNINSTALL') && version_compare(DC_VERSION,DC_CUSTOM_MIN_DC_VERSION,"<"))
	throw new Exception(sprintf(__('Dotclear version %s minimum is required. "%s" is deactivated'),DC_CUSTOM_MIN_DC_VERSION,'dcCustom'));

define("DC_CUSTOM_RCSTART","#Added by dcCustom");
define("DC_CUSTOM_RCEND","#/Added by dcCustom - Please do not edit this block by hand, install or uninstall dcCustom from plugins panel.");
define("DC_CUSTOM_RC","if(defined(\"DC_CONTEXT_ADMIN\") && file_exists(\"%1\$s/_prepend.php\") && !file_exists(\"%1\$s/_disabled\"))\n\tinclude_once \"%1\$s/_prepend.php\";");
$conf_file=file_get_contents(DC_RC_PATH);
$do=false;
if($start=strpos($conf_file,DC_CUSTOM_RCSTART)){
	$end=strpos($conf_file,DC_CUSTOM_RCEND,$start);
	if(!$end) throw new Exception ("dcCustom install : Impossible to proceed.");
	$length=$end+strlen(DC_CUSTOM_RCEND)-$start+1;
	$conf_file=  substr_replace($conf_file, '',$start, $length);
	$do=true;
}

if(!defined('DC_CUSTOM_UNINSTALL')){
	$conf_file.="\n".DC_CUSTOM_RCSTART."\n".sprintf(DC_CUSTOM_RC,dirname(__FILE__))."\n".DC_CUSTOM_RCEND."\n";
	$do=true;
}

if($do)file_put_contents(DC_RC_PATH, $conf_file);
@mkdir(dirname(__FILE__)."/cache",0775);
$core->setVersion('dcCustom',$new_version);
return true;
