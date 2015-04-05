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

if (!defined('DC_RC_PATH')) {return;}

$this->registerModule(
	/* Name */			"dcCustom",
	/* Description*/		"Customize dotclear admin side",
	/* Author */			"Onurb Teva <dev@taktile.fr>",
	/* Version */			'2015.04.05',
	/* Permissions */		array(
								'permissions' =>	'usage,contentadmin',
								'priority' =>		10,
								'type'		=>		'plugin'
							)
);
