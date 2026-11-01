// HEBE Admin JS
document.getElementById('sidebarToggle')?.addEventListener('click', () => {
  document.querySelector('.admin-sidebar').classList.toggle('open');
});
const dark = document.getElementById('darkToggle');
if(dark){
  if(localStorage.getItem('hebeDark')==='1') document.body.classList.add('dark');
  dark.addEventListener('click', () => {
    document.body.classList.toggle('dark');
    localStorage.setItem('hebeDark', document.body.classList.contains('dark') ? '1' : '0');
  });
}
// Confirm deletions
document.addEventListener('submit', e => {
  if(e.target.matches('[data-confirm]')) {
    if(!confirm(e.target.dataset.confirm || 'Are you sure?')) e.preventDefault();
  }
});
