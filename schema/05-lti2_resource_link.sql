CREATE TABLE IF NOT EXISTS lti2_resource_link (
  resource_link_pk SERIAL,
  context_pk integer DEFAULT NULL REFERENCES lti2_context,
  consumer_pk integer DEFAULT NULL REFERENCES lti2_consumer,
  title varchar(255) DEFAULT NULL,
  lti_resource_link_id varchar(255) NOT NULL,
  settings text,
  primary_resource_link_pk integer DEFAULT NULL REFERENCES lti2_resource_link,
  share_approved boolean DEFAULT NULL,
  created timestamp NOT NULL,
  updated timestamp NOT NULL,
  PRIMARY KEY (resource_link_pk)
);