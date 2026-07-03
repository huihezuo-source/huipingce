/* 汇评测 — 前台微交互 */
(function(){
  'use strict';

  // ── 进场动画 ──
  function reveal(){
    var els = document.querySelectorAll('.reveal');
    if (!('IntersectionObserver' in window) || !els.length){
      els.forEach(function(e){ e.classList.add('in'); }); return;
    }
    var io = new IntersectionObserver(function(en){
      en.forEach(function(x){ if(x.isIntersecting){ x.target.classList.add('in'); io.unobserve(x.target); } });
    }, {threshold:.08});
    els.forEach(function(e){ io.observe(e); });
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
  ready(function(){ reveal(); initRateStars(); initRateSubmit(); initUseful(); });
})();
