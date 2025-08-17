// Tự động import các phần dùng chung vào trang
function includeHTML() {
  const includes = [
    { selector: '#header-partial', file: 'partials/header.html' },
    { selector: '#sidebar-partial', file: 'partials/sidebar.html' },
    { selector: '#scripts-partial', file: 'partials/scripts.html' }
  ];
  includes.forEach(inc => {
    const el = document.querySelector(inc.selector);
    if (el) {
      fetch(inc.file)
        .then(res => res.text())
        .then(data => { el.innerHTML = data; });
    }
  });
}
document.addEventListener('DOMContentLoaded', includeHTML);
