$(document).ready(function(){$(".faq-item").find(".faq-content").hide(),$(".faq-item.open").find(".faq-content").show(),$(".faq-item").on("click",function(e){$(this).is(":not(.open)")&&(e.preventDefault(),$(".faq-more-btn").show(),$(".faq-more").hide(),$(".faq-item.open").find(".faq-content").slideUp("fast"),$(".faq-item.open").removeClass("open"),$(this).addClass("open"),$(this).stop(!0,!0).find(".faq-content").slideDown("fast"))}),$(".faq-more").hide(),$(".faq-more-btn").on("click",function(){event.preventDefault(),$(this).next($(".faq-more")).show(),$(this).hide()}),$(".faq-more-close-btn").on("click",function(){event.preventDefault(),$(".faq-more").hide(),$(".faq-more-btn").show()})});