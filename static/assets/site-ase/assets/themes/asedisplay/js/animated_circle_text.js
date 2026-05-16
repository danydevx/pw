(function ($) {
  $.fn.circleText = function () {
    return this.each(function () {
      const $el = $(this);
      const text = $el.text().trim();
      $el.empty();

      const styles = window.getComputedStyle(this);
      const fontSize = parseFloat(styles.fontSize);
      const fontFamily = styles.fontFamily;
      const letterSpacing = styles.letterSpacing;

      // Buat dummy span untuk hitung lebar real teks
      const $dummy = $("<span>", {
        text: text,
        css: {
          fontSize: fontSize + "px",
          fontFamily: fontFamily,
          letterSpacing: letterSpacing,
          visibility: "hidden",
          whiteSpace: "nowrap",
          position: "absolute",
        },
      }).appendTo("body");

      const textWidth = $dummy[0].getBoundingClientRect().width;
      $dummy.remove();

      const length = text.length;

      // keliling = lebar teks asli
      const circumference = textWidth;
      const radius = circumference / (2 * Math.PI);
      const circleSize = radius * 2 + fontSize * 2;

      // set ukuran lingkaran
      $el.css("--circle-size", circleSize + "px");
      $el.parent().css({
        width: circleSize + "px",
        height: circleSize + "px",
      });

      // generate span per huruf (sudut rata)
      for (let i = 0; i < length; i++) {
        const angle = (360 / length) * i;
        const $span = $("<span>", {
          text: text[i],
          css: {
            transform: `rotate(${angle}deg) translate(${radius}px) rotate(90deg)`,
            fontSize: fontSize + "px",
            fontFamily: fontFamily,
            letterSpacing: letterSpacing,
          },
        });
        $el.append($span);
      }
    });
  };
})(jQuery);

$(document).ready(function () {
  $("#circleText").circleText();
});
