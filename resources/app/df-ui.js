/**
 * Diálogos DoceFlow (SweetAlert2) alinhados ao tema.
 */
(function (global) {
  function primaryColor() {
    const v = getComputedStyle(document.documentElement).getPropertyValue('--primary');
    return (v && v.trim()) || '#EC4899';
  }

  const base = {
    customClass: {
      popup: 'rounded-2xl shadow-lg border border-theme-light',
      title: 'font-heading text-theme-primary',
      htmlContainer: 'text-sm text-theme-text',
      confirmButton: 'rounded-xl font-semibold px-5 py-2',
      cancelButton: 'rounded-xl font-semibold px-5 py-2',
    },
    buttonsStyling: true,
    confirmButtonColor: primaryColor(),
    cancelButtonColor: '#94a3b8',
  };

  global.dfAlert = function (title, text, icon) {
    if (typeof global.Swal === 'undefined') {
      global.alert(text ? title + '\n' + text : title);
      return Promise.resolve();
    }
    return global.Swal.fire({
      ...base,
      icon: icon || 'info',
      title: title || '',
      text: text || undefined,
    });
  };

  global.dfToast = function (title, icon) {
    if (typeof global.Swal === 'undefined') {
      return Promise.resolve();
    }
    return global.Swal.fire({
      toast: true,
      position: 'top-end',
      icon: icon || 'success',
      title: title,
      showConfirmButton: false,
      timer: 3000,
      timerProgressBar: true,
    });
  };

  global.dfConfirm = function (title, text, options) {
    options = options || {};
    if (typeof global.Swal === 'undefined') {
      return Promise.resolve(global.confirm(text ? title + '\n' + text : title));
    }
    return global.Swal.fire({
      ...base,
      icon: options.icon || 'warning',
      title: title || 'Confirmar',
      text: text || undefined,
      showCancelButton: true,
      confirmButtonText: options.confirmText || 'Sim',
      cancelButtonText: options.cancelText || 'Cancelar',
      reverseButtons: true,
      focusCancel: true,
    }).then(function (r) {
      return r.isConfirmed;
    });
  };
})(window);
