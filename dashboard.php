<?php
declare(strict_types=1);

require __DIR__ . '/config/db.php';
require __DIR__ . '/includes/layout.php';
require_login();

$user = current_user();
$role = $user['role'];
$where = $role === 'employee' ? 'WHERE assigned_employee_id = :user_id' : '';
$parameters = $role === 'employee' ? ['user_id' => $user['id']] : [];
$statement = $pdo->prepare("SELECT status, COUNT(*) AS total FROM tasks {$where} GROUP BY status");
$statement->execute($parameters);
$counts = array_fill_keys(['pending', 'in_progress', 'completed', 'overdue', 'cancelled'], 0);
foreach ($statement as $row) { $counts[$row['status']] = (int) $row['total']; }

$recent = $pdo->prepare("SELECT tasks.*, CONCAT(employee.first_name, ' ', employee.last_name) AS employee_name FROM tasks JOIN users employee ON employee.id = tasks.assigned_employee_id {$where} ORDER BY tasks.updated_at DESC LIMIT 6");
$recent->execute($parameters);
page_header('Dashboard');
?>
<section class="welcome-band mb-4"><div><p class="eyebrow mb-2">Good to see you</p><h2><?= e($user['first_name']) ?>, here is the pulse of your work.</h2></div><a class="btn btn-primary" href="tasks/index.php">View task board</a></section>
<div class="row g-3 mb-4">
    <?php foreach ([['My queue', $counts['pending'] + $counts['in_progress'], 'pending'], ['In progress', $counts['in_progress'], 'in-progress'], ['Completed', $counts['completed'], 'completed'], ['Overdue', $counts['overdue'], 'overdue']] as [$label, $value, $tone]): ?>
        <div class="col-6 col-xl-3"><div class="metric-card"><span class="metric-label"><?= e($label) ?></span><strong class="metric-value <?= e($tone) ?>"><?= $value ?></strong></div></div>
    <?php endforeach; ?>
</div>
<section class="panel"><div class="panel-heading"><div><p class="eyebrow mb-1">Activity</p><h2 class="h4 mb-0">Recently updated tasks</h2></div><a href="tasks/index.php">See all</a></div>
    <div class="table-responsive"><table class="table align-middle mb-0"><thead><tr><th>Task</th><th>Employee</th><th>Priority</th><th>Status</th><th>Due</th></tr></thead><tbody>
    <?php foreach ($recent as $task): ?><tr><td><strong><?= e($task['title']) ?></strong></td><td><?= e($task['employee_name']) ?></td><td><span class="priority <?= e($task['priority']) ?>"><?= e(ucfirst($task['priority'])) ?></span></td><td><?= e(ucwords(str_replace('_', ' ', $task['status']))) ?></td><td><?= e($task['due_date']) ?></td></tr><?php endforeach; ?>
    <?php if ($recent->rowCount() === 0): ?><tr><td colspan="5" class="text-secondary py-4">No tasks have been created yet.</td></tr><?php endif; ?></tbody></table></div>
</section>
<?php page_footer(); ?>