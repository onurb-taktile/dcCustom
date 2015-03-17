# dcCustom
A small plugin to customize dotclear V2.6+ on admin side

* replaces dcPage with a customized one. 
	Dirty hack, it writes some code in config.php to autoload the customized class instead of the regular one.
	- loads jquery 1.11.2 + jquery-ui (overloading of dcPage::jsCommon and dcPage::open
	- replaces the ugly dcDatePicker by a jquery-ui-datetimepicker (brute force overloading of dcPage::jsDatePicker
		+ jsDatePicker.js to simulate dcDatePicker loading
	- adds dcPage::cssRefactor which translates a css file to correct the url() urls with file caching.