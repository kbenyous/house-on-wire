SET search_path = public, pg_catalog;

-- XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX

CREATE TABLE teleinfo (
    date timestamp without time zone,
    isousc integer DEFAULT 0 NOT NULL,
    hchp integer DEFAULT 0 NOT NULL,
    hchc integer DEFAULT 0 NOT NULL,
    ptec character varying(2) NOT NULL,
    iinst integer DEFAULT 0 NOT NULL,
    imax integer DEFAULT 0 NOT NULL,
    pmax integer DEFAULT 0 NOT NULL,
    papp integer DEFAULT 0 NOT NULL
);

-- XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX

ALTER TABLE ONLY teleinfo
    ADD CONSTRAINT teleinfo_pkey PRIMARY KEY (date);

-- XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX

CREATE TABLE teleinfo_cout (
    date timestamp without time zone NOT NULL,
    hchc integer,
    hchp integer,
    cout_hc numeric,
    cout_hp numeric,
    cout_abo numeric
);

-- XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX

CREATE TABLE teleinfo_histo (
    date timestamp with time zone NOT NULL,
    ptec character varying(2) NOT NULL,
    papp numeric,
    papp_min integer,
    papp_max integer,
    iinst numeric,
    iinst_min integer,
    iinst_max integer,
    hchc integer,
    hchp integer
);

-- XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX

CREATE TABLE teleinfo_minute (
    date timestamp without time zone NOT NULL,
    ptec character varying(2) NOT NULL,
    papp numeric,
    papp_min integer,
    papp_max integer,
    iinst numeric,
    iinst_min integer,
    iinst_max integer,
    hchc integer,
    hchp integer
);

-- XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX

ALTER TABLE ONLY teleinfo_cout
    ADD CONSTRAINT teleinfo_cout_pkey PRIMARY KEY (date);

-- XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX

ALTER TABLE ONLY teleinfo_histo
    ADD CONSTRAINT teleinfo_histo_pkey PRIMARY KEY (date, ptec);

-- XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX

ALTER TABLE ONLY teleinfo_minute
    ADD CONSTRAINT teleinfo_minute_pkey PRIMARY KEY (date, ptec);


-- XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX


-- HISTORISATION DES DONNEES
-- Batch a lancer dans la nuit
--
-- Objectif :
-- 48 dernieres heures en full ( env 1 enreg / sec )
-- J-3 a J-7 en 1 point par 1 min
-- J-8 a ... en 1 point par 5 min

-- Creation de données agrégée sur 1 min
CREATE FUNCTION historisation_teleinfo() RETURNS void AS $$
INSERT INTO teleinfo_minute
SELECT 
    date_trunc('minute', date) as date,
    ptec,
    avg(papp) as papp, 
    min(papp) as papp_min,
    max(papp) as papp_max,
    avg(iinst) as inst1, 
    min(iinst) as inst1_min,
    max(iinst) as inst1_max,
    max(hchc) as hchc,
    max(hchp) as hchp

FROM    
    teleinfo 
WHERE 
    date_trunc('day', date) = current_date - interval '1 day' 
GROUP BY
    date_trunc('minute', date), ptec;

-- Creation des données agregée sur 5 min
INSERT INTO teleinfo_histo
SELECT
    to_timestamp( trunc(EXTRACT(EPOCH FROM date)/300)*300 ), 
    ptec, 
    avg(papp) as papp, 
    min(papp) as papp_min,
    max(papp) as papp_max,
    avg(iinst) as iinst, 
    min(iinst) as iinst_min,
    max(iinst) as iinst_max,
    max(hchc) as hchc,
    max(hchp) as hchp

FROM    
    teleinfo 
WHERE 
    date_trunc('day', date) = current_date - interval '1 day' 
GROUP BY
    to_timestamp( trunc(EXTRACT(EPOCH FROM date)/300)*300 ), ptec;



-- MISE A JOUR DE LA TABLE DES COUTS
INSERT INTO teleinfo_cout 
SELECT
current_date - interval '1 day',
hchc_max - hchc_min AS hchc,
hchp_max - hchp_min AS hchp,
0.0895 * ((hchc_max - hchc_min) / 1000) AS cout_hc,
0.1312 * ((hchp_max - hchp_min) / 1000) AS cout_hp,
94.06 / 365 as cout_abo
FROM
(
SELECT
    (SELECT min(hchc) FROM teleinfo WHERE date_trunc('day', date) = current_date )::integer AS hchc_max,
    (SELECT min(hchp) FROM teleinfo WHERE date_trunc('day', date) = current_date )::integer AS hchp_max,
    (SELECT min(hchc) FROM teleinfo WHERE date_trunc('day', date) = current_date - interval '1 day')::integer AS hchc_min,
    (SELECT min(hchp) FROM teleinfo WHERE date_trunc('day', date) = current_date - interval '1 day')::integer AS hchp_min
)foo;

-- Suppression des données dont on ne veut plus
--     DELETE FROM teleinfo WHERE date < current_date - interval '5 days';
--     DELETE FROM teleinfo_minute WHERE date < current_date - interval '15 days';

$$ LANGUAGE sql;

-- XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX

