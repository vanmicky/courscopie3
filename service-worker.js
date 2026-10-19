/* Service worker — mise en cache prudente.
 *
 * Objectif : accélérer le site sans jamais figer un ancien fichier.
 *
 *  • Pages HTML / PHP : jamais mises en cache (réseau d'abord, page hors-ligne
 *    en secours). Une page en cache empêcherait toute mise à jour visible.
 *  • JS et CSS du site : réseau d'abord, cache en secours. Un fichier corrigé
 *    est donc pris en compte dès le rechargement suivant.
 *  • Ressources stables (polices, icônes) : cache d'abord, elles changent peu.
 *  • Administration : jamais mise en cache.
 */

const CACHE_NAME = "cours-gedion-v124";

const CORE_ASSETS = [
  "./offline.php",
  "./manifest.webmanifest",
  "./assets/fonts/inter-var-latin.woff2",
  "./assets/fonts/krub-700-latin.woff2",
  "./assets/fonts/krub-600-latin.woff2",
  "./assets/img/icons/icon-192.png"
];

/** Ressources servies depuis le cache sans aller sur le réseau. */
const IMMUABLES = /\.(woff2?|png|jpe?g|webp|avif|svg|ico)$/i;

self.addEventListener("install", event => {
  event.waitUntil(
    caches.open(CACHE_NAME)
      .then(cache => cache.addAll(CORE_ASSETS))
      .then(() => self.skipWaiting())
  );
});

self.addEventListener("activate", event => {
  event.waitUntil(
    caches.keys()
      .then(keys => Promise.all(keys.filter(k => k !== CACHE_NAME).map(k => caches.delete(k))))
      .then(() => self.clients.claim())
  );
});

self.addEventListener("fetch", event => {
  const req = event.request;
  if (req.method !== "GET") return;

  const url = new URL(req.url);
  if (url.origin !== self.location.origin) return;

  // L'administration n'est jamais mise en cache.
  if (url.pathname.includes("/admin/")) return;

  // Pages : réseau d'abord, page hors-ligne en secours.
  if (req.mode === "navigate" || /\.(php|html)$/i.test(url.pathname) || url.pathname.endsWith("/")) {
    event.respondWith(fetch(req).catch(() => caches.match("./offline.php")));
    return;
  }

  // Polices et images : cache d'abord.
  if (IMMUABLES.test(url.pathname)) {
    event.respondWith(
      caches.match(req).then(cached => cached || fetch(req).then(response => {
        if (response.ok) {
          const copy = response.clone();
          caches.open(CACHE_NAME).then(cache => cache.put(req, copy));
        }
        return response;
      }))
    );
    return;
  }

  // JS et CSS : réseau d'abord, cache en secours (mise à jour immédiate).
  event.respondWith(
    fetch(req).then(response => {
      if (response.ok) {
        const copy = response.clone();
        caches.open(CACHE_NAME).then(cache => cache.put(req, copy));
      }
      return response;
    }).catch(() => caches.match(req))
  );
});
