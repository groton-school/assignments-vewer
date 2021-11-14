CREATE TABLE IF NOT EXISTS lti2_user_result (
  user_result_pk SERIAL,
  resource_link_pk integer NOT NULL REFERENCES lti2_resource_link,
  lti_user_id varchar(255) NOT NULL,
  lti_result_sourcedid varchar(1024) NOT NULL,
  created timestamp NOT NULL,
  updated timestamp NOT NULL,
  PRIMARY KEY (user_result_pk)
);