// script.js
// simple helper: prevent accidental double submit
document.addEventListener('submit', function(e){
  const btn = e.target.querySelector('button[type="submit"]');
  if(btn){ btn.disabled = true; setTimeout(()=>btn.disabled=false, 1000); }
});
