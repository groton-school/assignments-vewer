CREATE TABLE IF NOT EXISTS lti2_context (
  context_pk SERIAL,
  consumer_pk integer NOT NULL REFERENCES lti2_consumer,
  title varchar(255) DEFAULT NULL,
  lti_context_id varchar(255) NOT NULL,
  type varchar(50) DEFAULT NULL,
  settings text DEFAULT NULL,
  created timestamp NOT NULL,
  updated timestamp NOT NULL,
  PRIMARY KEY (context_pk)
);