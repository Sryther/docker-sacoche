<?php
/**
 * @version $Id$
 * @author Thomas Crespin <thomas.crespin@sesamath.net>
 * @copyright Thomas Crespin 2009-2015
 * 
 * ****************************************************************************************************
 * SACoche <http://sacoche.sesamath.net> - Suivi d'Acquisitions de Compétences
 * © Thomas Crespin pour Sésamath <http://www.sesamath.net> - Tous droits réservés.
 * Logiciel placé sous la licence libre Affero GPL 3 <https://www.gnu.org/licenses/agpl-3.0.html>.
 * ****************************************************************************************************
 * 
 * Ce fichier est une partie de SACoche.
 * 
 * SACoche est un logiciel libre ; vous pouvez le redistribuer ou le modifier suivant les termes 
 * de la “GNU Affero General Public License” telle que publiée par la Free Software Foundation :
 * soit la version 3 de cette licence, soit (à votre gré) toute version ultérieure.
 * 
 * SACoche est distribué dans l’espoir qu’il vous sera utile, mais SANS AUCUNE GARANTIE :
 * sans même la garantie implicite de COMMERCIALISABILITÉ ni d’ADÉQUATION À UN OBJECTIF PARTICULIER.
 * Consultez la Licence Publique Générale GNU Affero pour plus de détails.
 * 
 * Vous devriez avoir reçu une copie de la Licence Publique Générale GNU Affero avec SACoche ;
 * si ce n’est pas le cas, consultez : <http://www.gnu.org/licenses/>.
 * 
 */

class cURL
{

  // ////////////////////////////////////////////////////////////////////////////////////////////////////
  // Attributs de la classe (équivalents des "variables")
  // ////////////////////////////////////////////////////////////////////////////////////////////////////

  private $handle    = '';
  private $url       = '';
  private $timeout   = 10;
  private $maxredirs = 3;
  private $tab_post  = array();

  // //////////////////////////////////////////////////
  // Méthodes internes (privées)
  // //////////////////////////////////////////////////

  /**
  * Méthode Magique - Constructeur
  */
  private function __construct( $url , $tab_post , $timeout , $maxredirs=3 )
  {
    $this->handle    = curl_init();
    $this->url       = $url;
    $this->tab_post  = $tab_post;
    $this->timeout   = $timeout;
    $this->maxredirs = $maxredirs;
  }

  /**
   * Options cURL communes à tous les appels
   *
   * @param void
   * @return void
   */
  private function setopt_commun()
  {
    curl_setopt($this->handle, CURLOPT_DNS_CACHE_TIMEOUT, 3600); // Le temps en seconde que cURL doit conserver les entrées DNS en mémoire. Cette option est définie à 120 secondes (2 minutes) par défaut.
    curl_setopt($this->handle, CURLOPT_SSL_VERIFYPEER, FALSE);   // FALSE pour que cURL ne vérifie pas le certificat (sinon, en l'absence de certificat, on récolte l'erreur "SSL certificate problem, verify that the CA cert is OK. Details: error:14090086:SSL routines:SSL3_GET_SERVER_CERTIFICATE:certificate verify failed").
    curl_setopt($this->handle, CURLOPT_SSL_VERIFYHOST, FALSE);   // CURLOPT_SSL_VERIFYHOST doit aussi être positionnée à 1 ou 0 si CURLOPT_SSL_VERIFYPEER est désactivée (par défaut à 2) ; sinon, on peut récolter l'erreur "SSL: certificate subject name 'secure.sesamath.fr' does not match target host name 'sacoche.sesamath.net'", mê si ça a été résolu depuis. (http://fr.php.net/manual/fr/function.curl-setopt.php#75711)
    curl_setopt($this->handle, CURLOPT_FAILONERROR, TRUE);       // TRUE pour que PHP traite silencieusement les codes HTTP supérieurs ou égaux à 400. Le comportement par défaut est de retourner la page normalement, en ignorant ce code.
    curl_setopt($this->handle, CURLOPT_TIMEOUT, $this->timeout); // Le temps maximum d'exécution de la fonction cURL (en s) ; éviter de monter cette valeur pour libérer des ressources plus rapidement : 'classiquement', le serveur doit répondre en qq ms, donc si au bout de 5s il a pas répondu c'est qu'il ne répondra plus, alors pas la peine de bloquer une connexion et de la RAM pendant plus longtemps.
    curl_setopt($this->handle, CURLOPT_RETURNTRANSFER, TRUE);    // TRUE retourne directement le transfert sous forme de chaîne de la valeur retournée par curl_exec() au lieu de l'afficher directement.
    curl_setopt($this->handle, CURLOPT_USERAGENT , CURL_AGENT);  // Envoyer un User-Agent (défini dans le loader) car il est arrivé qu'un prestataire ENT bloque les requêtes sans User-Agent (ce qui est idiot car cette valeur n'est pas fiable, n'importe qui peut présenter n'importe quel User-Agent !).
  }

