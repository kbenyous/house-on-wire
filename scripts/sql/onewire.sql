CREATE TABLE onewire_data (
	date timestamp without time zone,
	id varchar(32) NOT NULL,
	value varchar(32) NOT NULL
);

CREATE TABLE onewire (
  id varchar(32) NOT NULL,
	name text NOT NULL,
  unity varchar(32),
  last_update timestamp without time zone
);
