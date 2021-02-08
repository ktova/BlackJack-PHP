# BlackJack-PHP
PHP OOP BlackJack

*But du jeu*

Après avoir reçu deux cartes, le joueur tire des cartes pour s’approcher de la valeur 21 sans la dépasser. 
Le but du joueur est de battre le croupier en obtenant un total de points supérieur à celui-ci ou en voyant ce dernier dépasser 21. 
Chaque joueur joue contre le croupier, qui représente la banque, ou le casino, et non contre les autres joueurs.
Si un joueur totalise exactement 21, il réalise un "Black Jack"

*Valeurs des cartes au blackjack*

- Chaque carte numérotée de 2 à 10 a sa valeur nominale (égale au numéro sur la carte)
- Les valets, les dames et les rois (les figures) ont une valeur de 10 points
- L’As vaut 11 points

*La table de Blackjack*

Le sabot (ou deck) contient en général 6 jeux de cartes, ainsi le sabot contient 24 versions de chaque carte (4 couleurs par carte dans chaque paquet).
On considère dans notre exercice que :
1. le sabot contient 1 seul jeu de 52 cartes, 
2. le joueur dispose de 20.-dans la cagnotte
3. le joueur joue seul face à l'ordinateur

*Déroulement d'une partie*

1. L'utilisateur entre sa mise (2.-, 5.-, 10.-, 25.-, 50.-, 100.-, 500.-, totalité de sa cagnotte)
2. L'utilisateur reçoit 2 cartes faces visibles, le croupier 1 carte face visible
3. Si l'utilisateur réalise un Black Jack, il récupère immédiatement 1,5 fois sa mise et la manche se termine
4. Tour du joueur, il dispose de 3 possibilités :
1. Il tire une nouvelle carte
2. Il passe son tour
3. Il double sa mise et obtient une nouvelle carte
5. Tour du croupier, il dispose de 2 possibilités :
1. Tant qu'il a moins de 17, il tire une carte
2. S'il a 17 mais avec un As en main, il tire une carte
3. Plus de 17, il passe son tour
6. On continue les étapes 4 et 5 tant que :
1. Personne n'a fait de Black Jack
2. Personne n'a dépassé 21
3. Tout le monde n'a pas passé son tour
*Fin de partie*

1. Si le joueur gagne, il récupère 1,5 fois sa mise
2. Si le croupier gagne, le joueur perd sa mise
3. En cas d'égalité, le joueur récupère sa mise 
