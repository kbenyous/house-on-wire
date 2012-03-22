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
    papp integer DEFAULT 0 NOT NULL
);

-- XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX

ALTER TABLE ONLY teleinfo
    ADD CONSTRAINT teleinfo_pkey PRIMARY KEY (date);
    
-- Pour accélerer le calcul des MIN et MAX sur PAPP
CREATE INDEX idx_papp ON teleinfo (papp);

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

CREATE TABLE teleinfo_agg (
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

CREATE VIEW teleinfo_view AS
    SELECT date, ptec, ROUND(papp) AS papp, hchc, hchp FROM teleinfo_agg
    UNION ALL
    SELECT date, ptec, papp, hchc, hchp FROM teleinfo
        WHERE date >= ( CURRENT_DATE - INTERVAL '1 day' )
ORDER BY date ASC;

-- XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX

ALTER TABLE ONLY teleinfo_cout
    ADD CONSTRAINT teleinfo_cout_pkey PRIMARY KEY (date);

-- XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX

ALTER TABLE ONLY teleinfo_agg
    ADD CONSTRAINT teleinfo_agg_pkey PRIMARY KEY (date, ptec);

-- XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX

-- Calcul du cout de l'électricité pour le jour spécifié en arg
--  arg=1 ==> la veille
--  arg=2 ==> l'avant-veille, etc

CREATE FUNCTION teleinfo_calcul_cout( IN int ) 
RETURNS void AS $$
INSERT INTO teleinfo_cout
SELECT CURRENT_DATE - CAST( (CAST($1 AS TEXT) || ' day') AS interval) AS jour,
            hchc_max - hchc_min AS hchc,
            hchp_max - hchp_min AS hchp,
            0.0895 * ((hchc_max - hchc_min) / 1000) AS cout_hc,
            0.1312 * ((hchp_max - hchp_min) / 1000) AS cout_hp,
            94.06 / 365 AS cout_abo
    FROM (
        SELECT
            MIN(hchc) as hchc_min,
            MAX(hchc) as hchc_max,
            MIN(hchp) as hchp_min,
            MAX(hchp) as hchp_max
        FROM (
            (SELECT
                date,
                hchc,
                hchp
                FROM teleinfo WHERE date BETWEEN (CURRENT_DATE - CAST( (CAST($1 AS TEXT) || ' day') AS interval)) AND (CURRENT_DATE - CAST( (CAST( ($1-1) AS TEXT) || ' day') AS interval)) ORDER BY date ASC LIMIT 1
            )
            UNION ALL
            (SELECT
                date,
                hchc,
                hchp
                FROM teleinfo WHERE date BETWEEN (CURRENT_DATE - CAST( (CAST($1 AS TEXT) || ' day') AS interval)) AND (CURRENT_DATE - CAST( (CAST( ($1-1) AS TEXT) || ' day') AS interval)) ORDER BY date DESC LIMIT 1
            )
            ) foo
    ) foo2;
$$ language sql;

-- XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX


-- HISTORISATION DES DONNEES
-- Batch a lancer dans la nuit
--
-- Objectif :
-- Creation des données agregée sur 5 min
-- 48 dernieres heures en full ( env 1 enreg / sec )
--  à partir de J-3, 1 point par 5 min

CREATE FUNCTION historisation_teleinfo() RETURNS void AS $$
INSERT INTO teleinfo_agg
SELECT
    TO_TIMESTAMP( TRUNC(EXTRACT(EPOCH FROM date)/300)*300 ) AS temps, 
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
    date BETWEEN
            COALESCE(   DATE_TRUNC('day', (SELECT MAX(date) FROM teleinfo_agg)),  -- dernier jour aggregé
                        '1970-01-01' )          -- à défaut, l'origine des temps
            + interval '1 day'                  -- on commence au lendemain
         AND (CURRENT_DATE - interval '1 day')  -- on n'aggrège pas les 48 dernières heures
GROUP BY
    temps, ptec;


-- Suppression des données dont on ne veut plus
--     DELETE FROM teleinfo WHERE date < current_date - interval '5 days';


-- Calcul du cout de la veille
SELECT teleinfo_calcul_cout( 1 );


$$ LANGUAGE sql;

-- XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX

