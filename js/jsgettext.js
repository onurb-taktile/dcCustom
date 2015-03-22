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

/*
 * __() emulates the gettext function using the dc.messages array populated by
 * php
 * extension : if a single | is found in the string, it is ignored. It allows to 
 * put some comments in the strings to translate.
 * a litteral | can be escaped using a second ||
 * __("hello|world") will return as "hello" if no translation is found.
 * if a translation is found (dc_messages["hello|world"]!=undefined), it will be
 * parsed to remove the trailing comment.
 * __("hello||world") will return "hello||world" or dc_messages("hello||world") 
 * if it exists.
 */

function __(text, plural, count) {
	if(dc_messages==undefined)
		var dc_messages={};
	var rettext = text;
	var stripPipes=function(str){
		var pat=/^([^\|]*(?:\|\|[^\|]*)*)/;
		var matches=pat.exec(str);
		if(!matches)
			return str.replace('||','|');
		return matches[1].replace('||','|');
	};
	
	if (plural !== undefined && count !== undefined)
		if (count > 1)
			rettext = plural;
	if (dc_messages[rettext])
		return stripPipes(dc_messages[rettext]);
	else
		return stripPipes(rettext);
}
