
CREATE TABLE product_branches (
  product_id INT NOT NULL,
  branch_id INT NOT NULL,
  PRIMARY KEY (product_id, branch_id),
  INDEX idx_product_id (product_id),
  INDEX idx_branch_id (branch_id)
);