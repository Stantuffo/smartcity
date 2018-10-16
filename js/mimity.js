
// Get breakpoints from body:before content =======================================================================================
var breakpoint = {};
breakpoint.refreshValue = function () {
  this.value = window.getComputedStyle(document.querySelector('body'), ':before').getPropertyValue('content').replace(/\"/g, '');
};
// ================================================================================================================================


// Make search input smaller (sm & xs only) =======================
var minifyInputSearch = function() {
  if ((breakpoint.value == 'sm') || (breakpoint.value == 'xs')) {
    $('.input-group-search').addClass('input-group-sm');
  } else {
    $('.input-group-search').removeClass('input-group-sm');
  }
};
// ================================================================


// Set owl-cover background and height =======================================================
var setupOwlCover = function() {
  $('.owl-cover').each(function() {
    var owlCover = $(this);
    owlCover.css('background-image', 'url(' + decodeURIComponent(owlCover.data('src')) + ')');
    if (owlCover.attr('data-height')) owlCover.css('height', owlCover.data('height'));
    switch (breakpoint.value) {
      case 'xs':
        if (owlCover.attr('data-xs-height')) owlCover.css('height', owlCover.data('xs-height'));
        break;
      case 'sm':
        if (owlCover.attr('data-sm-height')) owlCover.css('height', owlCover.data('sm-height'));
        break;
      case 'md':
        if (owlCover.attr('data-md-height')) owlCover.css('height', owlCover.data('md-height'));
        break;
      case 'lg':
        if (owlCover.attr('data-lg-height')) owlCover.css('height', owlCover.data('lg-height'));
        break;
      case 'xl':
        if (owlCover.attr('data-xl-height')) owlCover.css('height', owlCover.data('xl-height'));
        break;
    }
  });
}
// ===========================================================================================


// Remove classes that attached to remove-class-on-*breakpoint* attribute ====================
var removeClassOn = function() {
  var bps = ['xs','sm','md','lg','xl'];
  bps.forEach(function(bp) {
    $('[remove-class-on-'+bp+']').each(function() {
      var thisVal = $(this).attr('remove-class-on-'+bp);
      if ((breakpoint.value == bp)) {
        $(this).removeClass(thisVal);
      } else {
        $(this).addClass(thisVal);
      }
    });
  });
}
// ===========================================================================================


// Add classes that attached to add-class-on-*breakpoint* attribute ==========================
var addClassOn = function() {
  var bps = ['xs','sm','md','lg','xl'];
  bps.forEach(function(bp) {
    $('[add-class-on-'+bp+']').each(function() {
      var thisVal = $(this).attr('add-class-on-'+bp);
      if ((breakpoint.value == bp)) {
        $(this).addClass(thisVal);
      } else {
        $(this).removeClass(thisVal);
      }
    });
  });
}
// ===========================================================================================


// Function to convert select element with 'select-dropdown' class to bootstrap dropdown =====================================================
var dropdownSelect = function() {
  var i = 1;
  $('.select-dropdown').each(function() {
    var t = $(this), tV = t.val();

    var tS = t.data('size');
    var tS = (tS == undefined) || (tS == '') ? '' : ' btn-'+tS;

    var tW = t.data('width');
    var tW = (tW == undefined) || (tW == '') ? 'min-width:10rem' : 'width:'+tW;

    var tMW = t.data('width');
    var tMW = (tMW == undefined) || (tMW == '') ? '' : 'style="min-width:'+tMW+'"';

    var sB = t.find('option:selected').data('before');
    var sB = (sB == undefined) || (sB == '') ? '' : sB;

    var st = sB+t.find('option:selected').html();

    var dI = '';
    t.find('option').each(function() {
      var iB = ($(this).data('before') == undefined) || ($(this).data('before') == '') ? '' : $(this).data('before');
      var ac = $(this).val() == tV ? ' active' : '';
      dI += '<button class="dropdown-item'+ac+'" type="button" data-value="'+$(this).val()+'">'+iB+$(this).html()+'</button>';
    });
    if (t.parent('.input-group-prepend').length) {
      t.parent('.input-group-prepend').addClass('dropdown dropdown-select');
      var dD = '<button class="btn btn-outline-default btn-select text-right dropdown-toggle'+tS+'" type="button" id="dropdownSelect'+i+'" \
                  data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" style="'+tW+'">\
                  <span class="float-left">'+st+'</span>\
                </button>\
                <div class="smooth dropdown-menu" aria-labelledby="dropdownSelect'+i+'" '+tMW+'>\
                  '+dI+'\
                </div>';
    } else {
      var dD = '<div class="dropdown dropdown-select" style="'+tW+'">\
                  <button class="btn btn-outline-default btn-select text-right dropdown-toggle'+tS+'" type="button" id="dropdownSelect'+i+'" \
                    data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" style="'+tW+'">\
                    <span class="float-left">'+st+'</span>\
                  </button>\
                  <div class="smooth dropdown-menu" aria-labelledby="dropdownSelect'+i+'" '+tMW+'>\
                    '+dI+'\
                  </div>\
                </div>';
    }
    var dD = dD.replace(/>[\n\t ]+</g, "><");
    t.prop('hidden',true);
    t.before(dD);
    i++;
  });
  $('.dropdown-select').each(function() {
    var t = $(this);
    var s = t.siblings('.select-dropdown').length ? t.siblings('.select-dropdown') : t.find('.select-dropdown');
    t.find('.dropdown-item').click(function() {
      var tI = $(this), tC = tI.html(), tV = tI.data('value');
      if (s.val() != tV) {
        tI.parents('.dropdown').find('.dropdown-toggle').html('<span class="float-left">'+tC+'</span>');
        tI.parents('.dropdown').find('.dropdown-item.active').removeClass('active');
        tI.addClass('active');
        s.val(tV);
        s.trigger('change');
      }
    });
  });
}
var dropdownSelectNav = function() {
  var i = 1;
  $('.select-dropdown-nav').each(function() {
    var t = $(this), tV = t.val();

    var tW = t.data('width');
    var tW = (tW == undefined) || (tW == '') ? 'min-width:10rem' : 'width:'+tW;

    var tMW = t.data('width');
    var tMW = (tMW == undefined) || (tMW == '') ? '' : 'style="min-width:'+tMW+'"';

    var sB = t.find('option:selected').data('before');
    var sB = (sB == undefined) || (sB == '') ? '' : sB;

    var st = sB+t.find('option:selected').html();

    var dI = '';
    t.find('option').each(function() {
      var iB = ($(this).data('before') == undefined) || ($(this).data('before') == '') ? '' : $(this).data('before');
      var ac = $(this).val() == tV ? ' active' : '';
      dI += '<button class="dropdown-item'+ac+'" data-value="'+$(this).val()+'">'+iB+$(this).html()+'</button>';
    });
    var dD = '<li class="nav-item dropdown dropdown-select-nav" style="'+tW+'">\
                <a href="#" class="nav-link text-right dropdown-toggle" role="button" \
                  data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" style="'+tW+'">\
                  <span class="float-left">'+st+'</span>\
                </a>\
                <div class="smooth dropdown-menu"  '+tMW+'>\
                  '+dI+'\
                </div>\
              </li>';
    var dD = dD.replace(/>[\n\t ]+</g, "><");
    t.prop('hidden',true);
    t.after(dD);
    i++;
  });
  $('.dropdown-select-nav').each(function() {
    var t = $(this);
    var s = t.prev();
    t.find('.dropdown-item').click(function() {
      var tI = $(this), tC = tI.html(), tV = tI.data('value');
      if (s.val() != tV) {
        $(this).parents('.dropdown').find('.dropdown-toggle').html('<span class="float-left">'+tC+'</span>');
        tI.parents('.dropdown').find('.dropdown-item.active').removeClass('active');
        tI.addClass('active');
        s.val(tV);
        s.trigger('change');
      }
    });
  });
}
// ===========================================================================================================================================


// Toggle offcanvas menu button ============================================================================
var toggleMenuBtn = function(action) {
  var pr = $('.menu-btn-wrapper').parent();
  var lw = $('.logo-wrapper').parent();
  if ((breakpoint.value == 'lg') || (breakpoint.value == 'xl')) {
    action == 'show' && (pr.removeClass('d-lg-none'), lw.removeClass('col-lg-3'), lw.addClass('col-lg-2'));
    action == 'hide' && (pr.addClass('d-lg-none'), lw.removeClass('col-lg-2'), lw.addClass('col-lg-3'));
  } else {
    pr.addClass('d-lg-none');
    lw.removeClass('col-lg-2');
  }
};
// =========================================================================================================


// Toggle fixed-top class ==================================================================================================
var mh = $('.middle-header'), mhA = 'animated slideInDown', wp = $('<div></div>'); mh.before(wp);
var ost = wp.offset().top, fixtop = 'fixed-top', lst = $(window).scrollTop();
$(window).on('load scroll resize', function() {
  var mhH = mh.height(), st = $(this).scrollTop();
  if (st < lst) {
    if (st <= ost) {
      if (mh.hasClass(fixtop)) {
        mh.removeClass(fixtop);
        mh.removeClass(mhA);
      }
      wp.height(0);
      toggleMenuBtn('hide');
    };
  } else {
    if (st >= ost + mhH + 55) {
      if (mh.hasClass(fixtop) != true) {
        mh.addClass(mhA);
      };
      mh.addClass(fixtop);
      wp.height(mhH);
      toggleMenuBtn('show');
    };
  };
  lst = st;
});
// =========================================================================================================================


// Animate product grid tools =========================================
var setupCardProduct = function() {
  $('.tools').each(function() {
    var that = $(this);
    if ((breakpoint.value != 'xs') && (breakpoint.value != 'sm')) {
      that.attr('hidden',true);
      that.addClass('animated');
    } else {
      that.attr('hidden',false);
      that.removeClass('animated');
    }
  });
  $('.card-product').hover(function() {
    var tools = $(this).find('.tools'),
        animateIn = tools.data('animate-in'),
        animateOut = tools.data('animate-out'),
        hasAnimated = tools.hasClass('animated');
    if (hasAnimated) {
      tools.attr('hidden',false);
      tools.removeClass(animateOut);
      tools.addClass(animateIn);
    }
  }, function() {
    var tools = $(this).find('.tools'),
        animateIn = tools.data('animate-in'),
        animateOut = tools.data('animate-out'),
        hasAnimated = tools.hasClass('animated');
    if (hasAnimated) {
      tools.removeClass(animateIn);
      tools.addClass(animateOut);
    }
  });
}
// ====================================================================


// Increase and decrease input quantity button ===================================================
var inputQty = function() {
  $('.input-group-qty').each(function() {
    var that = $(this),
        input = that.find('input[type="text"]'),
        down = that.find('.btn-down'),
        up = that.find('.btn-up'),
        min = input.data('min'),
        max = input.data('max'),
        min = (min == undefined) || (min == '') || (min < 0) ? 0 : min,
        max = (max == undefined) || (max == '') || (max < 0) ? 1000 : max; // maximum 1000 qty
    input.change(function() {
      if (!$.isNumeric($(this).val()) || $(this).val() < min) {
        $(this).val(min);
      } else if ($(this).val() > max) {
        $(this).val(max);
      }
    });
    up.click(function() {
      input.val(parseInt(input.val()) + 1).trigger('change');
    });
    down.click(function() {
      input.val(parseInt(input.val()) - 1).trigger('change');
    });
  });
}
// ===============================================================================================


// Refresh breakpoint and run functions on resized ================
$(window).resize(function () {
  breakpoint.refreshValue();
  minifyInputSearch();
  setupOwlCover();
  removeClassOn();
  addClassOn();
  setupCardProduct();
}).resize();
// ================================================================


$(function() {

  // Offcanvas Menu =========================================================================
  var offcanvas      = $('.offcanvas'),
      body           = $('body'),
      container      = $('#container'),
      offcanvasOpen  = 'offcanvas-open';

  var toggleOffcanvas = function() {
    mh.removeClass(mhA);
    setTimeout(function() {
      body.toggleClass(offcanvasOpen); $('html, body').toggleClass('offcanvas-overflow');
    },10);
  };
  $(document).keyup(function(e) {
    if (e.keyCode == 27 && body.hasClass(offcanvasOpen)) { //close menu with esc key
      toggleOffcanvas();
    }
  });
  $('.offcanvas-btn, .content-overlay').on('click', function() {
    toggleOffcanvas();
  });
  $('.list-menu a').addClass('list-group-item');
  $('.list-menu i.fa').addClass('fa-fw');
  // ========================================================================================


  // init
  dropdownSelect();
  dropdownSelectNav();
  $('[data-toggle="tooltip"]').tooltip();
  setupOwlCover();
  removeClassOn();
  addClassOn();
  setupCardProduct();
  inputQty();


  // navbar show dropdown on hover =====================================================
  $('body').on('mouseenter mouseleave','.navbar-theme .nav-item.dropdown',function(e) {
    var _d=$(e.target).closest('.dropdown');_d.addClass('show');
    setTimeout(function() {
      _d[_d.is(':hover')?'addClass':'removeClass']('show');
    },0);
  });
  // ===================================================================================


  // Typeahead example =================================
  $('#search-input').typeahead({
    fitToElement: true,
    source: [
      'U.S. Polo Assn. Green Solid Slim Fit',
      'U.S. Polo Assn. Red Solid Slim Fit',
      'U.S. Polo Assn. Yellow Solid',
      'Red Tape Blue Solid Slim Fit',
      'U.S. Polo Assn. Black Solid Regular Fit',
      'Gas Mustard Yellow Solid Slim Fit',
      'Basics Black Striped',
      'Superdry Blue Solid Slim Fit',
      'Gritstones Olive Solid',
      'Celio Brown Textured',
      'Numero Uno White Striped Regular Fit',
    ]
  });
  // ===================================================


  // home slider =========================================================
  var owl = $('.home-slider');
  if (owl.length) {
    owl.on('initialized.owl.carousel', function(event) {
      owl.find('.owl-item.active').find('.animated').each(function() {
        $(this).addClass($(this).data('animate'));
      });
    });
    owl.owlCarousel({
      items: 1,
      loop: true,
      dots: false,
      nav: true,
      navText: ['<i class="fa fa-angle-left"></i>','<i class="fa fa-angle-right"></i>'],
      autoplay:true,
      // autoplayHoverPause:true,
      autoplayTimeout: 4000
    });
    owl.on('changed.owl.carousel', function(event) {
      var owlItem = owl.find('.owl-item');
      owlItem.find('.animated').each(function() {
        $(this).removeClass($(this).data('animate'));
      });
      owlItem.eq(event.item.index).find('.animated').each(function() {
        $(this).addClass($(this).data('animate'));
      });
    });
  }
  // =====================================================================

  var productSlider = $('.product-slider');
  if (productSlider.length) {
    productSlider.owlCarousel({
      dots: false,
      nav: true,
      navText:['<i class="fa fa-angle-left"></i>','<i class="fa fa-angle-right"></i>'],
      responsive:{
        0:{
          items:2,
        },
        768:{
          items:3,
        },
        992:{
          items:4,
        },
        1200:{
          items:5,
        }
      }
    });
  }
  var brandSlider = $('.brand-slider');
  if (brandSlider.length) {
    brandSlider.owlCarousel({
      dots: false,
      nav: true,
      navText:['<i class="fa fa-angle-left"></i>','<i class="fa fa-angle-right"></i>'],
      responsive:{
        0:{
          items:2,
          margin: 10
        },
        576:{
          items:3,
          margin: 10
        },
        768:{
          items:4,
          margin: 15
        },
        992:{
          items:5,
          margin: 30
        },
        1200:{
          items:6,
          margin: 30
        }
      }
    });
  }

  var quickviewSlider = $('.quickview-slider');
  if (quickviewSlider.length) {
    quickviewSlider.owlCarousel({
      dots: false,
      nav: true,
      navText: ['<i class="fa fa-angle-left"></i>','<i class="fa fa-angle-right"></i>'],
      responsive:{
        0:{
          items:2,
        },
        576:{
          items:1,
        }
      }
    });
  }

  // Example to show quickview modal
  $('.quick-view').click(function(){
    $('#QuickViewModal').modal('show');
  });

  // Price Range
  var priceRange = $('#price');
  if (priceRange.length) {
    var priceRange = document.getElementById('price');
    noUiSlider.create(priceRange, {
      start: [20, 80],
      connect: true,
      range: {
        'min': 0,
        'max': 100
      }
    });
    priceRange.noUiSlider.on('update', function(values, handle) {
      var value = values[handle];
      handle ? $('#max-price').val(Math.round(value)).attr('value', Math.round(value)) : $('#min-price').val(Math.round(value)).attr('value', Math.round(value));
    });
    $('#max-price').on('change', function() {
      priceRange.noUiSlider.set([null, this.value]);
    });
    $('#min-price').on('change', function() {
      priceRange.noUiSlider.set([this.value, null]);
    });
  }

  // Rating Range
  var ratingRange = $('#rating-range');
  if (ratingRange.length) {
    var ratingRange = document.getElementById('rating-range');
    noUiSlider.create(ratingRange, {
      start: [$('#min-range').val(), $('#max-range').val()],
      connect: true,
      orientation: 'vertical',
      snap: true,
      direction: 'rtl',
      range: {
        'min': 1,
        '25%': 2,
        '50%': 3,
        '75%': 4,
        'max': 5,
      },
      pips: {
        mode: 'values',
        values: [1, 2, 3, 4, 5]
      }
    });
    ratingRange.noUiSlider.on('update', function(values, handle) {
      var ratingDom = $('#rating-range');
      ratingDom.find('.noUi-value[style="bottom: 100%;"]').html('<div class="rating"><i class="fa fa-star-o"></i><i class="fa fa-star-o"></i><i class="fa fa-star-o"></i><i class="fa fa-star-o"></i><i class="fa fa-star-o"></i></div>');
      ratingDom.find('.noUi-value[style="bottom: 75%;"]').html('<div class="rating"><i class="fa fa-star-o"></i><i class="fa fa-star-o"></i><i class="fa fa-star-o"></i><i class="fa fa-star-o"></i></div>');
      ratingDom.find('.noUi-value[style="bottom: 50%;"]').html('<div class="rating"><i class="fa fa-star-o"></i><i class="fa fa-star-o"></i><i class="fa fa-star-o"></i></div>');
      ratingDom.find('.noUi-value[style="bottom: 25%;"]').html('<div class="rating"><i class="fa fa-star-o"></i><i class="fa fa-star-o"></i></div>');
      ratingDom.find('.noUi-value[style="bottom: 0%;"]').html('<div class="rating"><i class="fa fa-star-o"></i></div>');
      var value = values[handle];
      handle ? $('#max-range').val(Math.round(value)).attr('value', Math.round(value)) : $('#min-range').val(Math.round(value)).attr('value', Math.round(value));
      var min_range = $('#min-range').val();
      var max_range = $('#max-range').val();
      var max_range = max_range == '' ? min_range : max_range;
      for (var i = min_range; i < parseInt(max_range) + 1; i++) {
        switch(i) {
          case 5: case '5': var percent = '100%'; break;
          case 4: case '4': var percent = '75%'; break;
          case 3: case '3': var percent = '50%'; break;
          case 2: case '2': var percent = '25%'; break;
          case 1: case '1': var percent = '0%'; break;
        }
        ratingDom.find('.noUi-value[style="bottom: '+percent+';"]').find('.fa').addClass('fa-star').removeClass('fa-star-o');
      }
    });
    $('#max-range').on('change', function() {
      ratingRange.noUiSlider.set([null, this.value]);
    });
    $('#min-range').on('change', function() {
      ratingRange.noUiSlider.set([this.value, null]);
    });
  }

  // owlcarosel (if items less than 4, hide nav, disable drag, hide touch)
  var productSliderDetail = $('.products-slider-detail');
  if (productSliderDetail.length) {
    var item_count = $('.products-slider-detail a').length;
    productSliderDetail.owlCarousel({
      margin:5,
      dots:false,
      nav:item_count < 5 ? false : true,
      mouseDrag:item_count < 5 ? false : true,
      touchDrag:item_count < 5 ? false : true,
      navText:['<i class="fa fa-angle-left"></i>','<i class="fa fa-angle-right"></i>'],
      responsive:{
          0:{ items:4, }
      }
    });
    $('.products-slider-detail a').click(function(){
      var src = $(this).find('img').attr('src');
      var zoom = $(this).find('img').attr('data-zoom-image');
      var detail = $('.image-detail');
      detail.attr('src',src);
      detail.attr('data-zoom-image',zoom);
      $('.zoomWindow').css('background-image', 'url("' + zoom + '")');
      return false;
    });
  }

  // Input Rating for Review
  var inputRating = $('.input-rating');
  if (inputRating.length) {
    inputRating.raty({
      'half': true,
      'starType' : 'i'
    });
  }

  // zoom image
  var imageDetail = $('.image-detail');
  if (imageDetail.length) {
    if ((breakpoint.value != 'sm') && (breakpoint.value != 'xs')) {
      $('.image-detail').ezPlus({
        responsive : true,
        respond: [
          {
            range: '1200-10000',
            zoomWindowHeight: 477,
            zoomWindowWidth: 762
          },
          {
            range: '992-1200',
            zoomWindowHeight: 504,
            zoomWindowWidth: 562
          },
          {
            range: '768-992',
            zoomWindowHeight: 449,
            zoomWindowWidth: 362
          },
          {
            range: '100-768',
            zoomWindowHeight: 0,
            zoomWindowWidth: 0
          }
        ]
      });
    }
  }

  // Back top Top
  $(window).scroll(function() {
    if ($(this).scrollTop() > 100) {
      $('.back-top').fadeIn();
    } else {
      $('.back-top').fadeOut();
    }
  });

});