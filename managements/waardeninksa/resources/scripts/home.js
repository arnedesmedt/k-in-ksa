var frame_width = 1280;
var overview_width = frame_width - 250;
var pull_overview_width = 35;
var tower_img_width = 650;
var tower_width_percentage = 280/500;
var tower_width_side_percentage = 1/5;

$(document).ready(function() {
	$("#tower").resizeimagemap();

	$("#site-overview-pull").on("click", function() {
		toggle_overview();
	});

});

function toggle_overview () {
	var $wrapper = $("#site-overview-wrapper");
	var $fa = $wrapper.find(".fa");
	var open = $wrapper.data("open");

	if(open) {
		var left_overview = frame_width - pull_overview_width;
		var left_tower = 0;
		$fa.removeClass("fa-arrow-circle-right");
		$fa.addClass("fa-arrow-circle-left");
	} else {
		var left_overview = frame_width - overview_width;
		var left_tower = (frame_width / 2) - (tower_img_width * tower_width_percentage / 2) + (tower_img_width * tower_width_side_percentage);
		$fa.removeClass("fa-arrow-circle-left");
		$fa.addClass("fa-arrow-circle-right");
	}

	//open overview
	$wrapper.animate({
		left: left_overview + "px",
	}, 500);

	//move tower
	$("#tower-wrapper").animate({
		left: "-" + left_tower + "px",
	})
	
	$wrapper.data("open", !open);
}