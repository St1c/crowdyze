
INSERT INTO `task` (`owner`, `token`, `title`, `description`, `salary`, `budget_type`, `workers`,
`deadline`, `promotion`) 
VALUES (2, 'p7y0opay', 'Navrh dizajnu landing page firemnej stranky www.jrk.sk', 'Jedna sa o web page firmy JRK Waste Management. Bol niekolko rokov neaktualizovany a hladame novu tvar nasej home page. Mozna konzultacia formou skype rozhovoru.<br/><br/>Co od workera chces ako proof, ze bol job ukonceny:<br/><br/>.jpg subor s navrhom', '100', 1, '10', '2014-03-31', NULL);
INSERT INTO `tag` (`tag`) 
VALUES ('web');
INSERT INTO `task_has_tag` (`tag_id`, `task_id`) 
VALUES (16, 101);
INSERT INTO `task_has_tag` (`tag_id`, `task_id`) 
VALUES (1, 101);
INSERT INTO `tag` (`tag`) 
VALUES ('logo');
INSERT INTO `task_has_tag` (`tag_id`, `task_id`) 
VALUES (17, 101);
INSERT INTO `budget` (`user_id`, `fee`, `budget`, `commission`, `promotion`, `task_id`) 
VALUES (2, 0.5, 100, 5, 0, 101);
INSERT INTO `income` (`from`, `type`, `amount`) 
VALUES (101, 1, 0.5);

INSERT INTO `task` (`owner`, `token`, `title`, `description`, `salary`, `budget_type`, `workers`,
`deadline`, `promotion`) 
VALUES (2, 'e3fht0de', 'Navrh noveho produktu', 'Po rokoch obchodovania s kompostermi sa chystame zaradit do vyroby vlastny typ kompostera. Mame predstavu ake poziadavky by mal splnat, Potrebujeme ich pretavit do navrhu designu a nasledne do technickej podoby, podla ktorej je mozne vyrobit formu na lisovanie vo vyrobe. Nutna konzultacia s majitelmi nasej firmy pred zacatim prace.<br/><br/>Co od workera chces ako proof, ze bol job ukonceny:<br/><br/> doc subor', '100', 2, '100', '2014-04-18', NULL);
INSERT INTO `tag` (`id`,`tag`) 
VALUES (18,'produkt');
INSERT INTO `task_has_tag` (`tag_id`, `task_id`) 
VALUES (18, 102);
INSERT INTO `task_has_tag` (`tag_id`, `task_id`) 
VALUES (1, 102);
INSERT INTO `tag` (`id`,`tag`) 
VALUES (19,'innovation');
INSERT INTO `task_has_tag` (`tag_id`, `task_id`) 
VALUES (19, 102);
INSERT INTO `budget` (`user_id`, `fee`, `budget`, `commission`, `promotion`, `task_id`) 
VALUES (2, 0.5, 1000, 50, 0, 102);
INSERT INTO `income` (`from`, `type`, `amount`) 
VALUES (102, 1, 0.5);

INSERT INTO `task` (`owner`, `token`, `title`, `description`, `salary`, `budget_type`, `workers`,
`deadline`, `promotion`) 
VALUES (2, 'me7bqcr1', 'Vyhotovenie zoznamu prebiehajucich verejnych sutazi zameranych na nakup komposterov v UK.', 'V UK prebieha rocne velke mnozstvo verejnych sutazi zameranych na nakup komposterov. Nasa firma sa ich chce zucastnit a preto potrebujeme zoznam aktualne prebiehajucich.<br/><br/>Co od workera chces ako proof, ze bol job ukonceny:<br/><br/>Zoznam .xls s odkazmi na prebiehajuce verejne sutaze.', '50', 1, '1', '2014-04-30', NULL);
INSERT INTO `task_has_tag` (`tag_id`, `task_id`) 
VALUES (2, 103);
INSERT INTO `task_has_tag` (`tag_id`, `task_id`) 
VALUES (12, 103);
INSERT INTO `budget` (`user_id`, `fee`, `budget`, `commission`, `promotion`, `task_id`) 
VALUES (2, 0.5, 50, 2.5, 0, 103);
INSERT INTO `income` (`from`, `type`, `amount`) 
VALUES (103, 1, 0.5);

