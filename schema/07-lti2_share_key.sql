CREATE TABLE IF NOT EXISTS lti2_share_key (
  share_key_id varchar(32) NOT NULL,
  resource_link_pk integer NOT NULL REFERENCES lti2_resource_link,
  auto_approve boolean NOT NULL,
  expires timestamp NOT NULL,
  PRIMARY KEY (share_key_id)
);