  /**
   * Options cURL de configuration d'un proxy
   *
   * @param void
   * @return void
   */
  private function setopt_proxy()
  {
    if( (defined('SERVEUR_PROXY_USED')) && (SERVEUR_PROXY_USED) )
    {
      // Serveur qui nécessite d'utiliser un tunnel à travers un proxy HTTP.
      curl_setopt($this->handle, CURLOPT_PROXY,     SERVEUR_PROXY_NAME);           // Le nom du proxy HTTP au tunnel qui le demande.
      curl_setopt($this->handle, CURLOPT_PROXYPORT, (int)SERVEUR_PROXY_PORT);      // Le numéro du port du proxy à utiliser pour la connexion. Ce numéro de port peut également être défini dans l'option CURLOPT_PROXY.
      curl_setopt($this->handle, CURLOPT_PROXYTYPE, constant(SERVEUR_PROXY_TYPE)); // Soit CURLPROXY_HTTP (par défaut), soit CURLPROXY_SOCKS5.
      if(SERVEUR_PROXY_AUTH_USED)
      {
        // Serveur qui nécessite de s'authentifier pour utiliser le proxy.
        curl_setopt($this->handle, CURLOPT_PROXYAUTH,    constant(SERVEUR_PROXY_AUTH_METHOD));                 // La méthode d'identification HTTP à utiliser pour la connexion à un proxy. Utilisez la même méthode que celle décrite dans CURLOPT_HTTPAUTH. Pour une identification avec un proxy, seuls CURLAUTH_BASIC et CURLAUTH_NTLM sont actuellement supportés.
        curl_setopt($this->handle, CURLOPT_PROXYUSERPWD, SERVEUR_PROXY_AUTH_USER.':'.SERVEUR_PROXY_AUTH_PASS); // Un nom d'utilisateur et un mot de passe formatés sous la forme "[username]:[password]" à utiliser pour la connexion avec le proxy.
      }
    }
  }

  /**
   * Options cURL de configuration d'une identification HTTP à utiliser
   *
   * @param void
   * @return void
   */
  private function setopt_auth()
  {
    if( defined('HTTP_AUTH_METHOD') && defined('HTTP_AUTH_PASS') )
    {
      curl_setopt($this->handle, CURLOPT_HTTPAUTH, HTTP_AUTH_METHOD); // La méthode d'identification HTTP à utiliser. Valeurs possibles : CURLAUTH_BASIC, CURLAUTH_DIGEST, CURLAUTH_GSSNEGOTIATE, CURLAUTH_NTLM, CURLAUTH_ANY et CURLAUTH_ANYSAFE.
      curl_setopt($this->handle, CURLOPT_USERPWD , HTTP_AUTH_PASS );  // Un nom d'utilisateur et un mot de passe formatés sous la forme "[username]:[password]" à utiliser pour la connexion.
    }
  }

