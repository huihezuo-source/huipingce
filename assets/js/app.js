/* 汇评测 — 微交互 */
(function(){
  'use strict';
  var reduce = window.matchMedia && window.matchMedia('(prefers-reduced-motion:reduce)').matches;

  // 顶栏滚动阴影
  var hdr = document.getElementById('hdr');
  function onScroll(){ if(hdr) hdr.classList.toggle('stuck', window.scrollY > 8); }
  window.addEventListener('scroll', onScroll, {passive:true}); onScroll();

  // 移动菜单
  window.hpcToggleMenu = function(){
    var nav = document.getElementById('mNav'), btn = document.getElementById('mBtn');
    if(!nav) return;
    nav.classList.toggle('open'); if(btn) btn.classList.toggle('on');
  };

  // 评分数字滚动
  function countUp(el){
    var target=parseFloat(el.dataset.count);
    if(isNaN(target)) return;
    var dec=(el.dataset.dec!=null)?parseInt(el.dataset.dec):1;
    if(reduce){ el.textContent=target.toFixed(dec); return; }
    var start=null, dur=900;
    function step(ts){
      if(!start) start=ts;
      var p=Math.min((ts-start)/dur,1), e=1-Math.pow(1-p,3);
      el.textContent=(target*e).toFixed(dec);
      if(p<1) requestAnimationFrame(step); else el.textContent=target.toFixed(dec);
    }
    requestAnimationFrame(step);
  }

  function fillRing(el){
    var p=parseFloat(el.dataset.p)||0;
    if(reduce){ el.style.setProperty('--p',p); return; }
    var start=null,dur=900;
    function step(ts){ if(!start)start=ts; var t=Math.min((ts-start)/dur,1),e=1-Math.pow(1-t,3);
      el.style.setProperty('--p',(p*e).toFixed(2)); if(t<1)requestAnimationFrame(step);}
    requestAnimationFrame(step);
  }

  if('IntersectionObserver' in window){
    var io=new IntersectionObserver(function(entries){
      entries.forEach(function(en){
        if(!en.isIntersecting) return;
        var el=en.target;
        el.classList.add('in');
        if(el.dataset.count!=null) countUp(el);
        if(el.classList.contains('ring')) fillRing(el);
        if(el.classList.contains('bar-fill')) el.style.width=(el.dataset.w||0)+'%';
        io.unobserve(el);
      });
    },{threshold:.18,rootMargin:'0px 0px -40px 0px'});
    document.querySelectorAll('.reveal,[data-count],.ring,.bar-fill').forEach(function(el){io.observe(el);});
  } else {
    document.querySelectorAll('.reveal').forEach(function(el){el.classList.add('in');});
    document.querySelectorAll('.ring').forEach(function(el){el.style.setProperty('--p',el.dataset.p||0);});
    document.querySelectorAll('.bar-fill').forEach(function(el){el.style.width=(el.dataset.w||0)+'%';});
    document.querySelectorAll('[data-count]').forEach(function(el){
      var t=parseFloat(el.dataset.count),d=(el.dataset.dec!=null)?+el.dataset.dec:1;
      if(!isNaN(t)) el.textContent=t.toFixed(d);
    });
  }

  // 列表即时搜索（data-search-list 容器 + .js-search 输入）
  document.querySelectorAll('[data-search-input]').forEach(function(inp){
    var sel=inp.getAttribute('data-search-input');
    var list=document.querySelectorAll(sel);
    inp.addEventListener('input',function(){
      var q=inp.value.trim().toLowerCase();
      list.forEach(function(item){
        var t=(item.getAttribute('data-search')||item.textContent).toLowerCase();
        item.style.display = (!q||t.indexOf(q)>-1)?'':'none';
      });
    });
  });
})();
