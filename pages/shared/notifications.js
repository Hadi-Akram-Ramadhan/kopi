// Function buat ambil notifikasi
function getNotifications() {
    fetch('notifications.php')
        .then(response => response.json())
        .then(notifications => {
            const notificationList = document.getElementById('notificationList');
            notificationList.innerHTML = '';

            notifications.forEach(notification => {
                const li = document.createElement('li');
                li.className = 'list-group-item';
                li.innerHTML = `
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="mb-0">${notification.message}</p>
                            <small class="text-muted">${notification.created_at}</small>
                        </div>
                        <button class="btn btn-sm btn-outline-primary" onclick="markAsRead(${notification.id})">
                            Tandai Dibaca
                        </button>
                    </div>
                `;
                notificationList.appendChild(li);
            });

            // Update badge jumlah notifikasi
            const badge = document.getElementById('notificationBadge');
            badge.textContent = notifications.length;
            badge.style.display = notifications.length > 0 ? 'inline' : 'none';
        })
        .catch(error => console.error('Error:', error));
}

// Function buat tandai notifikasi udah dibaca
function markAsRead(id) {
    const formData = new FormData();
    formData.append('id', id);

    fetch('mark_notification.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                getNotifications();
            }
        })
        .catch(error => console.error('Error:', error));
}

// Update notifikasi setiap 30 detik
setInterval(getNotifications, 30000);

// Load notifikasi pertama kali
getNotifications();