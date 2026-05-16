$(document).ready(function () {
  const container = $(".marquee-container");
  container.each(function () {
    let cont = $(this);
    const content = cont.find(".marquee-content");
    const clone = content.clone();
    const clone2 = clone.clone();
    cont.append(clone);
    cont.append(clone2);

    content.addClass("marquee");
    clone.addClass("marquee");
    clone2.addClass("marquee");
  });
});