INSERT INTO `task` (`owner`, `token`, `title`, `description`, `salary`, `budget_type`, `workers`,
`deadline`, `promotion`) 
VALUES (2, 'p819iuih', 'Preklad marketingovych materialov do francuzstiny', 'Nasa firma sa ucastni obchodnych jednani s francuzskymi partnermi, potrebujeme prelozit potrebne dokumenty zo slovenciny do francuzstiny<br/><br/>Co od workera chces ako proof, ze bol job ukonceny:<br/><br/>prelozene texty vo formate .doc', '100', 1, '3', '2014-05-31', NULL);
INSERT INTO `task_has_tag` (`tag_id`, `task_id`) 
VALUES (9, 104);
INSERT INTO `budget` (`user_id`, `fee`, `budget`, `commission`, `promotion`, `task_id`) 
VALUES (2, 0.5, 100, 5, 0, 104);
INSERT INTO `income` (`from`, `type`, `amount`) 
VALUES (104, 1, 0.5);


INSERT INTO `task` (`owner`, `token`, `title`, `description`, `salary`, `budget_type`, `workers`,
`deadline`, `promotion`) 
VALUES (2, 'w1zvrcnw', 'Preklad marketingovych materialov do srbstin', 'Nasa firma sa ucastni obchodnych jednani s francuzskymi partnermi, potrebujeme prelozit potrebne dokumenty zo slovenciny do srbstiny<br/><br/>Co od workera chces ako proof, ze bol job ukonceny:<br/><br/>prelozene texty vo formate .doc', '100', 1, '3', '2014-05-31', NULL);
INSERT INTO `task_has_tag` (`tag_id`, `task_id`) 
VALUES (9, 105);
INSERT INTO `budget` (`user_id`, `fee`, `budget`, `commission`, `promotion`, `task_id`) 
VALUES (2, 0.5, 100, 5, 0, 105);
INSERT INTO `income` (`from`, `type`, `amount`) 
VALUES (105, 1, 0.5);

-- With promotions

INSERT INTO `task` (`owner`, `token`, `title`, `description`, `salary`, `budget_type`, `workers`,
`deadline`, `promotion`) 
VALUES (2, 'p7y0obay', 'Navrh dizajnu landing page firemnej stranky www.jrk.sk', 'Jedna sa o web page firmy JRK Waste Management. Bol niekolko rokov neaktualizovany a hladame novu tvar nasej home page. Mozna konzultacia formou skype rozhovoru.<br/><br/>Co od workera chces ako proof, ze bol job ukonceny:<br/><br/>.jpg subor s navrhom', '100', 1, '10', '2014-03-31', 1);
INSERT INTO `task_has_tag` (`tag_id`, `task_id`) 
VALUES (16, 106);
INSERT INTO `task_has_tag` (`tag_id`, `task_id`) 
VALUES (1, 106);
INSERT INTO `task_has_tag` (`tag_id`, `task_id`) 
VALUES (17, 106);
INSERT INTO `budget` (`user_id`, `fee`, `budget`, `commission`, `promotion`, `task_id`) 
VALUES (2, 0.5, 100, 5, 0, 106);
INSERT INTO `income` (`from`, `type`, `amount`) 
VALUES (106, 1, 0.5);

INSERT INTO `task` (`owner`, `token`, `title`, `description`, `salary`, `budget_type`, `workers`,
`deadline`, `promotion`) 
VALUES (2, 'e3fhtbde', 'Navrh noveho produktu', 'Po rokoch obchodovania s kompostermi sa chystame zaradit do vyroby vlastny typ kompostera. Mame predstavu ake poziadavky by mal splnat, Potrebujeme ich pretavit do navrhu designu a nasledne do technickej podoby, podla ktorej je mozne vyrobit formu na lisovanie vo vyrobe. Nutna konzultacia s majitelmi nasej firmy pred zacatim prace.<br/><br/>Co od workera chces ako proof, ze bol job ukonceny:<br/><br/> doc subor', '100', 2, '100', '2014-04-18', 2);
INSERT INTO `task_has_tag` (`tag_id`, `task_id`) 
VALUES (18, 107);
INSERT INTO `task_has_tag` (`tag_id`, `task_id`) 
VALUES (1, 107);
INSERT INTO `task_has_tag` (`tag_id`, `task_id`) 
VALUES (19, 107);
INSERT INTO `budget` (`user_id`, `fee`, `budget`, `commission`, `promotion`, `task_id`) 
VALUES (2, 0.5, 1000, 50, 0, 107);
INSERT INTO `income` (`from`, `type`, `amount`) 
VALUES (107, 1, 0.5);

