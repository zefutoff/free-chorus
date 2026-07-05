-- Initial table for Chorus Pro technical and functional logs.

CREATE TABLE llx_choruspro_log (
  rowid integer AUTO_INCREMENT PRIMARY KEY,
  entity integer NOT NULL DEFAULT 1,
  fk_submission integer,
  level varchar(16),
  direction varchar(16),
  endpoint varchar(255),
  http_code integer,
  business_code varchar(64),
  message text,
  payload_hash varchar(128),
  datec datetime,
  fk_user integer
) ENGINE=innodb;
