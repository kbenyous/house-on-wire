-- Creation de la table des cout d'abonnement

CREATE TABLE abonnement (type text, montant numeric, date timestamptz);

INSERT INTO abonnement (type, montant, date) VALUES ('elect_abo', 191.59, '2012-01-01'::timestamptz);
INSERT INTO abonnement (type, montant, date) VALUES ('elect_hc', 0.1312,'2012-01-01'::timestamptz);
INSERT INTO abonnement (type, montant, date) VALUES ('elect_hp', 0.0895,'2012-01-01'::timestamptz);

INSERT INTO abonnement (type, montant, date) VALUES ('elect_abo', 195.30, '2012-07-23'::timestamptz);
INSERT INTO abonnement (type, montant, date) VALUES ('elect_hc', 0.1353,'2012-07-23'::timestamptz);
INSERT INTO abonnement (type, montant, date) VALUES ('elect_hp', 0.0926,'2012-07-23'::timestamptz);

-- Suppression de la table d'historisation à la minute

DROP VIEW teleinfo_view;
DROP TABLE teleinfo_minute;

-- Reprise de l'historisation

ALTER TABLE teleinfo ALTER COLUMN date SET NOT NULL;

CREATE OR REPLACE FUNCTION historisation_teleinfo() RETURNS void AS $$
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
elect_hc * ((hchc_max - hchc_min) / 1000) AS cout_hc,
elect_hp * ((hchp_max - hchp_min) / 1000) AS cout_hp,
elect_abo / 365 as cout_abo
FROM
(
SELECT
    (SELECT min(hchc) FROM teleinfo WHERE date_trunc('day', date) = current_date )::integer AS hchc_max,
    (SELECT min(hchp) FROM teleinfo WHERE date_trunc('day', date) = current_date )::integer AS hchp_max,
    (SELECT min(hchc) FROM teleinfo WHERE date_trunc('day', date) = current_date - interval '1 day')::integer AS hchc_min,
    (SELECT min(hchp) FROM teleinfo WHERE date_trunc('day', date) = current_date - interval '1 day')::integer AS hchp_min,
    (SELECT montant FROM abonnement WHERE type = 'elect_abo' and date_trunc('day', date) < current_date - interval '1 day' order by date desc limit 1) AS elect_abo,
    (SELECT montant FROM abonnement WHERE type = 'elect_hc' and date_trunc('day', date) < current_date - interval '1 day' order by date desc limit 1) AS elect_hc,
    (SELECT montant FROM abonnement WHERE type = 'elect_hp' and date_trunc('day', date) < current_date - interval '1 day' order by date desc limit 1) AS elect_hp
)foo;

-- Suppression des données dont on ne veut plus
--     DELETE FROM teleinfo WHERE date < current_date - interval '5 days';
--     DELETE FROM teleinfo_minute WHERE date < current_date - interval '15 days';

$$ LANGUAGE sql;