INSERT INTO `task` (`owner`, `token`, `title`, `description`, `salary`, `budget_type`, `workers`,
`deadline`, `promotion`) 
VALUES (2, 'me7bqbr1', 'Vyhotovenie zoznamu prebiehajucich verejnych sutazi zameranych na nakup komposterov v UK.', 'V UK prebieha rocne velke mnozstvo verejnych sutazi zameranych na nakup komposterov. Nasa firma sa ich chce zucastnit a preto potrebujeme zoznam aktualne prebiehajucich.<br/><br/>Co od workera chces ako proof, ze bol job ukonceny:<br/><br/>Zoznam .xls s odkazmi na prebiehajuce verejne sutaze.', '50', 1, '1', '2014-04-30', 3);
INSERT INTO `task_has_tag` (`tag_id`, `task_id`) 
VALUES (2, 108);
INSERT INTO `task_has_tag` (`tag_id`, `task_id`) 
VALUES (12, 108);
INSERT INTO `budget` (`user_id`, `fee`, `budget`, `commission`, `promotion`, `task_id`) 
VALUES (2, 0.5, 50, 2.5, 0, 108);
INSERT INTO `income` (`from`, `type`, `amount`) 
VALUES (108, 1, 0.5);

INSERT INTO `task` (`owner`, `token`, `title`, `description`, `salary`, `budget_type`, `workers`,
`deadline`, `promotion`) 
VALUES (2, 'p819ibih', 'Preklad marketingovych materialov do francuzstiny', 'Nasa firma sa ucastni obchodnych jednani s francuzskymi partnermi, potrebujeme prelozit potrebne dokumenty zo slovenciny do francuzstiny<br/><br/>Co od workera chces ako proof, ze bol job ukonceny:<br/><br/>prelozene texty vo formate .doc', '100', 1, '3', '2014-05-31', 1);
INSERT INTO `task_has_tag` (`tag_id`, `task_id`) 
VALUES (9, 109);
INSERT INTO `budget` (`user_id`, `fee`, `budget`, `commission`, `promotion`, `task_id`) 
VALUES (2, 0.5, 100, 5, 0, 109);
INSERT INTO `income` (`from`, `type`, `amount`) 
VALUES (109, 1, 0.5);


INSERT INTO `task` (`owner`, `token`, `title`, `description`, `salary`, `budget_type`, `workers`,
`deadline`, `promotion`) 
VALUES (2, 'w1zvrbnw', 'Preklad marketingovych materialov do srbstin', 'Nasa firma sa ucastni obchodnych jednani s francuzskymi partnermi, potrebujeme prelozit potrebne dokumenty zo slovenciny do srbstiny<br/><br/>Co od workera chces ako proof, ze bol job ukonceny:<br/><br/>prelozene texty vo formate .doc', '100', 1, '3', '2014-05-31', 2);
INSERT INTO `task_has_tag` (`tag_id`, `task_id`) 
VALUES (9, 110);
INSERT INTO `budget` (`user_id`, `fee`, `budget`, `commission`, `promotion`, `task_id`) 
VALUES (2, 0.5, 100, 5, 0, 110);
INSERT INTO `income` (`from`, `type`, `amount`) 
VALUES (110, 1, 0.5);


-- With different promotions

INSERT INTO `task` (`owner`, `token`, `title`, `description`, `salary`, `budget_type`, `workers`,
`deadline`, `promotion`) 
VALUES (2, 'p7y0oaay', 'Navrh dizajnu landing page firemnej stranky www.jrk.sk', 'Jedna sa o web page firmy JRK Waste Management. Bol niekolko rokov neaktualizovany a hladame novu tvar nasej home page. Mozna konzultacia formou skype rozhovoru.<br/><br/>Co od workera chces ako proof, ze bol job ukonceny:<br/><br/>.jpg subor s navrhom', '100', 1, '10', '2014-03-31', 2);
INSERT INTO `task_has_tag` (`tag_id`, `task_id`) 
VALUES (16, 111);
INSERT INTO `task_has_tag` (`tag_id`, `task_id`) 
VALUES (1, 111);
INSERT INTO `task_has_tag` (`tag_id`, `task_id`) 
VALUES (17, 111);
INSERT INTO `budget` (`user_id`, `fee`, `budget`, `commission`, `promotion`, `task_id`) 
VALUES (2, 0.5, 100, 5, 0, 111);
INSERT INTO `income` (`from`, `type`, `amount`) 
VALUES (111, 1, 0.5);

