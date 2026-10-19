function renderMath(root = document.body) {
  if (typeof renderMathInElement !== "function") return;

  renderMathInElement(root, {
    delimiters: [
      { left: "$$", right: "$$", display: true },
      { left: "\\[", right: "\\]", display: true },
      { left: "$", right: "$", display: false },
      { left: "\\(", right: "\\)", display: false }
    ],
    throwOnError: false,
    ignoredTags: ["script", "noscript", "style", "textarea", "pre", "code"]
  });
}

document.addEventListener("DOMContentLoaded", function () {
  renderMath(document.body);
});

window.renderMath = renderMath;
