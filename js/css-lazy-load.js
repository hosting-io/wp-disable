(function ($) {
	"use strict";
	$(function () {
		if( 'undefined'!== typeof WpDisableAsyncLinks && Object.keys(WpDisableAsyncLinks).length ){
			var key;
			for(key in WpDisableAsyncLinks){
				if(WpDisableAsyncLinks.hasOwnProperty(key)){
					loadCSS(WpDisableAsyncLinks[key]);
				}
			}
		}
	});
}(jQuery));