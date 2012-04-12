select max(papp), (round(extract( epoch from date )/3600)) as mydate from teleinfo group by  mydate;
