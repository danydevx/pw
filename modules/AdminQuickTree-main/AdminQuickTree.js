// trigger ajax tree nav
$(document).ready(function () {

  if (window.location !== window.parent.location || $('body').hasClass('modal')) {
    // The page is in an iframe
    return false;
  }

  // spawn page-tree ajax nav after load
  setTimeout(() => {
    $('.page-8- a:not(.pw-ajax-items-loaded)').first().trigger('mouseenter');
  }, 200);

  // trigger again if not set
  $(document).on("mouseenter", ".page-3- > a", function () {
    $('.page-8- a:not(.pw-ajax-items-loaded)').first().trigger('mouseenter');
  });

});


