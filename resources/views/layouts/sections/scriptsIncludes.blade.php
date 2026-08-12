@php
use Illuminate\Support\Facades\Vite;
@endphp
<!-- Restaurar el estado colapsado del sidebar antes del render (evita parpadeo al navegar) -->
<script>
  (function () {
    try {
      if (window.innerWidth >= 1200 && localStorage.getItem('menuCollapsed') === 'true') {
        document.documentElement.classList.add('layout-menu-collapsed');
      }
    } catch (e) {}
  })();
</script>
<!-- laravel style -->
@vite(['resources/assets/vendor/js/helpers.js'])

<!--? Config:  Mandatory theme config file contain global vars & default theme options, Set your preferred theme option in this file.  -->
@vite(['resources/assets/js/config.js'])

<!-- Place this tag in your head or just before your close body tag. -->
<script async defer src="https://buttons.github.io/buttons.js"></script>
