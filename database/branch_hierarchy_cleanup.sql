-- Run this AFTER branch_hierarchy_migration.sql (and branch_hierarchy_missing_staff_profiles.sql
-- if you needed the hotfix) have been applied and the app has been working correctly for a while.
-- Back up the database before running this in phpMyAdmin.
--
-- As of this cleanup, no PHP code references branch_staff or staff_branches anymore:
-- Branch.php's staff_count now comes from staff_profiles, and staff assignment goes
-- through the Seller -> Branch Manager -> Staff flow only (Rule 25 / Rule 33 in staff.txt —
-- no many-to-many Staff-to-Branch table). These two tables are dead weight.

DROP TABLE IF EXISTS staff_branches;
DROP TABLE IF EXISTS branch_staff;