  /**
   * Options cURL d'envoi de données en POST.
   * À appeler une fois l'URL bien définie ou après chaque redirection.
   *
   * @param void
   * @return void
   */
  private function setopt_post()
  {
    if( is_array($this->tab_post) && (strpos($this->url,'.zip')===FALSE) && (strpos($this->url,'.xml')===FALSE) && (strpos($this->url,'.txt')===FALSE) )
    {
      curl_setopt($this->handle, CURLOPT_POST,       TRUE);             // TRUE pour que PHP fasse un HTTP POST. Un POST est un encodage normal application/x-www-from-urlencoded, utilisé couramment par les formulaires HTML. 
      curl_setopt($this->handle, CURLOPT_POSTFIELDS, $this->tab_post);  // Toutes les données à passer lors d'une opération de HTTP POST. Peut être passé sous la forme d'une chaîne encodée URL, comme 'para1=val1&para2=val2&...' ou sous la forme d'un tableau dont le nom du champ est la clé, et les données du champ la valeur. Si le paramètre value est un tableau, l'en-tête Content-Type sera définie à multipart/form-data. 
      curl_setopt($this->handle, CURLOPT_HTTPHEADER, array('Expect:')); // Eviter certaines erreurs cURL 417 ; voir explication http://fr.php.net/manual/fr/function.curl-setopt.php#82418 ou http://www.gnegg.ch/2007/02/the-return-of-except-100-continue/
    }
    else
    {
      curl_setopt($this->handle, CURLOPT_POST, FALSE);                  // Si pas de données à poster, mieux vaut forcer un appel en GET, sinon ça peut poser pb. http://fr.php.net/manual/fr/function.curl-setopt.php#104387
    }
  }

  /**
   * Indique qu'il faut suivre les redirections, sauf si "safe_mode" ou "open_basedir", auquel cas on cherche "manuellement" l'adresse finale avec la méthode new_url().
   *
   * @param void
   * @return string  $new_url
   */
  private function setopt_redirection()
{
  curl_setopt($this->handle, CURLOPT_HEADER, FALSE);                 // FALSE pour ne pas inclure l'en-tête dans la valeur de retour.
  if( (!ini_get('safe_mode')) && (!ini_get('open_basedir')) )
  {
    // Option CURLOPT_FOLLOWLOCATION sous conditions car certaines installations renvoient "CURLOPT_FOLLOWLOCATION cannot be activated when in safe_mode or an open_basedir is set" (http://www.php.net/manual/fr/features.safe-mode.functions.php#92192)
    curl_setopt($this->handle, CURLOPT_FOLLOWLOCATION, TRUE);        // TRUE pour suivre toutes les en-têtes "Location: " que le serveur envoie dans les en-têtes HTTP (notez que cette fonction est récursive et que PHP suivra toutes les en-têtes "Location: " qu'il trouvera à moins que CURLOPT_MAXREDIRS ne soit définie).
    curl_setopt($this->handle, CURLOPT_MAXREDIRS, $this->maxredirs); // Le nombre maximal de redirections HTTP à suivre. Utilisez cette option avec l'option CURLOPT_FOLLOWLOCATION.
    return $this->url;
  }
  else
  {
    // Solution de remplacement inspirée de http://fr.php.net/manual/fr/function.curl-setopt.php#102121
    curl_setopt($this->handle, CURLOPT_FOLLOWLOCATION, FALSE);
    return cURL::new_url();
  }
}

  /**
   * Execute cURL avec l'URL à appeler (les options doivent déjà avoir été définies).
   *
   * @param void
   * @return string
   */
  private function exec()
  {
    curl_setopt( $this->handle , CURLOPT_URL , $this->url );
    return curl_exec($this->handle);
  }

