$(document).ready(function () {
  function wrapLetters(selector = ".animation-heading") {
    $(selector).each(function () {
      const $node = $(this);
      if ($node.data("wrapped")) return;

      const text = $node.text().trim();
      $node.empty();

      // Pisah per kata
      const words = text.split(/\s+/);

      words.forEach((word, wIndex) => {
        const $wordSpan = $("<span/>", { class: "word" });

        // Bungkus setiap huruf
        [...word].forEach((ch) => {
          $wordSpan.append(
            $("<span/>", {
              class: "letter",
              text: ch,
            })
          );
        });

        $node.append($wordSpan);

        // Tambahkan spasi setelah word kecuali terakhir
        if (wIndex < words.length - 1) {
          $node.append(" ");
        }
      });

      $node.data("wrapped", true);
    });
  }

  function animateLettersStacked(
    selector = ".animation-heading",
    pause = 1000,
    duration = 2000
  ) {
    const $letters = $(`${selector} .letter`);
    if (!$letters.length) return;

    const totalDuration = duration;
    const interval = totalDuration / $letters.length;
    let index = 0;

    function step() {
      if (index === 0) {
        $letters.removeClass("active"); // reset
      }

      const $letter = $letters.eq(index);
      $letter[0].offsetHeight; // trigger reflow
      $letter.addClass("active");

      index++;

      if (index < $letters.length) {
        setTimeout(step, interval);
      } else {
        setTimeout(() => {
          index = 0;
          step();
        }, pause);
      }
    }

    step();
  }

  // Eksekusi
  wrapLetters(".animation-heading");
  animateLettersStacked(".animation-heading", 2000, 2000);
});
