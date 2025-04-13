<?php
$pageTitle = 'Announcements';
require_once '../layouts/main.php';

// Database connection
require_once '../../includes/config.php';

// Fetch announcements
$query = "SELECT a.*, u.username as author 
          FROM announcements a 
          JOIN users u ON a.user_id = u.id 
          ORDER BY a.created_at DESC";
$result = $connection->query($query);
?>

<div class="row mb-4">
    <div class="col-md-6">
        <h2 class="mb-0">Announcements</h2>
    </div>
    <div class="col-md-6 text-md-end">
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addAnnouncementModal">
            <i class="fas fa-plus me-2"></i>New Announcement
        </button>
    </div>
</div>

<!-- Announcements List -->
<div class="row">
    <?php while ($announcement = $result->fetch_assoc()): ?>
        <div class="col-md-6 col-lg-4 mb-4">
            <div class="card h-100">
                <div class="card-header bg-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><?php echo htmlspecialchars($announcement['title']); ?></h5>
                        <div class="dropdown">
                            <button class="btn btn-link text-dark p-0" data-bs-toggle="dropdown">
                                <i class="fas fa-ellipsis-v"></i>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li>
                                    <button class="dropdown-item" onclick="editAnnouncement(<?php echo $announcement['id']; ?>)">
                                        <i class="fas fa-edit me-2"></i>Edit
                                    </button>
                                </li>
                                <li>
                                    <button class="dropdown-item text-danger" onclick="deleteAnnouncement(<?php echo $announcement['id']; ?>)">
                                        <i class="fas fa-trash me-2"></i>Delete
                                    </button>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <p class="card-text"><?php echo nl2br(htmlspecialchars($announcement['content'])); ?></p>
                </div>
                <div class="card-footer bg-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <small class="text-muted">
                                <i class="fas fa-user me-1"></i>
                                <?php echo htmlspecialchars($announcement['author']); ?>
                            </small>
                        </div>
                        <div>
                            <small class="text-muted">
                                <i class="fas fa-clock me-1"></i>
                                <?php echo date('M d, Y H:i', strtotime($announcement['created_at'])); ?>
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php endwhile; ?>
</div>

<!-- Add Announcement Modal -->
<div class="modal fade" id="addAnnouncementModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">New Announcement</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="announcementForm">
                    <div class="mb-3">
                        <label class="form-label">Title</label>
                        <input type="text" class="form-control" name="title" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Content</label>
                        <textarea class="form-control" name="content" rows="5" required></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Priority</label>
                        <select class="form-select" name="priority">
                            <option value="normal">Normal</option>
                            <option value="high">High</option>
                            <option value="urgent">Urgent</option>
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="saveAnnouncement()">Publish</button>
            </div>
        </div>
    </div>
</div>

<script>
// Save new announcement
function saveAnnouncement() {
    const form = document.getElementById('announcementForm');
    const formData = new FormData(form);
    
    fetch('save_announcement.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert('Error saving announcement: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while saving the announcement.');
    });
}

// Delete announcement
function deleteAnnouncement(id) {
    if (confirm('Are you sure you want to delete this announcement?')) {
        fetch(`delete_announcement.php?id=${id}`, {
            method: 'POST'
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Error deleting announcement: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while deleting the announcement.');
        });
    }
}

// Edit announcement
function editAnnouncement(id) {
    // Fetch announcement details
    fetch(`get_announcement.php?id=${id}`)
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const form = document.getElementById('announcementForm');
            form.querySelector('[name="title"]').value = data.announcement.title;
            form.querySelector('[name="content"]').value = data.announcement.content;
            form.querySelector('[name="priority"]').value = data.announcement.priority;
            
            // Show modal
            const modal = new bootstrap.Modal(document.getElementById('addAnnouncementModal'));
            modal.show();
            
            // Update form action
            form.dataset.editId = id;
        } else {
            alert('Error fetching announcement: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while fetching the announcement.');
    });
}
</script> 