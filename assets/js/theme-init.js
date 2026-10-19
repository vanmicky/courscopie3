try {
  document.documentElement.classList.toggle('dark', localStorage.getItem('theme') === 'dark');
} catch (error) {}
// Le menu mobile passe en tiroir seulement si JavaScript est disponible :
// sans JavaScript, il reste une bande horizontale visible.
document.documentElement.classList.add('js-nav');
