$(function(e){"use strict";e(".animsition").animsition(),e(".nav-col").matchHeight(),e(".counter").counterUp({delay:10,time:1e3}),e(".owl-carousel-single").owlCarousel({loop:!0,navRewind:!1,margin:10,dots:!0,nav:!1,autoplay:!1,navText:[],items:1}),Swiper(".swiper-container",{loop:!0,pagination:".swiper-pagination",paginationClickable:!0,nextButton:".swiper-button-next",prevButton:".swiper-button-prev"}),e(".swiper-slide","#swiper").each(function(){var t=e(this).data("slide-img");e(this).css("background-image","url("+t+")")}),e("[data-toggle='tooltip']").tooltip(),window.sr=ScrollReveal(),sr.reveal(".one-step",{delay:200,duration:600,distance:"60px",easing:"ease-in-out",rotate:{z:0},scale:1},500),e("#animated-text").textillate({loop:!0}),e(window).on("scroll resize",function(){return e(window).scrollTop()>=75?void e("body").addClass("body-scrolled"):e("body").removeClass("body-scrolled")})}),$(function(){var e,t,n,o,i,a,p,s=document.getElementById("errorText");$(".next").click(function(){return $("#pass").val()&&null!=$("#pass").val().match(/.{8,}/i)?(($("#pass").val()||null!=$("#pass").val().match(/.{8,}/i))&&(s.textContent=""),$("input").blur(function(e){e.target.checkValidity()}).bind("invalid",function(e){setTimeout(function(){$(e.target).focus()},50)}),!p&&(p=!0,e=$(this).parent(),t=$(this).parent().next(),$("#progressbar li").eq($("fieldset").index(t)).addClass("active"),t.show(),void e.animate({opacity:0},{step:function(n,p){a=1-.5*(1-n),o=200*n+"%",i=1-n,e.css({transform:"scale("+a+")"}),t.css({left:o,opacity:i})},duration:500,complete:function(){e.hide(),p=!1}}))):($("#pass").focus(),s.textContent="Please enter a valid password with 8 characters long",s.style.color="red",!1)}),$(".previous").click(function(){if(p)return!1;p=!0,e=$(this).parent(),n=$(this).parent().prev(),$("#progressbar li").eq($("fieldset").index(e)).removeClass("active"),n.show(),e.animate({opacity:0},{step:function(t,p){a=.8+.2*(1-t),o=200*(1-t)+"%",i=1-t,e.css({left:o}),n.css({transform:"scale("+a+")",opacity:i})},duration:500,complete:function(){e.hide(),p=!1}})}),$(".submit").click(function(e){$("#storeURL").val()&&$(".finalSubmit").attr("value","Loading...")}),$("form input").keydown(function(e){if(13==e.keyCode)return e.preventDefault(),!1}),$("select").keydown(function(e){if(13==e.keyCode)return e.preventDefault(),!1}),$(".business_types").change(function(){var e=$(this).children("option:selected").val();switch($(".custom_industry").attr("hidden","others"!=e),$(".industryType").val(e),"ecommerce_retail"==e?$(".urlLabel").html("Store URL <sup class='text-danger'>*</sup>"):$(".urlLabel").html("Landing page URL <sup class='text-danger'>*</sup>"),$(".prev_url").attr("disabled",""==e),$(".event_type").attr("disabled",""==e),e){case"ecommerce_retail":$(".urlLabel").text("Store URL"),$(".event_type").find("option").remove().end(),$(".event_type").append('<option value="">Select event type...</option>'),$(".event_type").append('<option value="registration">Registration</option>'),$(".event_type").append('<option value="purchase">Purchase</option>'),$(".event_type").append('<option value="custom">Custom</option>');break;case"gaming":$(".event_type").find("option").remove().end(),$(".event_type").append('<option value="">Select event type...</option>'),$(".event_type").append('<option value="registration">Registration</option>'),$(".event_type").append('<option value="gameplay">Gameplay</option>'),$(".event_type").append('<option value="deposit">Deposit</option>'),$(".event_type").append('<option value="custom">Custom</option>');break;case"travel":$(".event_type").find("option").remove().end(),$(".event_type").append('<option value="">Select event type...</option>'),$(".event_type").append('<option value="registration">Registration</option>'),$(".event_type").append('<option value="booking">Booking</option>'),$(".event_type").append('<option value="custom">Custom</option>');break;case"entertainment_music":case"financial_services":case"business_productivity":case"transportation":$(".event_type").find("option").remove().end(),$(".event_type").append('<option value="">Select event type...</option>'),$(".event_type").append('<option value="registration">Registration</option>'),$(".event_type").append('<option value="custom">Custom</option>');break;case"food_drink":$(".event_type").find("option").remove().end(),$(".event_type").append('<option value="">Select event type...</option>'),$(".event_type").append('<option value="custom">Custom</option>');break;default:$(".event_type").find("option").remove().end(),$(".event_type").append('<option value="">Select event type...</option>'),$(".event_type").append('<option value="registration">Registration</option>'),$(".event_type").append('<option value="purchase">Purchase</option>'),$(".event_type").append('<option value="custom">Custom</option>')}}),$(".event_type").change(function(){var e=$(this).children("option:selected").val();$(".custom_event").attr("hidden","custom"!=e),$(".customEvent").val(e)})});

$(document).ready(function(){
	$('.showPassword').click(function(){
		let isShow = $(this).data('isshow'), 
			show = $(this).data('show'), 
			hide = $(this).data('hide'),
			target = $(this).data('target-password');
		if (isShow) {
			$(target).prop('type', 'text');
			$(show).show();
			$(hide).hide();
		} else {
			$(target).prop('type', 'password');
			$(show).show();
			$(hide).hide();
		}
	})

	$("#pass").on("focus keyup", function () {
		var score = 0;
		var a = $(this).val();
		var desc = [];

		desc[0] = "Too short";
		desc[1] = "Weak";
		desc[2] = "Medium";
		desc[3] = "Strong";
		
		if (a.length >= 1) {
			$("#pwd_strength_wrap").fadeIn(200);
		} else {
			$("#pwd_strength_wrap").fadeOut(200);
		}
		 
		if (a.length >= 8) {
			$("#length").removeClass("invalid").addClass("valid");
			score++;
		} else {
			$("#length").removeClass("valid").addClass("invalid");
		}
 
		if (a.match(/\d/)) {
			$("#pnum").removeClass("invalid").addClass("valid");
			score++;
		} else {
			$("#pnum").removeClass("valid").addClass("invalid");
		}

		if ( a.match(/.[!,@,#,$,%,^,&,*,?,_,~,-,(,)]/) ) {
			$("#spchar").removeClass("invalid").addClass("valid");
			score++;
		} else {
			$("#spchar").removeClass("valid").addClass("invalid");
		}
 
		if (a.length > 0) {
			$("#passwordDescription").text('Password Strength: ' + desc[score]);
			$("#passwordStrength").removeClass().addClass("strength" + score);
		} else {
			$("#passwordDescription").text("Password not entered");
			$("#passwordStrength").removeClass().addClass("strength" + score);
		}
	});

	$("#pass").blur(function () {
		$("#pwd_strength_wrap").fadeOut(200);
	});
})