  /**
   * Recherche "manuelle" de l'adresse finale si on ne peut pas utiliser l'option CURLOPT_FOLLOWLOCATION.
   *
   * @param void
   * @return string  $new_url
   */
  private function new_url()
  {
    $cURL_new = new cURL( $this->url , $this->tab_post , $this->timeout );
    curl_setopt($cURL_new->handle, CURLOPT_HEADER, TRUE);
    curl_setopt($cURL_new->handle, CURLOPT_NOBODY, TRUE); // A invoquer avant cURL::setopt_post()
    curl_setopt($cURL_new->handle, CURLOPT_FORBID_REUSE, FALSE);
    $cURL_new->setopt_commun();
    $cURL_new->setopt_proxy();
    do
    {
      $cURL_new->setopt_post(); // dans la boucle car $tab_post n'est envoyé que si fichier php (sinon erreur 405 "Method Not Allowed")
      $header = $cURL_new->exec();
      if (curl_errno($cURL_new->handle))
      {
        $code = 0;
      }
      else
      {
        $code = curl_getinfo($cURL_new->handle, CURLINFO_HTTP_CODE);
        if ($code == 301 || $code == 302)
        {
          preg_match('/Location:(.*?)\n/', $header, $matches);
          $newurl = trim(array_pop($matches));
          // Pb : l'URL peut être relative, et si on perd le domaine alors après ça plante
          if( (substr($newurl,0,4)!='http') && (substr($newurl,0,3)!='ftp') )
          {
            $pos_last_slash = strrpos($cURL_new->url,'/');
            $newurl_debut = ($pos_last_slash>7) ? substr($cURL_new->url,0,$pos_last_slash+1) : $cURL_new->url.'/' ;
            $newurl_fin   = ($newurl{0}=='/')   ? substr($newurl,1)                          : $newurl            ;
            $newurl = $newurl_debut.$newurl_fin;
          }
          $cURL_new->url = $newurl;
        }
        else
        {
          $code = 0;
        }
      }
    }
    while ($code && --$cURL_new->maxredirs);
    curl_close($cURL_new->handle);
    return $cURL_new->url;
  }

  // //////////////////////////////////////////////////
  // Méthode publique
  // //////////////////////////////////////////////////

  /**
   * Équivalent de file_get_contents pour récupérer un fichier sur un serveur distant.
   * Méthode déclarée comme statique ("static") afin de pouvoir y accéder sans avoir besoin d'instancier la classe : cURL::get_contents(...)
   * 
   * On peut aussi l'utiliser pour récupérer le résultat d'un script PHP exécuté sur un serveur distant.
   * On peut alors envoyer au script des paramètres en POST.
   * 
   * On n'utilise pas file_get_contents() car certains serveurs n'acceptent pas d'utiliser une URL comme nom de fichier (gestionnaire fopen non activé).
   * On utilise donc la bibliothèque cURL en remplacement.
   * 
   * Concernant le timeout.
   * La fonction set_time_limit(), tout comme la directive de configuration de php.ini max_execution_time, n'affectent que le temps d'exécution du script lui-même. Tout temps passé en dehors du script, comme un appel système utilisant system(), des opérations sur les flux, les requêtes sur base de données, etc. n'est pas pris en compte lors du calcul de la durée maximale d'exécution du script.
   * Un appel cURL est un exemple d'opération de flux et n'est donc pas limité parun max_execution_time.
   * Du point du vue de l'administrateur système, un timeout cURL élevé n'est pas un souci : une connexion ouverte sans trafic dessus, tant qu'il n'y en a pas des milliers, c'est pas important.
   * Le timeout cURL sert juste à fixer "à partir de X secondes je n'attends plus et j'annonce que ça a planté", donc avec un timeout cURL élevé l'utilisateur risque juste de poireauter davantage avant de se prendre une erreur.
   * Le timeout cURL sert aussi à ne pas laisser de connexion ouverte indéfiniment.
   * 
   * @param string $url
   * @param array  $tab_post   tableau[nom]=>valeur de données à envoyer en POST (facultatif)
   * @param int    $timeout    valeur du timeout en s ; facultatif, par défaut 10
   * @return string
   */
  public static function get_contents( $url , $tab_post=FALSE , $timeout=10 )
  {
    $cURL = new cURL( $url , $tab_post , $timeout );
    $cURL->url = $cURL->setopt_redirection();
    $cURL->setopt_commun();
    $cURL->setopt_proxy();
    $cURL->setopt_auth();
    $cURL->setopt_post();
    $requete_reponse = $cURL->exec();
    if($requete_reponse === FALSE)
    {
      $requete_reponse = 'Erreur : '.curl_error($cURL->handle);
    }
    curl_close($cURL->handle);
    return $requete_reponse;
  }

}
?>