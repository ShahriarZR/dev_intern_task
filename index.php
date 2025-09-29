<?php
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Office Task Tracker</title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <style>
    :root{
      --bg:#f8fafc;
      --card:#ffffff;
      --text:#0f172a;
      --muted:#64748b;
      --border:#e2e8f0;
      --ring:#2563eb;
      --primary:#2563eb;
      --primary-hover:#1e50c4;
      --danger:#ef4444;
      --danger-hover:#dc2626;
      --success:#22c55e;
      --error:#ef4444;
      --toast-fg:#ffffff;
    }
    *{ box-sizing:border-box; }
    body{
      margin:0;
      font-family: ui-sans-serif, system-ui, -apple-system, Segoe UI, Roboto, Helvetica, Arial, "Apple Color Emoji", "Segoe UI Emoji";
      background:var(--bg);
      color:var(--text);
    }
    .container{
      max-width: 920px;
      margin: 40px auto;
      padding: 0 16px;
    }
    .page-title{
      font-weight:700;
      font-size: 26px;
      line-height:1.2;
      color:#111827;
      margin: 0 0 16px;
    }
    .card{
      background:var(--card);
      border:1px solid var(--border);
      border-radius: 10px;
      box-shadow: 0 2px 10px rgba(15,23,42,0.04);
      margin-bottom: 18px;
      overflow:hidden;
    }
    .card-header{
      padding: 14px 18px;
      font-weight: 600;
      color:#111827;
      background:#ffffff;
      border-bottom:1px solid var(--border);
    }
    .card-body{
      padding: 16px 18px;
    }
    .add-row{
      display:flex;
      gap: 10px;
      align-items:center;
    }
    .input{
      flex:1 1 auto;
      height: 40px;
      padding: 8px 12px;
      border:1px solid var(--border);
      border-radius: 8px;
      background:#ffffff;
      color:var(--text);
      outline:none;
      transition: border-color .15s, box-shadow .15s;
    }
    .input:focus{
      border-color: var(--ring);
      box-shadow: 0 0 0 3px rgba(37,99,235,0.15);
    }
    .input::placeholder{
      color: var(--muted);
    }
    .btn{
      height: 40px;
      padding: 0 14px;
      border:1px solid transparent;
      border-radius: 8px;
      background: var(--primary);
      color:#fff;
      font-weight:600;
      cursor:pointer;
      transition: background .15s, box-shadow .15s, border-color .15s;
      white-space:nowrap;
    }
    .btn:hover{ background: var(--primary-hover); }
    .btn.secondary{
      background:#f8fafc;
      color:#0f172a;
      border-color: var(--border);
    }
    .btn.secondary:hover{
      border-color:#cbd5e1;
      background:#f1f5f9;
    }
    .btn.danger{
      background: var(--danger);
    }
    .btn.danger:hover{
      background: var(--danger-hover);
    }
    .table-wrap{ width:100%; overflow-x:auto; }
    table{ width:100%; border-collapse: collapse; background:#ffffff; }
    thead th{
      font-size: 13px;
      text-transform: uppercase;
      letter-spacing: .02em;
      color: #475569;
      background:#f8fafc;
      border-bottom:1px solid var(--border);
      padding: 10px 12px;
      text-align:left;
      white-space:nowrap;
    }
    tbody td{
      padding: 12px;
      border-bottom:1px solid var(--border);
      color:#0f172a;
      vertical-align: middle;
    }
    tbody tr:hover{ background:#fbfdff; }

    .col-sl{ width:70px; }
    .col-title{ min-width: 280px; }
    .col-created{ min-width: 180px; }
    .col-action{ width: 120px; }

    @media (max-width:600px){
      .add-row{ flex-direction: column; align-items: stretch; }
      .btn{ width:100%; }
    }
    #toast{
      position: fixed;
      top: 20px;
      right: 20px;
      background: var(--success);
      color: var(--toast-fg);
      padding: 10px 14px;
      border-radius: 8px;
      box-shadow: 0 8px 24px rgba(2,6,23,0.15);
      display: none;
      z-index: 1000;
      opacity: 0;
      transform: translateX(120%);
      transition: opacity .35s ease, transform .35s ease, background .2s ease;
      font-weight: 600;
      letter-spacing: .01em;
    }
    #toast.show{
      display:block;
      opacity:1;
      transform: translateX(0);
    }
    #toast.hide{
      opacity:0;
      transform: translateX(120%);
    }
  </style>
