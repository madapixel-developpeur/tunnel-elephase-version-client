INSERT INTO `user`
(id, nom_utilisateur, roles, password, nom, prenom, mail, adresse, num_tel, image, date_inscription, status)
VALUES(1, 'admin', '["ROLE_ADMIN"]', '$2y$13$mVhwTYXhXc2fU91HQakUAOjfiUg8mP7EmCLBXVf6H946TgYo1PxMC', 'Jean', 'Paul', 'admin@gmail.com', NULL, NULL, NULL, '2022-05-27 08:00:57', 2);
INSERT INTO `user`
(id, nom_utilisateur, roles, password, nom, prenom, mail, adresse, num_tel, image, date_inscription, status)
VALUES(2, 'client', '["ROLE_CLIENT"]', '$2y$13$mVhwTYXhXc2fU91HQakUAOjfiUg8mP7EmCLBXVf6H946TgYo1PxMC', 'Rakotoarivony', 'Princie', 'princierakotoarivony4@gmail.com', 'IJ6 Anjomakely', '0344252056', NULL, '2022-05-27 08:00:57', 2);
INSERT INTO `user`
(id, nom_utilisateur, roles, password, nom, prenom, mail, adresse, num_tel, image, date_inscription, status)
VALUES(3, 'redacteur', '["ROLE_REDACTEUR"]', '$2y$13$ExUnl3C.9o.rrkGOCaGiO.uvkK8RKzzqjp19wF7YZ0RQ62e6UMeLi', 'Rakotoarivony', 'Eudoxie', 'eudoxierakoto4@gmail.com', 'IJ6 Anjomakely', '0346827195', NULL, '2022-11-14 12:57:51', 2);


INSERT INTO coffret
(id, nom, description, prix, image, statut)
VALUES(1, 'Coffret', NULL, 89.00, 'coffret/coffret.png', 1);

## Tva
INSERT INTO config
(id, nom, val, num, statut)
VALUES(1, 'TVA', 20.0, 1, 1);
