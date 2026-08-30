-- Optional but recommended: persists the size guide and fit information fields
-- already shown in the product editor. The Product model remains compatible
-- with older databases even before this migration is run.
ALTER TABLE products ADD COLUMN size_guide TEXT NULL AFTER description;
ALTER TABLE products ADD COLUMN fit_information TEXT NULL AFTER size_guide;
