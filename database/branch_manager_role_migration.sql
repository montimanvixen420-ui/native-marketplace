-- Run this AFTER deploying the updated PHP code (models/User.php, models/BranchManager.php,
-- core/Controller.php, controllers/StaffController.php, controllers/AuthController.php,
-- views/partials/admin-sidebar.php).
--
-- Moves existing Branch Manager accounts from the shared role='staff' to their own
-- role='manager', so they are no longer counted or treated as regular Staff.
-- Regular Staff (staff_profiles.position != 'branch_manager') are left untouched.

UPDATE users u
INNER JOIN branch_managers bm ON bm.user_id = u.id
SET u.role = 'manager'
WHERE u.role = 'staff';
