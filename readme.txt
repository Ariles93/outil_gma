######################################################################
#                                                                    #
#      Dépendances et Paquets pour l'Application de Gestion Matériel      #
#                                                                    #
######################################################################

Ce fichier résume tous les paquets logiciels et les dépendances nécessaires 
pour faire fonctionner l'application en production sur un serveur Linux 
basé sur Debian ou Ubuntu.


## 1. Paquets Système (à installer avec 'apt')
--------------------------------------------------

Ces paquets constituent la base de l'environnement serveur (LAMP).

- apache2
  Rôle: Serveur web HTTP qui héberge et sert les pages de l'application.

- mariadb-server
  Rôle: Système de gestion de base de données (équivalent à MySQL) pour 
        stocker toutes les données de l'application (matériels, agents, etc.).

- php
  Rôle: Le langage de programmation principal de l'application.

- php-mysql
  Rôle: Extension PHP indispensable pour permettre au code de communiquer 
        avec la base de données MariaDB/MySQL via l'interface PDO.

- php-mbstring
  Rôle: Extension PHP pour la manipulation avancée des chaînes de caractères 
        multibyte (UTF-8). C'est une bonne pratique de l'avoir.

- composer
  Rôle: Gestionnaire de dépendances pour PHP. Il n'est pas indispensable 
        en production si le dossier 'vendor' est déjà présent, mais il est 
        nécessaire pour l'installation initiale des librairies.

- krb5-user
  Rôle (pour le SSO): Client Kerberos nécessaire pour que le serveur Linux 
                      puisse communiquer avec le domaine Active Directory.

- libapache2-mod-auth-gssapi
  Rôle (pour le SSO): Module Apache qui gère l'authentification des 
                      utilisateurs via le protocole Kerberos/GSSAPI.


## 2. Modules Apache (à activer avec 'a2enmod')
----------------------------------------------------

Ces modules étendent les fonctionnalités d'Apache.

- rewrite
  Rôle: Permet la réécriture d'URL. Utilisé si vous optez pour la redirection 
        HTTPS via le fichier .htaccess.

- ldap & authnz_ldap
  Rôle (pour le SSO): Permettent à Apache de se connecter à Active Directory 
                      pour vérifier l'appartenance d'un utilisateur à un groupe.


## 3. Dépendances PHP (gérées par Composer)
------------------------------------------------

Ces librairies sont utilisées par le code de l'application. Elles sont 
installées dans le dossier 'vendor/'.

- vlucas/phpdotenv
  Rôle: Librairie PHP qui permet de charger les variables d'environnement 
        (comme les identifiants de la base de données) depuis le fichier .env 
        de manière sécurisée, séparant la configuration du code.


## 4. Fichiers de Configuration Clés
---------------------------------------

Ces fichiers ne sont pas des paquets mais sont essentiels au fonctionnement.

- .env
  Rôle: Fichier de configuration à la racine du projet. Contient les secrets 
        qui ne doivent pas être dans le code (identifiants BDD).

- /etc/apache2/sites-available/gestion-materiel.conf
  Rôle: Fichier de configuration d'Apache (Virtual Host) qui définit comment 
        le serveur doit servir le site, son nom de domaine local, etc.

- /etc/krb5.conf
  Rôle (pour le SSO): Fichier de configuration Kerberos qui définit comment 
                      le serveur se connecte au domaine Active Directory.

----------------------------------------------------------------------
Cette liste non exhaustive constitue l'environnement complet nécessaire au déploiement et 
au bon fonctionnement de l'application.

Dompdf version récente pour générer PDF en PHP
