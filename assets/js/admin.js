/* 汇评测 后台交互 */
function rtCmd(cmd, val){ document.execCommand(cmd, false, val||null); document.getElementById('rtEdit').focus(); }
function rtBlock(tag){ document.execCommand('formatBlock', false, tag); document.getElementById('rtEdit').focus(); }
function rtLink(){ var u=prompt('链接地址 URL:'); if(u) document.execCommand('createLink',false,u); }

// 评分滑块 → 实时聚合总分
function recalcOverall(){
  var rows=document.querySelectorAll('.score-edit[data-dim]');
  var sum=0,w=0;
  rows.forEach(function(r){
    var s=parseFloat(r.querySelector('.sv').textContent)||0;
    var wt=parseFloat(r.dataset.weight)||1;
    sum+=s*wt; w+=wt;
  });
  var ov=w>0?(sum/w):0;
  var ob=document.getElementById('ovVal');
  if(ob && !document.getElementById('ovManual').checked){
    ob.textContent=ov.toFixed(1);
    document.getElementById('overallInput').value=ov.toFixed(1);
  }
}
function bindScores(){
  document.querySelectorAll('.score-edit[data-dim] input[type=range]').forEach(function(rg){
    rg.addEventListener('input',function(){
      rg.closest('.score-edit').querySelector('.sv').textContent=parseFloat(rg.value).toFixed(1);
      recalcOverall();
    });
  });
  var man=document.getElementById('ovManual');
  if(man) man.addEventListener('change',function(){
    document.getElementById('overallInput').readOnly=!man.checked;
    if(!man.checked) recalcOverall();
  });
}

// 保存评测
function saveReview(status){
  var ed=document.getElementById('rtEdit');
  var scores=[];
  document.querySelectorAll('.score-edit[data-dim]').forEach(function(r){
    scores.push({dimension:r.dataset.dim, score:parseFloat(r.querySelector('.sv').textContent)||0,
                 weight:parseFloat(r.dataset.weight)||1});
  });
  var data={
    id:document.getElementById('rvId').value,
    broker_id:document.getElementById('f_broker').value,
    title:document.getElementById('f_title').value.trim(),
    cover:document.getElementById('f_cover').value.trim(),
    verdict:document.getElementById('f_verdict').value.trim(),
    summary:document.getElementById('f_summary').value.trim(),
    tags:document.getElementById('f_tags').value.trim(),
    author:document.getElementById('f_author').value.trim(),
    read_time:parseInt(document.getElementById('f_readtime').value)||5,
    overall_score:document.getElementById('overallInput').value,
    overall_manual:document.getElementById('ovManual').checked?1:0,
    pros:document.getElementById('f_pros').value.split('\n').map(function(s){return s.trim();}).filter(Boolean),
    cons:document.getElementById('f_cons').value.split('\n').map(function(s){return s.trim();}).filter(Boolean),
    content:ed.innerHTML,
    scores:scores,
    status:status,
    publish_at:document.getElementById('f_publish').value
  };
  if(!data.title){ alert('请填写标题'); return; }
  var btn=document.getElementById('saveBtns'); if(btn) btn.style.opacity=.5;
  fetch('/admin/api/review_save.php',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify(data)})
    .then(function(r){return r.json();})
    .then(function(res){
      if(res.ok){ location.href='/admin/reviews.php?saved=1'; }
      else { alert('保存失败：'+(res.error||'未知错误')); if(btn) btn.style.opacity=1; }
    }).catch(function(e){ alert('网络错误：'+e); if(btn) btn.style.opacity=1; });
}

// 通用删除
function admDelete(url, id, name){
  if(!confirm('确定删除「'+name+'」？此操作不可恢复。')) return;
  fetch(url,{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({id:id})})
    .then(function(r){return r.json();})
    .then(function(res){ if(res.ok) location.reload(); else alert('删除失败：'+(res.error||'')); });
}

document.addEventListener('DOMContentLoaded',function(){ bindScores(); recalcOverall(); });
