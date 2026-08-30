-- Starter keyword list for product screening. Run once in phpMyAdmin.
-- Review and adapt this list to your marketplace policy and local laws.
INSERT INTO prohibited_items (item_name, description, created_at)
SELECT 'firearm', 'Firearms and weapon listings are prohibited.' , NOW()
WHERE NOT EXISTS (SELECT 1 FROM prohibited_items WHERE item_name = 'firearm');
INSERT INTO prohibited_items (item_name, description, created_at)
SELECT 'pistol', 'Firearms and weapon listings are prohibited.' , NOW()
WHERE NOT EXISTS (SELECT 1 FROM prohibited_items WHERE item_name = 'pistol');
INSERT INTO prohibited_items (item_name, description, created_at)
SELECT 'rifle', 'Firearms and weapon listings are prohibited.' , NOW()
WHERE NOT EXISTS (SELECT 1 FROM prohibited_items WHERE item_name = 'rifle');
INSERT INTO prohibited_items (item_name, description, created_at)
SELECT 'shotgun', 'Firearms and weapon listings are prohibited.' , NOW()
WHERE NOT EXISTS (SELECT 1 FROM prohibited_items WHERE item_name = 'shotgun');
INSERT INTO prohibited_items (item_name, description, created_at)
SELECT 'ammunition', 'Ammunition is prohibited.' , NOW()
WHERE NOT EXISTS (SELECT 1 FROM prohibited_items WHERE item_name = 'ammunition');
INSERT INTO prohibited_items (item_name, description, created_at)
SELECT 'explosive', 'Explosive materials are prohibited.' , NOW()
WHERE NOT EXISTS (SELECT 1 FROM prohibited_items WHERE item_name = 'explosive');
INSERT INTO prohibited_items (item_name, description, created_at)
SELECT 'bomb', 'Explosive devices are prohibited.' , NOW()
WHERE NOT EXISTS (SELECT 1 FROM prohibited_items WHERE item_name = 'bomb');
INSERT INTO prohibited_items (item_name, description, created_at)
SELECT 'grenade', 'Explosive devices are prohibited.' , NOW()
WHERE NOT EXISTS (SELECT 1 FROM prohibited_items WHERE item_name = 'grenade');
INSERT INTO prohibited_items (item_name, description, created_at)
SELECT 'illegal drugs', 'Illegal or controlled drugs are prohibited.' , NOW()
WHERE NOT EXISTS (SELECT 1 FROM prohibited_items WHERE item_name = 'illegal drugs');
INSERT INTO prohibited_items (item_name, description, created_at)
SELECT 'narcotics', 'Illegal or controlled drugs are prohibited.' , NOW()
WHERE NOT EXISTS (SELECT 1 FROM prohibited_items WHERE item_name = 'narcotics');
INSERT INTO prohibited_items (item_name, description, created_at)
SELECT 'counterfeit', 'Counterfeit goods are prohibited.' , NOW()
WHERE NOT EXISTS (SELECT 1 FROM prohibited_items WHERE item_name = 'counterfeit');
INSERT INTO prohibited_items (item_name, description, created_at)
SELECT 'fake brand', 'Counterfeit goods are prohibited.' , NOW()
WHERE NOT EXISTS (SELECT 1 FROM prohibited_items WHERE item_name = 'fake brand');
INSERT INTO prohibited_items (item_name, description, created_at)
SELECT 'stolen goods', 'Stolen goods are prohibited.' , NOW()
WHERE NOT EXISTS (SELECT 1 FROM prohibited_items WHERE item_name = 'stolen goods');
INSERT INTO prohibited_items (item_name, description, created_at)
SELECT 'hazardous chemicals', 'Hazardous chemicals are prohibited.' , NOW()
WHERE NOT EXISTS (SELECT 1 FROM prohibited_items WHERE item_name = 'hazardous chemicals');
INSERT INTO prohibited_items (item_name, description, created_at)
SELECT 'poison', 'Poisons are prohibited.' , NOW()
WHERE NOT EXISTS (SELECT 1 FROM prohibited_items WHERE item_name = 'poison');
INSERT INTO prohibited_items (item_name, description, created_at)
SELECT 'pornographic content', 'Adult content is prohibited.' , NOW()
WHERE NOT EXISTS (SELECT 1 FROM prohibited_items WHERE item_name = 'pornographic content');
