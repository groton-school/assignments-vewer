CREATE TABLE IF NOT EXISTS lti2_nonce (
  consumer_pk integer NOT NULL REFERENCES lti2_consumer,
  value varchar(50) NOT NULL,
  expires timestamp NOT NULL,
  PRIMARY KEY (consumer_pk, value)
);