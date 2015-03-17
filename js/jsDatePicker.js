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

function datePicker(target){
	$(target).datetimepicker({
		regional: "fr",
//		showButtonPanel: true,
//		showOtherMonths: true,
//		selectOtherMonths: true,
//		changeMonth: true,
//		changeYear: true,
		dateFormat: "yy-mm-dd",
		timeFormat: "HH:mm",
		controlType: "select",
		oneLine:true
	});
	
	target.img_top=0;
	target.draw=function(){
				//do nothing :)
	};

	return target;
}

