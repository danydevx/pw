document.addEventListener("DOMContentLoaded", function () {
  if (typeof Swiper === "undefined") return;

  var sliderElements = document.querySelectorAll(".js-swiper");
  sliderElements.forEach(function (element) {
    var options = {
      loop: element.dataset.loop === "true",
      speed: Number(element.dataset.speed || 400),
      spaceBetween: Number(element.dataset.space || 16),
      slidesPerView: Number(element.dataset.slides || 1),
      pagination: {
        el: element.querySelector(".swiper-pagination"),
        clickable: true,
      },
      navigation: {
        nextEl: element.querySelector(".swiper-button-next"),
        prevEl: element.querySelector(".swiper-button-prev"),
      },
    };

    new Swiper(element, options);
  });
});
