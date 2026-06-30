-- Initial table for Chorus Pro invoice submissions.

CREATE TABLE llx_choruspro_submission (
  rowid integer AUTO_INCREMENT PRIMARY KEY,
  entity integer NOT NULL DEFAULT 1,
  fk_facture integer NOT NULL,
  fk_soc integer NOT NULL,
  siret_destinataire varchar(20),
  code_service varchar(128),
  engagement_juridique varchar(128),
  chorus_invoice_id varchar(128),
  chorus_deposit_id varchar(128),
  status varchar(64),
  status_label varchar(255),
  environment varchar(32),
  last_sync datetime,
  datec datetime,
  tms timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  fk_user_author integer,
  fk_user_modif integer,
  import_key varchar(14)
) ENGINE=innodb;
