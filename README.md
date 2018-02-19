# Docker SACoche

## Prérequis

1. Docker ([comment installer](https://docs.docker.com/install/))
2. Docker Compose ([comment installer](https://docs.docker.com/compose/install/))

## A propos de SACoche

Logiciel libre et gratuit de suivi d'acquisition de compétences, pour évaluer par compétences et positionner sur le socle commun.

Site web officiel de SACoche : https://sacoche.sesamath.net/

Le code de SACoche est placé sous la [licence libre AGPL 3](https://www.gnu.org/licenses/agpl-3.0.html).

Ce code ne m'appartient pas.

## Docker

Pour lancer un serveur SACoche avec Docker :

1. Copiez le fichier de template `docker-compose.yml.template` en `docker-compose.yml`

```bash
cp docker-compose.yml.template docker-compose.yml
```

2. Editez le fichier `docker-compose.yml` avec votre éditeur (pour l'exemple, `nano` sous Linux)

```bash
nano docker-compose.yml
```

3. Modifiez au besoin la configuration docker-compose en suivant la [documentation Docker Compose](https://docs.docker.com/compose/)

4. Lancez la commande suivante pour construire l'image Docker :

```bash
docker-compose build
```

5. Lancez la commande suivante pour lancer SACoche :

```bash
docker-compose up -d
```

6. Lancez votre navigateur et allez sur l'URL où le serveur écoute (dans l'exemple, `localhost` sur le port `80`) : `http://localhost:80`

7. Suivez les instructions du site

## Licence

Licence libre GNU AGPL 3
