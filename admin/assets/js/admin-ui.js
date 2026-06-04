(function(){
  const THEME_KEY='myshop_admin_theme';
  const SIDEBAR_KEY='myshop_admin_sidebar_collapsed';
  function pageTitle(){
    const h=document.querySelector('.container h1,.container h2,.admin-banners-page h2,.admin-offers-page h2');
    return h ? h.textContent.trim() : 'Admin Panel';
  }
  function applyTheme(theme){
    document.body.classList.toggle('admin-dark', theme==='dark');
    const btn=document.getElementById('adminThemeToggle');
    if(btn) btn.innerHTML = theme==='dark' ? '☀️ <span>Light</span>' : '🌙 <span>Dark</span>';
  }
  window.toggleAdminTheme=function(){
    const next=document.body.classList.contains('admin-dark')?'light':'dark';
    localStorage.setItem(THEME_KEY,next); applyTheme(next);
    window.showAdminToast && window.showAdminToast(next==='dark'?'Dark theme enabled':'Light theme enabled','success');
  };
  window.toggleAdminSidebar=function(){
    const sidebar=document.getElementById('adminSidebar');
    if(!sidebar) return;
    if(window.innerWidth<=991){ sidebar.classList.toggle('mobile-open'); return; }
    sidebar.classList.toggle('collapsed');
    localStorage.setItem(SIDEBAR_KEY, sidebar.classList.contains('collapsed')?'1':'0');
  };
  window.showAdminToast=function(message,type='success'){
    const old=document.querySelector('.admin-toast'); if(old) old.remove();
    const div=document.createElement('div'); div.className='admin-toast '+type; div.textContent=message; document.body.appendChild(div);
    setTimeout(()=>{div.style.opacity='0';div.style.transform='translateY(-10px)';setTimeout(()=>div.remove(),220)},2600);
  };
  window.exportAdminPage=function(){
    const table=document.querySelector('table');
    const title=(pageTitle()||'admin-data').toLowerCase().replace(/[^a-z0-9]+/g,'-').replace(/^-|-$/g,'');
    let csv='';
    if(table){
      const rows=[...table.querySelectorAll('tr')];
      csv=rows.map(row=>[...row.querySelectorAll('th,td')].map(cell=>'"'+cell.innerText.replace(/"/g,'""').replace(/\s+/g,' ').trim()+'"').join(',')).join('\n');
    }else{
      const cards=[...document.querySelectorAll('.card')];
      csv='Section,Value\n'+cards.map((card,i)=>'"Card '+(i+1)+'","'+card.innerText.replace(/"/g,'""').replace(/\s+/g,' ').trim()+'"').join('\n');
    }
    if(!csv.trim()){ showAdminToast('No data available to export','danger'); return; }
    const blob=new Blob(['\ufeff'+csv],{type:'text/csv;charset=utf-8;'});
    const url=URL.createObjectURL(blob); const a=document.createElement('a');
    a.href=url; a.download=title+'-'+new Date().toISOString().slice(0,10)+'.csv'; document.body.appendChild(a); a.click(); a.remove(); URL.revokeObjectURL(url);
    showAdminToast('Export downloaded. Open it in Excel.','success');
  };
  document.addEventListener('DOMContentLoaded',function(){
    document.body.classList.add('admin-body');
    applyTheme(localStorage.getItem(THEME_KEY)||'light');
    const sidebar=document.getElementById('adminSidebar');
    if(sidebar && localStorage.getItem(SIDEBAR_KEY)==='1' && window.innerWidth>991) sidebar.classList.add('collapsed');
    const title=document.getElementById('adminTopTitle'); if(title) title.textContent=pageTitle();
    document.addEventListener('click',e=>{ if(window.innerWidth<=991 && sidebar && sidebar.classList.contains('mobile-open') && !sidebar.contains(e.target) && !e.target.closest('.admin-toggle')) sidebar.classList.remove('mobile-open'); });
  });
})();
