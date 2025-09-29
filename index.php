<?php
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Office Task Tracker</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            background: #f4f4f4;
        }

        h1 {
            text-align: center;
            color: #333;
        }

        table {
            width: 80%;
            margin: 20px auto;
            border-collapse: collapse;
            background: #fff;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        th,
        td {
            padding: 12px;
            border: 1px solid #ddd;
            text-align: left;
        }

        th {
            background-color: #f0f0f0;
        }

        .btn {
            padding: 10px 20px;
            margin: 10px;
            border: none;
            background-color: #007BFF;
            color: white;
            cursor: pointer;
            border-radius: 5px;
        }

        .btn:hover {
            background-color: #0056b3;
        }

        #deleteTaskBtn {
            background-color: #ff0101ff;
        }

        #addTaskBtn {
            display: block;
            margin: 0 auto;
        }

        #popupForm {
            display: none;
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: white;
            padding: 20px;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.3);
            z-index: 1001;
            border-radius: 10px;
        }

        #popupForm input {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            box-sizing: border-box;
        }

        #popupForm .btn {
            margin: 5px;
        }

        #overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.3);
            backdrop-filter: blur(5px);
            z-index: 1000;
        }

        #toast {
            position: fixed;
            top: 20px;
            right: 20px;
            background: #28a745;
            color: white;
            padding: 10px 20px;
            border-radius: 5px;
            display: none;
            z-index: 1002;
            opacity: 0;
            transform: translateX(100%);
            transition: opacity 0.5s, transform 0.5s;
        }

        #toast.show {
            display: block;
            opacity: 1;
            transform: translateX(0);
        }

        #toast.hide {
            opacity: 0;
            transform: translateX(100%);
            transition: opacity 0.5s, transform 0.5s;
        }
    </style>
</head>

<body>
    <h1>Office Task Tracker</h1>
    <button id="addTaskBtn" class="btn">Add Task</button>

    <table id="taskTable">
        <thead>
            <tr>
                <th>ID</th>
                <th>Title</th>
                <th>Created At</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody></tbody>
    </table>

    <div id="overlay"></div>
    <div id="popupForm">
        <h3>Add New Task</h3>
        <input type="text" id="taskTitle" placeholder="Enter task title">
        <button class="btn" onclick="submitTask()">Add</button>
        <button class="btn" onclick="closeForm()">Cancel</button>
    </div>

    <div id="toast"></div>

    <script>
        function showToast(message, success = true, duration = 3000) {
            const toast = document.getElementById('toast');
            toast.textContent = message;
            toast.style.background = success ? '#28a745' : '#dc3545';
            toast.classList.add('show');
            toast.style.display = 'block';
            setTimeout(() => {
                toast.classList.remove('show');
                toast.classList.add('hide');
                toast.addEventListener('transitionend', () => {
                    toast.classList.remove('hide');
                    toast.style.display = 'none';
                }, {
                    once: true
                });

            }, duration);
        }


        function openForm() {
            document.getElementById('popupForm').style.display = 'block';
            document.getElementById('overlay').style.display = 'block';
        }

        function closeForm() {
            document.getElementById('popupForm').style.display = 'none';
            document.getElementById('overlay').style.display = 'none';
        }

        document.getElementById('addTaskBtn').addEventListener('click', openForm);

        function submitTask() {
            const title = document.getElementById('taskTitle').value.trim();
            if (!title) {
                showToast('Task title is required', false);
                return;
            }

            fetch('api.php?action=add', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        title
                    })
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        showToast('Task added successfully');
                        loadTasks();
                        closeForm();
                        document.getElementById('taskTitle').value = '';
                    } else {
                        showToast(data.message || 'Failed to add task', false);
                    }
                });
        }

        function deleteTask(id) {
            fetch('api.php?action=delete&id=' + id, {
                    method: 'POST'
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        showToast('Task deleted');
                        loadTasks();
                    } else {
                        showToast(data.message || 'Failed to delete task', false);
                    }
                });
        }

        function loadTasks() {
            fetch('api.php?action=list')
                .then(res => res.json())
                .then(data => {
                    const tbody = document.querySelector('#taskTable tbody');
                    tbody.innerHTML = '';
                    if (data.success && data.tasks.length) {
                        data.tasks.forEach(task => {
                            const row = document.createElement('tr');
                            row.innerHTML = `<td>${task.id}</td><td>${task.title}</td><td>${task.created_at}</td>
              <td><button class="btn" id="deleteTaskBtn" onclick="deleteTask(${task.id})">Delete</button></td>`;
                            tbody.appendChild(row);
                        });
                    } else {
                        tbody.innerHTML = '<tr><td colspan="4">No tasks found</td></tr>';
                    }
                });
        }

        loadTasks();
    </script>
</body>

</html>