/* 汇评测 — 前台微交互 */
(function(){
  'use strict';

  // ── 卡片入场动画（自动挂载 + 交错延迟）──
  function reveal(){
    var reduce = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    // 自动给主要卡片挂 .reveal（页面里手写的 .reveal 也一并处理）
    document.querySelectorAll('.card').forEach(function(e){ e.classList.add('reveal'); });
    var els = [].slice.call(document.querySelectorAll('.reveal'));
    if (reduce || !('IntersectionObserver' in window) || !els.length){
      els.forEach(function(e){ e.classList.add('in'); }); return;
    }
    // 同一批进入视口的元素按顺序交错 60ms
    var batch = 0, lastT = 0;
    var io = new IntersectionObserver(function(en){
      var now = performance.now();
      if (now - lastT > 200) batch = 0;      // 新一批
      lastT = now;
      en.forEach(function(x){
        if(!x.isIntersecting) return;
        x.target.style.setProperty('--d', (batch++ % 6) * 0.06 + 's');
        x.target.classList.add('in');
        io.unobserve(x.target);
      });
    }, {threshold:.08, rootMargin:'0px 0px -4% 0px'});
    els.forEach(function(e){ io.observe(e); });
  }

  // ── 滚动视差（rAF 节流写入 --sy，CSS 端消费）──
  function parallax(){
    if (window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;
    var root = document.documentElement, ticking = false;
    function update(){
      root.style.setProperty('--sy', window.scrollY || 0);
      ticking = false;
    }
    window.addEventListener('scroll', function(){
      if (!ticking){ ticking = true; requestAnimationFrame(update); }
    }, {passive:true});
    update();
  }

  // ── 交互星标（打分）──
  function initRateStars(){
    var box = document.querySelector('.rate-stars');
    if (!box) return;
    var stars = [].slice.call(box.querySelectorAll('.rs'));
    var input = document.getElementById('rateStars');
    var word  = document.getElementById('rateWord');
    var words = {1:'很差',2:'较差',3:'还行',4:'推荐',5:'力荐'};
    function paint(n, cls){
      stars.forEach(function(s,i){
        s.classList.remove('hover','on');
        if (i < n) s.classList.add(cls);
      });
    }
    var current = parseInt(input && input.value || '0', 10) || 0;
    if (current) { paint(current,'on'); if(word) word.textContent = words[current]; }
    stars.forEach(function(s,i){
      s.addEventListener('mouseenter', function(){ paint(i+1,'hover'); if(word) word.textContent = words[i+1]; });
      s.addEventListener('click', function(){
        current = i+1;
        if (input) input.value = current;
        paint(current,'on');
        if(word) word.textContent = words[current];
      });
    });
    box.addEventListener('mouseleave', function(){
      paint(current,'on');
      if(word) word.textContent = current ? words[current] : '点击星星打分';
    });
  }

  // ── 提交测评/打分 ──
  function initRateSubmit(){
    var form = document.getElementById('rateForm');
    if (!form) return;
    form.addEventListener('submit', function(e){
      e.preventDefault();
      var stars = parseInt((document.getElementById('rateStars')||{}).value || '0', 10);
      if (!stars){ alert('请先点击星星打分'); return; }
      var content = (document.getElementById('rateContent')||{}).value || '';
      var btn = form.querySelector('button[type=submit]');
      if (btn) btn.disabled = true;
      fetch('/api/rate.php', {
        method:'POST', headers:{'Content-Type':'application/json'},
        body: JSON.stringify({broker_id: form.dataset.broker, stars: stars, content: content})
      }).then(function(r){ return r.json(); }).then(function(d){
        if (d.ok){ location.reload(); }
        else { alert(d.msg || '提交失败'); if(btn) btn.disabled = false; }
      }).catch(function(){ alert('网络错误'); if(btn) btn.disabled = false; });
    });
  }

  // ── 「有用」投票 ──
  function initUseful(){
    document.querySelectorAll('.review-useful').forEach(function(el){
      el.addEventListener('click', function(){
        if (el.classList.contains('voted')) return;
        var id = el.dataset.review;
        fetch('/api/review_useful.php', {
          method:'POST', headers:{'Content-Type':'application/json'},
          body: JSON.stringify({review_id:id})
        }).then(function(r){ return r.json(); }).then(function(d){
          if (d.ok){
            el.classList.add('voted');
            var c = el.querySelector('.uc'); if(c) c.textContent = d.count;
          } else if (d.need_login){
            location.href = '/login.php?next=' + encodeURIComponent(location.pathname + location.search);
          } else { alert(d.msg || '操作失败'); }
        }).catch(function(){});
      });
    });
  }

  function ready(fn){ if(document.readyState!='loading') fn(); else document.addEventListener('DOMContentLoaded', fn); }
  ready(function(){ reveal(); parallax(); initRateStars(); initRateSubmit(); initUseful(); });
})();
