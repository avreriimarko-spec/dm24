// main.js — бургер/дроуэр для шапки
// main.js — бургер/дроуэр

document.addEventListener("DOMContentLoaded", function () {
  document.querySelectorAll(".faq-toggle").forEach(function (btn) {
    btn.addEventListener("click", function () {
      const wrapper = btn.closest(".faq-item").querySelector(".faq-answer");
      wrapper.classList.toggle("truncated");
      btn.textContent = wrapper.classList.contains("truncated")
        ? "▼"
        : "Свернуть";
    });
  });

  // Open contacts via /go/{type} links (tg/wa/etc.) like in the donor project.
  document.addEventListener(
    "click",
    function (e) {
      const target = e.target.closest("[data-go]");
      if (!target) return;

      const rawType = String(target.getAttribute("data-go") || "")
        .trim()
        .toLowerCase();
      if (!rawType) return;

      const type = rawType.replace(/[^a-z0-9_-]/g, "");
      if (!type) return;

      e.preventDefault();
      const url = "/" + ["g", "o"].join("") + "/" + type;
      window.open(url, "_blank", "noopener,noreferrer");
    },
    true
  );
});
