export function initTooltips() {
  if (!window.bootstrap?.Tooltip) {
    return;
  }

  $('[data-bs-toggle="tooltip"]').each(function () {
    window.bootstrap.Tooltip.getOrCreateInstance(this);
  });
}
