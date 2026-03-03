https://chat.deepseek.com/a/chat/s/66aa454a-9f56-4db6-91c2-a8f19f8c9166


AdevOptique 

CREATE STORE
MAKE REGISTER ONLINE 
SWITCH BETTWZN STORE
CREATE LANDING PAGE FOR APP
Create mobile app  
CREATE IMPORT DATA FOR OTHER SOFTWARE 
MAKE MENU SUGGISTATION 
NOTFACATION ABOUT PAYEMENT AND OTHHER PLAN YOU CAN BUY


fIRST PLAN 1 store and  2 compte 1000 dh 3am

add new store add 500 dh 
add new compte  100 dh
SELECT 
    v.voiture AS VOITURE,
    SUM(fl.fuel_quantity) AS NBRE_DE_LITRES,
    SUM(fl.fuel_cost) AS MONTANT,
    AVG(fl.mileage_at_refuel - prev_mileage.mileage_at_refuel) AS KILOMETRAGE_MOYEN_PARCOURU,
    (SUM(fl.fuel_quantity) / NULLIF(SUM(fl.mileage_at_refuel - prev_mileage.mileage_at_refuel), 0)) * 100 AS CONSOMMATION_AU_100_KILOMETRES
FROM 
    Voiture v
INNER JOIN 
    VoitureDetails vd ON v.id = vd.voiture_id
INNER JOIN 
    FuelLogs fl ON vd.id = fl.vehicle_id
LEFT JOIN 
    FuelLogs prev_mileage ON fl.vehicle_id = prev_mileage.vehicle_id AND fl.refuel_date > prev_mileage.refuel_date
WHERE 
    fl.refuel_date BETWEEN @StartDate AND @EndDate
GROUP BY 
    v.voiture