</head>
<body>
  <div class="container">
    <h1 class="page-title">Office Task Tracker</h1>
    <section class="card">
      <div class="card-header">Add New Task</div>
      <div class="card-body">
        <div class="add-row">
          <input id="taskTitle" class="input" type="text" placeholder="Task title..." />
          <button id="addTaskBtn" class="btn">Add</button>
          <button id="clearTaskBtn" class="btn secondary" type="button">Clear</button>
        </div>
      </div>
    </section>

    <section class="card">
      <div class="card-header">Tasks (Showing Recently Added Tasks First)</div>
      <div class="card-body">
        <div class="table-wrap">
          <table id="taskTable">
            <thead>
              <tr>
                <th class="col-sl">SL</th>
                <th class="col-title">Title</th>
                <th class="col-created">Created At</th>
                <th class="col-action">Action</th>
              </tr>
            </thead>
            <tbody></tbody>
          </table>
        </div>
      </div>
    </section>
  </div>

  <div id="toast"></div>

  <script>
    function showToast(message, success = true, duration = 3000){
      const toast = document.getElementById('toast');
      toast.textContent = message;
      toast.style.background = success ? 'var(--success)' : 'var(--error)';
      toast.classList.remove('hide');
      toast.classList.add('show');
      toast.style.display = 'block';

      clearTimeout(showToast._t);
      showToast._t = setTimeout(()=>{
        toast.classList.remove('show');
        toast.classList.add('hide');
        toast.addEventListener('transitionend', ()=>{
          toast.style.display = 'none';
          toast.classList.remove('hide');
        }, { once:true });
      }, duration);
    }

    function submitTask(){
      const input = document.getElementById('taskTitle');
      const title = input.value.trim();
      if(!title){
        showToast('Task title is required', false);
        input.focus();
        return;
      }

      fetch('api.php?action=add', {
        method:'POST',
        headers:{ 'Content-Type':'application/json' },
        body: JSON.stringify({ title })
      })
      .then(r=>r.json())
      .then(data=>{
        if(data.success){
          showToast('Task added successfully', true);
          input.value='';
          loadTasks();
        }else{
          showToast(data.message || 'Failed to add task', false);
        }
      })
      .catch(()=> showToast('Network error while adding task', false));
    }

    function deleteTask(id){
      fetch('api.php?action=delete&id='+encodeURIComponent(id), { method:'POST' })
      .then(r=>r.json())
      .then(data=>{
        if(data.success){
          showToast('Task deleted', true);
          loadTasks();
        }else{
          showToast(data.message || 'Failed to delete task', false);
        }
      })
      .catch(()=> showToast('Network error while deleting task', false));
    }

    function loadTasks(){
      fetch('api.php?action=list')
      .then(r=>r.json())
      .then(data=>{
        const tbody = document.querySelector('#taskTable tbody');
        tbody.innerHTML = '';

        if(data.success && Array.isArray(data.tasks) && data.tasks.length){
          data.tasks.forEach((task, idx)=>{
            const tr = document.createElement('tr');

            const tdSl = document.createElement('td');
            tdSl.textContent = String(idx+1);

            const tdTitle = document.createElement('td');
            tdTitle.textContent = task.title ?? '';

            const tdCreated = document.createElement('td');
            tdCreated.textContent = task.created_at ?? '';

            const tdAction = document.createElement('td');
            const btn = document.createElement('button');
            btn.className = 'btn danger';
            btn.textContent = 'Delete';
            btn.onclick = ()=> deleteTask(task.id);
            tdAction.appendChild(btn);

            tr.appendChild(tdSl);
            tr.appendChild(tdTitle);
            tr.appendChild(tdCreated);
            tr.appendChild(tdAction);
            tbody.appendChild(tr);
          });
        }else{
          const tr = document.createElement('tr');
          const td = document.createElement('td');
          td.colSpan = 4;
          td.textContent = 'No tasks found';
          td.style.color = '#64748b';
          tr.appendChild(td);
          tbody.appendChild(tr);
        }
      })
      .catch(()=>{
        const tbody = document.querySelector('#taskTable tbody');
        tbody.innerHTML = '<tr><td colspan="4">Failed to load tasks</td></tr>';
      });
    }

    document.getElementById('addTaskBtn').addEventListener('click', submitTask);
    document.getElementById('clearTaskBtn').addEventListener('click', ()=>{
      const input = document.getElementById('taskTitle');
      input.value = '';
      input.focus();
    });
    document.getElementById('taskTitle').addEventListener('keydown', (e)=>{
      if(e.key === 'Enter'){ submitTask(); }
    });

    loadTasks();
  </script>
</body>
</html>
