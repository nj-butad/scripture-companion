const widget = document.querySelector('#chatWidget');
const panel = document.querySelector('#chatWidgetPanel');
const launcher = document.querySelector('#openChatWidget');
const frame = panel?.querySelector('.chat-widget-frame');

if (widget && panel && launcher && frame) {
  const setOpen = (open) => {
    panel.hidden = !open;
    launcher.setAttribute('aria-expanded', String(open));
    document.body.classList.toggle('chat-widget-open', open);

    if (open && !frame.hasAttribute('src')) {
      frame.src = frame.dataset.src;
    }
  };

  launcher.addEventListener('click', () => {
    setOpen(panel.hidden);
  });

  document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape' && !panel.hidden) {
      setOpen(false);
      launcher.focus();
    }
  });
}
