$(document).ready(() => {
  const btnFilter = $(".btn-filter");
  const galleryEl = document.querySelector(".swiper.gallery");
  let swiper = null;

  if (galleryEl && typeof Swiper !== "undefined") {
    swiper = new Swiper(".swiper.gallery", {
      slidesPerView: 1,
      spaceBetween: 10,
      grabCursor: true,
      loop: false,
      breakpoints: {
        320: {
          slidesPerView: 1,
          spaceBetween: 10,
        },
        480: {
          slidesPerView: 2,
          spaceBetween: 10,
        },
        640: {
          slidesPerView: 3,
          spaceBetween: 15,
        },
      },
    });
  }

  btnFilter.on("click", (e) => {
    e.preventDefault();
    const $this = $(e.currentTarget);
    $this.addClass("active");
    btnFilter.not($this).removeClass("active");
    filtering();
  });

  function filtering() {
    const activeBtn = $(".btn-filter.active");
    let filter = activeBtn.data("filter-by");
    if (filter == "all") {
      $("[data-filter]").show();
      if (swiper && swiper.el) swiper.update();
    } else {
      let filterEL = $("[data-filter='" + filter + "']");
      filterEL.show();
      $("[data-filter]").not(filterEL).hide();
      if (swiper && swiper.el) swiper.update();
    }
  }

  filtering();
});
