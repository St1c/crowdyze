User
====
zakladne udaje o uzivatelovi

-> active: info ci je uzivatelsky ucet aktivny. Zatial neplni ziadnu funkciu, vsetky ucty su aktivne automaticky
-> role: sem pribudne moznost definovat uzivatela ako administratora, ktory bude mat moznost administrovat vsetky ulohy
- informacie ziskane zo soc. sieti, pripadne editovanim profilu
- Uzivatelska penazenka. Vytvori sa pri registracii noveho uzivatela.
- Uzivatel si moze dobit kredit pomocou paypal, goPay, bankovy prevod
- Uzivatelovi sa sem pridavaju vsetky peniaze ktore zarobil vykonanim urcitej ulohy (Task)


Relationship
============
- definuje vztahy medzi uzivatelmi, moznost prodavat si priatelov a pisat im spravy
- zatial neimplementovane

Relationship Type
=================
- Typ vztahu medzi uzivatelmi - friends, co-workers, employees
- zatial neimplementovane

Message
=======
- Sluzi na ukladanie sprav medzi uzivatelmi
- zatial neimplementovane

Transfer
========
- Logovanie vsetkych penaznych transakcii, za vykonane ulohy
- Sluzi na dohladanie chybnych transakcii, pre lepsiu administraciu

Departments
===========
- Bude sluzit na vytvaranie pracovnych oddeleni, kde si mozem definovat skupinu uzivatelov a pri vytvarani ulohy zadefinujem, ze iba uzivatelia v tom danom oddeleni (department) ju mozu vykonat
- zatial neimplementovane

Task
====
- Vsetky detaily tykajuce sa vytvorenej ulohy
-> status: sluzi na oznacenie ulohy - ci je schvalena administratormi, ukoncena, 
pauznuta a podobne
-> pri vytvoreni novej ulohy sa vytvori aj novy zaznam v tabulke budget, 
ktora obsahuje rezervu na peniaze potrebne na vykonanie danej ulohy + peniaze 
na rezijne naklady (nasa commission a fees)
-> za vytvorenie ulohy sa okrem 5% commission uctuje aj fixna suma, 
ktora sa ale rovno prevadza na nas interny ucet (tabulka income)
-> dynamicke poplatky (5% commission, a percenta za promoted task) su 
zaznamenane v danych stlpcoch tabulky a dynamicky sa odpocitavaju a updatuju, 
ak zadavatel ulohy oznaci nejaky vysledok za spravny. Tieto dynamicke poplatky 
sa nasledne presuvaju na interny ucet (income table)
-> peniaze prinaleziace za spravne vykonany vysledok sa presuvaju na ucet 
uzivatela ktory ho spravil (do jeho wallet)

priklad:

1. Vytvorim ulohu pre 100 workerov a kazdemu zaplatim 1 euro (salary). Chcem mat zobrazenu ulohu hned hore v prehlade uloh (na hlavnej stranke) tak si zaplatim 5% poplatok za promotion
2. Naklady (budget): 100 workerov * 1 euro  = 100.00 Eur 
											+   0.50 Eur (fixny poplatok)
											+   5.00 Eur (5% commission)
											+   5.00 Eur (5% poplatok za promotion)
											---------------------------------------
											  110.50 Eur

3. 110.50 sa odpocita z aktualneho balance vo wallet tabulke uzivatela ktory ulohu vytvara
4. vytvori sa zaznam v tabulke budget:
	fee 		= 0.50
	budget 		= 100.00
	commission 	= 5.00
	promotion 	= 5.00 
5. 0.50 Eur sa hned presunie na nas ucet (vytvori sa novy zaznam v tabulke Income s typom '1' - fee for creating new task)

6. Nejaky worker oznaci ulohu ako ze ju chce vykonat (tu by bolo fajn implementovat nejaky deadline, to ktoreho musi worker vysledok odoslat. napriklad 24hodin, a po nom by sa dany riadok v DB - accepted task automaticky zmazal. Neviem ci sa to da v mysql).

7. Worker odosle vysledok, zmeni sa status na '2 pending'
8. Employer (clovek co vytvoril ulohu) oznaci vysledok ako spravny
9. Updatne sa zaznam v tabulke budget na nove hodnoty:
	budget 		= 99.00 	(100.00 - 1.00 Eur)
	commission 	=  4.95 	(5% z 1.00 Eur)
	promotion 	=  4.95 	(5% z 1.00 Eur)

	Dovod tohto dynamickeho odpocitavania je ten, ze zadavatel ulohy ju moze kedykolvek pauznut, alebo ukoncit. Vtedy sa mu zostavajuca suma vrati naspat do jeho wallet, pricom my uz budeme mat poplatky za uspesne vykonane vysledky u nas na ucte.

10. Suma 1 Eur sa presunie uzivatelovi ktory vykonal ulohu do jeho wallet
11. Sumy 0.05 Eur (commission) a 0.05 eur (promotion) sa presunu ku nam na ucet (tabulka Income) s nalezitymi typmi
12. Uloha je ukoncena ak je dosiahnuty pocet vysledok rovny 100, alebo ju ukoncil zadavatel sam.
13. Vsetky transakcie by mali byt zalogovane v tabulke Transfer (nie je implementovane zatial)


Comment
=======
- zatial neimplementovane
- komentare pre danu ulohu, viditelne vsetkymi, zobrazene pod popisom ulohy

Budget type
===========
- Forma odmeny pre korektne odoslany vysledok
- mozu dostat peniaze vsetci, alebo len prvych 10, alebo len najlepsi

Promotion
=========
- zatial neimplementovane
- Sluzi na zvyraznenie novo-vytvorenej ulohy v rozpise uloh na hlavnej stranky
- planovane su tri stupne mozneho zvyraznenia, na to je viazane aj suma potrebna na vytvorenie a zobrazenie danej ulohy

Tag a Task has Tag
==================
- Many-to-Many tabulka
- tagy prinaleziace danej ulohe

Task Status
===========
- look-up table popisujuca jednotlive stavy ulohy

Task attachment
===============
- subory pripojene ku danej ulohe pri jej vytvoreni

Attachment Type
===============
- look-up table
- Typ prilohy - doc, video, zip...


Accepted Task
=============
- Tabulka obsahujuca zaznamy ktory uzivatel sa prihlasil na vykonanie tej-ktorej ulohy.
- Ak sa uzivatel prihlasi na vykonanie ulohy, zaznam dostane stav 'accepted (1)'
- Ak uzivatel odosle vysledok na ulohu na ktoru sa prihlasil (status: accepted), zmeni sa stav na 'pending(2)'
- Zadavatel ulohy je notifikovany o tom ze su k dispozicii vysledky ku ulohe ktoru zadal a moze si ich pozriet a dane vysledky oznacit ako spravne alebo nespravne. Potom sa stav zmeni na 'satisfied (3)'' and 'unsatisfied (4)'

Result Attachment
=================
- tabulka obsahujuca cesty ku suborom prinaleziacim ku danemu odoslanemu vysledku
