(function ($) {

	"use strict";

	var $toogle_el = {
		feeds: null,
		comments: null,
		googleMaps: null,
		spamCommentsCleaner: null,
		certainPostsComments: null,
	};

	function on_change_feeds(ev){
		$('.feeds-group').css('display', $toogle_el.feeds.is(":checked") ? '' : 'none');
	}
	
	function on_change_comments(ev){
		var isChecked = $toogle_el.comments.is(":checked");
		$('.comments-group').css('display', isChecked ? 'none' : '');
		on_change_certainPostsComments();
	}
	
	function on_change_googleMaps(ev){
		$('.disable-google-maps-group').css('display', $toogle_el.googleMaps.is(":checked") ? '' : 'none');
	}
	
	function on_change_spamCommentsCleaner(ev){
		var isChecked = ! $toogle_el.comments.is(":checked") && $toogle_el.spamCommentsCleaner.is(":checked");
		$('.delete-spam-comments-group').css('display', isChecked ? '' : 'none');
	}

	function on_change_certainPostsComments(ev){
		$('.certain-posts-comments-group').css('display', ! $toogle_el.certainPostsComments.is(":checked") ? 'none' : ( $toogle_el.comments.is(":checked") ? 'none' : '' ) );
	}

	$(function () {

		$toogle_el = {
			feeds: $('input[name="disable_rss"]'),
			comments: $('input[name="disable_all_comments"]'),
			googleMaps: $('input[name="disable_google_maps"]'),
			spamCommentsCleaner: $('input[name="spam_comments_cleaner"]'),
			certainPostsComments: $('input[name="disable_comments_on_certain_post_types"]'),
		};
		
		$toogle_el.feeds.on('change', on_change_feeds);
		$toogle_el.comments.on('change', on_change_comments);
		$toogle_el.googleMaps.on('change', on_change_googleMaps);
		$toogle_el.spamCommentsCleaner.on('change', on_change_spamCommentsCleaner);
		$toogle_el.certainPostsComments.on('change', on_change_certainPostsComments);

		on_change_feeds();
		on_change_comments();
		on_change_googleMaps();
		on_change_spamCommentsCleaner();
		on_change_certainPostsComments();
	});
	
}(jQuery));