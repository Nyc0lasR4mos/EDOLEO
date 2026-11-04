const menuToggle = document.querySelector('.menu-toggle');
const mainNav = document.querySelector('.main-nav');

menuToggle?.addEventListener('click', () => {
  const isOpen = mainNav?.classList.toggle('open') ?? false;
  menuToggle.setAttribute('aria-expanded', String(isOpen));
});
