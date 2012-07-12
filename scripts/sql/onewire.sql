CREATE TABLE onewire_data (
	date timestamp without time zone,
	id varchar(32) NOT NULL,
	value varchar(32) NOT NULL
);

CREATE TABLE onewire (
  id varchar(32) NOT NULL,
  name text NOT NULL,
  unity varchar(32),
  last_update timestamp without time zone,
  last_value varchar(32),
  comment text
);

CREATE INDEX idx_owd_id_date ON onewire_data (id, date);

CREATE OR REPLACE FUNCTION tf_onewiredata_aiu()
  RETURNS trigger AS
$BODY$
begin

UPDATE onewire SET last_update = NEW.date, last_value = NEW.value WHERE id = NEW.id;

return null;
END;
$BODY$
  LANGUAGE plpgsql VOLATILE
  COST 100;


CREATE TRIGGER trg_onewiredata_aiu
  AFTER UPDATE OR INSERT
  ON onewire_data
  FOR EACH ROW
  EXECUTE PROCEDURE tf_onewiredata_aiu();