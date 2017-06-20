$(document).ready(function () {
    $(".teacher-list").slick({
        infinite: !1,
        slidesToShow: 3,
        slidesToScroll: 3,
        responsive: [{
            breakpoint: 1024,
            settings: {
                dots: !0,
                arrows: !1,
                slidesToShow: 2,
                slidesToScroll: 2
            }
        },
        {
            breakpoint: 640,
            settings: {
                dots: !0,
                arrows: !1,
                slidesToShow: 1,
                slidesToScroll: 1
            }
        }]
    }),
    $(".busi_success-list").slick({
        infinite: !1,
        slidesToShow: 5,
        slidesToScroll: 5,
        responsive: [{
            breakpoint: 1024,
            settings: {
                dots: !0,
                arrows: false,
                slidesToShow: 3,
                slidesToScroll: 3
            }
        },
        {
            breakpoint: 640,
            settings: {
                dots: !0,
                arrows: !1,
                slidesToShow: 2,
                slidesToScroll: 2
            }
        }]
    });
    var s = $(".busi-start-img ul"),
        e = "right_img",
        o = "left_img";
    s.find("li").on({
            mouseover: function (i) {
                i.preventDefault();
                var l = $(this).index();
                0 == l ? (s.removeClass(e), s.addClass(o)) : 1 == l && (s.removeClass(o), s.addClass(e))
            },
            mouseleave: function (i) {
                i.preventDefault(),
                s.removeClass(e),
                s.removeClass(o)
            }
        })
});

// $(document).ready(function () {
//     $(".teacher-list").slick({
//         infinite: !1,
//         slidesToShow: 3,
//         slidesToScroll: 3,
//         responsive: [{
//             breakpoint: 1024,
//             settings: {
//                 dots: !0,
//                 arrows: !1,
//                 slidesToShow: 2,
//                 slidesToScroll: 2
//             }
//         },
//         {
//             breakpoint: 640,
//             settings: {
//                 dots: !0,
//                 arrows: !1,
//                 slidesToShow: 1,
//                 slidesToScroll: 1
//             }
//         }]
//     }),
//     $(".busi_success-list").slick({
//         infinite: !1,
//         slidesToShow: 5,
//         slidesToScroll: 5,
//         responsive: [{
//             breakpoint: 1024,
//             settings: {
//                 dots: !0,
//                 arrows: !1,
//                 slidesToShow: 3,
//                 slidesToScroll: 3
//             }
//         },
//         {
//             breakpoint: 640,
//             settings: {
//                 dots: !0,
//                 arrows: !1,
//                 slidesToShow: 2,
//                 slidesToScroll: 2
//             }
//         }]
//     });
//     var s = $(".busi-start-img ul"),
//         e = "right_img",
//         o = "left_img";
//     s.find("li").on({
//             mouseover: function (i) {
//                 i.preventDefault();
//                 var l = $(this).index();
//                 0 == l ? (s.removeClass(e), s.addClass(o)) : 1 == l && (s.removeClass(o), s.addClass(e))
//             },
//             mouseleave: function (i) {
//                 i.preventDefault(),
//                 s.removeClass(e),
//                 s.removeClass(o)
//             }
//         })
// });