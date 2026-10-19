
(function () {
  if ("serviceWorker" in navigator && (location.protocol === "https:" || location.hostname === "localhost")) {
    window.addEventListener("load", () => {
      const scriptUrl = document.currentScript ? document.currentScript.src : "";
      const siteRoot = scriptUrl ? new URL("../../", scriptUrl) : new URL("./", location.href);
      const workerUrl = new URL("service-worker.js", siteRoot);
      navigator.serviceWorker.register(workerUrl, { scope: siteRoot.pathname }).catch(() => {});
    });
  }
})();
