CREATE TABLE product_branches (
  product_id INT NOT NULL,
  branch_id INT NOT NULL,
  PRIMARY KEY (product_id, branch_id),
  FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
  FOREIGN KEY (branch_id) REFERENCES branches(id) ON DELETE CASCADE
);
