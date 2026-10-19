# V68 - Atelier complet d'un chapitre

- nouvel ecran centralise `/admin/chapitre.php` avec onglets Informations, Documents, Exercices, Videos, QCM, Flashcards et Savoirs ;
- creation, modification, publication et suppression des ressources sans quitter le chapitre ;
- gestion des questions et choix d'un QCM ainsi que des cartes recto/verso dans le meme ecran ;
- controles de securite pour empecher la modification d'une ressource appartenant a un autre chapitre ;
- alerte avant de quitter un formulaire contenant des changements non enregistres ;
- nettoyage des regles CSS contradictoires et harmonisation des bandeaux selon la couleur du niveau ;
- formulaires, titres, cases a cocher et cartes d'administration rendus plus compacts et coherents ;
- tableau des documents fiabilise sur mobile avec tri, filtres, pagination et compteur ;
- synchronisation du mode sombre entre le site public et l'administration ;
- correction du redemarrage des flashcards et du rendu LaTeX au verso ;
- mise a jour du cache hors ligne pour les nouveaux scripts.

Aucune migration SQL supplementaire n'est requise apres la V65.