INSERT INTO `task` (`owner`, `token`, `title`, `description`, `salary`, `budget_type`, `workers`,
`deadline`, `promotion`) 
VALUES (2, 'e3fht0ae', 'Navrh noveho produktu', 'Po rokoch obchodovania s kompostermi sa chystame zaradit do vyroby vlastny typ kompostera. Mame predstavu ake poziadavky by mal splnat, Potrebujeme ich pretavit do navrhu designu a nasledne do technickej podoby, podla ktorej je mozne vyrobit formu na lisovanie vo vyrobe. Nutna konzultacia s majitelmi nasej firmy pred zacatim prace.<br/><br/>Co od workera chces ako proof, ze bol job ukonceny:<br/><br/> doc subor', '100', 2, '100', '2014-04-18', 3);
INSERT INTO `task_has_tag` (`tag_id`, `task_id`) 
VALUES (18, 112);
INSERT INTO `task_has_tag` (`tag_id`, `task_id`) 
VALUES (1, 112);
INSERT INTO `task_has_tag` (`tag_id`, `task_id`) 
VALUES (19, 112);
INSERT INTO `budget` (`user_id`, `fee`, `budget`, `commission`, `promotion`, `task_id`) 
VALUES (2, 0.5, 1000, 50, 0, 112);
INSERT INTO `income` (`from`, `type`, `amount`) 
VALUES (112, 1, 0.5);

INSERT INTO `task` (`owner`, `token`, `title`, `description`, `salary`, `budget_type`, `workers`,
`deadline`, `promotion`) 
VALUES (2, 'me7aqcr1', 'Vyhotovenie zoznamu prebiehajucich verejnych sutazi zameranych na nakup komposterov v UK.', 'V UK prebieha rocne velke mnozstvo verejnych sutazi zameranych na nakup komposterov. Nasa firma sa ich chce zucastnit a preto potrebujeme zoznam aktualne prebiehajucich.<br/><br/>Co od workera chces ako proof, ze bol job ukonceny:<br/><br/>Zoznam .xls s odkazmi na prebiehajuce verejne sutaze.', '50', 1, '1', '2014-04-30', 1);
INSERT INTO `task_has_tag` (`tag_id`, `task_id`) 
VALUES (2, 113);
INSERT INTO `task_has_tag` (`tag_id`, `task_id`) 
VALUES (12, 113);
INSERT INTO `budget` (`user_id`, `fee`, `budget`, `commission`, `promotion`, `task_id`) 
VALUES (2, 0.5, 50, 2.5, 0, 113);
INSERT INTO `income` (`from`, `type`, `amount`) 
VALUES (113, 1, 0.5);

INSERT INTO `task` (`owner`, `token`, `title`, `description`, `salary`, `budget_type`, `workers`,
`deadline`, `promotion`) 
VALUES (2, 'p81aiuih', 'Preklad marketingovych materialov do francuzstiny', 'Nasa firma sa ucastni obchodnych jednani s francuzskymi partnermi, potrebujeme prelozit potrebne dokumenty zo slovenciny do francuzstiny<br/><br/>Co od workera chces ako proof, ze bol job ukonceny:<br/><br/>prelozene texty vo formate .doc', '100', 1, '3', '2014-05-31', 2);
INSERT INTO `task_has_tag` (`tag_id`, `task_id`) 
VALUES (9, 114);
INSERT INTO `budget` (`user_id`, `fee`, `budget`, `commission`, `promotion`, `task_id`) 
VALUES (2, 0.5, 100, 5, 0, 114);
INSERT INTO `income` (`from`, `type`, `amount`) 
VALUES (114, 1, 0.5);


INSERT INTO `task` (`owner`, `token`, `title`, `description`, `salary`, `budget_type`, `workers`,
`deadline`, `promotion`) 
VALUES (2, 'w1zvranw', 'Preklad marketingovych materialov do srbstin', 'Nasa firma sa ucastni obchodnych jednani s francuzskymi partnermi, potrebujeme prelozit potrebne dokumenty zo slovenciny do srbstiny<br/><br/>Co od workera chces ako proof, ze bol job ukonceny:<br/><br/>prelozene texty vo formate .doc', '100', 1, '3', '2014-05-31', 3);
INSERT INTO `task_has_tag` (`tag_id`, `task_id`) 
VALUES (9, 115);
INSERT INTO `budget` (`user_id`, `fee`, `budget`, `commission`, `promotion`, `task_id`) 
VALUES (2, 0.5, 100, 5, 0, 115);
INSERT INTO `income` (`from`, `type`, `amount`) 
VALUES (115, 1, 0.5);
