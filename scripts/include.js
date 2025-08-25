// Incluye nav.html y footer.html en los divs correspondientes
function includeHTML(id, file) {
  fetch(file)
    .then(res => res.text())
    .then(html => {
      document.getElementById(id).innerHTML = html;
    });
}
document.addEventListener('DOMContentLoaded', function() {
  if(document.getElementById('nav-include')) includeHTML('nav-include', 'nav.html');
  if(document.getElementById('footer-include')) includeHTML('footer-include', 'footer.html');
});
