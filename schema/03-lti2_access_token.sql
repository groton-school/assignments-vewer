CREATE TABLE IF NOT EXISTS lti2_access_token (
  consumer_pk integer NOT NULL REFERENCES lti2_consumer,
  scopes text NOT NULL,
  token varchar(2000) NOT NULL,
  expires timestamp NOT NULL,
  created timestamp NOT NULL,
  updated timestamp NOT NULL,
  PRIMARY KEY (consumer_pk)
);