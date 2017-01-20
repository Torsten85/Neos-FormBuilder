const repatchaReady = function () {
  const widgets = document.querySelectorAll('.g-recaptcha');
  for (const widget of widgets) {

    const sitekey = widget.dataset.sitekey;
    const theme = widget.dataset.theme;
    const input = widget.nextElementSibling;

    grecaptcha.render(widget, {
      sitekey,
      theme,
      callback(response) {
        input.value = response;
      },
      'expired-callback'() {
        input.value = '';
      }
    });
  }
};

// Neos Backend integration
document.addEventListener('Neos.PageLoaded', repatchaReady, false);